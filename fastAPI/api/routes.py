from fastapi import APIRouter, File, UploadFile
import io
import cv2
import numpy as np
from PIL import Image, ImageOps
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
        
        # 1. Decode dengan dukungan Auto-Transpose EXIF (mengoreksi rotasi sensor kamera HP)
        try:
            pil_img = Image.open(io.BytesIO(contents))
            pil_img = ImageOps.exif_transpose(pil_img)
            if pil_img.mode != 'RGB':
                pil_img = pil_img.convert('RGB')
            img = cv2.cvtColor(np.array(pil_img), cv2.COLOR_RGB2BGR)
        except Exception:
            nparr = np.frombuffer(contents, np.uint8)
            img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return {"status": "error", "pesan": "File gambar tidak valid atau corrupt."}

        # 2. Standarisasi orientasi & dimensi:
        # - Jika portrait (Tinggi > Lebar), otomatis diputar 90 derajat ke Landscape.
        # - Kunci skala ke lebar ideal 1200px (stabil untuk HP, Tablet & Webcam).
        img = normalize_ktp_image(img, target_width=1200)

        # 3. Hybrid Blue Channel Preprocessing:
        # Memanfaatkan keunggulan Blue Channel (memutihkan background cyan/biru e-KTP)
        # dipadukan dengan CLAHE jika kondisi cahaya lampu kuning atau remang.
        gray = get_adaptive_ktp_gray(img)

        # 4. Deteksi dan potong batas kartu KTP
        card_gray, detected = detect_and_crop_ktp(gray)
        if not detected:
            card_gray, _ = deskew_by_text(gray)

        # 5. Ekstraksi Paralel NIK dan Full-Text (2 worker pas untuk 2 vCPU VPS)
        with ThreadPoolExecutor(max_workers=2) as executor:
            future_nik = executor.submit(extract_nik_fast, card_gray, detected)
            future_text = executor.submit(extract_fulltext, card_gray)

            nik_candidates = future_nik.result()
            nama, dob_digits, provinsi_code, raw_text = future_text.result()

        # 6. Rekonstruksi NIK Cerdas dengan Cross-Validation Tanggal Lahir & Provinsi
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
