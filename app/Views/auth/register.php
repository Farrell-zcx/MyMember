<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>MyMember Admin Register</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
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
                    },
                    "fontSize": {
                        "headline-md": ["20px", {
                            "lineHeight": "28px",
                            "fontWeight": "600"
                        }],
                        "label-md": ["14px", {
                            "lineHeight": "20px",
                            "letterSpacing": "0.05em",
                            "fontWeight": "500"
                        }],
                        "body-lg": ["18px", {
                            "lineHeight": "28px",
                            "fontWeight": "400"
                        }],
                        "headline-xl": ["36px", {
                            "lineHeight": "44px",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }],
                        "headline-lg-mobile": ["20px", {
                            "lineHeight": "28px",
                            "fontWeight": "600"
                        }],
                        "body-md": ["16px", {
                            "lineHeight": "24px",
                            "fontWeight": "400"
                        }],
                        "label-sm": ["12px", {
                            "lineHeight": "16px",
                            "fontWeight": "600"
                        }],
                        "body-sm": ["14px", {
                            "lineHeight": "20px",
                            "fontWeight": "400"
                        }],
                        "headline-lg": ["24px", {
                            "lineHeight": "32px",
                            "letterSpacing": "-0.01em",
                            "fontWeight": "600"
                        }]
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

        .login-card-shadow {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class="bg-background text-on-surface min-h-screen flex flex-col">
    <!-- Subtle Decorative Background -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] rounded-full bg-secondary opacity-[0.03] blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] rounded-full bg-primary opacity-[0.03] blur-[120px]"></div>
    </div>
    <!-- Main Content Canvas -->
    <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-margin-mobile md:px-margin-desktop py-xl">
        <!-- Back to Selector Link -->
        <div class="w-full max-w-[440px] mb-6">
            <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-secondary group transition-colors">
                <span class="material-symbols-outlined text-[16px] text-outline group-hover:text-secondary transition-colors" data-icon="arrow_back">arrow_back</span>
                <span class="font-semibold transition-colors">Kembali ke Pemilihan Portal</span>
            </a>
        </div>
        <div class="w-full max-w-[440px]">
            <!-- Brand Identity Header -->
            <div class="text-center mb-xl">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white border border-outline-variant rounded-xl mb-md login-card-shadow">
                    <span class="material-symbols-outlined text-[32px] text-secondary" data-icon="person_add">person_add</span>
                </div>
                <h1 class="font-headline-xl text-headline-xl text-primary tracking-tight">MyMember</h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Register New Admin Portal Account</p>
            </div>

            <!-- Flash Message Notification -->
            <?php if (session()->getFlashdata('msg')): ?>
                <?php 
                    $msg = session()->getFlashdata('msg');
                    $isSuccess = (strpos(strtolower($msg), 'sukses') !== false || strpos(strtolower($msg), 'berhasil') !== false);
                    $bgClass = $isSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-error-container text-on-error-container border-error/10';
                    $iconColor = $isSuccess ? 'text-emerald-600' : 'text-error';
                    $iconName = $isSuccess ? 'check_circle' : 'error';
                ?>
                <div class="mb-md p-md <?= $bgClass ?> border rounded-lg flex items-start gap-xs text-body-sm shadow-sm animate-fade-in">
                    <span class="material-symbols-outlined text-[20px] <?= $iconColor ?> flex-shrink-0" data-icon="<?= $iconName ?>"><?= $iconName ?></span>
                    <div class="flex-grow">
                        <?= esc($msg) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Register Card -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-xl login-card-shadow">
                <form action="/register/process" class="space-y-lg" method="POST">
                    <?= csrf_field() ?>
                    
                    <!-- Nama Resepsionis (Full Name) -->
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase tracking-wider" for="nama_resepsionis">
                            Nama Lengkap (Resepsionis)
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none text-on-surface-variant group-focus-within:text-secondary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" data-icon="badge">badge</span>
                            </div>
                            <input class="w-full h-[52px] pl-[48px] pr-md bg-white border border-outline-variant rounded-lg font-body-md text-body-md focus:ring-2 focus:ring-secondary/10 focus:border-secondary transition-all outline-none" id="nama_resepsionis" name="nama_resepsionis" placeholder="Nama Lengkap" required="" type="text" autocomplete="off" />
                        </div>
                    </div>

                    <!-- Admin Username Field -->
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase tracking-wider" for="username">
                            Admin Username
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none text-on-surface-variant group-focus-within:text-secondary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" data-icon="person">person</span>
                            </div>
                            <input class="w-full h-[52px] pl-[48px] pr-md bg-white border border-outline-variant rounded-lg font-body-md text-body-md focus:ring-2 focus:ring-secondary/10 focus:border-secondary transition-all outline-none" id="username" name="username" placeholder="Username" required="" type="text" autocomplete="off" />
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-xs">
                        <label class="font-label-sm text-label-sm text-on-surface-variant block uppercase tracking-wider" for="password">
                            Password
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none text-on-surface-variant group-focus-within:text-secondary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" data-icon="lock">lock</span>
                            </div>
                            <input class="w-full h-[52px] pl-[48px] pr-md bg-white border border-outline-variant rounded-lg font-body-md text-body-md focus:ring-2 focus:ring-secondary/10 focus:border-secondary transition-all outline-none" id="password" name="password" placeholder="••••••••" required="" type="password" />
                        </div>
                    </div>

                    <!-- Sign Up Button -->
                    <button class="w-full h-[52px] bg-secondary text-on-secondary font-label-md text-label-md rounded-lg hover:bg-[#004ca5] active:scale-[0.98] transition-all flex items-center justify-center gap-xs shadow-md" type="submit">
                        <span>Register Account</span>
                        <span class="material-symbols-outlined text-[18px]" data-icon="person_add">person_add</span>
                    </button>
                </form>

                <!-- Back to Login Redirect Link -->
                <div class="mt-lg text-center">
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        Already have an account? <a class="text-secondary font-semibold hover:underline" href="/login">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Decorative Bar -->
    <footer class="relative z-10 py-lg px-margin-desktop text-center">
        <p class="font-label-sm text-label-sm text-outline tracking-wider">
            © 2026 MyMember Admin.
        </p>
    </footer>

    <!-- Micro-interaction Script -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            setTimeout(() => {
                btn.disabled = true;
            }, 0);

            btn.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Registering Account...</span>
        `;
        });
    </script>
</body>

</html>
