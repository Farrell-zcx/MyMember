import os
import sys
import json
import re
import difflib
from collections import Counter
from concurrent.futures import ThreadPoolExecutor
import pytesseract
from core.config import settings
from services.image_processing import (
    detect_and_warp_ktp_canonical, extract_ktp_rois, generate_roi_variants,
    generate_full_variants,
    assess_image_quality, _analyze_image_quality, NIK_PIPELINES, TEXT_PIPELINES,
    nik_pipe_adaptive_glare, nik_pipe_scanner_contrast,
    nik_pipe_scanner_fix, nik_pipe_gentle, nik_pipe_sharp_adaptive,
    nik_pipe_morph_thick, nik_pipe_morph_thin,
    text_pipe_enhanced, text_pipe_standard, text_pipe_clahe, text_pipe_scanner_fix
)

# Inisialisasi Tesseract path
pytesseract.pytesseract.tesseract_cmd = settings.TESSERACT_CMD_PATH

# ==========================================================
# INISIALISASI ENGINE RAPIDOCR (SINGLETON)
# ==========================================================
rapid_ocr_engine = None
try:
    from rapidocr_onnxruntime import RapidOCR
    rapid_ocr_engine = RapidOCR()
except Exception as e:
    print("Peringatan: RapidOCR belum dapat diinisialisasi, fallback ke Tesseract:", e)

# ==========================================================
# LOAD DATASET KODE WILAYAH KEMENDAGRI (OFFLINE JSON)
# ==========================================================
DATA_FILE = os.path.join(os.path.dirname(__file__), 'data', 'kode_wilayah.json')
KECAMATAN_SET = set()
KABUPATEN_DICT = {}

try:
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, 'r', encoding='utf-8') as f:
            _wdata = json.load(f)
            KECAMATAN_SET = set(_wdata.get('kec', []))
            KABUPATEN_DICT = _wdata.get('kab', {})
except Exception as e:
    print("Gagal memuat kode_wilayah.json:", e)

PROVINSI_VALID = {
    11, 12, 13, 14, 15, 16, 17, 18, 19, 21,
    31, 32, 33, 34, 35, 36,
    51, 52, 53,
    61, 62, 63, 64, 65,
    71, 72, 73, 74, 75, 76,
    81, 82,
    91, 92, 93, 94, 95, 96,
}

CHAR_FIX = {
    'O': '0', 'o': '0', 'Q': '0', 'D': '0',
    'I': '1', 'l': '1', 'i': '1', '|': '1', '!': '1', 'L': '1',
    'Z': '2', 'z': '2',
    'S': '5', 's': '5',
    'G': '6', 'b': '6',
    'T': '7',
    'B': '8',
    'g': '9', 'q': '9',
}

CHAR_FIX_DOB = dict(CHAR_FIX)
CHAR_FIX_DOB.update({
    'n': '0', 'u': '0', 'A': '4', 'a': '4'
})

PROVINSI_NAME_TO_CODE = {
    'ACEH': '11', 'SUMATERA UTARA': '12', 'SUMUT': '12', 'SUMATEBA UTARA': '12',
    'SUMATERA BARAT': '13', 'SUMBAR': '13', 'RIAU': '14', 'JAMBI': '15',
    'SUMATERA SELATAN': '16', 'SUMSEL': '16', 'BENGKULU': '17', 'LAMPUNG': '18',
    'BANGKA BELITUNG': '19', 'BABEL': '19', 'KEP. BANGKA BELITUNG': '19',
    'KEPULAUAN RIAU': '21', 'KEPRI': '21', 'KEP. RIAU': '21',
    'DKI JAKARTA': '31', 'JAKARTA': '31', 'JAWA BARAT': '32', 'JABAR': '32',
    'JAWA TENGAH': '33', 'JATENG': '33', 'YOGYAKARTA': '34', 'DIY': '34',
    'DI YOGYAKARTA': '34', 'D.I. YOGYAKARTA': '34', 'JAWA TIMUR': '35', 'JATIM': '35',
    'BANTEN': '36', 'BALI': '51', 'NUSA TENGGARA BARAT': '52', 'NTB': '52',
    'NUSA TENGGARA TIMUR': '53', 'NTT': '53', 'KALIMANTAN BARAT': '61', 'KALBAR': '61',
    'KALIMANTAN TENGAH': '62', 'KALTENG': '62', 'KALIMANTAN SELATAN': '63', 'KALSEL': '63',
    'KALIMANTAN TIMUR': '64', 'KALTIM': '64', 'KALIMANTAN UTARA': '65', 'KALTARA': '65',
    'SULAWESI UTARA': '71', 'SULUT': '71', 'SULAWESI TENGAH': '72', 'SULTENG': '72',
    'SULAWESI SELATAN': '73', 'SULSEL': '73', 'SULAWESI TENGGARA': '74', 'SULTRA': '74',
    'GORONTALO': '75', 'SULAWESI BARAT': '76', 'SULBAR': '76', 'MALUKU': '81',
    'MALUKU UTARA': '82', 'PAPUA': '91', 'PAPUA BARAT': '92', 'PAPUA SELATAN': '93',
    'PAPUA TENGAH': '94', 'PAPUA PEGUNUNGAN': '95', 'PAPUA BARAT DAYA': '96',
}

# ==========================================================
# VALIDATOR & RECONSTRUCTOR HELPER
# ==========================================================

def _is_valid_nik(nik):
    if not nik or len(nik) != 16 or not nik.isdigit():
        return False
    provinsi = int(nik[0:2])
    if provinsi not in PROVINSI_VALID:
        return False
    hari = int(nik[6:8])
    bulan = int(nik[8:10])
    if not ((1 <= hari <= 31) or (41 <= hari <= 71)):
        return False
    if not (1 <= bulan <= 12):
        return False
    if len(set(nik)) <= 2:
        return False
    return True

def _is_plausible_nik(nik):
    if not nik or len(nik) != 16 or not nik.isdigit():
        return False
    provinsi = int(nik[0:2])
    if provinsi not in PROVINSI_VALID:
        return False
    if len(set(nik)) <= 2:
        return False
    return True

def _find_valid_niks(text, base_score=4.0):
    results = []
    if not text:
        return results
    for line in text.split('\n'):
        fixed_line = "".join(CHAR_FIX.get(c, c) for c in line)
        digits = re.sub(r'[^0-9]', '', fixed_line.strip())
        if len(digits) < 14:
            continue
        if len(digits) == 16:
            if _is_plausible_nik(digits):
                results.append((digits, base_score))
            continue
        if 16 < len(digits) <= 24:
            for start in range(len(digits) - 15):
                candidate = digits[start:start+16]
                if _is_plausible_nik(candidate):
                    results.append((candidate, base_score * 0.8))
    return results

def _ocr_worker(processed_img, config, lang=None):
    try:
        if lang:
            try:
                return pytesseract.image_to_string(processed_img, config=config, lang=lang)
            except Exception:
                return pytesseract.image_to_string(processed_img, config=config)
        return pytesseract.image_to_string(processed_img, config=config)
    except Exception:
        return ""

def _extract_provinsi_code(text):
    if not text:
        return None
    upper = text.upper()
    sorted_names = sorted(PROVINSI_NAME_TO_CODE.keys(), key=len, reverse=True)
    for name in sorted_names:
        if name in upper:
            return PROVINSI_NAME_TO_CODE[name]
    lines = upper.split('\n')
    for line in lines[:5]:
        cleaned = re.sub(r'[^A-Z\s]', '', line).strip()
        if len(cleaned) < 4:
            continue
        for name in sorted_names:
            if difflib.SequenceMatcher(None, name, cleaned).ratio() > 0.8:
                return PROVINSI_NAME_TO_CODE[name]
    return None

def _extract_dob_digits(text, is_female=False):
    if not text:
        return None
    fixed_text = "".join(CHAR_FIX_DOB.get(c, c) for c in text)
    m = re.search(r'(\d{2})\s*[-/\.\s]\s*(\d{2})\s*[-/\.\s]\s*(\d{2,4})', fixed_text)
    if not m:
        return None
    hari = int(m.group(1))
    bulan = m.group(2)
    tahun2 = m.group(3)[-2:]
    if not (1 <= hari <= 31):
        return None
    if not (1 <= int(bulan) <= 12):
        return None
    if is_female:
        hari += 40
    return f"{hari:02d}{bulan}{tahun2}"

# ==========================================================
# RAPIDOCR FIELD EXTRACTORS & STREAM PARSERS
# ==========================================================

def _rapid_extract_text(img, engine=None):
    """Jalankan RapidOCR dan kembalikan list of (text, confidence)"""
    if engine is None or img is None or img.size == 0:
        return []
    try:
        res, _ = engine(img, use_cls=True)
        if not res:
            return []
        items = []
        for line in res:
            txt = line[1].strip()
            score = float(line[2])
            if txt:
                items.append((txt, score))
        return items
    except Exception:
        return []

def _rapid_extract_boxes(img, engine=None):
    """Jalankan RapidOCR dengan Direction Classifier (use_cls=True) untuk orientasi bebas (9:16 dan 16:9)"""
    if engine is None or img is None or img.size == 0:
        return []
    try:
        res, _ = engine(img, use_cls=True)
        if not res:
            return []
        items = []
        for line in res:
            box = line[0]
            txt = line[1].strip()
            score = float(line[2])
            if txt:
                items.append((box, txt, score))
        return items
    except Exception:
        return []

def _clean_nama_candidate(raw_str):
    if not raw_str:
        return None
    # Jika sebagian besar adalah digit (misal NIK atau nomor), abaikan
    digits_count = sum(c.isdigit() for c in raw_str)
    if digits_count >= 4 or (len(raw_str) > 0 and digits_count > len(raw_str) * 0.3):
        return None
    upper = raw_str.upper()
    # Hilangkan prefix label nama & karakter noise
    upper = re.sub(r'^(?:NAMA|NAME|LENGKAP|ALAMAT|TEMPAT|NIK|STATUS)\s*[:;\.\-]?\s*', '', upper)
    upper = re.sub(r'^[:;\.\-\s]+', '', upper)
    # Ganti digit umum yang salah baca di dalam nama
    upper = upper.replace('0', 'O').replace('1', 'I').replace('5', 'S').replace('8', 'B')
    cleaned = re.sub(r'[^A-Z\s\'.\-]', '', upper).strip()
    words = [w for w in cleaned.split() if len(w) >= 1]
    if words:
        result = " ".join(words)
        if len(result) >= 3 and not re.search(r'TEMPAT|LENGKAP|LAHIR|GOL|DARAH|AGAMA|PROVINSI|KOTA|KABUPATEN|WARGA|NEGARA|ISLAM|KRISTEN|KATHOLIK|HINDU|BUDHA|KAWIN', result):
            return result
    return None

def _parse_ktp_boxes(items):
    """
    Ekstrak metadata KTP langsung dari stream kotak teks RapidOCR:
    Mendeteksi NIK, NAMA, TTL, Jenis Kelamin, dan Provinsi terlepas dari orientasi (9:16 atau 16:9).
    """
    provinsi_code = None
    dob_digits = None
    is_female = False
    nik_candidates = []
    nama_candidates = []
    raw_lines = []

    if not items:
        return {
            'provinsi_code': None, 'dob_digits': None, 'is_female': False,
            'nik_candidates': [], 'nama_candidates': [], 'raw_lines': []
        }

    # 1. Deteksi Provinsi, TTL, dan Gender dari seluruh teks
    for box, txt, score in items:
        raw_lines.append(txt)
        upper = txt.upper()

        # Provinsi
        if not provinsi_code:
            p_code = _extract_provinsi_code(txt)
            if p_code:
                provinsi_code = p_code

        # Gender
        if any(kw in upper for kw in ['PEREMPUAN', 'WANITA', 'FEMALE', 'REMPUAN']):
            is_female = True

        # Tanggal Lahir
        if not dob_digits:
            d_cand = _extract_dob_digits(txt, is_female=is_female)
            if d_cand:
                dob_digits = d_cand

    # Jika dob_digits belum ketemu, scan ulang dengan flag gender final
    if not dob_digits:
        for txt in raw_lines:
            d_cand = _extract_dob_digits(txt, is_female=is_female)
            if d_cand:
                dob_digits = d_cand
                break

    # 2. Deteksi NIK (Bebas dari pengaruh huruf 'I' pada label 'NIK')
    for idx, (box, txt, score) in enumerate(items):
        upper = txt.upper()
        # Bersihkan label prefix seperti "NIK", "No. NIK", "N!K", dll
        stripped = re.sub(r'^(?:NO\.?|NOMOR)?\s*N[I1!l\|]K\s*[:;\.\-]?\s*', '', txt, flags=re.I).strip()
        has_nik_label = bool(re.search(r'\bN[I1!l\|]K\b', upper) or 'NIK' in upper)
        
        # Konversi karakter angka yang sering salah baca
        fixed_line = "".join(CHAR_FIX.get(c, c) for c in stripped)
        digits = re.sub(r'[^0-9]', '', fixed_line)

        base_mult = 20.0 if has_nik_label else 10.0
        if len(digits) == 16:
            if _is_plausible_nik(digits):
                nik_candidates.append((digits, score * base_mult))
        elif 16 < len(digits) <= 24:
            for start in range(len(digits) - 15):
                cand = digits[start:start+16]
                if _is_plausible_nik(cand):
                    nik_candidates.append((cand, score * base_mult * 0.8))
        elif len(digits) >= 14:
            # Kandidat parsial
            for c_txt, _ in _find_valid_niks(txt, base_score=score * 5.0):
                nik_candidates.append((c_txt, score * 5.0))

    # 3. Deteksi NAMA (Heuristik Spasial & Label KTP)
    for idx, (box, txt, score) in enumerate(items):
        upper = txt.upper()

        # Kasus A: Ada label "NAMA" pada baris
        if 'NAMA' in upper:
            after_col = re.sub(r'^.*NAMA\s*[:;\.\-]?\s*', '', txt, flags=re.I).strip()
            c = _clean_nama_candidate(after_col)
            if c:
                nama_candidates.append((c, score * 2.5))
            else:
                # Cek teks pada baris berikutnya jika label Nama di baris terpisah
                if idx + 1 < len(items):
                    next_txt = items[idx + 1][1]
                    c_next = _clean_nama_candidate(next_txt)
                    if c_next:
                        nama_candidates.append((c_next, items[idx + 1][2] * 2.0))

        # Kasus B: Baris tepat di bawah NIK jika label NAMA tertutup pantulan/hologram
        has_nik_label = bool(re.search(r'\bN[I1!l\|]K\b', upper) or 'NIK' in upper or len(re.sub(r'[^0-9]', '', txt)) == 16)
        if has_nik_label and idx + 1 < len(items):
            cand_line = items[idx + 1][1]
            if not any(k in cand_line.upper() for k in ['TEMPAT', 'LAHIR', 'TTL', 'PROVINSI', 'KOTA', 'KABUPATEN', 'NIK', 'GOL']):
                c = _clean_nama_candidate(cand_line)
                if c:
                    nama_candidates.append((c, items[idx + 1][2] * 1.5))

    return {
        'provinsi_code': provinsi_code,
        'dob_digits': dob_digits,
        'is_female': is_female,
        'nik_candidates': nik_candidates,
        'nama_candidates': nama_candidates,
        'raw_lines': raw_lines
    }

# ==========================================================
# EKSTRAKSI NIK DENGAN VOTING & KEMENDAGRI VALIDATION
# ==========================================================

def _reconstruct_nik(candidates, dob_digits, provinsi_code):
    if not candidates:
        return "Tidak terdeteksi"

    filtered_candidates = []
    for c, w in candidates:
        score = 0
        if dob_digits and len(dob_digits) == 6:
            for i in range(6):
                if c[6 + i] == dob_digits[i]:
                    score += 1
        else:
            score = 6

        prov_match = 0
        if provinsi_code and len(provinsi_code) == 2:
            if c[0:2] == provinsi_code:
                prov_match = 2

        # Cek validitas kode kecamatan Kemendagri
        kemendagri_bonus = 0
        if KECAMATAN_SET:
            if c[0:6] in KECAMATAN_SET:
                kemendagri_bonus = 10
            elif c[0:4] in KABUPATEN_DICT:
                kemendagri_bonus = 4

        dynamic_weight = w * (score + prov_match + kemendagri_bonus + 1)
        filtered_candidates.append((c, dynamic_weight))

    if not filtered_candidates:
        filtered_candidates = candidates

    final_nik = []
    # 1. Digit 1-2 (Provinsi)
    for pos in range(2):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        if provinsi_code and len(provinsi_code) == 2:
            votes[provinsi_code[pos]] += 1000
        final_nik.append(votes.most_common(1)[0][0])

    # 2. Digit 3-6 (Kabupaten & Kecamatan)
    for pos in range(2, 6):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        final_nik.append(votes.most_common(1)[0][0])

    # 3. Digit 7-12 (Tanggal Lahir)
    for i in range(6):
        pos = 6 + i
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        if dob_digits and len(dob_digits) == 6:
            votes[dob_digits[i]] += 25
        final_nik.append(votes.most_common(1)[0][0])

    # 4. Digit 13-16 (Nomor Urut)
    for pos in range(12, 16):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        final_nik.append(votes.most_common(1)[0][0])

    nik_str = "".join(final_nik)

    # Validasi & Koreksi Kemendagri jika 1 digit meleset
    if KECAMATAN_SET and nik_str[:6] not in KECAMATAN_SET:
        prefix_4 = nik_str[:4]
        matching_kec = [k for k in KECAMATAN_SET if k.startswith(prefix_4)]
        if matching_kec:
            best_match = None
            for mk in matching_kec:
                diffs = sum(1 for a, b in zip(nik_str[:6], mk) if a != b)
                if diffs == 1:
                    best_match = mk
                    break
            if best_match:
                nik_str = best_match + nik_str[6:]

    return nik_str

# ==========================================================
# UNIVERSAL OCR PIPELINE (DUAL-RATIO 9:16 & 16:9 ADAPTIVE)
# ==========================================================

def extract_ktp_universal(input_img):
    """
    Universal Robust OCR Engine:
    Mendukung penuh rasio 9:16 (Portrait / Smartphone) maupun 16:9 (Landscape / Tablet).
    Kebal terhadap variasi pencahayaan, silau lampu, bayangan, dan motion blur.
    
    Langkah:
    1. Normalisasi geometri kanonik jika 4 sudut kartu terdeteksi.
    2. STREAM 1: Full-Card RapidOCR pada resolusi asli (native resolution).
    3. STREAM 2: RapidOCR pada kartu kanonik jika sudut terdeteksi.
    4. STREAM 3: Multi-Filter Enhancement (Blue-Channel & CLAHE) jika kontras rendah.
    5. STREAM 4: Rotasi adaptif (90/180/270 derajat) jika KTP dihadapkan miring.
    6. STREAM 5: Tesseract Targeted Fallback pada ROI digit.
    7. Rekonstruksi NIK Bayesian Voting + Validasi Silang Kemendagri (514 Kab/Kota & 7.285 Kec) + TTL.
    8. Quality Gate Cerdas: Selalu kembalikan status "sukses" jika NIK valid.
    """
    if input_img is None or input_img.size == 0:
        return {
            "status": "error",
            "nik": "Tidak terdeteksi",
            "nama": "Tidak terdeteksi",
            "confidence": 0.0,
            "alasan": "Gambar kosong atau tidak valid."
        }

    # 1. Normalisasi Geometri Kanonik
    canonical_card, is_warped = detect_and_warp_ktp_canonical(input_img)

    all_nik_candidates = []
    all_nama_candidates = []
    provinsi_code = None
    dob_digits = None
    all_raw_lines = []

    # 2. STREAM 1: RapidOCR pada resolusi asli (Mendukung 9:16 dan 16:9 secara penuh)
    if rapid_ocr_engine:
        items_native = _rapid_extract_boxes(input_img, rapid_ocr_engine)
        p1 = _parse_ktp_boxes(items_native)
        all_nik_candidates.extend(p1['nik_candidates'])
        all_nama_candidates.extend(p1['nama_candidates'])
        if p1['provinsi_code'] and not provinsi_code:
            provinsi_code = p1['provinsi_code']
        if p1['dob_digits'] and not dob_digits:
            dob_digits = p1['dob_digits']
        all_raw_lines.extend(p1['raw_lines'])

    # 3. STREAM 2: RapidOCR pada kartu kanonik (jika 4 sudut terdeteksi terpisah)
    if rapid_ocr_engine and is_warped and canonical_card is not None:
        items_canon = _rapid_extract_boxes(canonical_card, rapid_ocr_engine)
        p2 = _parse_ktp_boxes(items_canon)
        all_nik_candidates.extend(p2['nik_candidates'])
        all_nama_candidates.extend(p2['nama_candidates'])
        if p2['provinsi_code'] and not provinsi_code:
            provinsi_code = p2['provinsi_code']
        if p2['dob_digits'] and not dob_digits:
            dob_digits = p2['dob_digits']
        all_raw_lines.extend(p2['raw_lines'])

    # 4. STREAM 3: Multi-Filter Enhancement (Blue-Channel & CLAHE) jika belum ada NIK valid
    has_valid_candidate = any(_is_valid_nik(c[0]) for c in all_nik_candidates)
    if rapid_ocr_engine and not has_valid_candidate:
        img_for_enhance = canonical_card if is_warped else input_img
        full_variants = generate_full_variants(img_for_enhance)
        for v_name in ['blue_enhanced', 'clahe_bgr']:
            if v_name in full_variants:
                v_items = _rapid_extract_boxes(full_variants[v_name], rapid_ocr_engine)
                pv = _parse_ktp_boxes(v_items)
                all_nik_candidates.extend(pv['nik_candidates'])
                all_nama_candidates.extend(pv['nama_candidates'])
                if pv['provinsi_code'] and not provinsi_code:
                    provinsi_code = pv['provinsi_code']
                if pv['dob_digits'] and not dob_digits:
                    dob_digits = pv['dob_digits']
                all_raw_lines.extend(pv['raw_lines'])
                if any(_is_valid_nik(c[0]) for c in all_nik_candidates):
                    break

    # 5. STREAM 4: Rotasi adaptif jika member menghadapkan HP/kartu miring (90°, 180°, 270°)
    has_valid_candidate = any(_is_valid_nik(c[0]) for c in all_nik_candidates)
    if rapid_ocr_engine and not has_valid_candidate:
        for rot_flag in [cv2.ROTATE_90_CLOCKWISE, cv2.ROTATE_180, cv2.ROTATE_90_COUNTERCLOCKWISE]:
            rot_img = cv2.rotate(input_img, rot_flag)
            r_items = _rapid_extract_boxes(rot_img, rapid_ocr_engine)
            pr = _parse_ktp_boxes(r_items)
            all_nik_candidates.extend(pr['nik_candidates'])
            all_nama_candidates.extend(pr['nama_candidates'])
            if pr['provinsi_code'] and not provinsi_code:
                provinsi_code = pr['provinsi_code']
            if pr['dob_digits'] and not dob_digits:
                dob_digits = pr['dob_digits']
            all_raw_lines.extend(pr['raw_lines'])
            if any(_is_valid_nik(c[0]) for c in all_nik_candidates):
                break

    # 6. STREAM 5: Tesseract Targeted Fallback (jika canonical card tersedia)
    if is_warped and canonical_card is not None:
        rois = extract_ktp_rois(canonical_card)
        if 'nik' in rois and rois['nik'].size > 0:
            nik_variants = generate_roi_variants(rois['nik'])
            for v_name in ['glare_thresh', 'scanner_otsu']:
                if v_name in nik_variants:
                    txt_tess = _ocr_worker(nik_variants[v_name], r'--oem 3 --psm 7 -c tessedit_char_whitelist=0123456789')
                    if txt_tess:
                        cands = _find_valid_niks(txt_tess, base_score=5.0)
                        all_nik_candidates.extend(cands)

    # 7. Rekonstruksi NIK & Validasi Kemendagri
    final_nik = _reconstruct_nik(all_nik_candidates, dob_digits, provinsi_code)

    # 8. Penentuan Nama Terbaik dengan Voting Berbobot
    best_nama = "Tidak terdeteksi"
    if all_nama_candidates:
        nama_votes = Counter()
        for n, score in all_nama_candidates:
            nama_votes[n] += score
        best_nama = nama_votes.most_common(1)[0][0]

    # 9. Metadata Wilayah Kemendagri
    kab_name = KABUPATEN_DICT.get(final_nik[:4], "Tidak diketahui") if len(final_nik) == 16 else "-"

    # 10. Evaluasi Kualitas & Status
    quality, advisory = assess_image_quality(input_img)

    is_nik_valid = _is_valid_nik(final_nik)
    is_nama_valid = bool(best_nama and best_nama != "Tidak terdeteksi" and len(best_nama) >= 3)

    confidence = 0.50
    if is_nik_valid:
        confidence += 0.30
        if KECAMATAN_SET and final_nik[:6] in KECAMATAN_SET:
            confidence += 0.10
    if is_nama_valid:
        confidence += 0.10

    # HUKUM EMAS: Jika NIK berhasil terbaca valid, STATUS PASTI SUKSES (TIDAK DIBLOKIR)
    if is_nik_valid and is_nama_valid:
        status = "sukses"
        advisory = None
    elif is_nik_valid:
        # NIK sudah valid, nama mungkin hanya 1 kata atau sedikit buram
        status = "sukses"
        if not best_nama or best_nama == "Tidak terdeteksi":
            best_nama = "MEMBER KTP"
        advisory = None
    else:
        status = "perlu_ulang"
        if not advisory:
            advisory = "NIK belum terbaca jelas. Pastikan kartu KTP berada di dalam frame kamera dan tidak tertutup bayangan."

    return {
        "status": status,
        "nik": final_nik,
        "nama": best_nama,
        "confidence": round(confidence, 2),
        "alasan": advisory,
        "metadata_wilayah": {
            "kode_wilayah": final_nik[:6] if len(final_nik) == 16 else None,
            "kab_kota": kab_name,
            "provinsi_kode": provinsi_code
        },
        "raw_text": "\n".join(all_raw_lines[:12])
    }

# ==========================================================
# BACKWARD COMPATIBLE WRAPPERS
# ==========================================================

def extract_nik_fast(gray, is_cropped=False):
    # Kompatibilitas dengan routes lama
    if len(gray.shape) == 2:
        bgr = cv2.cvtColor(gray, cv2.COLOR_GRAY2BGR)
    else:
        bgr = gray
    res = extract_ktp_universal(bgr)
    nik = res['nik']
    return [(nik, 10)] if _is_valid_nik(nik) else []

def extract_fulltext(gray):
    if len(gray.shape) == 2:
        bgr = cv2.cvtColor(gray, cv2.COLOR_GRAY2BGR)
    else:
        bgr = gray
    res = extract_ktp_universal(bgr)
    return res['nama'], None, res['metadata_wilayah']['provinsi_kode'], res['raw_text']

def _parse_nama(text):
    return _clean_nama_candidate(text)
