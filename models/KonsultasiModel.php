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
        // 1️⃣ Insert konsultasi
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->tableKonsultasi} (id_user, tanggal) VALUES (?, NOW())"
        );
        $stmt->bind_param("i", $id_user);

        if (!$stmt->execute()) {
            return ["error" => "Gagal membuat sesi konsultasi"];
        }

        $id_konsultasi = $this->conn->insert_id;

        // 2️⃣ Simpan jawaban user
        $stmtJawaban = $this->conn->prepare(
            "INSERT INTO {$this->tableJawaban} (id_konsultasi, id_gejala, jawaban_user) VALUES (?, ?, ?)"
        );

        foreach ($jawaban as $id_gejala => $jwb) {
            $stmtJawaban->bind_param("iii", $id_konsultasi, $id_gejala, $jwb);
            $stmtJawaban->execute();
        }

        // 3️⃣ Ambil rules
        $sql = "
            SELECT 
                r.id_rules,
                r.id_penyebab,
                r.total_kondisi,
                p.nama_penyebab,
                p.deskripsi,
                p.solusi,
                rc.id_gejala,
                rc.jawaban AS jawaban_rules
            FROM {$this->tableRules} r
            JOIN {$this->tableKondisi} rc ON r.id_rules = rc.id_rules
            JOIN {$this->tablePenyebab} p ON r.id_penyebab = p.id_penyebab
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ["error" => "Error DB"];
        }

        // 4️⃣ Grouping rules
        $grouped = [];

        while ($row = $result->fetch_assoc()) {
            $id_rules = $row['id_rules'];

            if (!isset($grouped[$id_rules])) {
                $grouped[$id_rules] = [
                    "id_rules" => $id_rules,
                    "id_penyebab" => $row['id_penyebab'],
                    "nama_penyebab" => $row['nama_penyebab'],
                    "deskripsi" => $row['deskripsi'],
                    "solusi" => $row['solusi'],
                    "total_kondisi" => $row['total_kondisi'],
                    "kondisi" => []
                ];
            }

            $grouped[$id_rules]["kondisi"][] = [
                "id_gejala" => $row['id_gejala'],
                "jawaban_rules" => $row['jawaban_rules']
            ];
        }

        // 5️⃣ Hitung forward chaining
        $resultAkhir = [];

        foreach ($grouped as $rule) {
            $terpenuhi = 0;

            foreach ($rule["kondisi"] as $c) {
                $userInput = isset($jawaban[$c["id_gejala"]]) ? $jawaban[$c["id_gejala"]] : 0;

                if ($userInput == $c["jawaban_rules"]) {
                    $terpenuhi++;
                }
            }

            $persen = round(($terpenuhi / $rule["total_kondisi"]) * 100);

            $resultAkhir[] = [
                "id_penyebab" => $rule["id_penyebab"],
                "nama_penyebab" => $rule["nama_penyebab"],
                "deskripsi" => $rule["deskripsi"],
                "solusi" => $rule["solusi"],
                "terpenuhi" => $terpenuhi,
                "total_kondisi" => $rule["total_kondisi"],
                "persen" => $persen
            ];
        }

        // 6️⃣ Sorting & ambil top 2
        usort($resultAkhir, function ($a, $b) {
            return $b['persen'] <=> $a['persen'];
        });

        $top2 = array_slice($resultAkhir, 0, 2);

        // 7️⃣ Simpan hasil
        $stmtHasil = $this->conn->prepare(
            "INSERT INTO {$this->tableHasil} (id_konsultasi, id_penyebab, terpenuhi, total_kondisi, persen) VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($top2 as $h) {
            $stmtHasil->bind_param(
                "iiiii",
                $id_konsultasi,
                $h["id_penyebab"],
                $h["terpenuhi"],
                $h["total_kondisi"],
                $h["persen"]
            );
            $stmtHasil->execute();
        }

        return $top2;
    }
}
