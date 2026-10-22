import cv2
import numpy as np

def normalize_ktp_image(img, target_width=1200):
    """
    Normalisasi Ukuran & Orientasi Universal:
    - Jika foto HP berorientasi potret (Tinggi > Lebar), otomatis diputar 90 derajat searah jarum jam ke landscape.
    - Standarisasi lebar gambar ke target ideal ~1200px (standar emas untuk OCR Tesseract):
      * Foto HP raksasa (3000px+) di-downscale dengan INTER_AREA (noise sensor lenyap, garis tajam).
      * Webcam resolusi rendah (<900px) di-upscale dengan INTER_CUBIC (huruf membesar ke ukuran ideal).
    """
    if img is None:
        return None
    h, w = img.shape[:2]
    if w <= 0 or h <= 0:
        return img
    
    # 1. Pastikan orientasi dokumen mendatar (Landscape: W > H)
    if h > w:
        img = cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
        h, w = img.shape[:2]

    # 2. Standarisasi ke target lebar ideal
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
    """
    Hybrid Adaptive Blue Channel Preprocessing:
    - 75% Blue Channel (memutihkan latar belakang cyan/biru e-KTP Indonesia secara alami).
    - 25% Standar Grayscale (menjaga kestabilan kontur tepi ukiran laser hitam).
    - Adaptif CLAHE jika pencahayaan ruangan remang/gelap atau lampu kuning.
    """
    if bgr_img is None:
        return None
    b, g, r = cv2.split(bgr_img)
    std_gray = cv2.cvtColor(bgr_img, cv2.COLOR_BGR2GRAY)
    
    # Inti keunggulan KTP: Blue Channel memutihkan background biru KTP secara instan
    blue_enhanced = cv2.addWeighted(b, 0.75, std_gray, 0.25, 0)
    
    mean_b = float(np.mean(b))
    mean_r = float(np.mean(r))
    mean_lum = float(np.mean(std_gray))
    
    # Jika ruangan redup (mean_lum < 110) atau lampu kuning (R > B):
    # terapkan CLAHE untuk menaikkan kontras lokal huruf tanpa membuat noise
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

def _upscale(gray, scale=None):
    h = gray.shape[0]
    if h >= 800:
        return gray
    scale = min(1.8, max(1.0, 900.0 / float(h)))
    if scale <= 1.05:
        return gray
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

# ==========================================================
# PIPELINE NIK TARGETED (DUAL-STREAM)
# ==========================================================

def nik_pipe_adaptive_glare(gray, quality=None):
    """
    Stream 1: Kebal Pantulan Cahaya (Anti-Glare) & Bayangan
    - Unsharp masking menegaskan garis angka yang buram / goyang.
    - Adaptive Gaussian Thresholding per-block 25x25 piksel:
      Pantulan cahaya lampu / hologram tidak akan memutihkan angka di sebelahnya.
    """
    # Unsharp Masking
    gaussian = cv2.GaussianBlur(gray, (0, 0), 2.0)
    sharp = cv2.addWeighted(gray, 1.5, gaussian, -0.5, 0)
    
    # Local Adaptive Thresholding
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, 25, 14
    )

def nik_pipe_scanner_contrast(gray, quality=None):
    """
    Stream 2: Optimal untuk Tablet Kiosk, Webcam & Foto Normal
    - Median blur mereduksi noise bintik sensor kamera.
    - Contrast stretch & Otsu thresholding untuk teks hitam pekat di atas putih.
    """
    denoised = cv2.medianBlur(gray, 3)
    norm = cv2.normalize(denoised, None, alpha=0, beta=255, norm_type=cv2.NORM_MINMAX)
    _, binary = cv2.threshold(norm, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

# Aliases untuk kompatibilitas
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

# ==========================================================
# PIPELINE FULL TEXT (NAMA, TGL LAHIR, PROVINSI)
# ==========================================================

def text_pipe_enhanced(gray, quality=None):
    alpha = 1.35
    beta = 10
    adjusted = cv2.convertScaleAbs(gray, alpha=alpha, beta=beta)
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

# ==========================================================
# DETEKSI DAN CROP KARTU KTP
# ==========================================================

def detect_and_crop_ktp(gray):
    """
    Deteksi kartu KTP cerdas dengan toleransi sudut miring & rasio KTP (1.30 - 1.90)
    """
    h, w = gray.shape[:2]
    img_aspect = float(w) / h if h > 0 else 0
    img_area = h * w

    # Jika foto sudah terpotong kartu (misal dari Kiosk auto-crop atau crop manual)
    if 1.35 <= img_aspect <= 1.85:
        return gray, True

    # Jika foto masih mencakup meja / dompet / tangan di sekitar KTP:
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    edges = cv2.Canny(blurred, 50, 150)
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (7, 7))
    closed = cv2.morphologyEx(edges, cv2.MORPH_CLOSE, kernel)
    
    contours, _ = cv2.findContours(closed, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    
    best_cnt = None
    max_area = 0
    for cnt in contours:
        rect = cv2.minAreaRect(cnt)
        (cx, cy), (cw, ch), angle = rect
        if cw == 0 or ch == 0:
            continue
        aspect = max(cw, ch) / min(cw, ch)
        area = cw * ch
        
        # Kartu KTP harus mencakup minimal 20% dan maksimal 95% dari frame
        if 0.20 * img_area < area < 0.95 * img_area:
            if 1.30 <= aspect <= 1.95:
                if area > max_area:
                    max_area = area
                    best_cnt = cnt
                    
    if best_cnt is not None:
        rect = cv2.minAreaRect(best_cnt)
        (cx, cy), (cw, ch), angle = rect
        
        # Warp perspective untuk meluruskan kartu jika sedikit miring
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
            
        dst_pts = np.array([
            [0, 0],
            [max_width - 1, 0],
            [max_width - 1, max_height - 1],
            [0, max_height - 1]
        ], dtype="float32")

        M = cv2.getPerspectiveTransform(src_pts, dst_pts)
        warped = cv2.warpPerspective(gray, M, (max_width, max_height), flags=cv2.INTER_CUBIC)
        return warped, True

    if 1.30 <= img_aspect <= 1.90:
        return gray, True

    return gray, False

def deskew_by_text(gray):
    thresh = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY_INV, 21, 15)
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (35, 3))
    dilated = cv2.dilate(thresh, kernel, iterations=1)
    
    contours, _ = cv2.findContours(dilated, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    
    angles = []
    for cnt in contours:
        rect = cv2.minAreaRect(cnt)
        (cx, cy), (cw, ch), angle = rect
        
        if cw > 0 and ch > 0:
            aspect_ratio = max(cw, ch) / min(cw, ch)
            if aspect_ratio > 3.0 and cw * ch > 200:
                if cw < ch:
                    angle -= 90
                if -45 < angle < 45:
                    angles.append(angle)
                    
    if angles:
        median_angle = float(np.median(angles))
        if abs(median_angle) > 1.5:
            scale = 1.5
            big_gray = cv2.resize(gray, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)
            (h, w) = big_gray.shape[:2]
            center = (w // 2, h // 2)
            M = cv2.getRotationMatrix2D(center, median_angle, 1.0)
            
            cos_a = np.abs(M[0, 0])
            sin_a = np.abs(M[0, 1])
            new_w = int((h * sin_a) + (w * cos_a))
            new_h = int((h * cos_a) + (w * sin_a))
            M[0, 2] += (new_w / 2) - center[0]
            M[1, 2] += (new_h / 2) - center[1]
            
            rotated_big = cv2.warpAffine(big_gray, M, (new_w, new_h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REPLICATE)
            rotated = cv2.resize(rotated_big, (int(new_w/scale), int(new_h/scale)), interpolation=cv2.INTER_AREA)
            return rotated, True
            
    return gray, False
