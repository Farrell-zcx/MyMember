import pytesseract
import re
import difflib
from collections import Counter
from concurrent.futures import ThreadPoolExecutor, as_completed
from core.config import settings
from services.image_processing import (
    _analyze_image_quality, NIK_PIPELINES, TEXT_PIPELINES,
    nik_pipe_scanner_fix, nik_pipe_gentle, nik_pipe_sharp_adaptive, text_pipe_standard
)

pytesseract.pytesseract.tesseract_cmd = settings.TESSERACT_CMD_PATH

PROVINSI_VALID = {
    11, 12, 13, 14, 15, 16, 17, 18, 19, 21,
    31, 32, 33, 34, 35, 36,
    51, 52, 53,
    61, 62, 63, 64, 65,
    71, 72, 73, 74, 75, 76,
    81, 82,
    91, 92, 93, 94, 95, 96,
}

# Tabel Koreksi Karakter OCR
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

# Mapping nama provinsi di KTP → 2 digit kode
PROVINSI_NAME_TO_CODE = {
    'ACEH': '11',
    'SUMATERA UTARA': '12', 'SUMUT': '12', 'SUMATEBA UTARA': '12',
    'SUMATERA BARAT': '13', 'SUMBAR': '13',
    'RIAU': '14',
    'JAMBI': '15',
    'SUMATERA SELATAN': '16', 'SUMSEL': '16',
    'BENGKULU': '17',
    'LAMPUNG': '18',
    'BANGKA BELITUNG': '19', 'BABEL': '19', 'KEP. BANGKA BELITUNG': '19',
    'KEPULAUAN RIAU': '21', 'KEPRI': '21', 'KEP. RIAU': '21',
    'DKI JAKARTA': '31', 'JAKARTA': '31',
    'JAWA BARAT': '32', 'JABAR': '32',
    'JAWA TENGAH': '33', 'JATENG': '33',
    'YOGYAKARTA': '34', 'DIY': '34', 'DI YOGYAKARTA': '34', 'D.I. YOGYAKARTA': '34',
    'JAWA TIMUR': '35', 'JATIM': '35',
    'BANTEN': '36',
    'BALI': '51',
    'NUSA TENGGARA BARAT': '52', 'NTB': '52',
    'NUSA TENGGARA TIMUR': '53', 'NTT': '53',
    'KALIMANTAN BARAT': '61', 'KALBAR': '61',
    'KALIMANTAN TENGAH': '62', 'KALTENG': '62',
    'KALIMANTAN SELATAN': '63', 'KALSEL': '63',
    'KALIMANTAN TIMUR': '64', 'KALTIM': '64',
    'KALIMANTAN UTARA': '65', 'KALTARA': '65',
    'SULAWESI UTARA': '71', 'SULUT': '71',
    'SULAWESI TENGAH': '72', 'SULTENG': '72',
    'SULAWESI SELATAN': '73', 'SULSEL': '73',
    'SULAWESI TENGGARA': '74', 'SULTRA': '74',
    'GORONTALO': '75',
    'SULAWESI BARAT': '76', 'SULBAR': '76',
    'MALUKU': '81',
    'MALUKU UTARA': '82',
    'PAPUA': '91',
    'PAPUA BARAT': '92',
    'PAPUA SELATAN': '93', 'PAPUA TENGAH': '94',
    'PAPUA PEGUNUNGAN': '95', 'PAPUA BARAT DAYA': '96',
}

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

# OCR WORKER
def _ocr_worker(processed_img, config):
    try:
        return pytesseract.image_to_string(processed_img, config=config)
    except Exception:
        return ""

# EKSTRAKSI NIK — VOTING + CROSS-VALIDATION
def _find_valid_niks(text):
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
                results.append(digits)
            continue

        # Jika panjang 17-22 digit, coba cari sub-sequence 16 digit yang plausible
        if len(digits) > 16 and len(digits) <= 22:
            for start in range(len(digits) - 15):
                candidate = digits[start:start+16]
                if _is_plausible_nik(candidate):
                    results.append(candidate)

    return results

def _vote_nik(candidates):
    if not candidates:
        return None
    if len(set(candidates)) == 1:
        return candidates[0]

    result = []
    for pos in range(16):
        votes = Counter(c[pos] for c in candidates)
        result.append(votes.most_common(1)[0][0])
    return "".join(result)

def _get_dob_match_score(candidate, dob):
    if len(candidate) != 16 or len(dob) != 6:
        return 0
    score = 0
    for i in range(6):
        if candidate[6 + i] == dob[i]:
            score += 1
    return score

def _reconstruct_nik(candidates, dob_digits, provinsi_code):
    if not candidates:
        return "Tidak terdeteksi"

    filtered_candidates = []
    for c, w in candidates:
        score = _get_dob_match_score(c, dob_digits) if (dob_digits and len(dob_digits) == 6) else 6
        prov_match = 0
        if provinsi_code and len(provinsi_code) == 2:
            if c[0:2] == provinsi_code:
                prov_match = 2
                
        if dob_digits and len(dob_digits) == 6 and score < 3:
            continue
            
        dynamic_weight = w * (score + prov_match)
        filtered_candidates.append((c, dynamic_weight))

    if not filtered_candidates:
        filtered_candidates = candidates

    final_nik = []
    
    # 1. Kode Provinsi (Digit 1-2)
    for pos in range(2):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        if provinsi_code and len(provinsi_code) == 2:
            votes[provinsi_code[pos]] += 1000
        final_nik.append(votes.most_common(1)[0][0])

    # 2. Digit 3-6 (Kode Wilayah Detail)
    for pos in range(2, 6):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        final_nik.append(votes.most_common(1)[0][0])

    # 3. Digit 7-12 (DOB) - Joint Voting/Consensus
    for i in range(6):
        pos = 6 + i
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        if dob_digits and len(dob_digits) == 6:
            votes[dob_digits[i]] += 15
        final_nik.append(votes.most_common(1)[0][0])

    # 4. Digit 13-16 (Nomor Urut)
    for pos in range(12, 16):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        final_nik.append(votes.most_common(1)[0][0])

    return "".join(final_nik)

def extract_nik_fast(gray, is_cropped=False):
    """
    Ekstraksi NIK super cepat dengan 2-stage execution:
    Stage 1: Top-crop (area NIK) dengan Scanner Fix, Gentle, & Sharp Adaptive (~0.5s).
    Stage 2: Fallback ke pipelines lain hanya jika Stage 1 belum menemukan NIK yang valid.
    """
    h, w = gray.shape[:2]
    crop_height = int(h * 0.65)
    top_crop = gray[:crop_height, :]
    top_quality = _analyze_image_quality(top_crop)

    # Stage 1: Fast Path (Scanner Fix, Gentle, Sharp Adaptive pada Top Crop)
    fast_tasks = [
        (nik_pipe_scanner_fix(top_crop, top_quality), r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789', 3),
        (nik_pipe_gentle(top_crop, top_quality), r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789', 2),
        (nik_pipe_sharp_adaptive(top_crop, top_quality), r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789', 2),
    ]

    candidates = []
    with ThreadPoolExecutor(max_workers=3) as executor:
        futures = {executor.submit(_ocr_worker, img, cfg): weight for img, cfg, weight in fast_tasks}
        for future in as_completed(futures):
            weight = futures[future]
            text = future.result()
            if text:
                found = _find_valid_niks(text)
                for f in found:
                    candidates.append((f, weight))

    # Fast Exit jika sudah menemukan kandidat NIK 16 digit yang valid
    if any(_is_plausible_nik(c[0]) for c in candidates):
        return candidates

    # Stage 2: Deep Fallback (Jika foto sangat gelap / miring / buram)
    digit_configs = [
        r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789',
        r'--oem 3 --psm 7 -c tessedit_char_whitelist=0123456789',
    ]

    fallback_tasks = []
    for pipe_fn in NIK_PIPELINES:
        try:
            p_img = pipe_fn(top_crop, top_quality)
            for cfg in digit_configs:
                fallback_tasks.append((p_img, cfg, 2))
        except Exception:
            continue

    with ThreadPoolExecutor(max_workers=4) as executor:
        futures = {executor.submit(_ocr_worker, img, cfg): weight for img, cfg, weight in fallback_tasks}
        for future in as_completed(futures):
            weight = futures[future]
            text = future.result()
            if text:
                found = _find_valid_niks(text)
                for f in found:
                    candidates.append((f, weight))

    return candidates

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
        cleaned_line = re.sub(r'[^A-Z\s]', '', line).strip()
        if len(cleaned_line) < 4:
            continue
        for name in sorted_names:
            if difflib.SequenceMatcher(None, name, cleaned_line).ratio() > 0.8:
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

def _parse_nama(text):
    if not text:
        return None
    lines = text.split('\n')
    for i, line in enumerate(lines):
        line_upper = line.strip().upper()

        match = re.search(r'([NM]\s*[A4R]\s*[MN]\s*[A4R])(?![A-Z])\s*[:;.\-]?\s*(.*)', line_upper)
        if match:
            if re.search(r'TEMPAT|LENGKAP|GADIS|IBU', line_upper):
                continue
                
            raw = match.group(2).strip()
            
            def clean_nama_string(raw_str):
                fixed_str = raw_str.replace('0', 'O').replace('1', 'I').replace('5', 'S').replace('8', 'B')
                cleaned = re.sub(r'[^A-Z\s\'.]', '', fixed_str).strip()
                words = [w for w in cleaned.split() if len(w) >= 1]
                if words:
                    return " ".join(words)
                return None
            
            if len(raw) > 3 and not re.search(r'TEMPAT|LENGKAP|LAHIR|BLOOD|GOL|DARAH', raw):
                parsed = clean_nama_string(raw)
                if parsed:
                    return parsed

            if i + 1 < len(lines):
                next_upper = lines[i + 1].strip().upper()
                if not re.search(r'TEMPAT|LAHIR|KELAMIN|AGAMA|ALAMAT|STATUS|PEKERJAAN|WARGA|NIK|RT|RW|KEWARGANEGARAAN', next_upper):
                    parsed = clean_nama_string(next_upper)
                    if parsed:
                        return parsed
    return None

def extract_fulltext(gray):
    """
    Full-text OCR dengan 2-stage execution:
    Stage 1: Standard Text pipeline with PSM 6 (~0.35s).
    Stage 2: Fallback ke CLAHE, Scanner Fix, Morph jika Nama atau teks belum lengkap.
    """
    quality = _analyze_image_quality(gray)

    # Stage 1: Fast path
    p_std = text_pipe_standard(gray, quality)
    raw_std = _ocr_worker(p_std, r'--oem 3 --psm 6')

    nama = _parse_nama(raw_std)
    dob = _extract_dob_digits(raw_std)
    prov = _extract_provinsi_code(raw_std)

    if nama and nama != "Tidak terdeteksi" and (dob or prov):
        return nama, dob, prov, raw_std

    # Stage 2: Deep Fallback
    configs = [r'--oem 3 --psm 6']
    tasks = []
    for pipe_fn in TEXT_PIPELINES:
        try:
            processed = pipe_fn(gray, quality)
            for cfg in configs:
                tasks.append((processed, cfg))
        except Exception:
            continue

    results = [raw_std] if raw_std else []
    with ThreadPoolExecutor(max_workers=3) as executor:
        futures = [executor.submit(_ocr_worker, img, cfg) for img, cfg in tasks]
        for future in futures:
            text = future.result()
            if text and text.strip():
                results.append(text)

    is_female = False
    for text in results:
        upper = text.upper()
        if any(kw in upper for kw in ['PEREMPUAN', 'REMPU', 'PUAN', 'PE12EMPUAN', 'PEAEMPUAN']):
            is_female = True
            break

    nama_candidates = [nama] if (nama and nama != "Tidak terdeteksi") else []
    best_dob = dob
    best_provinsi = prov
    best_text = raw_std

    for text in results:
        n = _parse_nama(text)
        if n:
            nama_candidates.append(n)
            if not best_text:
                best_text = text
                
        if not best_dob:
            d = _extract_dob_digits(text, is_female=is_female)
            if d:
                best_dob = d
        if not best_provinsi:
            p = _extract_provinsi_code(text)
            if p:
                best_provinsi = p

    if nama_candidates:
        votes = Counter(nama_candidates)
        best_nama = votes.most_common(1)[0][0]
    else:
        best_nama = "Tidak terdeteksi"

    return best_nama, best_dob, best_provinsi, best_text
