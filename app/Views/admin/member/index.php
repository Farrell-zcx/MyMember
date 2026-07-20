<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMember Admin - Daftar Member</title>
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
        <!-- Dashboard -->
        <a href="/admin/dashboard" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="dashboard">dashboard</span>
            <span class="">Dashboard</span>
        </a>
        <!-- Members (Active) -->
        <a href="/admin/member" class="flex items-center px-4 py-3 transition-colors duration-200 text-secondary dark:text-secondary-fixed font-bold border-r-4 border-secondary font-body-md text-body-md bg-secondary/5 group">
            <span class="material-symbols-outlined mr-4" data-icon="group" style="font-variation-settings: 'FILL' 1;">group</span>
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
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
            <input id="crudSearch" class="w-full bg-surface-container-low border-none rounded-full pl-10 pr-4 py-2 text-label-md font-label-md focus:ring-2 focus:ring-secondary/20 transition-all" placeholder="Cari member..." type="text">
        </div>
    </div>
</header>

<!-- Main Canvas -->
<main class="ml-[280px] pt-16 min-h-screen">
    <div class="p-8 max-w-[1440px] mx-auto">
        <!-- Page Header -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Daftar Member</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">List lengkap seluruh member yang terdaftar di sistem.</p>
            </div>
        </div>

        <section class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-outline-variant p-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                                <th class="py-3 px-4 rounded-l-lg">NIK</th>
                                <th class="py-3 px-4">Nama Lengkap</th>
                                <th class="py-3 px-4">Kontak</th>
                                <th class="py-3 px-4">Tipe Member</th>
                                <th class="py-3 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3 px-4 rounded-r-lg">Masa Aktif</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs text-on-surface divide-y divide-surface-container">
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-outline italic">
                                        Belum ada data member.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $m): ?>
                                    <tr class="hover:bg-surface-container-low transition-all">
                                        <td class="py-3.5 px-4 font-mono font-bold text-on-surface"><?= esc($m['NIK']) ?></td>
                                        <td class="py-3.5 px-4 font-semibold"><?= esc($m['nama_lengkap']) ?></td>
                                        <td class="py-3.5 px-4 leading-relaxed">
                                            <?= esc($m['nomor_hp']) ?><br>
                                            <span class="text-on-surface-variant"><?= esc($m['email']) ?></span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="bg-primary/10 text-primary font-bold px-2 py-1 rounded-sm"><?= esc($m['type_member'] ?? 'Unknown') ?></span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary px-2 py-1 rounded font-bold">
                                                <?= esc($m['sisa_kuota']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold">
                                            <?= !empty($m['tgl_expired_member']) ? date('d M Y', strtotime($m['tgl_expired_member'])) : 'No Limit' ?>
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
    // Real-time table search functionality
    const searchInput = document.getElementById('crudSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return;
                
                const cells = row.getElementsByTagName('td');
                if (cells.length < 3) return;
                
                const nik = cells[0].textContent.toLowerCase();
                const nama = cells[1].textContent.toLowerCase();
                const kontak = cells[2].textContent.toLowerCase();
                
                const matches = nik.includes(query) || nama.includes(query) || kontak.includes(query);
                row.style.display = matches ? '' : 'none';
            });
        });
    }
</script>
</body>
</html>
