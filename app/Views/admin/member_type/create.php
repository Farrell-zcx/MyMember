<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>MyMember Admin - Tambah Tipe Member<?= $this->endSection() ?>

<?= $this->section('header_content') ?>
    <h2 class="font-headline-md text-headline-md text-primary">Add Member Type</h2>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="flex items-center justify-center min-h-[calc(100vh-4rem)]">
    <div class="p-margin-desktop w-full max-w-[600px]">
        <div class="glass-panel rounded-2xl p-xl">
            <h3 class="font-headline-xl text-headline-xl text-primary mb-6">Tambah Tipe Member Baru</h3>
            
            <form action="<?= base_url('admin/member-type/store') ?>" method="POST" class="space-y-lg">
                <?= csrf_field() ?>
                
                <div class="space-y-xs">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Nama Tipe Member</label>
                    <input type="text" name="type_member" placeholder="Contoh: Silver, Gold, Platinum" required
                        class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                </div>

                <div class="space-y-xs">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Kuota Kunjungan (Default)</label>
                    <input type="number" name="kuota_kunjungan" placeholder="Contoh: 10" required
                        class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all">
                </div>

                <div class="space-y-xs">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase tracking-wider">Deskripsi Benefit</label>
                    <textarea name="deskripsi_benefit" rows="4" placeholder="Detail keuntungan tipe member..."
                        class="w-full glass-input rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none transition-all"></textarea>
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
    </div>
    </div>
<?= $this->endSection() ?>
