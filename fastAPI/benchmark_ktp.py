"""
Benchmark Akurasi & Latensi OCR KTP (MyMember Universal OCR Engine)
Menghitung exact-match rate untuk NIK & Nama, plus latensi per gambar.
"""
import os
import sys
import time
import json
import glob
import cv2

sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from services.image_processing import detect_and_warp_ktp_canonical
from services.ocr_engine import extract_ktp_universal

def run_benchmark():
    test_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'test_ktp_images')
    if not os.path.exists(test_dir):
        print(f"Folder test '{test_dir}' tidak ditemukan. Buat folder dan letakkan foto KTP di dalamnya.")
        return

    exts = ('*.jpg', '*.jpeg', '*.png', '*.webp')
    image_paths = []
    for ext in exts:
        image_paths.extend(glob.glob(os.path.join(test_dir, ext)))

    if not image_paths:
        print(f"Belum ada foto KTP di '{test_dir}'.")
        print("Silakan masukkan foto KTP dengan format nama: {NIK}_{NAMA_LENGKAP}.jpg")
        print("Contoh: 3201012304950001_BUDI SANTOSO.jpg")
        return

    # Load ground truth jika ada
    gt_file = os.path.join(test_dir, 'ground_truth.json')
    gt_map = {}
    if os.path.exists(gt_file):
        try:
            with open(gt_file, 'r', encoding='utf-8') as f:
                gt_map = json.load(f)
        except Exception as e:
            print("Gagal membaca ground_truth.json:", e)

    print(f"\n=======================================================")
    print(f"  BENCHMARK AKURASI OCR KTP (Total: {len(image_paths)} foto)")
    print(f"=======================================================\n")

    total = 0
    nik_correct = 0
    nama_correct = 0
    full_correct = 0
    latencies = []

    for img_path in sorted(image_paths):
        filename = os.path.basename(img_path)
        img = cv2.imread(img_path)
        if img is None:
            print(f"[SKIP] {filename} (Gagal membaca gambar)")
            continue

        total += 1
        gt_nik = None
        gt_nama = None

        if filename in gt_map:
            gt_nik = gt_map[filename].get('nik')
            gt_nama = gt_map[filename].get('nama')
        else:
            base, _ = os.path.splitext(filename)
            parts = base.split('_', 1)
            if len(parts) == 2 and len(parts[0]) == 16 and parts[0].isdigit():
                gt_nik = parts[0]
                gt_nama = parts[1].strip().upper()

        t0 = time.time()
        canonical, detected = detect_and_warp_ktp_canonical(img)
        res = extract_ktp_universal(canonical)
        dur = (time.time() - t0) * 1000.0
        latencies.append(dur)

        pred_nik = res.get('nik', '')
        pred_nama = res.get('nama', '')
        status = res.get('status', '')

        is_nik_match = (gt_nik is not None and pred_nik == gt_nik)
        is_nama_match = (gt_nama is not None and pred_nama.upper() == gt_nama.upper())

        if is_nik_match:
            nik_correct += 1
        if is_nama_match:
            nama_correct += 1
        if is_nik_match and is_nama_match:
            full_correct += 1

        print(f"[{status.upper():10s}] {filename[:30]:30s} | {dur:6.1f}ms")
        print(f"  NIK   -> Pred: {pred_nik:16s} | GT: {str(gt_nik):16s} | {'MATCH' if is_nik_match else 'DIFF'}")
        print(f"  NAMA  -> Pred: {pred_nama:25s} | GT: {str(gt_nama):25s} | {'MATCH' if is_nama_match else 'DIFF'}")
        print(f"  Info  -> Conf: {res.get('confidence', 0):.2f} | Kab: {res.get('metadata_wilayah', {}).get('kab_kota')}\n")

    avg_lat = sum(latencies) / len(latencies) if latencies else 0
    p95_lat = sorted(latencies)[int(len(latencies) * 0.95)] if latencies else 0

    print("=======================================================")
    print("  RINGKASAN AKURASI:")
    print("=======================================================")
    print(f"Total Diuji          : {total} foto")
    if total > 0:
        print(f"NIK Match Rate       : {nik_correct}/{total} ({nik_correct/total*100:.2f}%)")
        print(f"Nama Match Rate      : {nama_correct}/{total} ({nama_correct/total*100:.2f}%)")
        print(f"Full Exact Match     : {full_correct}/{total} ({full_correct/total*100:.2f}%)")
    print(f"Rata-rata Latensi    : {avg_lat:.1f} ms")
    print(f"P95 Latensi          : {p95_lat:.1f} ms")
    print("=======================================================\n")

if __name__ == "__main__":
    run_benchmark()
