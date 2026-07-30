<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Histori Kunjungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 80px;
            display: block;
            margin: 0 auto 10px auto;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .header-subtitle {
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        .report-info {
            margin-bottom: 15px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- mPDF Header feature for repeating on every page -->
    <htmlpageheader name="myHeader1" style="display:none">
        <div class="header">
            <img src="<?= FCPATH . 'images/logo.png' ?>" alt="Logo">
            <h1 class="header-title"><span style="color: #0058be;">My</span>Member</h1>
            <p class="header-subtitle">Laporan Histori Kunjungan Member</p>
        </div>
    </htmlpageheader>

    <sethtmlpageheader name="myHeader1" page="O" value="on" show-this-page="1" />

    <div class="report-info">
        <p><strong>Periode:</strong> <?= (!empty($start_date) && !empty($end_date)) ? esc($start_date) . ' s/d ' . esc($end_date) : 'Semua Waktu' ?></p>
        <p><strong>Tanggal Dicetak:</strong> <?= date('d M Y H:i:s') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Waktu Check-in</th>
                <th width="20%">NIK</th>
                <th width="25%">Nama Lengkap</th>
                <th width="15%">Tipe Member</th>
                <th width="10%">Kuota Awal</th>
                <th width="10%">Sisa Kuota</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data kunjungan pada periode ini.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= date('d M Y H:i:s', strtotime($log['waktu_kunjungan'])) ?></td>
                        <td><?= esc($log['NIK']) ?></td>
                        <td><?= esc($log['nama_lengkap'] ?? 'Tidak Diketahui') ?></td>
                        <td><?= esc($log['type_member'] ?? '-') ?></td>
                        <td class="text-center"><?= esc($log['kuota_awal']) ?></td>
                        <td class="text-center"><?= esc($log['kuota_akhir']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
