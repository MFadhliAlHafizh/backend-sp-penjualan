<?php
class Konsultasi {
    private $conn;

    private $tableKonsultasi = "konsultasi";
    private $tableJawaban = "jawaban_konsultasi";
    private $tableHasil = "hasil_konsultasi";
    private $tableRules = "rules";
    private $tableKondisi = "kondisi";
    private $tablePenyebab = "penyebab";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function rekomendasi($id_user, $jawaban) {
        // 1. Simpan konsultasi
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->tableKonsultasi} (id_user, tanggal) VALUES (?, NOW())"
        );
        $stmt->bind_param("i", $id_user);

        if (!$stmt->execute()) {
            return ["error" => "Gagal membuat sesi konsultasi"];
        }

        $id_konsultasi = $this->conn->insert_id;

        // 2. Simpan jawaban user
        $stmtJawaban = $this->conn->prepare(
            "INSERT INTO {$this->tableJawaban} (id_konsultasi, id_gejala, jawaban_user) VALUES (?, ?, ?)"
        );

        foreach ($jawaban as $id_gejala => $jwb) {
            $stmtJawaban->bind_param("iii", $id_konsultasi, $id_gejala, $jwb);
            $stmtJawaban->execute();
        }

        // 3. Ambil seluruh rule
        $sql = "
            SELECT 
                r.id_rules,
                r.id_penyebab,
                r.total_kondisi,

                p.nama_penyebab,
                p.deskripsi,
                p.solusi,

                k.id_gejala,
                k.jawaban AS jawaban_rules
            FROM {$this->tableRules} r
            JOIN {$this->tableKondisi} k ON r.id_rules = k.id_rules
            JOIN {$this->tablePenyebab} p ON r.id_penyebab = p.id_penyebab
            ORDER BY r.id_rules
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ["error" => "Gagal mengambil data rule"];
        }

        // 4. Group rule
        $groupedRules = [];

        while ($row = $result->fetch_assoc()) {
            $id_rules = $row['id_rules'];

            if (!isset($groupedRules[$id_rules])) {
                $groupedRules[$id_rules] = [
                    "id_rules" => $id_rules,
                    "id_penyebab" => $row['id_penyebab'],
                    "nama_penyebab" => $row['nama_penyebab'],
                    "deskripsi" => $row['deskripsi'],
                    "solusi" => $row['solusi'],
                    "total_kondisi" => $row['total_kondisi'],
                    "kondisi" => []
                ];
            }

            $groupedRules[$id_rules]["kondisi"][] = [
                "id_gejala" => $row['id_gejala'],
                "jawaban_rules" => $row['jawaban_rules']
            ];
        }

        // 5. Evaluasi rule
        $hasilPenyebab = [];

        foreach ($groupedRules as $rule) {
            $terpenuhi = 0;

            foreach ($rule["kondisi"] as $kondisi) {
                $userInput = isset(
                    $jawaban[$kondisi["id_gejala"]]
                )
                    ? $jawaban[$kondisi["id_gejala"]]
                    : 0;

                if ($userInput == $kondisi["jawaban_rules"]) {
                    $terpenuhi++;
                }
            }

            // Rule terpenuhi jika semua kondisi terpenuhi
            $ruleTerpenuhi =
                ($terpenuhi == $rule["total_kondisi"]);

            $idPenyebab = $rule["id_penyebab"];

            // Inisialisasi penyebab
            if (!isset($hasilPenyebab[$idPenyebab])) {

                $hasilPenyebab[$idPenyebab] = [
                    "id_penyebab" => $idPenyebab,
                    "nama_penyebab" => $rule["nama_penyebab"],
                    "deskripsi" => $rule["deskripsi"],
                    "solusi" => $rule["solusi"],
                    "rule_terpenuhi" => 0,
                    "total_rule" => 0
                ];
            }

            // Total rule milik penyebab
            $hasilPenyebab[$idPenyebab]["total_rule"]++;

            // Tambah jika rule terpenuhi
            if ($ruleTerpenuhi) {
                $hasilPenyebab[$idPenyebab]["rule_terpenuhi"]++;
            }
        }

        // 6. Hitung persentase
        $resultAkhir = [];

        foreach ($hasilPenyebab as $penyebab) {

            $persen =
                $penyebab["total_rule"] > 0
                ? round(
                    ($penyebab["rule_terpenuhi"] / $penyebab["total_rule"]) * 100, 2
                )
                : 0;

            $resultAkhir[] = [
                "id_penyebab" => $penyebab["id_penyebab"],
                "nama_penyebab" => $penyebab["nama_penyebab"],
                "deskripsi" => $penyebab["deskripsi"],
                "solusi" => $penyebab["solusi"],
                "rule_terpenuhi" => $penyebab["rule_terpenuhi"],
                "total_rule" => $penyebab["total_rule"],
                "persen" => $persen
            ];
        }

        // 7. Urutkan tertinggi
        usort($resultAkhir, function ($a, $b) {

            if ($b['persen'] == $a['persen']) {
                return $b['rule_terpenuhi']
                    <=> $a['rule_terpenuhi'];
            }

            return $b['persen']
                <=> $a['persen'];
        });

        // 8. Ambil Top 2
        $top2 = array_slice(
            $resultAkhir,
            0,
            2
        );

        // 9. Simpan hasil
        $stmtHasil = $this->conn->prepare(
            "INSERT INTO {$this->tableHasil}
            (
                id_konsultasi,
                id_penyebab,
                rule_terpenuhi,
                total_rule,
                persen
            )
            VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($top2 as $hasil) {

            $stmtHasil->bind_param(
                "iiiid",
                $id_konsultasi,
                $hasil["id_penyebab"],
                $hasil["rule_terpenuhi"],
                $hasil["total_rule"],
                $hasil["persen"]
            );

            $stmtHasil->execute();
        }

        // 10. Return hasil
        return $top2;
    }
}