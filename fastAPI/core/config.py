import os
from dotenv import load_dotenv

# Load .env file
load_dotenv()

class Settings:
    TESSERACT_CMD_PATH: str = os.getenv("TESSERACT_CMD_PATH", r"D:\Tesseract-OCR\tesseract.exe")

settings = Settings()
