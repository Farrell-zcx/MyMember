<p align="center">
  <img src="public/images/logo.png" alt="MyMember Logo" width="80" />
</p>

<h1 align="center">MyMember</h1>

<p align="center">
  <strong>Membership Management System with OCR & IoT Kiosk Integration</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white" alt="CodeIgniter 4" />
  <img src="https://img.shields.io/badge/FastAPI-0.100+-009688?style=for-the-badge&logo=fastapi&logoColor=white" alt="FastAPI" />
  <img src="https://img.shields.io/badge/Tesseract-OCR-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Tesseract OCR" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/Python-3.10+-3776AB?style=for-the-badge&logo=python&logoColor=white" alt="Python 3.10+" />
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/TailwindCSS-CDN-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT" />
</p>

<p align="center">
  <em>Sistem manajemen keanggotaan berbasis web yang mengintegrasikan OCR (Optical Character Recognition) untuk check-in otomatis melalui pemindaian KTP, remote kiosk control via tablet, live camera monitoring, serta notifikasi email otomatis untuk perpanjangan membership.</em>
</p>

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Configuration](#configuration)
- [Usage Guide](#usage-guide)
- [API Reference](#api-reference)
- [Scheduling Email Reminders](#scheduling-email-reminders)
- [License](#license)

---

## Overview

**MyMember** adalah aplikasi full stack yang dirancang untuk mengotomasi seluruh proses manajemen keanggotaan di tempat usaha (gym, coworking space, club, dll). Sistem ini menggantikan proses check-in manual dengan **pemindaian KTP otomatis** menggunakan kamera tablet kiosk yang dikendalikan secara remote oleh admin/resepsionis.

### Permasalahan yang Diselesaikan

| Masalah | Solusi MyMember |
|---------|----------------|
| Check-in manual lambat & rawan error | OCR otomatis baca NIK & Nama dari KTP |
| Admin tidak bisa pantau kiosk real-time | Live camera feed + remote capture trigger |
| Member lupa perpanjang membership | Email reminder otomatis H-2 sebelum expired |
| Laporan kunjungan sulit dibuat | Export ke Excel & PDF dengan satu klik |
| Registrasi member baru memakan waktu | Auto-fill data dari hasil scan KTP |

---

## Key Features

### Smart OCR Check-in
- Pemindaian KTP otomatis via kamera tablet
- Multi-pipeline preprocessing (Blue Channel, CLAHE, Adaptive Threshold, Morphology)
- Consensus voting per-digit untuk akurasi NIK maksimal
- Cross-validation NIK dengan tanggal lahir dan kode provinsi
- Tabel koreksi karakter OCR (O→0, I→1, S→5, dll)

### Remote Kiosk Control
- Admin mengirim sinyal capture ke tablet kiosk dari panel desktop
- Cache based signaling ringan, tanpa infrastruktur tambahan
- Tablet kiosk polling setiap 1 detik untuk menunggu instruksi

### Live Kiosk Camera Feed
- AJAX Polling berbasis JPEG Frame Relay (~15 FPS)
- Auto-reconnect saat kiosk offline
- Indikator status koneksi real-time

### H-2 Email Reminder
- Notifikasi otomatis 2 hari sebelum membership kedaluwarsa
- Template email HTML profesional dengan branding
- Flag anti-duplikasi (`reminder_terkirim`) yang auto-reset saat data di-update
- Mendukung SMTP (Gmail, Mailtrap, atau provider lainnya)

### Dashboard & Reporting
- Dashboard ringkasan dengan live stats (AJAX polling)
- Riwayat kunjungan dengan filter tanggal
- Export ke **Excel (.xlsx)** via PhpSpreadsheet
- Export ke **PDF** via mPDF
- Log kunjungan real-time (auto-update tanpa refresh)

### Membership Management
- CRUD member dengan soft-delete
- Master tipe member (kuota & masa aktif otomatis)
- Auto-deduct kuota saat check-in
- Auto-assign kuota & expired date dari master type saat registrasi
- Validasi status: Aktif, Kuota Habis, Expired, Belum Terdaftar

### Authentication
- Login & registrasi admin/resepsionis
- Password hashing dengan bcrypt (`PASSWORD_DEFAULT`)
- Session-based authentication
- CSRF protection pada semua form

---

## Technology Stack

### Backend
| Technology | Purpose |
|-----------|---------|
| **CodeIgniter 4** | PHP MVC Framework utama |
| **FastAPI (Python)** | OCR Microservice — async HTTP server |
| **Tesseract OCR** | Engine ekstraksi teks dari gambar KTP |
| **OpenCV (cv2)** | Image preprocessing & manipulation |
| **MySQL / MariaDB** | Relational database |
| **PhpSpreadsheet** | Export data ke Excel (.xlsx) |
| **mPDF** | Export data ke PDF |

### Frontend
| Technology | Purpose |
|-----------|---------|
| **Tailwind CSS (CDN)** | Utility-first CSS framework |
| **Liquidglass Theme** | Custom glassmorphism UI theme |
| **jQuery 3.6** | DOM manipulation & AJAX |
| **SweetAlert2** | Beautiful alert/dialog modals |
| **Material Symbols** | Google icon set |
| **Hanken Grotesk** | Google Fonts typography |

### Infrastructure
| Technology | Purpose |
|-----------|---------|
| **Laragon** | Local development (Apache + MySQL) |
| **SMTP** | Email delivery (Mailtrap / Gmail) |
| **Cron Job / Task Scheduler** | Scheduled email reminders |
| **CI4 Cache (File)** | Signaling & data relay antara kiosk dan admin |

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                                │
├──────────────────────┬──────────────────────────────────────────────┤
│   Tablet Kiosk        │   Admin Panel (Desktop Browser)              │
│   ─────────────────  │   ──────────────────────────────────        │
│   • Camera capture   │   • Dashboard + Live Stats                  │
│   • OCR scan trigger │   • Remote Kiosk Control                    │
│   • Self check-in    │   • Member CRUD + OCR Integration           │
│   • Live streaming   │   • Live Camera Feed                        │
│                      │   • Visit History + Export (Excel/PDF)       │
└──────────┬───────────┴────────────────┬─────────────────────────────┘
           │                            │
           │  HTTP (AJAX/Fetch API)      │  HTTP (jQuery AJAX)
           │                            │
┌──────────▼────────────────────────────▼─────────────────────────────┐
│                    BACKEND LAYER (CI4 + FastAPI)                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   ┌─────────────────────────────────────────────────────────┐      │
│   │  CodeIgniter 4 (PHP 8.x)                                │      │
│   │  ├── Controllers (Auth, Dashboard, Kiosk, AdminKiosk,   │      │
│   │  │               OcrController, MemberType, LogKunjungan)│     │
│   │  ├── Models (AdminModel, MemberTypeModel)               │      │
│   │  ├── Views (Tailwind CSS + Liquidglass Theme)           │      │
│   │  ├── Commands (SendReminderEmail — Spark CLI)           │      │
│   │  └── Cache Service (File-based signaling)               │      │
│   └─────────────────────────┬───────────────────────────────┘      │
│                             │                                       │
│   ┌─────────────────────────▼───────────────────────────────┐      │
│   │  FastAPI (Python 3.10+)                                  │      │
│   │  ├── Tesseract OCR Engine                                │      │
│   │  ├── OpenCV Preprocessing (Blue Channel, CLAHE, etc.)   │      │
│   │  ├── Multi-Pipeline Voting System                        │      │
│   │  └── Cross-validation (DOB + Province Code)             │      │
│   └─────────────────────────────────────────────────────────┘      │
│                                                                     │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │   DATA LAYER            │
                    ├─────────────────────────┤
                    │  MySQL / MariaDB        │
                    │  ├── admin              │
                    │  ├── master_type_member │
                    │  ├── members            │
                    │  └── log_kunjungan      │
                    └─────────────────────────┘
```

---

## Database Schema

| Table | Primary Key | Description |
|-------|------------|-------------|
| `admin` | `id_admin` (AI) | Akun admin/resepsionis (username, password bcrypt) |
| `master_type_member` | `id_type` (AI) | Tipe membership (nama, kuota, benefit, masa aktif) |
| `members` | `NIK` (VARCHAR 16) | Data member (nama, HP, email, tipe, kuota, expired) |
| `log_kunjungan` | `id_kunjungan` (AI) | Log setiap check-in (NIK, waktu, kuota awal/akhir) |

### Relationships
```
master_type_member (1) ──── (N) members (1) ──── (N) log_kunjungan
        id_type ←── FK ── id_type    NIK ←── FK ── NIK
```

---

## Project Structure

```
mymember/
│
├── app/
│   ├── Commands/
│   │   └── SendReminderEmail.php       # CLI command: email:reminder
│   │
│   ├── Config/
│   │   ├── Routes.php                  # All route definitions
│   │   ├── Email.php                   # SMTP configuration
│   │   ├── Database.php                # Database connection
│   │   └── ...
│   │
│   ├── Controllers/
│   │   ├── Auth.php                    # Login / Register / Logout
│   │   ├── Dashboard.php               # Dashboard + live stats API
│   │   ├── Kiosk.php                   # Tablet kiosk endpoints
│   │   ├── AdminKiosk.php              # Admin remote control kiosk
│   │   ├── OcrController.php           # OCR proxy + check-in logic
│   │   ├── MemberType.php              # CRUD Member + registration
│   │   ├── LogKunjungan.php            # Visit history + export
│   │   └── Member.php                  # Read-only member list
│   │
│   ├── Database/Migrations/
│   │   ├── 2026-07-15-123815_Admin.php
│   │   ├── 2026-07-15-123824_MemberTypes.php
│   │   ├── 2026-07-15-123829_Members.php
│   │   └── 2026-07-15-123834_LogKunjungan.php
│   │
│   ├── Models/
│   │   ├── AdminModel.php
│   │   └── MemberTypeModel.php
│   │
│   └── Views/
│       ├── admin/
│       │   ├── dashboard.php           # Admin dashboard
│       │   ├── kiosk.php               # Remote kiosk + live feed
│       │   ├── log_kunjungan.php       # Visit history
│       │   ├── member.php              # Member list (read-only)
│       │   ├── member_type/            # CRUD member views
│       │   └── export/                 # PDF export template
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── layouts/
│       │   └── admin.php               # Master layout (sidebar + topbar)
│       └── upload_ktp.php              # Kiosk tablet interface
│
├── fastAPI/
│   └── main.py                         # OCR Microservice (895 lines)
│
├── public/
│   ├── css/
│   │   └── liquidglass.css             # Custom glassmorphism theme
│   ├── images/                         # Logo & static assets
│   └── index.php                       # Application entry point
│
├── .env                                # Environment config (DB, SMTP)
├── composer.json                       # PHP dependencies
└── spark                               # CI4 CLI runner
```

---

## Getting Started

### Prerequisites

| Requirement | Version | Notes |
|------------|---------|-------|
| PHP | 8.2+ | Extensions: intl, mbstring, curl, json, mysqlnd |
| Composer | 2.x | PHP dependency manager |
| Python | 3.10+ | For OCR microservice |
| pip | Latest | Python package manager |
| Tesseract OCR | 5.x | Must be installed & added to PATH |
| MySQL / MariaDB | 5.7+ / 10.4+ | Database engine |
| Laragon (Windows) | Latest | Or Apache/Nginx on Linux |

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/your-username/mymember.git
cd mymember
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Configure environment**

```bash
cp env .env
```

Edit `.env` and configure:

```ini
# Application
CI_ENVIRONMENT = development
app.baseURL = 'https://mymember.test/'

# Database
database.default.hostname = localhost
database.default.database = mymember_db
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi

# Email (SMTP)
email.fromEmail = "noreply@mymember.id"
email.fromName  = "MyMember Official"
email.protocol  = "smtp"
email.SMTPHost  = "smtp.gmail.com"
email.SMTPUser  = "your-email@gmail.com"
email.SMTPPass  = "your-app-password"
email.SMTPPort  = 587
email.SMTPCrypto= "tls"
email.mailType  = "html"
```

**4. Run database migration**

```bash
php spark migrate
```

**5. Set up Python OCR microservice**

```bash
cd fastAPI

# Create virtual environment (recommended)
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Install dependencies
pip install fastapi uvicorn pytesseract opencv-python numpy python-multipart
```

> **Note:** Make sure Tesseract OCR is installed and the path is configured in `fastAPI/main.py` line 12:
> ```python
> pytesseract.pytesseract.tesseract_cmd = r'D:\Tesseract-OCR\tesseract.exe'
> ```

**6. Start the services**

```bash
# Terminal 1 — Start OCR microservice
cd fastAPI
uvicorn main:app --host 127.0.0.1 --port 8000 --reload

# Terminal 2 — Start web server (if not using Laragon)
php spark serve
```

**7. Access the application**

| Interface | URL |
|-----------|-----|
| Admin Panel | `https://mymember.test/admin/dashboard` |
| Kiosk (Tablet) | `https://mymember.test/ocr` |
| Login | `https://mymember.test/login` |
| Register Admin | `https://mymember.test/register` |

---

## Configuration

### SMTP Email Setup

#### Development (Mailtrap)
```ini
email.SMTPHost = "sandbox.smtp.mailtrap.io"
email.SMTPUser = "your-mailtrap-user"
email.SMTPPass = "your-mailtrap-pass"
email.SMTPPort = 2525
```

#### Production (Gmail)
```ini
email.SMTPHost = "smtp.gmail.com"
email.SMTPUser = "your-email@gmail.com"
email.SMTPPass = "xxxx xxxx xxxx xxxx"   # App Password (16 chars)
email.SMTPPort = 587
```

> **Gmail App Password:** Go to [Google Account Security](https://myaccount.google.com/security) → Enable 2-Step Verification → App passwords → Generate for "Mail".

### Tesseract OCR Path

Edit `fastAPI/main.py` line 12:

```python
# Windows
pytesseract.pytesseract.tesseract_cmd = r'D:\Tesseract-OCR\tesseract.exe'

# Linux
pytesseract.pytesseract.tesseract_cmd = r'/usr/bin/tesseract'
```

---

## Usage Guide

### Admin Workflow

```
1. Login ──► 2. Dashboard (live stats)
                  │
                  ├── 3. Kiosk Controller ──► Remote capture + live feed
                  │
                  ├── 4. Kelola Member ──► CRUD + auto-fill via OCR
                  │
                  ├── 5. Daftar Member ──► Read-only member list
                  │
                  └── 6. Riwayat Kunjungan ──► Filter + Export (Excel/PDF)
```

### Member Check-in Flow

```
Member shows KTP to tablet
        │
        ▼
Admin clicks "Take Picture & Scan"
        │
        ▼
Tablet captures & sends to OCR
        │
        ▼
OCR extracts NIK & Name
        │
        ▼
Tablet shows confirmation screen
        │
        ▼
Member confirms -> Check-in done
        │
        ├── Registered → Kuota -1, Welcome!
        ├── Unregistered → Admin registers new member
        ├── Quota Empty → "Please top-up"
        └── Expired → "Please renew"
```

---

## API Reference

### OCR Microservice

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/extract-ktp` | Extract NIK & Name from KTP image |

**Request:** `multipart/form-data` with field `ktp_image`

**Response:**
```json
{
  "status": "sukses",
  "nama_file": "ktp_auto_scan.png",
  "data_ktp": {
    "nik": "3201234567890001",
    "nama": "JOHN DOE"
  },
  "hasil_bacaan_mentah": "PROVINSI JAWA BARAT\nNIK: 3201234567890001\n..."
}
```

### Internal CI4 APIs

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/admin/dashboard/live` | Yes | Live dashboard stats |
| `GET` | `/admin/kiosk/getStreamFrame` | Yes | Get latest camera frame (JPEG) |
| `POST` | `/admin/kiosk/trigger` | Yes | Send capture signal to kiosk |
| `GET` | `/kiosk/checkTrigger` | No | Kiosk polls for admin signal |
| `POST` | `/kiosk/streamFrame` | No | Kiosk uploads camera frame |
| `POST` | `/ocr/scan` | No | Proxy OCR request to FastAPI |
| `POST` | `/ocr/checkin` | No | Process member check-in |
| `GET` | `/ocr/get-member` | No | Lookup member by NIK |
| `GET` | `/admin/log-kunjungan/live` | Yes | Live visit logs |
| `GET` | `/admin/log-kunjungan/export/excel` | Yes | Export to Excel |
| `GET` | `/admin/log-kunjungan/export/pdf` | Yes | Export to PDF |

---

## Scheduling Email Reminders

The `email:reminder` command sends H-2 expiry notifications. Schedule it via OS-level cron/task scheduler.

### Test manually first

```bash
php spark email:reminder
```

### Linux (Cron Job)

```bash
crontab -e
```

```cron
# Send at 7:00 AM daily
0 7 * * * cd /var/www/mymember && /usr/bin/php spark email:reminder >> /var/log/mymember-email.log 2>&1

# Send at 6:00 PM daily
0 18 * * * cd /var/www/mymember && /usr/bin/php spark email:reminder >> /var/log/mymember-email.log 2>&1
```

### Windows (Task Scheduler)

```powershell
# Morning (7:00 AM)
schtasks /create /tn "MyMember_Reminder_AM" /tr "php spark email:reminder" /sc daily /st 07:00 /rl highest /f

# Evening (6:00 PM)
schtasks /create /tn "MyMember_Reminder_PM" /tr "php spark email:reminder" /sc daily /st 18:00 /rl highest /f
```

---

## CLI Commands

| Command | Description |
|---------|-------------|
| `php spark migrate` | Run database migrations |
| `php spark email:reminder` | Send H-2 expiry reminder emails |
| `php spark serve` | Start development server |
| `php spark list` | List all available commands |

---

## Server Requirements

- PHP 8.2+ with extensions: `intl`, `mbstring`, `curl`, `json`, `mysqlnd`
- Python 3.10+ with packages: `fastapi`, `uvicorn`, `pytesseract`, `opencv-python`, `numpy`
- Tesseract OCR 5.x
- MySQL 5.7+ / MariaDB 10.4+

---

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  <strong>Built with CodeIgniter 4, FastAPI, and Tesseract OCR</strong>
  <br />
  <sub>© 2026 MyMember. All rights reserved.</sub>
</p>
