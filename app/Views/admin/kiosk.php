<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?><?= esc($title ?? 'Kiosk Controller') ?><?= $this->endSection() ?>

<?= $this->section('header_content') ?>
    <div class="text-sm text-outline font-semibold">Remote Control Tablet Kiosk</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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
        if(errorCount > 5) {
            $('#kiosk-live-feed').addClass('hidden');
            $('#feed-status').removeClass('hidden').html(
                '<span class="material-symbols-outlined text-4xl mb-2">videocam_off</span>' +
                '<p class="text-sm text-red-400">Kiosk Offline / Kamera Mati</p>' +
                '<p class="text-xs text-white/40 mt-2">Auto-reconnect dalam 5 detik...</p>'
            );
            // Auto-reconnect: coba lagi setelah 5 detik
            setTimeout(function() {
                errorCount = 0;
                $('#feed-status').html(
                    '<span class="material-symbols-outlined text-4xl mb-2 animate-bounce">videocam_off</span>' +
                    '<p class="text-sm">Menunggu koneksi Kiosk...</p>'
                );
                fetchNextFrame();
            }, 5000);
            return;
        }
        if (isStreaming) {
            setTimeout(fetchNextFrame, 2000); // Jika error, jeda lebih lama sebelum coba lagi
        }
    };

    window.handleStreamSuccess = function() {
        errorCount = 0;
        $('#feed-status').addClass('hidden');
        $('#kiosk-live-feed').removeClass('hidden');
        if (isStreaming) {
            // Target ~15 FPS
            setTimeout(fetchNextFrame, 66);
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
<?= $this->endSection() ?>
