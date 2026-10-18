import cv2
import numpy as np

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

def _get_optimal_scale(gray, target_h=950.0):
    h = gray.shape[0]
    if h <= 0:
        return 1.0
    scale = target_h / float(h)
    return min(2.0, max(1.0, scale))

def _upscale(gray, scale=None):
    if scale is None:
        scale = _get_optimal_scale(gray)
    else:
        h = gray.shape[0]
        if h * scale > 1100:
            scale = max(1.0, 1100.0 / h)
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

def _denoise(gray):
    return cv2.fastNlMeansDenoising(gray, h=12, templateWindowSize=7, searchWindowSize=21)

# PIPELINE NIK 
def nik_pipe_gentle(gray, quality=None):
    big = _upscale(gray)
    alpha = 1.3
    beta = 10
    if quality:
        if quality['is_dark']:
            alpha, beta = 1.6, 30
        elif quality['is_bright']:
            alpha, beta = 1.1, 0
    return cv2.convertScaleAbs(big, alpha=alpha, beta=beta)

def nik_pipe_otsu(gray, quality=None):
    big = _upscale(gray)
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
    big = _upscale(enhanced)
    _, binary = cv2.threshold(big, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def nik_pipe_adaptive_threshold(gray, quality=None):
    big = _upscale(gray)
    blurred = cv2.GaussianBlur(big, (3, 3), 0)
    block_size = 25 if (quality and quality['is_bright']) else 21
    return cv2.adaptiveThreshold(
        blurred, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, 
        cv2.THRESH_BINARY, block_size, 15
    )

def nik_pipe_sharp_adaptive(gray, quality=None):
    normalized = cv2.normalize(gray, None, 0, 255, cv2.NORM_MINMAX)
    denoised = cv2.bilateralFilter(normalized, 7, 50, 50)
    big = _upscale(denoised)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    block_size = 21 if (quality and quality['is_bright']) else 19
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, block_size, 12
    )

def nik_pipe_scanner_fix(gray, quality=None):
    ksize = 3
    denoised = cv2.medianBlur(gray, ksize)
    big = _upscale(denoised)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    block_size = 21 if (quality and quality['is_bright']) else 19
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, block_size, 12
    )

def nik_pipe_morph_thick(gray, quality=None):
    big = _upscale(gray)
    adjusted = cv2.convertScaleAbs(big, alpha=1.3, beta=10)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.erode(binary, kernel, iterations=1)

def nik_pipe_morph_thin(gray, quality=None):
    big = _upscale(gray)
    adjusted = cv2.convertScaleAbs(big, alpha=1.3, beta=10)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.dilate(binary, kernel, iterations=1)

NIK_PIPELINES = [
    nik_pipe_scanner_fix,
    nik_pipe_gentle,
    nik_pipe_sharp_adaptive,
    nik_pipe_morph_thick,
    nik_pipe_morph_thin
]

# Pipeline Full Text (Nama + DOB + Provinsi)
def text_pipe_standard(gray, quality=None):
    big = _upscale(gray)
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
    big = _upscale(enhanced)
    _, binary = cv2.threshold(big, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_scanner_fix(gray, quality=None):
    ksize = 3
    denoised = cv2.medianBlur(gray, ksize)
    big = _upscale(denoised)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    _, binary = cv2.threshold(sharp, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    return binary

def text_pipe_adaptive_sharpen(gray, quality=None):
    denoised = cv2.medianBlur(gray, 3)
    big = _upscale(denoised)
    strong_sharpen = quality['is_blurry'] if quality else False
    sharp = _sharpen(big, strong=strong_sharpen)
    block_size = 23 if (quality and quality['is_bright']) else 21
    return cv2.adaptiveThreshold(
        sharp, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY, block_size, 15
    )

def text_pipe_morph_thick(gray, quality=None):
    big = _upscale(gray)
    adjusted = cv2.convertScaleAbs(big, alpha=1.4, beta=15)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.erode(binary, kernel, iterations=1)

def text_pipe_morph_thin(gray, quality=None):
    big = _upscale(gray)
    adjusted = cv2.convertScaleAbs(big, alpha=1.4, beta=15)
    _, binary = cv2.threshold(adjusted, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel = np.ones((2, 2), np.uint8)
    return cv2.dilate(binary, kernel, iterations=1)

TEXT_PIPELINES = [
    text_pipe_standard, 
    text_pipe_scanner_fix,
    text_pipe_clahe,
    text_pipe_morph_thick,
    text_pipe_morph_thin
]

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

def detect_and_crop_ktp(gray):
    h, w = gray.shape[:2]
    img_aspect = float(w) / h if h > 0 else 0
    img_area = h * w
    
    # 1. Jika aspek rasio gambar sudah merupakan kartu/frame horizontal KTP (1.30 - 1.90),
    # kamera Kiosk / crop frontend sudah memposisikan KTP dalam frame ini.
    # Jangan potong lagi agar pinggiran NIK dan nama tidak terpotong (menghindari double-cropping).
    if 1.30 <= img_aspect <= 1.90:
        return gray, True
    
    # 2. Untuk gambar foto utuh dari kamera HP / scan dokumen besar (rasio bukan kartu standar):
    blurred = cv2.GaussianBlur(gray, (9, 9), 0)
    corners = [
        blurred[0:15, 0:15],
        blurred[0:15, w-15:w],
        blurred[h-15:h, 0:15],
        blurred[h-15:h, w-15:w]
    ]
    bg_mean = np.mean([np.mean(c) for c in corners])
    
    threshold_vals = [
        int(bg_mean - 20),
        int(bg_mean - 35),
        int(bg_mean - 55),
        int(bg_mean - 70)
    ]
    threshold_vals = [max(90, min(245, val)) for val in threshold_vals]
    for b in [200, 180, 160, 140, 110, 80]:
        if b not in threshold_vals:
            threshold_vals.append(b)
    
    best_cnt = None
    max_area = 0
    
    for threshold_val in threshold_vals:
        _, thresh = cv2.threshold(blurred, threshold_val, 255, cv2.THRESH_BINARY_INV)
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        
        for cnt in contours:
            rect = cv2.minAreaRect(cnt)
            (cx, cy), (cw, ch), angle = rect
            
            if cw == 0 or ch == 0:
                continue
            aspect_ratio = max(cw, ch) / min(cw, ch)
            area = cw * ch
            
            # Kartu KTP memiliki rasio ~1.586. Kontur kartu yang sah harus 1.30 - 1.90
            if 0.20 * img_area < area < 0.96 * img_area:
                if 1.30 <= aspect_ratio <= 1.90:
                    if area > max_area:
                        max_area = area
                        best_cnt = cnt
                        
    if best_cnt is not None:
        rect = cv2.minAreaRect(best_cnt)
        (cx, cy), (cw, ch), angle = rect
        
        if ch > cw:
            angle -= 90
            
        while angle < -45:
            angle += 90
        while angle > 45:
            angle -= 90
            
        if abs(angle) < 2.5:
            x, y, cw_bound, ch_bound = cv2.boundingRect(best_cnt)
            pad = 15
            x1 = max(0, x - pad)
            y1 = max(0, y - pad)
            x2 = min(w, x + cw_bound + pad)
            y2 = min(h, y + ch_bound + pad)
            return gray[y1:y2, x1:x2], True
        else:
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
