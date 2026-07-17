<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>MyMember Portal Selector</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
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
            box-shadow: 0 10px 30px -10px rgba(0, 88, 190, 0.05);
        }
    </style>
</head>

<body class="bg-background text-on-surface min-h-screen flex flex-col justify-between">
    <!-- Subtle Decorative Background Shapes -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] rounded-full bg-secondary opacity-[0.03] blur-[150px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] rounded-full bg-primary opacity-[0.03] blur-[150px]"></div>
    </div>

    <!-- Header Logo Identity -->
    <header class="relative z-10 w-full px-6 py-6 max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[32px] text-secondary font-bold" data-icon="shield_person">shield_person</span>
            <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">MyMember</h1>
        </div>
        <span class="text-xs bg-surface-container-high text-on-surface-variant border border-outline-variant px-3 py-1.5 rounded-full font-bold uppercase tracking-wider">
            Portal Selection
        </span>
    </header>

    <!-- Main Selection Cards -->
    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-margin-mobile py-xl w-full max-w-4xl mx-auto space-y-xl">
        <!-- Title Info -->
        <div class="text-center space-y-sm">
            <h2 class="font-headline-xl text-headline-xl md:text-[40px] text-primary tracking-tight leading-tight">Selamat Datang di MyMember</h2>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-lg mx-auto">
                Silakan pilih jenis portal di bawah ini untuk memulai akses Anda ke dalam sistem.
            </p>
        </div>

        <!-- Role Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg w-full">
            <!-- Card 1: MEMBER CHECK-IN -->
            <a href="<?= base_url('ocr') ?>" class="group bg-surface-container-lowest border border-outline-variant hover:border-secondary hover:ring-2 hover:ring-secondary/15 rounded-2xl p-xl card-shadow flex flex-col justify-between space-y-lg transition-all duration-300 transform hover:-translate-y-1 active:scale-[0.99] text-left">
                <div class="space-y-md">
                    <!-- Icon Wrapper -->
                    <div class="w-14 h-14 bg-secondary/10 text-secondary rounded-xl flex items-center justify-center group-hover:bg-secondary group-hover:text-on-secondary transition-all">
                        <span class="material-symbols-outlined text-[32px]" data-icon="document_scanner">document_scanner</span>
                    </div>
                    <!-- Text Info -->
                    <div class="space-y-base">
                        <h3 class="font-headline-lg text-headline-lg text-primary group-hover:text-secondary transition-colors">
                            MEMBER CHECK-IN
                        </h3>
                        <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Tap to Scan KTP</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                            Pindai KTP Anda secara mandiri di sini untuk check-in kunjungan harian secara instan.
                        </p>
                    </div>
                </div>
                <!-- Action Arrow -->
                <div class="flex items-center gap-xs text-secondary font-bold text-xs uppercase tracking-wider pt-md border-t border-outline-variant/30">
                    <span>Mulai Scan KTP</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform" data-icon="arrow_forward">arrow_forward</span>
                </div>
            </a>

            <!-- Card 2: ADMIN PORTAL -->
            <a href="<?= base_url('login') ?>" class="group bg-surface-container-lowest border border-outline-variant hover:border-secondary hover:ring-2 hover:ring-secondary/15 rounded-2xl p-xl card-shadow flex flex-col justify-between space-y-lg transition-all duration-300 transform hover:-translate-y-1 active:scale-[0.99] text-left">
                <div class="space-y-md">
                    <!-- Icon Wrapper -->
                    <div class="w-14 h-14 bg-secondary/10 text-secondary rounded-xl flex items-center justify-center group-hover:bg-secondary group-hover:text-on-secondary transition-all">
                        <span class="material-symbols-outlined text-[32px]" data-icon="shield_person">shield_person</span>
                    </div>
                    <!-- Text Info -->
                    <div class="space-y-base">
                        <h3 class="font-headline-lg text-headline-lg text-primary group-hover:text-secondary transition-colors">
                            ADMIN PORTAL
                        </h3>
                        <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Staff Sign In</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">
                            Khusus untuk Resepsionis / Admin guna mengelola tipe member, data transaksi, dan log kunjungan.
                        </p>
                    </div>
                </div>
                <!-- Action Arrow -->
                <div class="flex items-center gap-xs text-secondary font-bold text-xs uppercase tracking-wider pt-md border-t border-outline-variant/30">
                    <span>Login Petugas</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform" data-icon="arrow_forward">arrow_forward</span>
                </div>
            </a>
        </div>
    </main>

    <!-- Footer Bar -->
    <footer class="relative z-10 py-6 text-center">
        <p class="font-label-sm text-label-sm text-outline tracking-wider uppercase">
            &copy; 2026 MyMember Corporate. All Rights Reserved.
        </p>
    </footer>
</body>

</html>
