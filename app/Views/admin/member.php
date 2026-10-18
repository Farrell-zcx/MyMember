<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>MyMember Admin - Daftar Member<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="p-8 max-w-[1440px] mx-auto">
        <!-- Page Header -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Daftar Member</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">List lengkap seluruh member yang terdaftar di sistem.</p>
            </div>
        </div>

        <section class="space-y-6">
            <div class="glass-panel rounded-2xl p-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="glass-header text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                                <th class="py-3 px-4 rounded-l-lg">NIK</th>
                                <th class="py-3 px-4">Nama Lengkap</th>
                                <th class="py-3 px-4">Kontak</th>
                                <th class="py-3 px-4">Tipe Member</th>
                                <th class="py-3 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3 px-4 rounded-r-lg">Masa Aktif</th>
                            </tr>
                        </thead>
                        <tbody id="memberTableBody" class="text-xs text-on-surface divide-y divide-surface-container">
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-outline italic">
                                        Belum ada data member.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $m): ?>
                                    <tr class="glass-table-row">
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function escapeHtml(unsafe) {
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Real-time table search functionality
    const searchInput = document.getElementById('globalSearch');

    function applySearchFilter() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#memberTableBody tr');
        
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            
            const cells = row.getElementsByTagName('td');
            if (cells.length < 3) return;
            
            const nik = cells[0].textContent.toLowerCase();
            const nama = cells[1].textContent.toLowerCase();
            const kontak = cells[2].textContent.toLowerCase();
            const tipe = cells[3] ? cells[3].textContent.toLowerCase() : '';
            
            const matches = nik.includes(query) || nama.includes(query) || kontak.includes(query) || tipe.includes(query);
            row.style.display = matches ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applySearchFilter);
    }

    // Real-time polling logic for live sync with check-ins and member management
    let lastMemberDataHash = '';

    setInterval(async () => {
        try {
            const response = await fetch('/admin/member/live?t=' + new Date().getTime());
            if (response.status !== 200) return;
            const result = await response.json();

            if (result.status === 'sukses' && result.data) {
                // Check if data changed
                const currentHash = JSON.stringify(result.data.map(m => m.NIK + ':' + m.sisa_kuota + ':' + m.id_type + ':' + m.updated_at));
                if (currentHash === lastMemberDataHash) return;
                lastMemberDataHash = currentHash;

                const tbody = document.getElementById('memberTableBody');
                if (!tbody) return;

                if (result.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-12 text-center text-outline italic">
                                Belum ada data member.
                            </td>
                        </tr>
                    `;
                } else {
                    let html = '';
                    result.data.forEach(m => {
                        const safeNik = escapeHtml(m.NIK);
                        const safeNama = escapeHtml(m.nama_lengkap);
                        const safeHp = escapeHtml(m.nomor_hp || '-');
                        const safeEmail = escapeHtml(m.email || '-');
                        const safeTipe = escapeHtml(m.type_member || 'Unknown');
                        const expired = escapeHtml(m.formatted_expired || 'No Limit');
                        const kuota = escapeHtml(m.sisa_kuota);

                        html += `
                            <tr class="glass-table-row hover:bg-surface-container/50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-on-surface">${safeNik}</td>
                                <td class="py-3.5 px-4 font-semibold">${safeNama}</td>
                                <td class="py-3.5 px-4 leading-relaxed">
                                    ${safeHp}<br>
                                    <span class="text-on-surface-variant">${safeEmail}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="bg-primary/10 text-primary font-bold px-2 py-1 rounded-sm">${safeTipe}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary px-2 py-1 rounded font-bold">
                                        ${kuota}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold">
                                    ${expired}
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                    applySearchFilter();
                }
            }
        } catch (e) {
            console.error('Error polling live members:', e);
        }
    }, 2000);
</script>
<?= $this->endSection() ?>
