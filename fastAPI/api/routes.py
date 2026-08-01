from fastapi import APIRouter, File, UploadFile
import cv2
import numpy as np
from concurrent.futures import ThreadPoolExecutor
from services.image_processing import detect_and_crop_ktp, deskew_by_text
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

        # Menggunakan Blue Channel agar background KTP menjadi sangat putih/terang
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

