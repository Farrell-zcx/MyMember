import cv2
import numpy as np

# ==========================================================
# GEOMETRI & PERSPECTIVE WARP KANONIK (1712 x 1080)
# ==========================================================

def detect_and_warp_ktp_canonical(img, target_w=1712, target_h=1080):
    """
    Normalisasi Geometri KTP Resolusi Penuh:
    1. Pastikan orientasi mendatar (Landscape: W > H).
    2. Deteksi 4 sudut fisik kartu KTP pada resolusi penuh (Gray + Saturation).
    3. Lakukan Perspective Warp ke rasio standar e-KTP (1712 x 1080 piksel).
    4. Fallback anggun jika kartu sudah terpotong (aspect ratio 1.40 - 1.80).
    """
    if img is None:
        return None, False

    h, w = img.shape[:2]
    if w <= 0 or h <= 0:
        return img, False

    # 1. Koreksi orientasi jika vertikal/potret
    if h > w:
        img = cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
        h, w = img.shape[:2]

    aspect = float(w) / float(h)

    # 2. Jika foto sudah terpotong kartu (misal dari kiosk frame 1.40 - 1.80)
    if 1.40 <= aspect <= 1.80:
        warped = cv2.resize(img, (target_w, target_h), interpolation=cv2.INTER_CUBIC)
        return warped, True

    # 3. Deteksi kontur kartu pada resolusi tinggi menggunakan Gray + Saturation
    if len(img.shape) == 3:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)
        sat = hsv[:, :, 1]
        combined = cv2.addWeighted(gray, 0.6, sat, 0.4, 0)
    else:
        gray = img
        combined = gray

    blurred = cv2.GaussianBlur(combined, (7, 7), 0)
    edges = cv2.Canny(blurred, 40, 130)
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (9, 9))
    closed = cv2.morphologyEx(edges, cv2.MORPH_CLOSE, kernel)

    contours, _ = cv2.findContours(closed, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    img_area = float(h * w)
    best_quad = None
    max_area = 0

    for cnt in contours:
        area = cv2.contourArea(cnt)
        if area < 0.15 * img_area or area > 0.98 * img_area:
            continue
        peri = cv2.arcLength(cnt, True)
        approx = cv2.approxPolyDP(cnt, 0.03 * peri, True)
        if len(approx) == 4:
            rect = cv2.minAreaRect(cnt)
            (cx, cy), (cw, ch), angle = rect
            if cw == 0 or ch == 0:
                continue
            r_aspect = max(cw, ch) / min(cw, ch)
            if 1.30 <= r_aspect <= 1.95:
                if area > max_area:
                    max_area = area
                    best_quad = approx.reshape(4, 2)

    if best_quad is not None:
        def order_points(pts):
            rect_ordered = np.zeros((4, 2), dtype="float32")
            s = pts.sum(axis=1)
            rect_ordered[0] = pts[np.argmin(s)]
            rect_ordered[2] = pts[np.argmax(s)]
            diff = np.diff(pts, axis=1)
            rect_ordered[1] = pts[np.argmin(diff)]
            rect_ordered[3] = pts[np.argmax(diff)]
            return rect_ordered

        src_pts = order_points(best_quad.astype("float32"))
        dst_pts = np.array([
            [0, 0],
            [target_w - 1, 0],
            [target_w - 1, target_h - 1],
            [0, target_h - 1]
        ], dtype="float32")

        M = cv2.getPerspectiveTransform(src_pts, dst_pts)
        warped = cv2.warpPerspective(img, M, (target_w, target_h), flags=cv2.INTER_CUBIC)
        return warped, True

    # Fallback jika kontur 4 sudut tidak terdeteksi sempurna
    warped = cv2.resize(img, (target_w, target_h), interpolation=cv2.INTER_CUBIC)
    return warped, False

# ==========================================================
# EKSTRAKSI REGION OF INTEREST (ROI) KTP KANONIK
# ==========================================================

def extract_ktp_rois(canonical_bgr):
    """
    Memotong field-field spesifik pada kartu kanonik 1712x1080.
    Setiap field memiliki resolusi vertikal tinggi (~40-60 px tinggi huruf).
    """
    h, w = canonical_bgr.shape[:2]
    return {
        'nik': canonical_bgr[int(h * 0.15):int(h * 0.32), int(w * 0.14):int(w * 0.85)],
        'nama': canonical_bgr[int(h * 0.28):int(h * 0.42), int(w * 0.20):int(w * 0.85)],
        'ttl': canonical_bgr[int(h * 0.38):int(h * 0.50), int(w * 0.20):int(w * 0.85)],
        'header': canonical_bgr[0:int(h * 0.20), int(w * 0.10):int(w * 0.90)],
        'full': canonical_bgr
    }

def generate_roi_variants(roi_bgr):
    """
    Menghasilkan 4 varian filter khusus per-ROI untuk mengatasi berbagai kondisi pencahayaan:
    1. raw_bgr: Warna asli tajam untuk RapidOCR deep-learning.
    2. blue_enhanced: 75% Blue Channel (memutihkan background KTP).
    3. clahe_bgr & clahe_gray: Peningkatan kontras lokal untuk ruangan remang/kuning.
    4. glare_thresh: Adaptive Gaussian Thresholding kebal pantulan cahaya hologram/lampu.
    5. scanner_otsu: Contrast stretch + Otsu untuk teks bersih.
    """
    variants = {}
    if roi_bgr is None or roi_bgr.size == 0:
        return variants

    variants['raw_bgr'] = roi_bgr

    if len(roi_bgr.shape) == 3:
        b, g, r = cv2.split(roi_bgr)
        std_gray = cv2.cvtColor(roi_bgr, cv2.COLOR_BGR2GRAY)
        blue_enhanced = cv2.addWeighted(b, 0.75, std_gray, 0.25, 0)
    else:
        std_gray = roi_bgr
        blue_enhanced = roi_bgr

    variants['blue_enhanced'] = blue_enhanced

    # Varian CLAHE (LAB Space)
    if len(roi_bgr.shape) == 3:
        lab = cv2.cvtColor(roi_bgr, cv2.COLOR_BGR2LAB)
        l, a, b_ch = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        l_clahe = clahe.apply(l)
        lab_clahe = cv2.merge((l_clahe, a, b_ch))
        variants['clahe_bgr'] = cv2.cvtColor(lab_clahe, cv2.COLOR_LAB2BGR)
        variants['clahe_gray'] = l_clahe
    else:
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8, 8))
        variants['clahe_gray'] = clahe.apply(std_gray)

    # Varian Anti-Glare (Local Adaptive Thresholding)
    gaussian = cv2.GaussianBlur(blue_enhanced, (0, 0), 2.0)
    sharp = cv2.addWeighted(blue_enhanced, 1.5, gaussian, -0.5, 0)
    variants['glare_thresh'] = cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, 25, 14
    )

    # Varian Scanner Contrast
    denoised = cv2.medianBlur(blue_enhanced, 3)
    norm = cv2.normalize(denoised, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX)
    _, binary = cv2.threshold(norm, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    variants['scanner_otsu'] = binary

    return variants

# ==========================================================
# QUALITY ASSESSMENT & RETAKE GUIDANCE
# ==========================================================

def assess_image_quality(img):
    """
    Evaluasi Kualitas Gambar:
    Mendeteksi silau (glare), blur/goyang (Laplacian variance), dan kegelapan.
    Jika kualitas terlalu buruk untuk OCR 100%, sistem menghasilkan instruksi retake yang ramah pengguna.
    """
    if img is None:
        return {'is_acceptable': False}, "Gambar tidak valid atau kosong."

    if len(img.shape) == 3:
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)
        v = hsv[:, :, 2]
        s = hsv[:, :, 1]
        glare_pixels = np.sum((v > 245) & (s < 30))
        glare_ratio = float(glare_pixels) / float(v.size)
    else:
        gray = img
        glare_ratio = float(np.sum(gray > 250)) / float(gray.size)

    mean_brightness = cv2.mean(gray)[0]
    laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()

    quality = {
        'brightness': mean_brightness,
        'blur_var': laplacian_var,
        'glare_ratio': glare_ratio,
        'is_dark': mean_brightness < 75,
        'is_blurry': laplacian_var < 100,
        'is_severe_glare': glare_ratio > 0.12,
        'is_acceptable': True
    }

    advisory = None
    if quality['is_severe_glare']:
        quality['is_acceptable'] = False
        advisory = "Pantulan cahaya (silau) menutupi bagian KTP. Miringkan sedikit kartu atau jauhkan dari pantulan lampu langsung."
    elif quality['is_blurry']:
        quality['is_acceptable'] = False
        advisory = "Foto KTP buram / tidak fokus. Pegang kamera lebih tenang dan pastikan tulisan tampak tajam."
    elif quality['is_dark']:
        quality['is_acceptable'] = False
        advisory = "Pencahayaan terlalu gelap. Dekatkan KTP ke sumber cahaya yang merata."

    return quality, advisory

# ==========================================================
# BACKWARD COMPATIBILITY ALIASES & HELPERS
# ==========================================================

def normalize_ktp_image(img, target_width=1200):
    if img is None:
        return None
    h, w = img.shape[:2]
    if w <= 0 or h <= 0:
        return img
    if h > w:
        img = cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
        h, w = img.shape[:2]
    if w > 1300:
        scale = target_width / float(w)
        new_w = target_width
        new_h = int(h * scale)
        return cv2.resize(img, (new_w, new_h), interpolation=cv2.INTER_AREA)
    elif w < 900:
        scale = target_width / float(w)
        new_w = target_width
        new_h = int(h * scale)
        return cv2.resize(img, (new_w, new_h), interpolation=cv2.INTER_CUBIC)
    return img

def get_adaptive_ktp_gray(bgr_img):
    if bgr_img is None:
        return None
    b, g, r = cv2.split(bgr_img)
    std_gray = cv2.cvtColor(bgr_img, cv2.COLOR_BGR2GRAY)
    blue_enhanced = cv2.addWeighted(b, 0.75, std_gray, 0.25, 0)
    mean_b = float(np.mean(b))
    mean_r = float(np.mean(r))
    mean_lum = float(np.mean(std_gray))
    if mean_lum < 110 or mean_r > mean_b:
        clahe = cv2.createCLAHE(clipLimit=2.2, tileGridSize=(8, 8))
        return clahe.apply(blue_enhanced)
    return blue_enhanced

def _analyze_image_quality(gray):
    mean_brightness = cv2.mean(gray)[0]
    laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
    return {
        'is_dark': mean_brightness < 90,
        'is_bright': mean_brightness > 165,
        'is_blurry': laplacian_var < 250,
        'brightness': mean_brightness,
        'blur_var': laplacian_var
    }

def nik_pipe_adaptive_glare(gray, quality=None):
    gaussian = cv2.GaussianBlur(gray, (0, 0), 2.0)
    sharp = cv2.addWeighted(gray, 1.5, gaussian, -0.5, 0)
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, 25, 14
    )

def nik_pipe_scanner_contrast(gray, quality=None):
    denoised = cv2.medianBlur(gray, 3)
    norm = cv2.normalize(denoised, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX)
    _, binary = cv2.threshold(norm, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def nik_pipe_scanner_fix(gray, quality=None):
    return nik_pipe_scanner_contrast(gray, quality)

def nik_pipe_gentle(gray, quality=None):
    adjusted = cv2.convertScaleAbs(gray, alpha=1.3, beta=10)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def nik_pipe_sharp_adaptive(gray, quality=None):
    return nik_pipe_adaptive_glare(gray, quality)

def nik_pipe_morph_thick(gray, quality=None):
    b = nik_pipe_scanner_contrast(gray, quality)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.erode(b, kernel, iterations=1)

def nik_pipe_morph_thin(gray, quality=None):
    b = nik_pipe_scanner_contrast(gray, quality)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.dilate(b, kernel, iterations=1)

NIK_PIPELINES = [
    nik_pipe_adaptive_glare,
    nik_pipe_scanner_contrast
]

def text_pipe_enhanced(gray, quality=None):
    adjusted = cv2.convertScaleAbs(gray, alpha=1.35, beta=10)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_standard(gray, quality=None):
    return text_pipe_enhanced(gray, quality)

def text_pipe_clahe(gray, quality=None):
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    _, binary = cv2.threshold(enhanced, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_scanner_fix(gray, quality=None):
    return nik_pipe_scanner_contrast(gray, quality)

TEXT_PIPELINES = [
    text_pipe_enhanced,
    text_pipe_clahe
]

def detect_and_crop_ktp(gray):
    # Disesuaikan menggunakan canonical perspective warp
    if len(gray.shape) == 2:
        bgr = cv2.cvtColor(gray, cv2.COLOR_GRAY2BGR)
    else:
        bgr = gray
    warped, ok = detect_and_warp_ktp_canonical(bgr)
    return cv2.cvtColor(warped, cv2.COLOR_BGR2GRAY), ok

def deskew_by_text(gray):
    return gray, False
