<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>MyMember Admin - Kelola Pengguna<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="p-8 max-w-[1440px] mx-auto space-y-8">
        
        <!-- Page Header -->
        <div class="flex justify-between items-end">
            <div>
                <h2 class="font-headline-xl text-headline-xl text-primary mb-1">Kelola Pengguna (Admin)</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Daftar pengguna (admin/resepsionis) yang memiliki akses ke aplikasi MyMember melalui SSO.</p>
            </div>
        </div>

        <section>
            <div class="glass-panel rounded-2xl p-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="glass-header text-on-surface-variant uppercase text-[10px] font-bold tracking-wider">
                                <th class="py-3 px-4 rounded-l-lg">ID</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Username</th>
                                <th class="py-3 px-4">Nama Resepsionis</th>
                                <th class="py-3 px-4 text-center rounded-r-lg">Sinkronisasi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs text-on-surface divide-y divide-surface-container">
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-outline italic">
                                        Belum ada pengguna.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($admins as $admin): ?>
                                    <tr class="glass-table-row">
                                        <td class="py-3.5 px-4 font-mono font-semibold">
                                            <?= esc($admin['id_admin']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-primary">
                                            <?= esc($admin['email']) ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?= esc($admin['username']) ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?= esc($admin['nama_resepsionis']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <?= esc($admin['synced_at']) ?>
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
