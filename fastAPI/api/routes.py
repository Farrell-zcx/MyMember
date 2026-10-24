from fastapi import APIRouter, File, UploadFile
from typing import Optional, List
import io
import cv2
import numpy as np
from PIL import Image, ImageOps
from services.image_processing import detect_and_warp_ktp_canonical
from services.ocr_engine import extract_ktp_universal

router = APIRouter()

def _decode_image_bytes(contents: bytes):
    try:
        pil_img = Image.open(io.BytesIO(contents))
        pil_img = ImageOps.exif_transpose(pil_img)
        if pil_img.mode != 'RGB':
            pil_img = pil_img.convert('RGB')
        return cv2.cvtColor(np.array(pil_img), cv2.COLOR_RGB2BGR)
    except Exception:
        nparr = np.frombuffer(contents, np.uint8)
        return cv2.imdecode(nparr, cv2.IMREAD_COLOR)

@router.post("/extract-ktp")
async def extract_ktp(
    ktp_image: Optional[UploadFile] = File(None),
    ktp_images: Optional[List[UploadFile]] = File(None)
):
    try:
        files_to_process = []
        if isinstance(ktp_images, (list, tuple)) and len(ktp_images) > 0:
            files_to_process.extend(ktp_images)
        elif ktp_image is not None and hasattr(ktp_image, 'read'):
            files_to_process.append(ktp_image)

        if not files_to_process:
            return {"status": "error", "pesan": "Tidak ada file gambar KTP yang diunggah."}

        best_result = None
        main_filename = files_to_process[0].filename

        for file_item in files_to_process:
            contents = await file_item.read()
            img = _decode_image_bytes(contents)
            if img is None:
                continue

            # Universal OCR Pipeline (Dual-Ratio 9:16 & 16:9, Multi-Stream RapidOCR + Kemendagri)
            result = extract_ktp_universal(img)

            # Jika berhasil valid, langsung return hasil terbaik
            if result.get("status") == "sukses":
                best_result = result
                main_filename = file_item.filename
                break
            elif best_result is None or (result.get("confidence", 0) > best_result.get("confidence", 0)):
                best_result = result
                main_filename = file_item.filename

        if best_result is None:
            return {"status": "error", "pesan": "File gambar tidak valid atau corrupt."}

        return {
            "status": best_result.get("status", "sukses"),
            "nama_file": main_filename,
            "data_ktp": {
                "nik": best_result.get("nik", "Tidak terdeteksi"),
                "nama": best_result.get("nama", "Tidak terdeteksi"),
                "kab_kota": best_result.get("metadata_wilayah", {}).get("kab_kota"),
                "provinsi": best_result.get("metadata_wilayah", {}).get("provinsi_kode")
            },
            "confidence": best_result.get("confidence", 0.0),
            "alasan": best_result.get("alasan"),
            "hasil_bacaan_mentah": best_result.get("raw_text", "")
        }

    except Exception as e:
        return {
            "status": "error",
            "pesan": f"Gagal memproses gambar: {str(e)}"
        }
