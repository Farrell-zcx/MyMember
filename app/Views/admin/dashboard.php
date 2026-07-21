
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <?php
    /** @noinspection PhpUndefinedVariableInspection */
    /** @noinspection PhpUndefinedFunctionInspection */
    /** @var int $total_members */
    /** @var int $total_kunjungan_hari_ini */
    /** @var array $recent_logs */
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMember Admin - Dashboard</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
<body class="bg-background text-on-surface">

<!-- SideNavBar Anchor -->
<aside class="fixed left-0 top-0 h-full w-[280px] bg-surface dark:bg-inverse-surface border-r border-outline-variant dark:border-outline flex flex-col py-6 transition-colors duration-200 z-50">
    <div class="px-6 mb-10">
        <h1 class="font-headline-xl text-headline-xl text-primary dark:text-inverse-primary tracking-tight">MyMember</h1>
        <p class="text-label-sm text-on-surface-variant opacity-70">Admin Portal</p>
    </div>
    <nav class="flex-1 space-y-1 px-3">
        <!-- Dashboard (Active) -->
        <a href="/admin/dashboard" class="flex items-center px-4 py-3 transition-colors duration-200 text-secondary dark:text-secondary-fixed font-bold border-r-4 border-secondary font-body-md text-body-md bg-secondary/5 group">
            <span class="material-symbols-outlined mr-4" data-icon="dashboard" style="font-variation-settings: 'FILL' 1;">dashboard</span>
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
<header class="fixed top-0 right-0 h-16 ml-[280px] w-[calc(100%-280px)] bg-surface-container-lowest dark:bg-inverse-surface flex justify-between items-center px-8 shadow-sm border-b border-outline-variant dark:border-outline z-40 transition-all duration-150">
    <div class="flex items-center flex-1 max-w-xl">
        <!-- Optional Top Search (Not strictly needed for dashboard) -->
        <div class="text-sm text-outline font-semibold">Today's Overview</div>
    </div>
</header>

<!-- Main Canvas -->
<main class="ml-[280px] pt-16 min-h-screen">
    <div class="p-8 max-w-[1440px] mx-auto space-y-8">
        
        <!-- Page Header -->
        <div class="flex justify-between items-end">
            <div>
                <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Welcome Back!</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Berikut adalah ringkasan operasional hari ini.</p>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card 1: Pengunjung Hari Ini -->
            <div class="bg-white rounded-2xl p-6 border border-outline-variant shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-secondary/5 rounded-bl-full -z-0 group-hover:scale-110 transition-transform"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-bold text-on-surface-variant uppercase tracking-wider mb-2">Pengunjung Hari Ini</p>
                        <h3 id="stat-kunjungan-hari-ini" class="text-5xl font-extrabold text-secondary tracking-tight">
                            <?= esc($total_kunjungan_hari_ini) ?>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[28px]">sensor_door</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-outline-variant relative z-10 flex items-center text-xs font-semibold text-outline">
                    <span class="material-symbols-outlined text-[16px] mr-1">today</span>
                    <span>Tercatat sejak tengah malam</span>
                </div>
            </div>

            <!-- Card 2: Total Member -->
            <div class="bg-white rounded-2xl p-6 border border-outline-variant shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-110 transition-transform"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-bold text-on-surface-variant uppercase tracking-wider mb-2">Total Member Aktif</p>
                        <h3 id="stat-total-member" class="text-5xl font-extrabold text-primary tracking-tight">
                            <?= esc($total_members) ?>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[28px]">groups</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-outline-variant relative z-10 flex items-center text-xs font-semibold text-outline">
                    <span class="material-symbols-outlined text-[16px] mr-1">monitoring</span>
                    <span>Keseluruhan di database</span>
                </div>
            </div>
        </div>

        <!-- Recent Check-ins Table -->
        <section>
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-3">
                    <h2 class="font-headline-md text-headline-md text-primary uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">flash_on</span>
                        Aktivitas Check-in Hari Ini (Terkini)
                    </h2>
                    <a href="/admin/log-kunjungan" class="text-xs font-bold text-secondary hover:underline flex items-center gap-1">
                        Lihat Semua Riwayat
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                                <th class="py-3 px-4 rounded-l-lg">Jam</th>
                                <th class="py-3 px-4">Nama Lengkap</th>
                                <th class="py-3 px-4 text-center">Status Kuota</th>
                            </tr>
                        </thead>
                        <tbody id="table-recent-logs" class="text-xs text-on-surface divide-y divide-surface-container">
                            <?php if (empty($recent_logs)): ?>
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-outline italic">
                                        Belum ada kunjungan hari ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_logs as $log): ?>
                                    <tr class="hover:bg-surface-container-low transition-all">
                                        <td class="py-3.5 px-4 font-mono font-semibold">
                                            <?= date('H:i', strtotime($log['waktu_kunjungan'])) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold">
                                            <?= esc($log['nama_lengkap'] ?? 'Tidak Diketahui') ?>
                                            <div class="text-[10px] text-outline font-mono mt-0.5">NIK: <?= esc($log['NIK']) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <?php if ($log['kuota_akhir'] <= 2): ?>
                                                <span class="inline-flex items-center gap-1 bg-error/10 text-error px-2 py-1 rounded font-bold">
                                                    <span class="material-symbols-outlined text-[12px]">warning</span>
                                                    Sisa: <?= esc($log['kuota_akhir']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary px-2 py-1 rounded font-bold">
                                                    Sisa: <?= esc($log['kuota_akhir']) ?>
                                                </span>
                                            <?php endif; ?>
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

<footer class="text-center py-6 text-xs text-outline border-t border-outline-variant bg-white ml-[280px]">
    &copy; 2026 MyMember Admin Dashboard.
</footer>

<script>
    function updateDashboardLive() {
        fetch('/admin/dashboard/live')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // Update KPI Cards
                document.getElementById('stat-kunjungan-hari-ini').textContent = data.total_kunjungan_hari_ini;
                document.getElementById('stat-total-member').textContent = data.total_members;

                // Update Table
                const tbody = document.getElementById('table-recent-logs');
                tbody.innerHTML = ''; // Clear current table rows

                if (data.recent_logs.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="py-12 text-center text-outline italic">
                                Belum ada kunjungan hari ini.
                            </td>
                        </tr>
                    `;
                } else {
                    data.recent_logs.forEach(log => {
                        let statusHtml = '';
                        if (log.kuota_akhir <= 2) {
                            statusHtml = `
                                <span class="inline-flex items-center gap-1 bg-error/10 text-error px-2 py-1 rounded font-bold">
                                    <span class="material-symbols-outlined text-[12px]">warning</span>
                                    Sisa: ${log.kuota_akhir}
                                </span>
                            `;
                        } else {
                            statusHtml = `
                                <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary px-2 py-1 rounded font-bold">
                                    Sisa: ${log.kuota_akhir}
                                </span>
                            `;
                        }

                        const nama = log.nama_lengkap ? escapeHtml(log.nama_lengkap) : 'Tidak Diketahui';
                        
                        const row = `
                            <tr class="hover:bg-surface-container-low transition-all">
                                <td class="py-3.5 px-4 font-mono font-semibold">
                                    ${log.jam}
                                </td>
                                <td class="py-3.5 px-4 font-semibold">
                                    ${nama}
                                    <div class="text-[10px] text-outline font-mono mt-0.5">NIK: ${escapeHtml(log.NIK)}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    ${statusHtml}
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                }
            })
            .catch(error => console.error('Error fetching live stats:', error));
    }

    // Helper function to escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Run every 3 seconds
    setInterval(updateDashboardLive, 3000);
</script>

</body>
</html>
