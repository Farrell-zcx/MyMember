
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <?php
    /** @noinspection PhpUndefinedVariableInspection */
    /** @noinspection PhpUndefinedFunctionInspection */
    /** @var array $logs */
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMember Admin - Riwayat Kunjungan</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
    <style>
        @keyframes flash-green {
            0% { background-color: rgba(34, 197, 94, 0.4); }
            100% { background-color: transparent; }
        }
        .row-highlight {
            animation: flash-green 3s ease-out forwards;
        }
    </style>
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
        <!-- Riwayat Kunjungan (Active) -->
        <a href="/admin/log-kunjungan" class="flex items-center px-4 py-3 transition-colors duration-200 text-secondary dark:text-secondary-fixed font-bold border-r-4 border-secondary font-body-md text-body-md bg-secondary/5 group">
            <span class="material-symbols-outlined mr-4" data-icon="history" style="font-variation-settings: 'FILL' 1;">history</span>
            <span class="">Riwayat Kunjungan</span>
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
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
            <input id="crudSearch" class="w-full glass-input rounded-full pl-10 pr-4 py-2 text-label-md font-label-md transition-all" placeholder="Cari riwayat kunjungan..." type="text">
        </div>
    </div>
</header>

<!-- Main Canvas -->
<main class="ml-[280px] pt-16 min-h-screen">
    <div class="p-8 max-w-[1440px] mx-auto">
        <!-- Page Header -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Riwayat Kunjungan</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Data riwayat kunjungan check-in member menggunakan OCR.</p>
            </div>
        </div>

        <section class="space-y-6">
            <div class="glass-panel rounded-2xl p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-3">
                    <h2 class="font-headline-md text-headline-md text-primary uppercase tracking-wider">
                        Log Check-in Terbaru
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="glass-header text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                                <th class="py-3 px-4 rounded-l-lg">Waktu Check-in</th>
                                <th class="py-3 px-4">NIK</th>
                                <th class="py-3 px-4">Nama Lengkap</th>
                                <th class="py-3 px-4 text-center">Kuota Awal</th>
                                <th class="py-3 px-4 text-center rounded-r-lg">Sisa Kuota</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody" class="text-xs text-on-surface divide-y divide-surface-container">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-outline italic">
                                        Belum ada data kunjungan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="glass-table-row">
                                        <td class="py-3.5 px-4 font-mono font-semibold"><?= date('d M Y H:i:s', strtotime($log['waktu_kunjungan'])) ?></td>
                                        <td class="py-3.5 px-4 font-mono font-bold text-on-surface"><?= esc($log['NIK']) ?></td>
                                        <td class="py-3.5 px-4 font-semibold"><?= esc($log['nama_lengkap'] ?? 'Tidak Diketahui') ?></td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center justify-center bg-surface-container-high px-2 py-1 rounded font-bold">
                                                <?= esc($log['kuota_awal']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary px-2 py-1 rounded font-bold">
                                                <?= esc($log['kuota_akhir']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<footer class="text-center py-6 text-xs text-outline glass-panel ml-[280px] border-t-0">
    &copy; 2026 MyMember Admin Dashboard.
</footer>

<script>
    // Real-time table search functionality
    const searchInput = document.getElementById('crudSearch');
    
    function applySearchFilter() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#logsTableBody tr');
        
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            
            const cells = row.getElementsByTagName('td');
            if (cells.length < 3) return;
            
            const waktu = cells[0].textContent.toLowerCase();
            const nik = cells[1].textContent.toLowerCase();
            const nama = cells[2].textContent.toLowerCase();
            
            const matches = nik.includes(query) || nama.includes(query) || waktu.includes(query);
            row.style.display = matches ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applySearchFilter);
    }

    // Real-time polling logic
    let knownLogIds = new Set();
    
    // Inisialisasi ID yang sudah ada saat halaman pertama kali dimuat
    <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $log): ?>
            knownLogIds.add("<?= esc($log['id_kunjungan']) ?>");
        <?php endforeach; ?>
    <?php endif; ?>

    setInterval(async () => {
        try {
            const response = await fetch('<?= base_url("admin/log-kunjungan/live") ?>?t=' + new Date().getTime());
            if (response.status !== 200) return;
            const result = await response.json();

            if (result.status === 'sukses' && result.data) {
                const logsTableBody = document.getElementById('logsTableBody');
                let newHtml = '';
                
                if (result.data.length === 0) {
                    newHtml = `<tr>
                        <td colspan="5" class="py-12 text-center text-outline italic">
                            Belum ada data kunjungan.
                        </td>
                    </tr>`;
                } else {
                    result.data.forEach(log => {
                        const isNew = !knownLogIds.has(log.id_kunjungan);
                        const rowClass = isNew ? 'glass-table-row row-highlight' : 'glass-table-row';
                        
                        // Tandai sebagai sudah diketahui
                        if (isNew) knownLogIds.add(log.id_kunjungan);

                        const namaLengkap = log.nama_lengkap ? log.nama_lengkap : 'Tidak Diketahui';
                        
                        // Menjaga agar ID tetap aman (sanitize string)
                        const safeNik = log.NIK.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        const safeNama = namaLengkap.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        
                        newHtml += `
                            <tr class="${rowClass}">
                                <td class="py-3.5 px-4 font-mono font-semibold">${log.waktu_format}</td>
                                <td class="py-3.5 px-4 font-mono font-bold text-on-surface">${safeNik}</td>
                                <td class="py-3.5 px-4 font-semibold">${safeNama}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center bg-surface-container-high px-2 py-1 rounded font-bold">
                                        ${log.kuota_awal}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary px-2 py-1 rounded font-bold">
                                        ${log.kuota_akhir}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                logsTableBody.innerHTML = newHtml;
                // Aplikasikan kembali filter pencarian setelah data dirender ulang
                applySearchFilter();
            }
        } catch (e) {
            // silent fail on polling error
        }
    }, 2000);
</script>
</body>
</html>
