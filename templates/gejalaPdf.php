<?php
$logo = realpath(__DIR__ . '/../assets/logo.png');
$formatter = new IntlDateFormatter(
    'id_ID',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Asia/Jakarta'
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Data Gejala</title>

    <style>
        <?php include __DIR__ . '/riwayatPdf.css'; ?>
    </style>
</head>

<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="<?= $logo ?>" class="logo">
                </td>
                <td class="title-cell">
                    <h1>SISTEM PAKAR</h1>
                    <h2>IDENTIFIKASI PENYEBAB PENURUNAN PENJUALAN UMKM</h2>
                    <p>Jl. Mayjen Sutoyo No.76, Kelurahan Cililitan, Kecamatan Kramat Jati, Kota Jakarta Timur</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="double-line"></div>

    <div class="document-title">
        LAPORAN DATA GEJALA
    </div>

    <br>
    <table class="table">
        <thead>
            <tr>
                <th width="25">No</th>
                <th width="40">Kode</th>
                <th>Nama Gejala</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($data as $index => $item): ?>

                <tr>
                    <td class="center">
                        <?= $index + 1 ?>
                    </td>

                    <td class="center">
                        <?= htmlspecialchars($item['kode_gejala']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($item['nama_gejala']) ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

    <table class="signature-table">
        <tr>
            <td width="60%"></td>

            <td width="40%" class="signature-content">
                Jakarta, <?= $formatter->format(time()) ?>
                <br><br><br><br><br>

                <strong>Dr. Hj. Eva Sundari, SE., MM., C.R.B.C</strong>
            </td>
        </tr>
    </table>

    <div class="footer" style="margin-top: 130px;">
        Dicetak otomatis oleh
        <strong>Sistem Pakar Identifikasi Penyebab Penurunan Penjualan UMKM</strong>
    </div>

</body>
</html>
