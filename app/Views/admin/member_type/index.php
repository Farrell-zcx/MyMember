<?php
/**
 * @var string|null $old_nik
 * @var string|null $nik
 * @var string|null $nama_lengkap
 * @var string|null $nomor_hp
 * @var string|null $email
 * @var int|string|null $id_type
 * @var int|string|null $sisa_kuota
 * @var string|null $tgl_expired_member
 * @var bool $is_edit
 * @var string|null $status
 * @var array $members
 */
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>MyMember Admin - Member CRUD<?= $this->endSection() ?>



<?= $this->section('content') ?>
    <input type="file" id="ktpInputFile" accept="image/*" class="hidden">

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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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
        const searchInput = document.getElementById('globalSearch');
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
<?= $this->endSection() ?>
