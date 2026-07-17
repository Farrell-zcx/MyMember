<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <?php 
    /** @noinspection PhpUndefinedVariableInspection */ 
    /** @noinspection PhpUndefinedFunctionInspection */ 
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMember Admin - Tambah Tipe Member</title>
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
            },
            "fontSize": {
                    "headline-md": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                    "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "500"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "headline-xl": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "headline-lg-mobile": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "600"}],
                    "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}]
            }
          },
        },
      }
    } catch(_e) {}
    </script>
    <meta charset="utf-8">
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
        <!-- Members -->
        <a href="/admin/member-type" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="group">group</span>
            <span class="">Members</span>
        </a>
        <!-- Billing -->
        <a href="#" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="payments">payments</span>
            <span class="">payments</span>
        </a>
        <!-- Reports -->
        <a href="#" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="assessment">assessment</span>
            <span class="">Reports</span>
        </a>
        <!-- Settings -->
        <a href="#" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
            <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="settings">settings</span>
            <span class="">Settings</span>
        </a>
        <!-- Logout -->
        <a href="/logout" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 font-body-md text-body-md group">
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
<header class="fixed top-0 right-0 h-16 ml-[280px] w-[calc(100%-280px)] bg-surface-container-lowest dark:bg-inverse-surface flex justify-between items-center px-margin-desktop shadow-sm border-b border-outline-variant dark:border-outline z-40 transition-all duration-150">
    <div class="flex items-center flex-1 max-w-xl">
        <h2 class="font-headline-md text-headline-md text-primary">Add Member Type</h2>
    </div>
    <div class="flex items-center gap-4">
        <button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:text-secondary hover:bg-secondary/5 transition-colors">
            <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
        </button>
        <button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:text-secondary hover:bg-secondary/5 transition-colors">
            <span class="material-symbols-outlined" data-icon="help_outline">help_outline</span>
        </button>
        <div class="h-6 w-[1px] bg-outline-variant mx-2"></div>
        <div class="flex items-center gap-2 cursor-pointer group">
            <span class="font-label-md text-label-md text-on-surface group-hover:text-secondary transition-colors">MyMember HQ</span>
            <span class="material-symbols-outlined text-outline" data-icon="expand_more">expand_more</span>
        </div>
    </div>
</header>

<!-- Main Canvas -->
<main class="ml-[280px] pt-16 min-h-screen flex items-center justify-center">
    <div class="p-margin-desktop w-full max-w-[600px]">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant p-xl">
            <h3 class="font-headline-xl text-headline-xl text-primary mb-6">Tambah Tipe Member Baru</h3>
            
            <form action="<?= base_url('admin/member-type/store') ?>" method="POST" class="space-y-lg">
                <?= csrf_field() ?>
                
                <div class="space-y-xs">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Nama Tipe Member</label>
                    <input type="text" name="type_member" placeholder="Contoh: Silver, Gold, Platinum" required
                        class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary focus:bg-white transition-all">
                </div>

                <div class="space-y-xs">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Kuota Kunjungan (Default)</label>
                    <input type="number" name="kuota_kunjungan" placeholder="Contoh: 10" required
                        class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary focus:bg-white transition-all">
                </div>

                <div class="space-y-xs">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Deskripsi Benefit</label>
                    <textarea name="deskripsi_benefit" rows="4" placeholder="Detail keuntungan tipe member..."
                        class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary focus:bg-white transition-all"></textarea>
                </div>

                <div class="flex justify-between gap-md pt-4">
                    <a href="<?= base_url('admin/member-type') ?>" class="px-6 py-3 border border-outline-variant text-outline rounded-lg hover:bg-surface-container-low font-label-md text-label-md transition-all active:scale-[0.98]">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-3 bg-secondary text-on-secondary rounded-lg hover:bg-secondary-container hover:shadow-lg font-label-md text-label-md transition-all active:scale-[0.98] shadow-md">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="text-center py-6 text-xs text-outline border-t border-outline-variant bg-white ml-[280px]">
    &copy; 2026 MyMember Admin Dashboard.
</footer>

</body>
</html>