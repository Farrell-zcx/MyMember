<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <?php
    /** @noinspection PhpUndefinedVariableInspection */
    /** @noinspection PhpUndefinedFunctionInspection */
    ?>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Self Check-In - MyMember</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-background": "#191c1e",
                        "inverse-primary": "#bec6e0",
                        "surface-variant": "#e0e3e5",
                        "primary-fixed-dim": "#bec6e0",
                        "inverse-on-surface": "#eff1f3",
                        "inverse-surface": "#2d3133",
                        "surface-container": "#eceef0",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f2f4f6",
                        "primary": "#000000",
                        "surface-dim": "#d8dadc",
                        "tertiary-fixed-dim": "#b7c8e1",
                        "on-secondary": "#ffffff",
                        "surface-container-highest": "#e0e3e5",
                        "on-surface-variant": "#45464d",
                        "primary-fixed": "#dae2fd",
                        "secondary": "#0058be",
                        "background": "#f7f9fb",
                        "primary-container": "#131b2e",
                        "error-container": "#ffdad6",
                        "surface": "#f7f9fb",
                        "on-primary-fixed": "#131b2e",
                        "on-error-container": "#93000a",
                        "outline-variant": "#c6c6cd",
                        "on-secondary-fixed": "#001a42",
                        "on-secondary-fixed-variant": "#004395",
                        "on-primary-container": "#7c839b",
                        "tertiary-fixed": "#d3e4fe",
                        "surface-container-high": "#e6e8ea",
                        "on-error": "#ffffff",
                        "tertiary-container": "#0b1c30",
                        "on-secondary-container": "#fefcff",
                        "on-surface": "#191c1e",
                        "error": "#ba1a1a",
                        "on-tertiary-fixed": "#0b1c30",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#75859d",
                        "surface-bright": "#f7f9fb",
                        "tertiary": "#000000",
                        "on-primary": "#ffffff",
                        "on-primary-fixed-variant": "#3f465c",
                        "secondary-fixed-dim": "#adc6ff",
                        "on-tertiary-fixed-variant": "#38485d",
                        "outline": "#76777d",
                        "secondary-container": "#2170e4",
                        "secondary-fixed": "#d8e2ff",
                        "surface-tint": "#565e74"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "base": "4px",
                        "xl": "32px",
                        "margin-mobile": "16px",
                        "xs": "8px",
                        "2xl": "48px",
                        "sm": "12px",
                        "lg": "24px",
                        "md": "16px",
                        "gutter": "24px",
                        "margin-desktop": "32px"
                    },
                    "fontFamily": {
                        "headline-md": ["Hanken Grotesk"],
                        "label-md": ["Hanken Grotesk"],
                        "body-lg": ["Hanken Grotesk"],
                        "headline-xl": ["Hanken Grotesk"],
                        "headline-lg-mobile": ["Hanken Grotesk"],
                        "body-md": ["Hanken Grotesk"],
                        "label-sm": ["Hanken Grotesk"],
                        "body-sm": ["Hanken Grotesk"],
                        "headline-lg": ["Hanken Grotesk"]
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: #f7f9fb;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .card-shadow {
            box-shadow: 0 10px 30px -10px rgba(0, 88, 190, 0.08);
        }
    </style>
</head>

<body class="bg-background text-on-surface min-h-screen flex flex-col justify-between">

    <!-- Subtle Decorative Background Shapes -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] rounded-full bg-secondary opacity-[0.03] blur-[150px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] rounded-full bg-primary opacity-[0.03] blur-[150px]"></div>
    </div>

    <!-- Header -->
    <header class="relative z-10 w-full px-6 py-6 max-w-7xl mx-auto flex items-center justify-between">
        <a href="<?= base_url('/') ?>" class="flex items-center gap-2 hover:text-secondary group transition-colors">
            <span class="material-symbols-outlined text-[20px] text-outline group-hover:text-secondary transition-colors" data-icon="arrow_back">arrow_back</span>
            <span class="font-label-sm text-label-sm text-outline group-hover:text-secondary font-semibold transition-colors">Kembali ke Beranda</span>
        </a>
        <span class="text-xs bg-secondary/15 text-secondary border border-secondary/20 px-3 py-1.5 rounded-full font-bold uppercase tracking-wider">
            Self Check-In Portal
        </span>
    </header>

    <!-- Main Kiosk Wrapper -->
    <main class="relative z-10 flex-grow flex items-center justify-center px-margin-mobile py-xl w-full max-w-xl mx-auto">

        <!-- STEP 1: SCAN CARD -->
        <div id="stepScan" class="w-full bg-white border border-outline-variant rounded-2xl p-xl card-shadow text-center space-y-xl transition-all duration-300">
            <div class="space-y-sm">
                <h2 class="font-headline-xl text-headline-xl text-primary tracking-tight">Selamat Datang</h2>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-sm mx-auto">
                    Silakan ketuk tombol di bawah ini untuk memindai KTP Anda secara otomatis.
                </p>
            </div>

            <form id="formKtp" class="space-y-md" data-scan-url="<?= base_url('ocr/scan') ?>" data-checkin-url="<?= base_url('ocr/checkin') ?>" data-get-member-url="<?= base_url('ocr/get-member') ?>" data-update-cache-url="<?= base_url('ocr/update-cache') ?>">
                <?= csrf_field() ?>
                <!-- hidden inputs to store scan files -->
                <input type="file" id="inputKtp" name="ktp_image" accept="image/*" capture="environment" class="hidden">

                <button type="button" onclick="document.getElementById('inputKtp').click()" class="mx-auto w-36 h-36 rounded-full bg-secondary text-on-secondary hover:bg-secondary-container hover:shadow-xl transition-all flex flex-col items-center justify-center gap-xs active:scale-95 group">
                    <span class="material-symbols-outlined text-[48px] group-hover:scale-110 transition-transform" data-icon="photo_camera">photo_camera</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Scan KTP</span>
                </button>
            </form>

            <div id="loading" class="hidden text-secondary font-medium flex items-center justify-center gap-xs text-body-sm">
                <svg class="animate-spin h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mengekstraksi data KTP Anda...</span>
            </div>
        </div>

        <!-- STEP 2: CONFIRMATION CARD -->
        <div id="stepConfirm" class="hidden w-full bg-white border border-outline-variant rounded-2xl p-xl card-shadow space-y-xl transition-all duration-300">
            <div class="text-center space-y-xs">
                <span class="material-symbols-outlined text-[48px] text-secondary" data-icon="fact_check">fact_check</span>
                <h2 class="font-headline-xl text-headline-xl text-primary tracking-tight">Konfirmasi Data</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    Apakah NIK & Nama Lengkap di bawah ini sudah sesuai?
                </p>
            </div>

            <div class="bg-surface-container-low border border-outline-variant rounded-xl p-md space-y-md font-mono">
                <div>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider block mb-1">NIK <span class="text-[10px] lowercase text-secondary">(Silakan edit jika salah)</span></span>
                    <input type="text" id="nikInput" class="w-full bg-white border border-outline-variant rounded-md px-3 py-2 text-lg font-bold text-primary focus:ring-2 focus:ring-secondary focus:border-secondary transition-all" value="">
                </div>
                <div class="border-t border-outline-variant/30 pt-md">
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider block mb-1">Nama Lengkap <span class="text-[10px] lowercase text-secondary">(Silakan edit jika salah)</span></span>
                    <input type="text" id="namaInput" class="w-full bg-white border border-outline-variant rounded-md px-3 py-2 text-lg font-bold text-primary uppercase focus:ring-2 focus:ring-secondary focus:border-secondary transition-all" value="">
                </div>
                <div id="phoneConfirmWrapper" class="hidden border-t border-outline-variant/30 pt-md">
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider block">Nomor HP</span>
                    <span id="phoneConfirm" class="text-lg font-bold text-primary">-</span>
                </div>
                <div id="emailConfirmWrapper" class="hidden border-t border-outline-variant/30 pt-md">
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider block">Email</span>
                    <span id="emailConfirm" class="text-lg font-bold text-primary">-</span>
                </div>
                <div id="typeConfirmWrapper" class="hidden border-t border-outline-variant/30 pt-md">
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider block">Tipe Member</span>
                    <span id="typeConfirm" class="text-lg font-bold text-primary">-</span>
                </div>
                <div id="expiredConfirmWrapper" class="hidden border-t border-outline-variant/30 pt-md">
                    <span class="text-xs text-on-surface-variant uppercase tracking-wider block">Tanggal Expired</span>
                    <span id="expiredConfirm" class="text-lg font-bold text-primary">-</span>
                </div>
            </div>

            <div class="flex justify-between gap-md">
                <button onclick="resetToScan()" class="flex-1 py-3 border border-outline-variant text-outline rounded-lg font-label-md text-label-md hover:bg-surface-container-low transition-all active:scale-[0.98]">
                    Batal / Pindai Ulang
                </button>
                <button id="btnCheckin" onclick="processCheckin()" class="flex-grow py-3 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:bg-secondary-container hover:shadow-lg transition-all active:scale-[0.98] shadow-md flex items-center justify-center gap-xs">
                    <span>Ya, Data Benar</span>
                    <span class="material-symbols-outlined text-[18px]" data-icon="login">login</span>
                </button>
            </div>
        </div>

        <!-- STEP 3: RESULT CARD -->
        <div id="stepResult" class="hidden w-full bg-white border border-outline-variant rounded-2xl p-xl card-shadow text-center space-y-xl transition-all duration-300">

            <!-- SUCCESS STATUS -->
            <div id="resultSuccess" class="hidden space-y-lg">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-full">
                    <span class="material-symbols-outlined text-[44px]" data-icon="check_circle">check_circle</span>
                </div>
                <div class="space-y-xs">
                    <h2 class="font-headline-xl text-headline-xl text-primary tracking-tight">Check-In Berhasil!</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Selamat datang kembali, <span id="successName" class="font-bold text-primary uppercase">-</span>.
                    </p>
                </div>
                <div class="p-md bg-green-50 rounded-xl border border-green-200 inline-block">
                    <p class="text-xs text-green-800 uppercase tracking-wider">Masa Kunjungan Terpotong 1</p>
                    <p class="text-lg font-bold text-green-900 mt-base">Sisa Kuota: <span id="successQuota">0</span> Kali</p>
                </div>
            </div>

            <!-- FAILURE STATUS (UNREGISTERED) -->
            <div id="resultUnregistered" class="hidden space-y-lg">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-error-container text-error rounded-full relative">
                    <span class="material-symbols-outlined text-[44px]" data-icon="person_add">person_add</span>
                    <span class="absolute top-0 right-0 w-5 h-5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-error opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-5 w-5 bg-error border-2 border-white"></span>
                    </span>
                </div>
                <div class="space-y-xs">
                    <h2 class="font-headline-xl text-headline-xl text-primary tracking-tight">Belum Terdaftar</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xs mx-auto">
                        NIK Anda belum terdaftar sebagai member di sistem kami.
                    </p>
                </div>
                <div class="p-md bg-surface-container-low rounded-xl border border-outline-variant flex flex-col items-center justify-center space-y-sm">
                    <svg class="animate-spin h-8 w-8 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-semibold text-primary">Harap tunggu sebentar...</p>
                    <p class="text-xs text-on-surface-variant max-w-[250px] mx-auto text-center">Resepsionis sedang memproses pendaftaran data Anda secara otomatis.</p>
                </div>
            </div>

            <!-- FAILURE STATUS (LIMIT) -->
            <div id="resultLimit" class="hidden space-y-lg">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 text-amber-600 rounded-full">
                    <span class="material-symbols-outlined text-[44px]" data-icon="hourglass_empty">hourglass_empty</span>
                </div>
                <div class="space-y-xs">
                    <h2 class="font-headline-xl text-headline-xl text-primary tracking-tight">Kuota Habis</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xs mx-auto">
                        Kuota kunjungan member Anda telah habis.
                    </p>
                </div>
                <div class="p-md bg-amber-50 rounded-xl border border-amber-200 text-amber-800">
                    <p class="text-sm font-semibold">Silakan hubungi petugas resepsionis untuk melakukan pengisian ulang (top-up) kuota.</p>
                </div>
            </div>

            <!-- FAILURE STATUS (EXPIRED) -->
            <div id="resultExpired" class="hidden space-y-lg">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 text-red-600 rounded-full">
                    <span class="material-symbols-outlined text-[44px]" data-icon="do_not_disturb_on">do_not_disturb_on</span>
                </div>
                <div class="space-y-xs">
                    <h2 class="font-headline-xl text-headline-xl text-primary tracking-tight">Expired Member</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xs mx-auto">
                        Masa aktif kartu member Anda telah kedaluwarsa.
                    </p>
                </div>
                <div class="p-md bg-red-50 rounded-xl border border-red-200 text-red-800">
                    <p class="text-sm font-semibold">Silakan lakukan perpanjangan keanggotaan di meja resepsionis.</p>
                </div>
            </div>

            <button id="btnKembaliResult" onclick="resetToScan()" class="w-full py-3 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:bg-secondary-container transition-all active:scale-[0.98]">
                Kembali
            </button>
        </div>

    </main>

    <!-- Footer Bar -->
    <footer class="relative z-10 py-6 text-center">
        <p class="font-label-sm text-label-sm text-outline tracking-wider uppercase">
            © 2026 MyMember Corporate. All Rights Reserved.
        </p>
    </footer>

    <script>
        let scannedNik = "";
        let scannedNama = "";
        let pollInterval = null;
        let updateCacheTimeout = null;

        function triggerCacheUpdate() {
            if (updateCacheTimeout) clearTimeout(updateCacheTimeout);
            updateCacheTimeout = setTimeout(async () => {
                const currentNik = document.getElementById('nikInput').value.trim();
                const currentNama = document.getElementById('namaInput').value.trim();
                if (currentNik !== undefined && currentNama !== undefined) {
                    const updateUrl = document.getElementById('formKtp').dataset.updateCacheUrl;
                    const formData = new FormData();
                    formData.append('nik', currentNik);
                    formData.append('nama', currentNama);

                    const csrfToken = document.querySelector('input[name="csrf_test_name"]');
                    if (csrfToken) formData.append(csrfToken.name, csrfToken.value);
                    try {
                        await fetch(updateUrl, {
                            method: 'POST',
                            body: formData
                        });
                    } catch (e) {
                        console.error("Gagal update cache");
                    }
                }
            }, 500);
        }

        // Image capture scan change listener
        document.getElementById('inputKtp').addEventListener('change', async function(e) {
            const file = this.files[0];
            if (!file) return;

            const loadingDiv = document.getElementById('loading');
            loadingDiv.classList.remove('hidden');

            const formData = new FormData();
            formData.append('ktp_image', file);

            try {
                // Get URL from data attributes
                const scanUrl = document.getElementById('formKtp').dataset.scanUrl;
                const response = await fetch(scanUrl, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'sukses' && result.data_ktp) {
                    scannedNik = result.data_ktp.nik;
                    scannedNama = result.data_ktp.nama;

                    document.getElementById('nikInput').value = scannedNik;
                    document.getElementById('namaInput').value = scannedNama;

                    // Hide optional wrappers initially
                    document.getElementById('phoneConfirmWrapper').classList.add('hidden');
                    document.getElementById('emailConfirmWrapper').classList.add('hidden');
                    document.getElementById('typeConfirmWrapper').classList.add('hidden');
                    document.getElementById('expiredConfirmWrapper').classList.add('hidden');

                    // Function to lookup member by NIK
                    const lookupMember = (nikToLookup) => {
                        // Reset fields first
                        document.getElementById('phoneConfirmWrapper').classList.add('hidden');
                        document.getElementById('emailConfirmWrapper').classList.add('hidden');
                        document.getElementById('typeConfirmWrapper').classList.add('hidden');
                        document.getElementById('expiredConfirmWrapper').classList.add('hidden');

                        const getMemberUrl = document.getElementById('formKtp').dataset.getMemberUrl;
                        fetch(`${getMemberUrl}?nik=${nikToLookup}`)
                            .then(res => res.json())
                            .then(memberRes => {
                                if (memberRes.status === 'sukses' && memberRes.exists) {
                                    const m = memberRes.data;
                                    if (m.nomor_hp) {
                                        document.getElementById('phoneConfirm').innerText = m.nomor_hp;
                                        document.getElementById('phoneConfirmWrapper').classList.remove('hidden');
                                    }
                                    if (m.email) {
                                        document.getElementById('emailConfirm').innerText = m.email;
                                        document.getElementById('emailConfirmWrapper').classList.remove('hidden');
                                    }
                                    if (m.type_member) {
                                        document.getElementById('typeConfirm').innerText = m.type_member;
                                        document.getElementById('typeConfirmWrapper').classList.remove('hidden');
                                    }
                                    if (m.tgl_expired_member) {
                                        // Format date YYYY-MM-DD to DD-MM-YYYY or readable format
                                        const d = new Date(m.tgl_expired_member);
                                        const formattedDate = !isNaN(d) ? d.toLocaleDateString('id-ID', {
                                            day: '2-digit',
                                            month: 'long',
                                            year: 'numeric'
                                        }) : m.tgl_expired_member;
                                        document.getElementById('expiredConfirm').innerText = formattedDate;
                                        document.getElementById('expiredConfirmWrapper').classList.remove('hidden');
                                    }
                                }
                            })
                            .catch(err => console.error("Error loading existing member details:", err));
                    };

                    // Initial lookup with scanned NIK
                    lookupMember(scannedNik);

                    // Tambahkan listener ke nikInput untuk pencarian koreksi manual dinamis
                    document.getElementById('nikInput').addEventListener('input', function(e) {
                        triggerCacheUpdate();
                        const currentNik = e.target.value.trim();
                        if (currentNik.length >= 10) {
                            lookupMember(currentNik);
                        } else {
                            document.getElementById('phoneConfirmWrapper').classList.add('hidden');
                            document.getElementById('emailConfirmWrapper').classList.add('hidden');
                            document.getElementById('typeConfirmWrapper').classList.add('hidden');
                            document.getElementById('expiredConfirmWrapper').classList.add('hidden');
                        }
                    });

                    // Listener namaInput untuk update cache admin real-time
                    document.getElementById('namaInput').addEventListener('input', function(e) {
                        triggerCacheUpdate();
                    });

                    document.getElementById('stepScan').classList.add('hidden');
                    document.getElementById('stepConfirm').classList.remove('hidden');
                } else {
                    alert("Gagal membaca KTP: " + (result.pesan || "Format gambar tidak dikenali."));
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Koneksi gagal ke server backend.");
            } finally {
                loadingDiv.classList.add('hidden');
                document.getElementById('inputKtp').value = '';
            }
        });

        // Check-in post
        async function processCheckin() {
            const btn = document.getElementById('btnCheckin');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memproses...</span>
        `;

            try {
                // Post checkout variables using FormData
                const checkinFormData = new FormData();
                const finalNik = document.getElementById('nikInput').value.trim();
                const finalNama = document.getElementById('namaInput').value.trim();

                checkinFormData.append('nik', finalNik);
                checkinFormData.append('nama', finalNama);

                const checkinUrl = document.getElementById('formKtp').dataset.checkinUrl;
                const response = await fetch(checkinUrl, {
                    method: 'POST',
                    body: checkinFormData
                });

                const result = await response.json();

                document.getElementById('stepConfirm').classList.add('hidden');
                document.getElementById('stepResult').classList.remove('hidden');

                document.getElementById('resultSuccess').classList.add('hidden');
                document.getElementById('resultUnregistered').classList.add('hidden');
                document.getElementById('resultLimit').classList.add('hidden');
                document.getElementById('resultExpired').classList.add('hidden');

                if (result.status === 'sukses') {
                    document.getElementById('successName').innerText = result.nama;
                    document.getElementById('successQuota').innerText = result.sisa_kuota;
                    document.getElementById('resultSuccess').classList.remove('hidden');
                    document.getElementById('btnKembaliResult').classList.remove('hidden');
                } else if (result.status === 'unregistered') {
                    document.getElementById('resultUnregistered').classList.remove('hidden');
                    document.getElementById('btnKembaliResult').classList.add('hidden'); // Sembunyikan tombol kembali saat menunggu admin

                    // Tunggu admin/resepsionis selesai registrasi member
                    pollInterval = setInterval(async () => {
                        try {
                            const getMemberUrl = document.getElementById('formKtp').dataset.getMemberUrl;
                            const res = await fetch(`${getMemberUrl}?nik=${finalNik}`);
                            if (res.status === 200) {
                                const memberRes = await res.json();
                                if (memberRes.status === 'sukses' && memberRes.exists) {
                                    // Sudah terdaftar oleh admin
                                    clearInterval(pollInterval);
                                    pollInterval = null;

                                    // Sembunyikan layar belum terdaftar
                                    document.getElementById('resultUnregistered').classList.add('hidden');

                                    // Munculkan kembali tombol KEMBALI
                                    document.getElementById('btnKembaliResult').classList.remove('hidden');

                                    // Tampilkan layar sukses dengan data terbaru
                                    document.getElementById('successName').innerText = memberRes.data.nama_lengkap;
                                    document.getElementById('successQuota').innerText = memberRes.data.sisa_kuota;
                                    document.getElementById('resultSuccess').classList.remove('hidden');
                                }
                            }
                        } catch (err) {
                            // Abaikan error jaringan sementara
                        }
                    }, 2000); // Polling setiap 2 detik

                } else if (result.status === 'limit') {
                    document.getElementById('resultLimit').classList.remove('hidden');
                    document.getElementById('btnKembaliResult').classList.remove('hidden');
                } else if (result.status === 'expired') {
                    document.getElementById('resultExpired').classList.remove('hidden');
                    document.getElementById('btnKembaliResult').classList.remove('hidden');
                } else {
                    alert("Kesalahan Sistem: " + result.pesan);
                    resetToScan();
                }

            } catch (error) {
                console.error("Check-in error:", error);
                alert("Gagal memproses check-in ke database.");
                resetToScan();
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        // Reset workflow back to starting scan state
        function resetToScan() {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }

            scannedNik = "";
            scannedNama = "";

            document.getElementById('stepConfirm').classList.add('hidden');
            document.getElementById('stepResult').classList.add('hidden');
            document.getElementById('stepScan').classList.remove('hidden');

            // Pastikan tombol kembali muncul 
            document.getElementById('btnKembaliResult').classList.remove('hidden');
        }
    </script>
</body>

</html>