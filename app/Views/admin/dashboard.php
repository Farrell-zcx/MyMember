<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>MyMember Admin - Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
            <div class="glass-panel rounded-2xl p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
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
            <div class="glass-panel rounded-2xl p-6 hover:shadow-md transition-shadow relative overflow-hidden group">
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
            <div class="glass-panel rounded-2xl p-6 overflow-hidden">
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
                            <tr class="glass-header text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
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
                                    <tr class="glass-table-row">
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Real-time polling logic
    let knownDashboardLogIds = new Set();
    
    // Inisialisasi ID yang sudah ada saat halaman pertama kali dimuat
    <?php if (!empty($recent_logs)): ?>
        <?php foreach ($recent_logs as $log): ?>
            knownDashboardLogIds.add("<?= esc($log['id_kunjungan']) ?>");
        <?php endforeach; ?>
    <?php endif; ?>

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
                        const isNew = !knownDashboardLogIds.has(log.id_kunjungan);
                        const rowClass = isNew ? 'glass-table-row row-highlight' : 'glass-table-row';
                        
                        // Tandai sebagai sudah diketahui
                        if (isNew) knownDashboardLogIds.add(log.id_kunjungan);

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
                            <tr class="${rowClass}">
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

    // Run every 2 seconds as requested by user
    setInterval(updateDashboardLive, 2000);
</script>

<?= $this->endSection() ?>
