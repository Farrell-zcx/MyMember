<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Kiosk Controller') ?></title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Liquidglass Theme -->
    <link href="<?= base_url('css/liquidglass.css') ?>" rel="stylesheet" />
    <script id="tailwind-config">
    try {
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
    } catch(_e) {}
    </script>
</head>
<body class="liquid-bg text-on-surface">

<!-- SideNavBar Anchor -->
<aside class="fixed left-0 top-0 h-full w-[280px] glass-panel flex flex-col py-6 transition-colors duration-200 z-50 border-r-0">
    <div class="px-6 mb-10">
        <div class="flex items-center gap-2 mb-2">
            <img src="<?= base_url('images/logo.png') ?>" alt="MyMember" class="h-10 w-auto mix-blend-multiply" />
            <h1 class="font-headline-xl text-headline-xl text-primary dark:text-inverse-primary tracking-tight">MyMember</h1>
        </div>
        <p class="text-label-sm text-on-surface-variant opacity-70">Admin Portal</p>
    </div>
    <nav class="flex-1 space-y-1 px-3">
        <!-- Dashboard -->
        <a href="/admin/dashboard" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="dashboard">dashboard</span>
            <span class="">Dashboard</span>
        </a>
        <!-- Members -->
        <a href="/admin/member" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="group">group</span>
            <span class="">Daftar Member</span>
        </a>
        <!-- Member Type -->
        <a href="/admin/member-type" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="card_membership">card_membership</span>
            <span class="">Kelola Member</span>
        </a>
        <!-- Riwayat Kunjungan -->
        <a href="/admin/log-kunjungan" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="history">history</span>
            <span class="">Riwayat Kunjungan</span>
        </a>
        <!-- Kiosk Controller (Active) -->
        <a href="/admin/kiosk" class="flex items-center px-4 py-3 transition-colors duration-200 text-secondary dark:text-secondary-fixed font-bold border-r-4 border-secondary font-body-md text-body-md bg-secondary/5 group">
            <span class="material-symbols-outlined mr-4" data-icon="aod" style="font-variation-settings: 'FILL' 1;">aod</span>
            <span class="">Kiosk Controller</span>
        </a>
        <!-- Logout -->
        <a href="/logout" class="flex items-center px-4 py-3 mt-4 transition-colors duration-200 hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-rose-700 transition-colors" data-icon="logout">logout</span>
            <span class="">Logout</span>
        </a>
    </nav>
    <div class="px-6 mt-auto">
        <div class="flex items-center p-3 rounded-xl bg-surface-container-low border border-outline-variant">
            <div>
                <p class="font-label-md text-label-md text-on-surface leading-tight"><?= esc(session()->get('nama_resepsionis') ?? 'Admin Rivera') ?></p>
                <p class="text-[10px] text-on-surface-variant uppercase tracking-wider"><?= esc(session()->get('username') ?? 'super_admin') ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- TopAppBar Anchor -->
<header class="fixed top-0 right-0 h-16 ml-[280px] w-[calc(100%-280px)] glass-panel flex justify-between items-center px-8 z-40 transition-all duration-150 border-b-0">
    <div class="flex items-center flex-1 max-w-xl">
        <div class="text-sm text-outline font-semibold">Remote Control Tablet Kiosk</div>
    </div>
</header>

<!-- Main Canvas -->
<main class="ml-[280px] mt-16 p-8 min-h-[calc(100vh-4rem)]">
    <div class="max-w-6xl mx-auto grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        <!-- Left: Controls -->
        <div class="glass-panel p-8 rounded-3xl flex flex-col justify-center text-center">
            
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 text-blue-600 mb-6 mx-auto">
                <span class="material-symbols-outlined text-4xl">aod</span>
            </div>

            <h2 class="text-3xl font-headline-md font-bold mb-4">Remote Capture Kiosk</h2>
            <p class="text-on-surface-variant mb-12 max-w-md mx-auto">
                Pastikan KTP member sudah berada tepat di tengah frame Tablet Kiosk. Klik tombol di bawah ini untuk mengambil foto dan memulai proses pemindaian OCR.
            </p>

            <button id="btn-trigger" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-5 px-12 rounded-full shadow-lg shadow-blue-500/30 transition-all active:scale-95 text-xl flex items-center mx-auto">
                <span class="material-symbols-outlined mr-3">camera</span>
                Take Picture & Scan
            </button>

            <div id="status-message" class="mt-8 text-lg font-medium text-green-600 hidden">
                Sinyal capture berhasil dikirim ke Kiosk!
            </div>
        </div>

        <!-- Right: Live Feed -->
        <div class="glass-panel p-8 rounded-3xl flex flex-col">
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-red-500 animate-pulse">fiber_manual_record</span>
                <h3 class="text-xl font-headline-md font-bold">Live Kiosk Feed</h3>
            </div>
            
            <div class="w-full aspect-video bg-black rounded-xl overflow-hidden relative shadow-inner border border-outline-variant/30 flex items-center justify-center">
                <!-- Fallback / Loading -->
                <div id="feed-status" class="absolute inset-0 flex flex-col items-center justify-center text-white/50 z-0">
                    <span class="material-symbols-outlined text-4xl mb-2 animate-bounce">videocam_off</span>
                    <p class="text-sm">Menunggu koneksi Kiosk...</p>
                </div>
                <!-- Stream Image -->
                <img id="kiosk-live-feed" src="" class="w-full h-full object-contain relative z-10 hidden" alt="Live Feed" onerror="handleStreamError()" onload="handleStreamSuccess()" />
            </div>
            <p class="text-xs text-on-surface-variant mt-4 opacity-70 text-center">* Resolusi dikurangi untuk optimalisasi jaringan.</p>
        </div>

    </div>
</main>

<script>
$(document).ready(function() {
    let isStreaming = false;
    let errorCount = 0;

    function fetchNextFrame() {
        if (!isStreaming) return;
        const imgUrl = '/admin/kiosk/getStreamFrame?t=' + new Date().getTime();
        $('#kiosk-live-feed').attr('src', imgUrl);
    }

    function startAdminStream() {
        isStreaming = true;
        fetchNextFrame();
    }

    window.handleStreamError = function() {
        errorCount++;
        if(errorCount > 3) {
            $('#kiosk-live-feed').addClass('hidden');
            $('#feed-status').removeClass('hidden').html('<span class="material-symbols-outlined text-4xl mb-2">videocam_off</span><p class="text-sm text-red-400">Kiosk Offline / Kamera Mati</p>');
        }
        if (isStreaming) {
            setTimeout(fetchNextFrame, 1000); // Jika error, jeda lebih lama sebelum coba lagi
        }
    };

    window.handleStreamSuccess = function() {
        errorCount = 0;
        $('#kiosk-live-feed').removeClass('hidden');
        $('#feed-status').addClass('hidden');
        
        if (isStreaming) {
            setTimeout(fetchNextFrame, 150); // frame baru lebih cepat Real-time FPS
        }
    };

    startAdminStream();

    $('#btn-trigger').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...');
        
        // Pause stream temporarily so we don't spam while it's processing
        isStreaming = false;

        $.ajax({
            url: '/admin/kiosk/trigger',
            type: 'POST',
            data: {
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                btn.html(originalText);
                btn.prop('disabled', false);
                
                $('#status-message').removeClass('hidden').fadeIn();
                setTimeout(() => {
                    $('#status-message').fadeOut();
                    // Resume streaming after 3 seconds
                    startAdminStream();
                }, 3000);
            },
            error: function() {
                alert('Gagal mengirim sinyal ke Kiosk.');
                btn.html(originalText);
                btn.prop('disabled', false);
                startAdminStream(); // Resume immediately on error
            }
        });
    });
});
</script>

</body>
</html>
