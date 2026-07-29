<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMember - Kiosk KTP Scan</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: #f7f9fb;
            margin: 0;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .scanner-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            aspect-ratio: 16/9;
            border-radius: 24px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        #kiosk-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* mirror effect if front camera, remove if back camera */
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 4px solid rgba(255, 255, 255, 0.2);
            pointer-events: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2rem;
            z-index: 10;
        }
        .ktp-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 60%;
            border: 3px dashed #fff;
            border-radius: 12px;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
        }
        .scanning-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 20;
            color: white;
        }
        .loader {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .result-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 30;
            padding: 2rem;
        }
    </style>
</head>
<body>

    <div class="scanner-container">
        <!-- Video Stream -->
        <video id="kiosk-video" autoplay playsinline></video>
        
        <!-- Guide Overlay -->
        <div class="overlay" id="guide-overlay">
            <div class="text-center w-full">
                <h1 class="text-3xl font-bold text-white drop-shadow-md">Kiosk Scan KTP</h1>
                <p class="text-white/80 mt-2 text-lg drop-shadow-md">Silakan letakkan KTP Anda di dalam area kotak</p>
            </div>
            <div class="ktp-frame"></div>
        </div>

        <!-- Scanning State -->
        <div class="scanning-overlay" id="scanning-overlay">
            <div class="loader"></div>
            <h2 class="text-3xl font-bold tracking-wider animate-pulse">SCANNING...</h2>
            <p class="mt-2 text-white/70">Sistem sedang mengekstrak data KTP Anda</p>
        </div>

        <!-- Result State -->
        <div class="result-overlay" id="result-overlay">
            <div class="text-center w-full max-w-md">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                    <span class="material-symbols-outlined text-5xl">check_circle</span>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Data Ditemukan</h2>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left border border-gray-200">
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">NIK</p>
                        <p class="text-2xl font-bold text-gray-900" id="result-nik">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">NAMA</p>
                        <p class="text-2xl font-bold text-gray-900" id="result-nama">-</p>
                    </div>
                </div>

                <button id="btn-lanjut" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xl transition-colors shadow-lg">
                    Lanjut / Konfirmasi
                </button>
                <button id="btn-ulangi" class="w-full py-4 mt-4 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xl transition-colors">
                    Ulangi Scan
                </button>
            </div>
        </div>
        
        <canvas id="kiosk-canvas" style="display:none;"></canvas>
    </div>

    <script>
        const video = document.getElementById('kiosk-video');
        const canvas = document.getElementById('kiosk-canvas');
        const guideOverlay = document.getElementById('guide-overlay');
        const scanningOverlay = document.getElementById('scanning-overlay');
        const resultOverlay = document.getElementById('result-overlay');
        let pollingInterval = null;

        // Initialize Camera (Request high resolution)
        async function initCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: "environment", 
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    } 
                });
                video.srcObject = stream;
                // Start polling once camera is ready
                startPolling();
            } catch (err) {
                console.error("Error accessing camera: ", err);
                alert("Tidak dapat mengakses kamera: " + err.message);
            }
        }

        // Start AJAX Polling
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(checkTrigger, 1000); // Check every 1 second
        }

        // Check if Admin triggered capture
        function checkTrigger() {
            $.ajax({
                url: '/kiosk/checkTrigger',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.trigger === true) {
                        captureAndProcess();
                    }
                }
            });
        }

        // Capture image and send to OCR
        function captureAndProcess() {
            clearInterval(pollingInterval);
            
            // Show Scanning UI
            scanningOverlay.style.display = 'flex';
            
            // Capture image from video
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = canvas.toDataURL('image/jpeg', 0.9); // Quality 90%
            
            // Send to OCR endpoint
            $.ajax({
                url: '/kiosk/processOcr',
                method: 'POST',
                data: { image: imageData },
                dataType: 'json',
                success: function(res) {
                    scanningOverlay.style.display = 'none';
                    if (res.status === 'success') {
                        // Show Results
                        $('#result-nik').text(res.nik || 'Tidak terdeteksi');
                        $('#result-nama').text(res.nama || 'Tidak terdeteksi');
                        resultOverlay.style.display = 'flex';
                    } else {
                        alert('Gagal mendeteksi KTP: ' + res.message);
                        startPolling(); 
                    }
                },
                error: function(err) {
                    scanningOverlay.style.display = 'none';
                    alert('Terjadi kesalahan server saat memproses KTP.');
                    startPolling();
                }
            });
        }

        // Button Actions
        $('#btn-lanjut').click(function() {
            alert('Data Dikonfirmasi! (Integrasi ke tabel kunjungan bisa ditambahkan di sini)');
            resetKiosk();
        });

        $('#btn-ulangi').click(function() {
            resetKiosk();
        });

        function resetKiosk() {
            resultOverlay.style.display = 'none';
            $('#result-nik').text('-');
            $('#result-nama').text('-');
            startPolling();
        }

        initCamera();
    </script>
</body>
</html>
