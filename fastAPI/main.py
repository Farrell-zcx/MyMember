from fastapi import FastAPI, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import pytesseract
import cv2
import numpy as np
import re
from collections import Counter
from concurrent.futures import ThreadPoolExecutor, as_completed
import difflib

pytesseract.pytesseract.tesseract_cmd = r'D:\Tesseract-OCR\tesseract.exe'

app = FastAPI(
    title="MyMember OCR API",
    description="Microservice untuk ekstraksi Data KTP (NIK & Nama)",
    version="5.1.0"
)

# Izinkan web mymember untuk mengakses API ini
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], 
    allow_credentials=True,
    allow_methods=["*"], 
    allow_headers=["*"], 
)

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

def _analyze_image_quality(gray):
    # Hitung rata-rata kecerahan (0-255)
    mean_brightness = cv2.mean(gray)[0]
    # Hitung varians Laplacian untuk deteksi keburaman
    laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
    
    return {
        'is_dark': mean_brightness < 90,
        'is_bright': mean_brightness > 165,
        'is_blurry': laplacian_var < 250,
        'brightness': mean_brightness,
        'blur_var': laplacian_var
    }

def _upscale(gray, scale=3.0):
    return cv2.resize(gray, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)

def _sharpen(gray, strong=False):
    if strong:
        kernel = np.array([[-1, -1, -1],
                           [-1, 10, -1],
                           [-1, -1, -1]])
    else:
        kernel = np.array([[-1, -1, -1],
                           [-1,  9, -1],
                           [-1, -1, -1]])
    return cv2.filter2D(gray, -1, kernel)

def _denoise(gray):
    return cv2.fastNlMeansDenoising(gray, h=12, templateWindowSize=7, searchWindowSize=21)


# PIPELINE NIK 
def nik_pipe_gentle(gray, quality=None):
    big = _upscale(gray, 3.0)
    alpha = 1.3
    beta = 10
    if quality:
        if quality['is_dark']:
            alpha, beta = 1.6, 30
        elif quality['is_bright']:
            alpha, beta = 1.1, 0
    return cv2.convertScaleAbs(big, alpha=alpha, beta=beta)

def nik_pipe_otsu(gray, quality=None):
    big = _upscale(gray, 3.0)
    alpha = 1.4
    beta = 10
    if quality and quality['is_dark']:
        alpha, beta = 1.7, 20
    adjusted = cv2.convertScaleAbs(big, alpha=alpha, beta=beta)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def nik_pipe_clahe(gray, quality=None):
    clip = 4.0 if (quality and quality['is_dark']) else 2.5
    clahe = cv2.createCLAHE(clipLimit=clip, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    big = _upscale(enhanced, 3.0)
    _, binary = cv2.threshold(big, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def nik_pipe_adaptive_threshold(gray, quality=None):
    big = _upscale(gray, 3.0)
    blurred = cv2.GaussianBlur(big, (3, 3), 0)
    block_size = 25 if (quality and quality['is_bright']) else 21
    return cv2.adaptiveThreshold(
        blurred, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, 
        cv2.THRESH_BINARY, block_size, 15
    )

def nik_pipe_sharp_adaptive(gray, quality=None):
    # Normalisasi kontras terlebih dahulu untuk cahaya redup/warna cold
    normalized = cv2.normalize(gray, None, 0, 255, cv2.NORM_MINMAX)
    denoised = cv2.bilateralFilter(normalized, 9, 75, 75)
    big = _upscale(denoised, 3.0)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    block_size = 21 if (quality and quality['is_bright']) else 19
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, block_size, 12
    )

def nik_pipe_scanner_fix(gray, quality=None):
    # Selalu gunakan ksize=3 karena ksize=5 terlalu blur dan bisa merusak angka 1 menjadi 4
    ksize = 3
    denoised = cv2.medianBlur(gray, ksize)
    big = _upscale(denoised, 3.0)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    block_size = 21 if (quality and quality['is_bright']) else 19
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, block_size, 12
    )

def nik_pipe_morph_thick(gray, quality=None):
    # Erosi (menebalkan teks hitam) untuk huruf putus-putus
    big = _upscale(gray, 3.0)
    adjusted = cv2.convertScaleAbs(big, alpha=1.3, beta=10)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.erode(binary, kernel, iterations=1)

def nik_pipe_morph_thin(gray, quality=None):
    # Dilasi (menipiskan teks hitam) untuk huruf yang meluber (bleeding)
    big = _upscale(gray, 3.0)
    adjusted = cv2.convertScaleAbs(big, alpha=1.3, beta=10)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.dilate(binary, kernel, iterations=1)

NIK_PIPELINES = [
    nik_pipe_gentle, 
    nik_pipe_sharp_adaptive,
    nik_pipe_scanner_fix,
    nik_pipe_morph_thick,
    nik_pipe_morph_thin
]


# Pipeline Full Text (Nama + DOB + Provinsi)
def text_pipe_standard(gray, quality=None):
    big = _upscale(gray, 2.5)
    alpha = 1.4
    beta = 15
    if quality:
        if quality['is_dark']:
            alpha, beta = 1.7, 30
        elif quality['is_bright']:
            alpha, beta = 1.2, 0
    adjusted = cv2.convertScaleAbs(big, alpha=alpha, beta=beta)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_clahe(gray, quality=None):
    clip = 4.0 if (quality and quality['is_dark']) else 3.0
    clahe = cv2.createCLAHE(clipLimit=clip, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    big = _upscale(enhanced, 2.5)
    _, binary = cv2.threshold(big, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_scanner_fix(gray, quality=None):
    ksize = 3
    denoised = cv2.medianBlur(gray, ksize)
    big = _upscale(denoised, 2.5)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    _, binary = cv2.threshold(sharp, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_adaptive_sharpen(gray, quality=None):
    denoised = cv2.medianBlur(gray, 3)
    big = _upscale(denoised, 2.5)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    block_size = 23 if (quality and quality['is_bright']) else 21
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, block_size, 15
    )

def text_pipe_morph_thick(gray, quality=None):
    big = _upscale(gray, 2.5)
    adjusted = cv2.convertScaleAbs(big, alpha=1.4, beta=15)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.erode(binary, kernel, iterations=1)

def text_pipe_morph_thin(gray, quality=None):
    big = _upscale(gray, 2.5)
    adjusted = cv2.convertScaleAbs(big, alpha=1.4, beta=15)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.dilate(binary, kernel, iterations=1)

TEXT_PIPELINES = [
    text_pipe_standard, 
    text_pipe_scanner_fix,
    text_pipe_morph_thick,
    text_pipe_morph_thin
]

def _is_valid_nik(nik):
    if len(nik) != 16 or not nik.isdigit():
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
    if len(nik) != 16 or not nik.isdigit():
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
    for line in text.split('\n'):
        # Terapkan koreksi karakter fuzzy terlebih dahulu agar karakter huruf tidak di-strip sia-sia
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

    # Filter & bobot candidates secara dinamis berdasarkan kecocokan DOB & Provinsi
    filtered_candidates = []
    for c, w in candidates:
        score = _get_dob_match_score(c, dob_digits) if (dob_digits and len(dob_digits) == 6) else 6
        prov_match = 0
        if provinsi_code and len(provinsi_code) == 2:
            if c[0:2] == provinsi_code:
                prov_match = 2
                
        # Jika DOB tersedia dan score < 3, maka ini adalah kandidat tergeser (shifted) dan dibuang
        if dob_digits and len(dob_digits) == 6 and score < 3:
            continue
            
        dynamic_weight = w * (score + prov_match)
        filtered_candidates.append((c, dynamic_weight))

    # Fallback jika semua terfilter (misalnya karena noise parah), gunakan semua kandidat awal
    if not filtered_candidates:
        filtered_candidates = candidates

    final_nik = []
    
    # 1. Kode Provinsi (Digit 1-2)
    for pos in range(2):
        votes = Counter()
        for c, w in filtered_candidates:
            votes[c[pos]] += w
        # Jika ada provinsi_code dari teks, beri bonus suara mutlak (+1000) untuk override error OCR
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
        
        # Tambah suara dari NIK candidates berdasarkan bobotnya
        for c, w in filtered_candidates:
            votes[c[pos]] += w
            
        # Tambah suara dari DOB digits (bobot penyeimbang kuat = 15)
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


def detect_and_crop_ktp(gray):
    """
    Mencoba mendeteksi batas kartu KTP (biasanya berkontras dengan latar belakang terang/meja)
    lalu memotong area kartu KTP tersebut untuk meningkatkan akurasi OCR.
    """
    h, w = gray.shape[:2]
    # Blur untuk menghilangkan detail tulisan kecil agar deteksi kontur fokus pada tepi kartu
    blurred = cv2.GaussianBlur(gray, (9, 9), 0)
    
    # Dapatkan perkiraan kecerahan background dari pojok-pojok gambar
    corners = [
        blurred[0:15, 0:15],
        blurred[0:15, w-15:w],
        blurred[h-15:h, 0:15],
        blurred[h-15:h, w-15:w]
    ]
    bg_mean = np.mean([np.mean(c) for c in corners])
    
    # Coba threshold dinamis relatif terhadap kecerahan background
    threshold_vals = [
        int(bg_mean - 20),
        int(bg_mean - 35),
        int(bg_mean - 55),
        int(bg_mean - 70)
    ]
    # Filter agar threshold tetap dalam batas aman [90, 245]
    threshold_vals = [max(90, min(245, val)) for val in threshold_vals]
    
    # Tambahkan beberapa nilai threshold statis universal sebagai backup jika pojok gambar tersemat KTP
    backup_vals = [200, 180, 160, 140, 110, 80]
    for b in backup_vals:
        if b not in threshold_vals:
            threshold_vals.append(b)
    
    for threshold_val in threshold_vals:
        _, thresh = cv2.threshold(blurred, threshold_val, 255, cv2.THRESH_BINARY_INV)
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        
        best_cnt = None
        max_area = 0
        img_area = h * w
        
        for cnt in contours:
            rect = cv2.minAreaRect(cnt)
            (cx, cy), (cw, ch), angle = rect
            area = cw * ch
            
            # KTP aspect ratio standar 1.58. Kita toleransi 1.0 s/d 2.2
            if ch > cw:
                aspect_ratio = float(ch) / cw if cw > 0 else 0
            else:
                aspect_ratio = float(cw) / ch if ch > 0 else 0
            
            if 0.12 * img_area < area < 0.95 * img_area:
                if 1.0 < aspect_ratio < 2.2:
                    if area > max_area:
                        max_area = area
                        best_cnt = cnt
                        
        if best_cnt is not None:
            # Dapatkan orientasi dan sudut
            rect = cv2.minAreaRect(best_cnt)
            (cx, cy), (cw, ch), angle = rect
            
            if ch > cw:
                angle -= 90
                
            while angle < -45:
                angle += 90
            while angle > 45:
                angle -= 90
                
            # Jika kemiringan sangat kecil (< 2 derajat), lakukan pemotongan tegak lurus (straight crop)
            # Ini sangat penting untuk menjaga pixel-perfect ketajaman huruf (menghindari blur akibat interpolasi warp)
            if abs(angle) < 2.0:
                x, y, cw_bound, ch_bound = cv2.boundingRect(best_cnt)
                pad = 15
                x1 = max(0, x - pad)
                y1 = max(0, y - pad)
                x2 = min(w, x + cw_bound + pad)
                y2 = min(h, y + ch_bound + pad)
                return gray[y1:y2, x1:x2], True
            else:
                # Lakukan perspective warp (Auto-Deskew) jika miring
                box = cv2.boxPoints(rect)
                box = np.intp(box)
                src_pts = box.astype("float32")
                
                def order_points(pts):
                    rect_ordered = np.zeros((4, 2), dtype="float32")
                    s = pts.sum(axis=1)
                    rect_ordered[0] = pts[np.argmin(s)] 
                    rect_ordered[2] = pts[np.argmax(s)] 
                    diff = np.diff(pts, axis=1)
                    rect_ordered[1] = pts[np.argmin(diff)] 
                    rect_ordered[3] = pts[np.argmax(diff)] 
                    return rect_ordered
                    
                src_pts = order_points(src_pts)
                width_A = np.linalg.norm(src_pts[2] - src_pts[3])
                width_B = np.linalg.norm(src_pts[1] - src_pts[0])
                max_width = max(int(width_A), int(width_B))
                
                height_A = np.linalg.norm(src_pts[1] - src_pts[2])
                height_B = np.linalg.norm(src_pts[0] - src_pts[3])
                max_height = max(int(height_A), int(height_B))
                
                if max_height > max_width:
                    max_width, max_height = max_height, max_width
                    pad = 10
                    dst_pts = np.array([
                        [max_width - 1 + pad, pad],
                        [max_width - 1 + pad, max_height - 1 + pad],
                        [pad, max_height - 1 + pad],
                        [pad, pad]
                    ], dtype="float32")
                else:
                    pad = 10
                    dst_pts = np.array([
                        [pad, pad],
                        [max_width - 1 + pad, pad],
                        [max_width - 1 + pad, max_height - 1 + pad],
                        [pad, max_height - 1 + pad]
                    ], dtype="float32")
                
                M = cv2.getPerspectiveTransform(src_pts, dst_pts)
                warped = cv2.warpPerspective(gray, M, (max_width + 2*pad, max_height + 2*pad), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REPLICATE)
                return warped, True
            
    return gray, False


def extract_nik_fast(gray, is_cropped=False):
    h, w = gray.shape[:2]
    
    # Analisis Kualitas Gambar
    quality = _analyze_image_quality(gray)

    digit_configs = [
        r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789',
        r'--oem 3 --psm 7 -c tessedit_char_whitelist=0123456789',
    ]

    tasks = []
    
    # Jalankan pada full cropped image 
    if is_cropped:
        for pipe_fn in NIK_PIPELINES:
            try:
                processed = pipe_fn(gray, quality)
                for config in digit_configs:
                    tasks.append((processed, config, 2))
            except Exception:
                continue

    # Jalankan pada top crop area sebagai safety backup 
    # Gunakan 65% tinggi gambar (memberi baseline context yang baik dan mencakup NIK jika KTP di tengah).
    crop_height = int(h * 0.65)
    top_crop = gray[:crop_height, :]
    top_quality = _analyze_image_quality(top_crop)
    for pipe_fn in NIK_PIPELINES:
        try:
            processed = pipe_fn(top_crop, top_quality)
            for config in digit_configs:
                tasks.append((processed, config, 2))
        except Exception:
            continue

    candidates = []
    with ThreadPoolExecutor(max_workers=4) as executor:
        futures = {executor.submit(_ocr_worker, img, cfg): weight for img, cfg, weight in tasks}
        for future in as_completed(futures):
            weight = futures[future]
            text = future.result()
            if text:
                found = _find_valid_niks(text)
                for f in found:
                    candidates.append((f, weight))

    return candidates

def _extract_provinsi_code(text):
    """
    Cari nama provinsi di teks KTP → return 2 digit kode.
    KTP selalu ada tulisan "PROVINSI JAWA TENGAH" dll di atas.
    """
    upper = text.upper()
    # Cari yang paling panjang dulu 
    sorted_names = sorted(PROVINSI_NAME_TO_CODE.keys(), key=len, reverse=True)
    for name in sorted_names:
        if name in upper:
            return PROVINSI_NAME_TO_CODE[name]
            
    # Fuzzy match fallback untuk menangani typo 
    lines = upper.split('\n')
    for line in lines[:5]: # Provinsi biasanya ada di 5 baris pertama KTP
        # Bersihkan karakter aneh yang bukan huruf/spasi
        cleaned_line = re.sub(r'[^A-Z\s]', '', line).strip()
        if len(cleaned_line) < 4:
            continue
            
        for name in sorted_names:
            if difflib.SequenceMatcher(None, name, cleaned_line).ratio() > 0.8:
                return PROVINSI_NAME_TO_CODE[name]
                
    return None


def _extract_dob_digits(text, is_female=False):
    """Ambil 6 digit DOB (DDMMYY) dari teks KTP untuk cross-validation digit 7-12."""
    # Terapkan CHAR_FIX terlebih dahulu agar huruf 'O' -> '0', 'S' -> '5' dll sebelum dicocokkan regex
    fixed_text = "".join(CHAR_FIX.get(c, c) for c in text)
    m = re.search(r'(\d{2})\s*[-/\.]\s*(\d{2})\s*[-/\.]\s*(\d{2,4})', fixed_text)
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
    lines = text.split('\n')
    for i, line in enumerate(lines):
        line_upper = line.strip().upper()

        # Ekstrak NAMA dengan menoleransi spasi di dalam label NAMA (misal: "N A M A")
        match = re.search(r'([NM]\s*[A4R]\s*[MN]\s*[A4R])(?![A-Z])\s*[:;.\-]?\s*(.*)', line_upper)
        
        if match:
            if re.search(r'TEMPAT|LENGKAP|GADIS|IBU', line_upper):
                continue
                
            raw = match.group(2).strip()
            
            def clean_nama_string(raw_str):
                # Reverse correction (menyelamatkan huruf yang terbaca sebagai angka)
                fixed_str = raw_str.replace('0', 'O').replace('1', 'I').replace('5', 'S').replace('8', 'B')
                # Hanya sisakan A-Z, spasi, titik, dan petik
                cleaned = re.sub(r'[^A-Z\s\'.]', '', fixed_str).strip()
                # Longgarkan filter, izinkan huruf tunggal (misal singkatan M., B.)
                words = [w for w in cleaned.split() if len(w) >= 1]
                if words:
                    return " ".join(words)
                return None
            
            # Jika NAMA ada di baris yang sama (setelah titik dua)
            if len(raw) > 3 and not re.search(r'TEMPAT|LENGKAP|LAHIR|BLOOD|GOL|DARAH', raw):
                return clean_nama_string(raw)

            # Jika NAMA ada di baris bawahnya
            if i + 1 < len(lines):
                next_upper = lines[i + 1].strip().upper()
                if not re.search(r'TEMPAT|LAHIR|KELAMIN|AGAMA|ALAMAT|STATUS|PEKERJAAN|WARGA|NIK|RT|RW|KEWARGANEGARAAN', next_upper):
                    return clean_nama_string(next_upper)
    return None


def extract_fulltext(gray):
    """
    Full-text OCR → hasilnya untuk 3 hal sekaligus:
    1. Nama provinsi → fix digit 1-2 NIK
    2. Tanggal lahir → fix digit 7-12 NIK
    3. Nama lengkap
    """
    # Analisis Kualitas Gambar
    quality = _analyze_image_quality(gray)

    configs = [r'--oem 3 --psm 6', r'--oem 3 --psm 4']

    tasks = []
    for pipe_fn in TEXT_PIPELINES:
        try:
            processed = pipe_fn(gray, quality)
            for config in configs:
                tasks.append((processed, config))
        except Exception:
            continue

    results = []
    with ThreadPoolExecutor(max_workers=4) as executor:
        futures = [executor.submit(_ocr_worker, img, cfg) for img, cfg in tasks]
        for future in futures:
            text = future.result()
            if text and text.strip():
                results.append(text)

    # Deteksi gender (Perempuan) di seluruh teks OCR untuk cross-validation
    is_female = False
    for text in results:
        upper = text.upper()
        if any(kw in upper for kw in ['PEREMPUAN', 'REMPU', 'PUAN', 'PE12EMPUAN', 'PEAEMPUAN']):
            is_female = True
            break

    best_dob = None
    best_provinsi = None
    best_text = ""
    nama_candidates = []

    for text in results:
        nama = _parse_nama(text)
        if nama:
            nama_candidates.append(nama)
            if not best_text:
                best_text = text
                
        if not best_dob:
            dob = _extract_dob_digits(text, is_female=is_female)
            if dob:
                best_dob = dob
        if not best_provinsi:
            prov = _extract_provinsi_code(text)
            if prov:
                best_provinsi = prov

    # Consensus Voting untuk NAMA
    if nama_candidates:
        votes = Counter(nama_candidates)
        best_nama = votes.most_common(1)[0][0]
    else:
        best_nama = "Tidak terdeteksi"

    return best_nama, best_dob, best_provinsi, best_text

def deskew_by_text(gray):
    """
    Fallback method: Meluruskan gambar berdasarkan orientasi baris teks (Hybrid Mode).
    """
    # Adaptive Thresholding (lebih kebal cahaya tidak rata)
    thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY_INV, 21, 15)
    
    # Dilation horizontal untuk menggabungkan huruf menjadi baris teks
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (35, 3))
    dilated = cv2.dilate(thresh, kernel, iterations=1)
    
    # Cari contour baris teks
    contours, _ = cv2.findContours(dilated, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    
    angles = []
    for cnt in contours:
        rect = cv2.minAreaRect(cnt)
        (cx, cy), (cw, ch), angle = rect
        
        if cw > 0 and ch > 0:
            aspect_ratio = max(cw, ch) / min(cw, ch)
            # Filter: Hanya ambil contour yang panjang (seperti baris teks)
            if aspect_ratio > 3.0 and cw * ch > 200:
                # Normalisasi sudut OpenCV ke range -45 sampai 45
                if cw < ch:
                    angle -= 90
                
                if -45 < angle < 45:
                    angles.append(angle)
                    
    if angles:
        # Ambil median sudut untuk mengabaikan noise (outlier)
        median_angle = float(np.median(angles))
        
        # Putar hanya jika kemiringannya cukup terasa (> 1.5 derajat) 
        # untuk menghindari blur interpolasi yang tidak perlu pada KTP yang nyaris lurus
        if abs(median_angle) > 1.5:
            # Upscale sebelum rotate untuk menjaga ketajaman pixel digit NIK
            scale = 2.0
            big_gray = cv2.resize(gray, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
            
            (h, w) = big_gray.shape[:2]
            center = (w // 2, h // 2)
            M = cv2.getRotationMatrix2D(center, median_angle, 1.0)
            
            # Hitung ukuran gambar baru agar tidak terpotong saat diputar
            cos_a = np.abs(M[0, 0])
            sin_a = np.abs(M[0, 1])
            new_w = int((h * sin_a) + (w * cos_a))
            new_h = int((h * cos_a) + (w * sin_a))
            M[0, 2] += (new_w / 2) - center[0]
            M[1, 2] += (new_h / 2) - center[1]
            
            rotated_big = cv2.warpAffine(big_gray, M, (new_w, new_h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REPLICATE)
            
            # Downscale kembali ke ukuran asli dengan INTER_AREA agar tetap tajam dan padat
            rotated = cv2.resize(rotated_big, (int(new_w/scale), int(new_h/scale)), interpolation=cv2.INTER_AREA)
            
            return rotated, True
            
    return gray, False


@app.post("/extract-ktp")
async def extract_ktp(ktp_image: UploadFile = File(...)):
    try:
        contents = await ktp_image.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return {"status": "error", "pesan": "File gambar tidak valid atau corrupt."}

        # Menggunakan Blue Channel agar background KTP (biru) menjadi sangat putih/terang
        # Teks hitam (NIK & Nama) akan tetap hitam, menghasilkan kontras maksimal
        b, g, r = cv2.split(img)
        gray = b

        # Deteksi dan potong area kartu KTP untuk mereduksi noise latar belakang
        card_gray, detected = detect_and_crop_ktp(gray)
        
        if not detected:
            # Jika gagal mendeteksi kotak kartu (misal krn background rumit/dompet),
            # langsung gunakan mode penyelamat (Text-based Deskew)
            card_gray, _ = deskew_by_text(gray)

        # NIK dan full-text diproses PARALEL
        with ThreadPoolExecutor(max_workers=2) as executor:
            future_nik = executor.submit(extract_nik_fast, card_gray, detected)
            future_text = executor.submit(extract_fulltext, card_gray)

            nik_candidates = future_nik.result()
            nama, dob_digits, provinsi_code, raw_text = future_text.result()

        # Rekonstruksi NIK dengan cross-validation & joint voting
        nik = _reconstruct_nik(nik_candidates, dob_digits, provinsi_code)

        # Fallback terakhir: Jika sudah diproses tapi hasil masih nihil,
        # kemungkinan KTP ter-crop salah oleh detect_and_crop_ktp.
        if detected and (nik == "Tidak terdeteksi" or not nama or nama == "Tidak terdeteksi"):
            # Lakukan fallback menggunakan gambar asli yang diluruskan by text
            rescue_gray, _ = deskew_by_text(gray)
            with ThreadPoolExecutor(max_workers=2) as executor:
                future_nik = executor.submit(extract_nik_fast, rescue_gray, False)
                future_text = executor.submit(extract_fulltext, rescue_gray)

                nik_candidates = future_nik.result()
                nama, dob_digits, provinsi_code, raw_text = future_text.result()
            
            nik = _reconstruct_nik(nik_candidates, dob_digits, provinsi_code)

        return {
            "status": "sukses",
            "nama_file": ktp_image.filename,
            "data_ktp": {
                "nik": nik,
                "nama": nama,
            },
            "hasil_bacaan_mentah": raw_text,
        }

    except Exception as e:
        return {
            "status": "error",
            "pesan": f"Gagal memproses gambar: {str(e)}"
        }

if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8000, reload=True)