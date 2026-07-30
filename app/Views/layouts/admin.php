<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?? 'MyMember Admin' ?></title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Liquidglass Theme -->
    <link href="<?= base_url('css/liquidglass.css') ?>" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        /* Micro-interactions */
        .glass-panel {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .glass-card-glow:hover {
            box-shadow: 0 0 15px rgba(0, 229, 255, 0.4);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(0, 229, 255, 0.3);
            border-right: 4px solid #fff;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="bg-surface-bright text-on-surface">

<!-- SideNavBar -->
<aside class="fixed left-0 top-0 h-full w-[280px] flex flex-col py-6 transition-colors duration-200 z-50 border-r border-white/10 shadow-lg bg-gradient-to-b from-[#00daf3] to-[#0058be] text-white">
    <div class="px-6 mb-10">
        <div class="flex items-center gap-3 mb-4 p-3 rounded-2xl bg-white/30 backdrop-blur-lg border border-white/40 shadow-lg">
            <div class="bg-white p-1.5 rounded-xl shadow-sm">
                <img src="<?= base_url('images/logo.png?v=' . time()) ?>" alt="MyMember" class="h-10 w-auto filter drop-shadow-md" />
            </div>
            <h1 class="font-headline-xl text-2xl text-white tracking-tight drop-shadow-sm"><span class="font-bold text-[#0058be]">My</span>Member</h1>
        </div>
        <p class="text-label-sm text-white/80">Admin Portal</p>
    </div>
    
    <?php
    $current_uri = uri_string();
    $menu_items = [
        ['url' => 'admin/dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
        ['url' => 'admin/kiosk', 'icon' => 'aod', 'label' => 'Kiosk Controller'],
        ['url' => 'admin/member', 'icon' => 'group', 'label' => 'Daftar Member'],
        ['url' => 'admin/member-type', 'icon' => 'card_membership', 'label' => 'Kelola Member'],
        ['url' => 'admin/log-kunjungan', 'icon' => 'history', 'label' => 'Riwayat Kunjungan'],
    ];
    ?>
    <nav class="flex-1 space-y-2 px-3">
        <?php foreach ($menu_items as $item): 
            $is_active = ($current_uri === $item['url'] || strpos($current_uri, $item['url'] . '/') === 0);
        ?>
        <a href="/<?= $item['url'] ?>" class="flex items-center px-4 py-3 rounded-lg text-white font-body-md text-body-md sidebar-item <?= $is_active ? 'active font-bold' : '' ?>">
            <span class="material-symbols-outlined mr-4" data-icon="<?= $item['icon'] ?>" <?= $is_active ? 'style="font-variation-settings: \'FILL\' 1;"' : '' ?>><?= $item['icon'] ?></span>
            <span><?= $item['label'] ?></span>
        </a>
        <?php endforeach; ?>
        
        <!-- Logout -->
        <a href="/logout" class="flex items-center px-4 py-3 mt-8 rounded-lg text-rose-100 hover:text-white font-body-md text-body-md sidebar-item hover:bg-rose-500/20">
            <span class="material-symbols-outlined mr-4" data-icon="logout">logout</span>
            <span>Logout</span>
        </a>
    </nav>
    <div class="px-6 mt-auto">
        <div class="flex items-center p-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white shadow-lg">
            <div>
                <p class="font-label-md text-label-md leading-tight font-semibold"><?= esc(session()->get('nama_resepsionis') ?? 'Admin Rivera') ?></p>
                <p class="text-[10px] text-white/70 uppercase tracking-wider"><?= esc(session()->get('username') ?? 'super_admin') ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- TopAppBar Anchor -->
<header class="fixed top-0 right-0 h-16 ml-[280px] w-[calc(100%-280px)] glass-panel flex justify-between items-center px-8 z-40 transition-all duration-150 border-b border-white/40 shadow-sm backdrop-blur-[40px] bg-white/40">
    <div class="flex items-center flex-1 max-w-xl">
        <div class="relative w-full mr-4">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
            <input id="globalSearch" class="w-full glass-input rounded-full pl-10 pr-4 py-2 text-label-md font-label-md transition-all border border-white/40 focus:border-white focus:ring-2 focus:ring-white/50" placeholder="Cari Member / NIK / Tipe..." type="text">
        </div>
    </div>
    <div class="flex items-center gap-4 shrink-0">
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
<main class="ml-[280px] pt-16 px-6 lg:px-12 min-h-screen relative z-10">
    <?= $this->renderSection('content') ?>
</main>

<?= $this->renderSection('scripts') ?>

<!-- Ensure glass card glow script is executed for dynamically generated elements if needed -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.glass-panel');
    cards.forEach(card => {
        if(!card.classList.contains('glass-card-glow') && card.tagName !== 'HEADER' && card.tagName !== 'ASIDE') {
            card.classList.add('glass-card-glow');
        }
    });
});
</script>
</body>
</html>
