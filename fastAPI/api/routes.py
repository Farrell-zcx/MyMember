from fastapi import APIRouter, File, UploadFile
import cv2
import numpy as np
from concurrent.futures import ThreadPoolExecutor
from services.image_processing import (
    normalize_ktp_image, get_adaptive_ktp_gray,
    detect_and_crop_ktp, deskew_by_text
)
from services.ocr_engine import extract_nik_fast, extract_fulltext, _reconstruct_nik

router = APIRouter()

@router.post("/extract-ktp")
async def extract_ktp(ktp_image: UploadFile = File(...)):
    try:
        contents = await ktp_image.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return {"status": "error", "pesan": "File gambar tidak valid atau corrupt."}

        # 1. Normalisasi ukuran cerdas: Downscale hanya jika resolusi raksasa (>1280px),
        # biarkan resolusi asli jika sudah pas (800-1280px).
        img = normalize_ktp_image(img, max_width=1280, max_height=900)

        # 2. Hybrid Adaptive Preprocessing:
        # Mempertahankan keunggulan Blue Channel (memutihkan background biru KTP),
        # dan adaptif dengan Grayscale + CLAHE jika pencahayaan ruangan kuning / remang-remang.
        gray = get_adaptive_ktp_gray(img)

        # Deteksi dan potong area kartu KTP untuk mereduksi noise latar belakang
        card_gray, detected = detect_and_crop_ktp(gray)
        
        if not detected:
            # Jika gagal mendeteksi kotak kartu (misal krn background rumit/dompet),
            # langsung gunakan mode penyelamat (Text-based Deskew)
            card_gray, _ = deskew_by_text(gray)

        # NIK dan full-text diproses PARALEL (max_workers=2 pas dengan 2 vCPU VPS)
        with ThreadPoolExecutor(max_workers=2) as executor:
            future_nik = executor.submit(extract_nik_fast, card_gray, detected)
            future_text = executor.submit(extract_fulltext, card_gray)

            nik_candidates = future_nik.result()
            nama, dob_digits, provinsi_code, raw_text = future_text.result()

        # Rekonstruksi NIK dengan cross-validation & joint voting
        nik = _reconstruct_nik(nik_candidates, dob_digits, provinsi_code)

        # Fallback terakhir: Hanya jika NIK dan Nama benar-benar tidak terdeteksi
        # (kemungkinan KTP ter-crop salah oleh detect_and_crop_ktp).
        if detected and (nik == "Tidak terdeteksi" and (not nama or nama == "Tidak terdeteksi")):
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

