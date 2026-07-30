<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>MyMember Admin - Riwayat Kunjungan<?= $this->endSection() ?>


<?= $this->section('content') ?>
<div class="p-8 max-w-[1440px] mx-auto">
    <!-- Page Header -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Riwayat Kunjungan</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Data riwayat kunjungan check-in member menggunakan OCR.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="get" action="<?= base_url('admin/log-kunjungan') ?>" class="glass-panel rounded-2xl p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-on-surface mb-1">Start Date</label>
                <input type="date" name="start_date" value="<?= esc($start_date ?? '') ?>" class="w-full bg-surface-container border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-on-surface mb-1">End Date</label>
                <input type="date" name="end_date" value="<?= esc($end_date ?? '') ?>" class="w-full bg-surface-container border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-medium hover:bg-primary/90 transition-colors">Filter</button>
                <?php if (!empty($start_date) || !empty($end_date)): ?>
                    <a href="<?= base_url('admin/log-kunjungan') ?>" class="bg-surface-container-high text-on-surface px-6 py-2 rounded-lg font-medium hover:bg-surface-container transition-colors">Reset</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <section class="space-y-6">
        <div class="glass-panel rounded-2xl p-6 overflow-hidden">
            <div class="flex justify-between items-center mb-4 border-b border-outline-variant pb-3">
                <h2 class="font-headline-md text-headline-md text-primary uppercase tracking-wider">
                    Log Check-in Terbaru
                </h2>
                <div class="flex gap-2">
                    <?php
                    $exportParams = [];
                    if (!empty($start_date)) $exportParams['start_date'] = $start_date;
                    if (!empty($end_date)) $exportParams['end_date'] = $end_date;
                    $queryStr = !empty($exportParams) ? '?' . http_build_query($exportParams) : '';
                    ?>
                    <a href="<?= base_url('admin/log-kunjungan/export/excel') . $queryStr ?>" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Excel
                    </a>
                    <a href="<?= base_url('admin/log-kunjungan/export/pdf') . $queryStr ?>" class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="glass-header text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                            <th class="py-3 px-4 rounded-l-lg">Waktu Check-in</th>
                            <th class="py-3 px-4">NIK</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Tipe Member</th>
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
                                    <td class="py-3.5 px-4 font-semibold"><?= esc($log['type_member'] ?? '-') ?></td>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Real-time table search functionality
    const searchInput = document.getElementById('globalSearch');

    function applySearchFilter() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#logsTableBody tr');

        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;

            const cells = row.getElementsByTagName('td');
            if (cells.length < 4) return;

            const waktu = cells[0].textContent.toLowerCase();
            const nik = cells[1].textContent.toLowerCase();
            const nama = cells[2].textContent.toLowerCase();
            const tipe = cells[3].textContent.toLowerCase();

            const matches = nik.includes(query) || nama.includes(query) || waktu.includes(query) || tipe.includes(query);
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
            const urlParams = new URLSearchParams(window.location.search);
            const startDate = urlParams.get('start_date') || '';
            const endDate = urlParams.get('end_date') || '';

            const response = await fetch('<?= base_url("admin/log-kunjungan/live") ?>?t=' + new Date().getTime() + '&start_date=' + startDate + '&end_date=' + endDate);
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
                        const rowClass = isNew ? 'glass-table-row row-highlight hover:bg-surface-container/50 transition-colors' : 'glass-table-row hover:bg-surface-container/50 transition-colors border-b border-surface-container';

                        // Tandai sebagai sudah diketahui
                        if (isNew) knownLogIds.add(log.id_kunjungan);

                        const namaLengkap = log.nama_lengkap ? log.nama_lengkap : 'Tidak Diketahui';
                        const typeMember = log.type_member ? log.type_member : '-';

                        // Menjaga agar ID tetap aman (sanitize string)
                        const safeNik = log.NIK.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        const safeNama = namaLengkap.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        const safeTipe = typeMember.replace(/</g, "&lt;").replace(/>/g, "&gt;");

                        newHtml += `
                            <tr class="${rowClass}">
                                <td class="py-3.5 px-4 font-mono font-semibold">${log.waktu_format}</td>
                                <td class="py-3.5 px-4 font-mono font-bold text-on-surface">${safeNik}</td>
                                <td class="py-3.5 px-4 font-semibold">${safeNama}</td>
                                <td class="py-3.5 px-4 font-semibold">${safeTipe}</td>
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
        } catch (e) {}
    }, 2000);
</script>
<?= $this->endSection() ?>