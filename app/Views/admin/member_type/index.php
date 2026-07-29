<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <?php
    /** @noinspection PhpUndefinedVariableInspection */
    /** @noinspection PhpUndefinedFunctionInspection */
    /**
     * @var string $nik
     * @var string $nama_lengkap
     * @var string $nomor_hp
     * @var string $email
     * @var int|string $id_type
     * @var int|string $sisa_kuota
     * @var string $tgl_expired_member
     * @var bool $is_edit
     * @var string $old_nik
     * @var string $status
     * @var array $members
     */
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMember Admin - Member CRUD</title>
    <!-- Google Fonts: Hanken Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Liquidglass Theme -->
    <link href="<?= base_url('css/liquidglass.css') ?>" rel="stylesheet" />
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
        } catch (_e) {}
    </script>
    <meta charset="utf-8">
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
            <!-- Member Type (Active) -->
            <a href="/admin/member-type" class="flex items-center px-4 py-3 transition-colors duration-200 text-secondary dark:text-secondary-fixed font-bold border-r-4 border-secondary font-body-md text-body-md bg-secondary/5 group">
                <span class="material-symbols-outlined mr-4" data-icon="card_membership" style="font-variation-settings: 'FILL' 1;">card_membership</span>
                <span class="">Kelola Member</span>
            </a>
            <!-- Riwayat Kunjungan -->
            <a href="/admin/log-kunjungan" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
                <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="history">history</span>
                <span class="">Riwayat Kunjungan</span>
            </a>
            <!-- Kiosk Controller -->
            <a href="/admin/kiosk" class="flex items-center px-4 py-3 transition-colors duration-200 hover:bg-surface-container dark:hover:bg-on-surface-variant text-on-surface-variant dark:text-surface-variant font-body-md text-body-md group">
                <span class="material-symbols-outlined mr-4 group-hover:text-secondary transition-colors" data-icon="aod">aod</span>
                <span class="">Kiosk Controller</span>
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
    <header class="fixed top-0 right-0 h-16 ml-[280px] w-[calc(100%-280px)] glass-panel flex justify-between items-center px-margin-desktop z-40 transition-all duration-150 border-b-0">
        <div class="flex items-center flex-1 max-w-xl">
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
                <input id="crudSearch" class="w-full glass-input rounded-full pl-10 pr-4 py-2 text-label-md font-label-md transition-all" placeholder="Search members..." type="text">
            </div>
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

    <input type="file" id="ktpInputFile" accept="image/*" class="hidden">

    <!-- Main Canvas -->
    <main class="ml-[280px] pt-16 min-h-screen">
        <div class="p-margin-desktop max-w-[1440px] mx-auto">

            <!-- Flash Status Notice -->
            <?php if (!empty($status)): ?>
                <div id="statusNotice" class="mb-md p-md bg-secondary/10 text-secondary border border-secondary/20 rounded-lg flex items-start gap-xs text-body-sm shadow-sm animate-fade-in">
                    <span class="material-symbols-outlined text-[20px] text-secondary flex-shrink-0" data-icon="check_circle">check_circle</span>
                    <div class="flex-grow font-semibold">
                        <?php
                        if ($status === 'sukses_simpan') echo 'Data member berhasil disimpan!';
                        elseif ($status === 'sukses_update') echo 'Data member berhasil diperbarui!';
                        elseif ($status === 'terhapus') echo 'Data member berhasil dihapus!';
                        else echo esc($status);
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Member Management</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">Add, update, delete and scan members with AI OCR integration.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- KIRI: FORM ACTION -->
                <section class="lg:col-span-1 space-y-6">
                    <div class="glass-panel rounded-2xl p-6">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 border-b border-outline-variant pb-2">
                            Form Input / Edit Member
                        </h2>

                        <form action="/admin/member-type" method="POST" class="space-y-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="old_nik" value="<?= esc($old_nik); ?>">

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Nomor NIK</label>
                                <input type="text" name="nik" value="<?= esc($nik); ?>" placeholder="Scan KTP untuk mengisi..." required maxlength="16"
                                    class="w-full glass-input rounded-lg px-3 py-2 text-sm font-mono font-bold text-on-surface focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= esc($nama_lengkap); ?>" placeholder="Masukkan nama..." required
                                    class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Nomor HP</label>
                                <input type="text" name="nomor_hp" value="<?= esc($nomor_hp); ?>" placeholder="Contoh: 08123456789" required
                                    class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Email</label>
                                <input type="email" name="email" value="<?= esc($email); ?>" placeholder="nama@email.com" required
                                    class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <!-- ID TYPE DROPDOWN -->
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">ID Type (FK)</label>
                                    <select name="id_type" class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                                        <option value="1" <?= ($id_type == 1) ? 'selected' : '' ?>>Platinum Member</option>
                                        <option value="2" <?= ($id_type == 2) ? 'selected' : '' ?>>Gold Member</option>
                                        <option value="3" <?= ($id_type == 3) ? 'selected' : '' ?>>Silver Member</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Sisa Kuota</label>
                                    <input type="number" name="sisa_kuota" value="<?= esc($sisa_kuota); ?>" placeholder="0"
                                        class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Tanggal Expired Member</label>
                                <input type="date" name="tgl_expired_member" value="<?= esc($tgl_expired_member); ?>"
                                    class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                            </div>

                            <div class="flex space-x-2 pt-2">
                                <button type="submit" name="action" value="create" <?= $is_edit ? 'disabled class="flex-1 bg-surface-container text-outline font-bold py-2.5 rounded-lg text-xs tracking-wider uppercase cursor-not-allowed"' : 'class="flex-1 bg-secondary text-on-secondary font-bold py-2.5 rounded-lg text-xs tracking-wider uppercase shadow-md cursor-pointer hover:bg-secondary-container hover:shadow-lg transition-all active:scale-[0.98]"'; ?>>
                                    Simpan
                                </button>
                                <button type="submit" name="action" value="update" <?= !$is_edit ? 'disabled class="flex-1 bg-surface-container text-outline font-bold py-2.5 rounded-lg text-xs tracking-wider uppercase cursor-not-allowed"' : 'class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-lg text-xs tracking-wider uppercase shadow-md cursor-pointer transition-all active:scale-[0.98]"'; ?>>
                                    Update
                                </button>
                            </div>

                            <?php if ($is_edit): ?>
                                <div class="text-center pt-1">
                                    <a href="/admin/member-type" class="text-xs text-rose-600 underline font-semibold hover:text-rose-700">Batal Edit / Tambah Baru</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </section>

                <!-- KANAN: TABEL DATA MASTER MEMBER -->
                <section class="lg:col-span-2 space-y-6">
                    <div class="glass-panel rounded-2xl p-6 overflow-hidden">
                        <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-3">
                            <h2 class="font-headline-md text-headline-md text-primary uppercase tracking-wider">
                                Master Data Member Terdaftar
                            </h2>
                            <button id="btnScanKtp" class="bg-secondary text-on-secondary hover:bg-secondary-container hover:shadow-lg text-xs font-bold px-4 py-2 rounded-lg flex items-center gap-1 shadow-sm cursor-pointer transition-all active:scale-95">
                                <span class="material-symbols-outlined text-[16px]" data-icon="document_scanner">document_scanner</span>
                                <span>Scan KTP</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="glass-header text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                                        <th class="py-3 px-4 rounded-l-lg">NIK</th>
                                        <th class="py-3 px-4">Nama Lengkap</th>
                                        <th class="py-3 px-4">Kontak & Masa Aktif</th>
                                        <th class="py-3 px-4 rounded-r-lg text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-xs text-on-surface divide-y divide-surface-container">
                                    <?php if (empty($members)): ?>
                                        <tr>
                                            <td colspan="4" class="py-12 text-center text-outline italic">
                                                Belum ada data member di database. Silakan klik "Scan KTP" untuk mengisi form otomatis!
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($members as $m): ?>
                                            <tr class="glass-table-row">
                                                <td class="py-3.5 px-4 font-mono font-bold text-on-surface"><?= esc($m['NIK']) ?></td>
                                                <td class="py-3.5 px-4 font-semibold"><?= esc($m['nama_lengkap']) ?></td>
                                                <td class="py-3.5 px-4 leading-relaxed">
                                                    HP: <?= esc($m['nomor_hp']) ?><br>
                                                    Email: <?= esc($m['email']) ?><br>
                                                    <span class="text-[10px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded-sm">Exp: <?= esc($m['tgl_expired_member'] ?? 'No Limit') ?></span>
                                                </td>
                                                <td class="py-3.5 px-4 text-center space-x-1 whitespace-nowrap">
                                                    <a href="/admin/member-type?edit=<?= esc($m['NIK']) ?>" class="bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold px-2.5 py-1.5 rounded-md transition-all">Edit</a>
                                                    <button onclick="hapusMember('<?= esc($m['NIK']) ?>')" class="bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold px-2.5 py-1.5 rounded-md transition-all">Hapus</button>
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
        </div>
    </main>

    <footer class="text-center py-6 text-xs text-outline glass-panel ml-[280px] border-t-0">
        &copy; 2026 MyMember Admin Dashboard.
    </footer>

    <script>
        function hapusMember(nik) {
            Swal.fire({
                title: 'Yakin mau hapus?',
                text: "Data member dengan NIK " + nik + " akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#f7f9fb',
                color: '#191c1e',
                borderRadius: '0.75rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/admin/member-type?delete=' + nik;
                }
            });
        }

        const btnScanKtp = document.getElementById('btnScanKtp');
        const ktpInputFile = document.getElementById('ktpInputFile');

        btnScanKtp.addEventListener('click', () => {
            ktpInputFile.click();
        });

        ktpInputFile.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const originalBtnContent = btnScanKtp.innerHTML;
            btnScanKtp.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>AI Scanning...</span>
        `;
            btnScanKtp.disabled = true;

            const formData = new FormData();
            formData.append('ktp_image', file);

            try {
                const response = await fetch('<?= base_url("admin/member-type/scan-ocr") ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === "sukses" && result.data_ktp) {
                    const data = result.data_ktp;
                    if (data.nik && data.nik !== "Tidak terdeteksi") {
                        document.querySelector('input[name="nik"]').value = data.nik;

                        // Fetch existing member details if NIK is already registered
                        fetch(`<?= base_url('ocr/get-member') ?>?nik=${data.nik}`)
                            .then(res => res.json())
                            .then(memberResult => {
                                if (memberResult.status === 'sukses' && memberResult.exists) {
                                    const m = memberResult.data;
                                    if (m.nomor_hp) document.querySelector('input[name="nomor_hp"]').value = m.nomor_hp;
                                    if (m.email) document.querySelector('input[name="email"]').value = m.email;
                                    if (m.id_type) document.querySelector('select[name="id_type"]').value = m.id_type;
                                    if (m.sisa_kuota !== undefined) document.querySelector('input[name="sisa_kuota"]').value = m.sisa_kuota;
                                    if (m.tgl_expired_member) document.querySelector('input[name="tgl_expired_member"]').value = m.tgl_expired_member;
                                }
                            })
                            .catch(err => console.error("Error loading existing member:", err));
                    }
                    if (data.nama && data.nama !== "Tidak terdeteksi") document.querySelector('input[name="nama_lengkap"]').value = data.nama;
                    alert("KTP Ter-scan! NIK & Nama Lengkap sukses terisi otomatis.");
                } else {
                    alert(result.pesan || "AI gagal membaca KTP.");
                }
            } catch (error) {
                alert("Gagal konek ke FastAPI server!");
            } finally {
                btnScanKtp.innerHTML = originalBtnContent;
                btnScanKtp.disabled = false;
                ktpInputFile.value = '';
            }
        });

        // Real-time table search functionality
        const searchInput = document.getElementById('crudSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    // Skip placeholder row if no data
                    if (row.querySelector('td[colspan]')) {
                        return;
                    }

                    const cells = row.getElementsByTagName('td');
                    if (cells.length < 3) return;

                    const nik = cells[0] ? cells[0].textContent.toLowerCase() : '';
                    const nama = cells[1] ? cells[1].textContent.toLowerCase() : '';
                    const kontak = cells[2] ? cells[2].textContent.toLowerCase() : '';

                    const matches = nik.includes(query) || nama.includes(query) || kontak.includes(query);
                    row.style.display = matches ? '' : 'none';
                });
            });
        }

        // Auto-hide status notice after 3 seconds
        const statusNotice = document.getElementById('statusNotice');
        if (statusNotice) {
            setTimeout(() => {
                statusNotice.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                statusNotice.style.opacity = '0';
                statusNotice.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    statusNotice.remove();
                }, 500);
            }, 3000);
        }
        // Real-time Kiosk Sync (Polling)
        setInterval(async () => {
            try {
                const response = await fetch('<?= base_url("admin/member-type/poll-scan") ?>?t=' + new Date().getTime());
                if (response.status !== 200) return;
                const result = await response.json();

                if (result.status === "sukses" && result.data_ktp) {
                    const data = result.data_ktp;
                    if (data.nik && data.nik !== "Tidak terdeteksi") {
                        document.querySelector('input[name="nik"]').value = data.nik;

                        // Fetch existing member details if NIK is already registered
                        fetch(`<?= base_url('ocr/get-member') ?>?nik=${data.nik}`)
                            .then(res => res.json())
                            .then(memberResult => {
                                if (memberResult.status === 'sukses' && memberResult.exists) {
                                    const m = memberResult.data;
                                    if (m.nomor_hp) document.querySelector('input[name="nomor_hp"]').value = m.nomor_hp;
                                    if (m.email) document.querySelector('input[name="email"]').value = m.email;
                                    if (m.id_type) document.querySelector('select[name="id_type"]').value = m.id_type;
                                    if (m.sisa_kuota !== undefined) document.querySelector('input[name="sisa_kuota"]').value = m.sisa_kuota;
                                    if (m.tgl_expired_member) document.querySelector('input[name="tgl_expired_member"]').value = m.tgl_expired_member;
                                }
                            })
                            .catch(err => console.error("Error loading existing member:", err));
                    }
                    if (data.nama && data.nama !== "Tidak terdeteksi") document.querySelector('input[name="nama_lengkap"]').value = data.nama;

                    // Show floating notification instead of blocking alert
                    const alertHtml = `
                    <div id="kioskNotice" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;" class="p-4 bg-secondary/10 text-secondary border border-secondary/20 rounded-lg flex items-center gap-3 shadow-lg">
                        <span class="material-symbols-outlined text-[28px]">contactless</span>
                        <div>
                            <div class="font-bold">KTP Ter-scan via Kiosk!</div>
                            <div class="text-sm">NIK & Nama Lengkap sukses ditarik.</div>
                        </div>
                    </div>
                `;
                    document.body.insertAdjacentHTML('beforeend', alertHtml);
                    setTimeout(() => {
                        const el = document.getElementById('kioskNotice');
                        if (el) el.remove();
                    }, 4000);
                }
            } catch (e) {
                // silent fail on polling error
            }
        }, 2000); // poll every 2 seconds
    </script>
</body>

</html>
