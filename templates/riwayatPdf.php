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
    <title>Laporan Hasil Identifikasi</title>

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
        LAPORAN HASIL IDENTIFIKASI
    </div>

    <h3>A. Informasi Konsultasi</h3>
    <table class="info-table">
        <tr>
            <td width="170">Username</td>
            <td width="10">:</td>
            <td><?= htmlspecialchars($profile['username']) ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td>:</td>
            <td><?= htmlspecialchars($profile['email']) ?></td>
        </tr>
        <tr>
            <td>Tanggal Konsultasi</td>
            <td>:</td>
            <td><?= $formatter->format(strtotime($profile['tanggal'])) ?></td>
        </tr>
    </table>

    <?php if (!empty($results)) : ?>
        <h3>B. Kesimpulan Hasil Identifikasi</h3>
        <table class="summary-table">
            <tr>
                <td width="170">Penyebab Utama</td>
                <td width="10">:</td>
                <td>
                    <strong>
                        <?= htmlspecialchars($results[0]['nama_penyebab']) ?>
                    </strong>
                </td>
            </tr>
            <tr>
                <td>Tingkat Kecocokan</td>
                <td>:</td>
                <td>
                    <strong><?= $results[0]['persen'] ?>%</strong>
                </td>
            </tr>
            <tr>
                <td>Rule Terpenuhi</td>
                <td>:</td>
                <td>
                    <?= $results[0]['rule_terpenuhi'] ?>
                    /
                    <?= $results[0]['total_rule'] ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <h3>C. Jawaban Konsultasi</h3>
    <table class="table">
        <thead>
            <tr>
                <th width="25">No</th>
                <th>Pertanyaan</th>
                <th width="60">Jawaban</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($responses as $index => $item) : ?>
                <tr>
                    <td class="center">
                        <?= $index + 1 ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($item['pertanyaan']) ?>
                    </td>
                    <td class="center">
                        <?= $item['jawaban_user'] == 1 ? 'Ya' : 'Tidak' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>D. Detail Hasil Identifikasi</h3>
    <?php foreach ($results as $index => $item) : ?>
        <div class="result-box">
            <div class="result-title">
                <?= $index + 1 ?>.
                <?= htmlspecialchars($item['nama_penyebab']) ?>
            </div>
            <table class="result-table">
                <tr>
                    <td width="180">
                        Tingkat Kecocokan
                    </td>
                    <td width="10">:</td>
                    <td>
                        <?= $item['persen'] ?>%
                    </td>
                </tr>
                <tr>
                    <td>Rule Terpenuhi</td>
                    <td>:</td>
                    <td>
                        <?= $item['rule_terpenuhi'] ?>
                        /
                        <?= $item['total_rule'] ?>
                    </td>
                </tr>
            </table>
            <div class="section-title">
                Deskripsi
            </div>

            <p class="description">
                <?= nl2br(htmlspecialchars($item['deskripsi'])) ?>
            </p>

            <div class="section-title">
                Solusi
            </div>

            <ol>
                <?php
                    $solusi = preg_split('/\r\n|\r|\n|\|/', $item['solusi']);
                    foreach ($solusi as $s):
                        if (trim($s) === '') continue;
                ?>
                    <li>
                        <?= htmlspecialchars(trim($s)) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endforeach; ?>

    <table class="signature-table">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="signature-content">
                Jakarta, <?= $formatter->format(time()) ?>
                <br><br><br><br><br>
                <strong>
                    <?= htmlspecialchars($profile['username']) ?>
                </strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh
        <strong>Sistem Pakar Identifikasi Penyebab Penurunan Penjualan UMKM</strong>
    </div>
</body>

</html>