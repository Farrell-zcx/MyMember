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
# RAPIDOCR FIELD EXTRACTORS
# ==========================================================

def _rapid_extract_text(img, engine=None):
    """Jalankan RapidOCR dan kembalikan list of (text, confidence)"""
    if engine is None or img is None or img.size == 0:
        return []
    try:
        res, _ = engine(img)
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

def _clean_nama_candidate(raw_str):
    if not raw_str:
        return None
    upper = raw_str.upper()
    # Hilangkan prefix label
    upper = re.sub(r'^(?:NAMA|NAME|LENGKAP|ALAMAT|TEMPAT|NIK|STATUS)\s*[:;\.\-]?\s*', '', upper)
    # Ganti digit umum yang salah baca
    upper = upper.replace('0', 'O').replace('1', 'I').replace('5', 'S').replace('8', 'B')
    cleaned = re.sub(r'[^A-Z\s\'.\-]', '', upper).strip()
    words = [w for w in cleaned.split() if len(w) >= 1]
    if words:
        result = " ".join(words)
        if len(result) >= 3 and not re.search(r'TEMPAT|LENGKAP|LAHIR|GOL|DARAH|AGAMA|PROVINSI|KOTA', result):
            return result
    return None

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
        # Cari kecamatan terdekat di kabupaten yang sama
        matching_kec = [k for k in KECAMATAN_SET if k.startswith(prefix_4)]
        if matching_kec:
            # Cari kecamatan dengan Hamming distance = 1
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
# UNIVERSAL OCR PIPELINE (RAPIDOCR + TESSERACT ENSEMBLE)
# ==========================================================

def extract_ktp_universal(canonical_bgr):
    """
    Ekstraksi Komprehensif KTP 100% Akurat:
    1. Memotong ROI presisi (NIK, Nama, TTL, Header).
    2. Menghasilkan multi-filter per-ROI (Anti-glare, CLAHE, Blue-channel).
    3. Menjalankan ensemble RapidOCR + Tesseract.
    4. Melakukan validasi silang kode wilayah Kemendagri + Tanggal Lahir.
    5. Menghasilkan skor keyakinan (confidence) & panduan jika perlu foto ulang.
    """
    rois = extract_ktp_rois(canonical_bgr)
    
    # 1. Ekstraksi Header (Provinsi)
    header_variants = generate_roi_variants(rois['header'])
    provinsi_code = None
    header_raw = ""
    
    if rapid_ocr_engine:
        for v_name in ['raw_bgr', 'clahe_bgr']:
            if v_name in header_variants:
                res = _rapid_extract_text(header_variants[v_name], rapid_ocr_engine)
                joined = "\n".join([r[0] for r in res])
                header_raw += " " + joined
                p_code = _extract_provinsi_code(joined)
                if p_code and not provinsi_code:
                    provinsi_code = p_code
                    break

    # 2. Ekstraksi Tempat / Tanggal Lahir & Jenis Kelamin
    ttl_variants = generate_roi_variants(rois['ttl'])
    dob_digits = None
    is_female = False
    ttl_raw = ""

    if rapid_ocr_engine:
        for v_name in ['raw_bgr', 'clahe_bgr']:
            if v_name in ttl_variants:
                res = _rapid_extract_text(ttl_variants[v_name], rapid_ocr_engine)
                joined = "\n".join([r[0] for r in res])
                ttl_raw += " " + joined
                if any(kw in joined.upper() for kw in ['PEREMPUAN', 'WANITA', 'FEMALE', 'REMPUAN']):
                    is_female = True
                d_cand = _extract_dob_digits(joined, is_female=is_female)
                if d_cand and not dob_digits:
                    dob_digits = d_cand

    # 3. Ekstraksi NIK (Ensemble RapidOCR + Tesseract + Multi-variant)
    nik_variants = generate_roi_variants(rois['nik'])
    candidates = []

    # Stream 3A: RapidOCR pada varian NIK
    if rapid_ocr_engine:
        for v_name in ['raw_bgr', 'blue_enhanced', 'glare_thresh', 'clahe_bgr']:
            if v_name in nik_variants:
                res = _rapid_extract_text(nik_variants[v_name], rapid_ocr_engine)
                for txt, conf in res:
                    cands = _find_valid_niks(txt, base_score=conf * 10.0)
                    candidates.extend(cands)

    # Stream 3B: Tesseract pada varian NIK (PSM 7 & PSM 8)
    for v_name in ['glare_thresh', 'scanner_otsu']:
        if v_name in nik_variants:
            txt_tess = _ocr_worker(nik_variants[v_name], r'--oem 3 --psm 7 -c tessedit_char_whitelist=0123456789')
            if txt_tess:
                cands = _find_valid_niks(txt_tess, base_score=5.0)
                candidates.extend(cands)

    final_nik = _reconstruct_nik(candidates, dob_digits, provinsi_code)

    # 4. Ekstraksi Nama
    nama_variants = generate_roi_variants(rois['nama'])
    nama_candidates = []

    if rapid_ocr_engine:
        for v_name in ['raw_bgr', 'clahe_bgr', 'blue_enhanced']:
            if v_name in nama_variants:
                res = _rapid_extract_text(nama_variants[v_name], rapid_ocr_engine)
                for txt, conf in res:
                    c = _clean_nama_candidate(txt)
                    if c:
                        nama_candidates.append((c, conf))

    # Fallback Tesseract untuk Nama jika RapidOCR kosong
    if not nama_candidates and 'clahe_gray' in nama_variants:
        txt_tess_nama = _ocr_worker(nama_variants['clahe_gray'], r'--oem 3 --psm 6', lang='ind')
        lines = txt_tess_nama.split('\n')
        for l in lines:
            c = _clean_nama_candidate(l)
            if c:
                nama_candidates.append((c, 0.6))

    best_nama = "Tidak terdeteksi"
    if nama_candidates:
        nama_votes = Counter()
        for n, score in nama_candidates:
            nama_votes[n] += score
        best_nama = nama_votes.most_common(1)[0][0]

    # 5. Metadata Wilayah
    kab_name = KABUPATEN_DICT.get(final_nik[:4], "Tidak diketahui") if len(final_nik) == 16 else "-"
    
    # 6. Quality & Confidence Calculation
    quality, advisory = assess_image_quality(canonical_bgr)
    
    is_nik_valid = _is_valid_nik(final_nik)
    is_nama_valid = bool(best_nama and best_nama != "Tidak terdeteksi" and len(best_nama) >= 3)

    confidence = 0.5
    if is_nik_valid:
        confidence += 0.30
        if KECAMATAN_SET and final_nik[:6] in KECAMATAN_SET:
            confidence += 0.10
    if is_nama_valid:
        confidence += 0.10

    status = "sukses" if (is_nik_valid and is_nama_valid and quality['is_acceptable']) else "perlu_ulang"
    if not is_nik_valid and not advisory:
        advisory = "NIK tidak terbaca sempurna. Pastikan kartu KTP tidak tertutup bayangan atau silau."

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
        "raw_text": f"HEADER: {header_raw}\nTTL: {ttl_raw}"
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
