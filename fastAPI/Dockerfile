# ============================================
# MyMember - OCR Microservice (FastAPI + Tesseract)
# ============================================
FROM python:3.10-slim

# Install Tesseract OCR + dependencies OpenCV
RUN apt-get update && apt-get install -y \
    tesseract-ocr \
    tesseract-ocr-ind \
    libgl1 \
    libglib2.0-0 \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Install Python dependencies
RUN pip install --no-cache-dir \
    fastapi \
    uvicorn[standard] \
    python-multipart \
    python-dotenv \
    opencv-python-headless \
    numpy \
    pytesseract

# Copy kode aplikasi
COPY . .

# Set environment variable Tesseract path untuk Linux
ENV TESSERACT_CMD_PATH=/usr/bin/tesseract

EXPOSE 8000

# Jalankan FastAPI dengan Uvicorn
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
