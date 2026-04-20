<?php
class Konsultasi {
    private $conn;

    private $tableKonsultasi = "konsultasi";
    private $tableJawaban = "jawaban_konsultasi";
    private $tableHasil = "hasil_konsultasi";
    private $tableRules = "rules";
    private $tableConditions = "rule_conditions";
    private $tablePlatform = "platform";

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
            "INSERT INTO {$this->tableJawaban} (id_konsultasi, id_kriteria, jawaban_user) VALUES (?, ?, ?)"
        );

        foreach ($jawaban as $id_kriteria => $jwb) {
            $stmtJawaban->bind_param("iii", $id_konsultasi, $id_kriteria, $jwb);
            $stmtJawaban->execute();
        }

        // 3️⃣ Ambil rules
        $sql = "
            SELECT 
                r.id_rule,
                r.id_platform,
                r.total_conditions,
                p.nama_platform,
                rc.id_kriteria,
                rc.jawaban AS jawaban_rule
            FROM {$this->tableRules} r
            JOIN {$this->tableConditions} rc ON r.id_rule = rc.id_rule
            JOIN {$this->tablePlatform} p ON r.id_platform = p.id_platform
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ["error" => "Error DB"];
        }

        // 4️⃣ Grouping rules
        $grouped = [];

        while ($row = $result->fetch_assoc()) {
            $id_rule = $row['id_rule'];

            if (!isset($grouped[$id_rule])) {
                $grouped[$id_rule] = [
                    "id_rule" => $id_rule,
                    "id_platform" => $row['id_platform'],
                    "nama_platform" => $row['nama_platform'],
                    "total_conditions" => $row['total_conditions'],
                    "conditions" => []
                ];
            }

            $grouped[$id_rule]["conditions"][] = [
                "id_kriteria" => $row['id_kriteria'],
                "jawaban_rule" => $row['jawaban_rule']
            ];
        }

        // 5️⃣ Hitung forward chaining
        $resultAkhir = [];

        foreach ($grouped as $rule) {
            $terpenuhi = 0;

            foreach ($rule["conditions"] as $c) {
                $userInput = isset($jawaban[$c["id_kriteria"]]) ? $jawaban[$c["id_kriteria"]] : 0;

                if ($userInput == $c["jawaban_rule"]) {
                    $terpenuhi++;
                }
            }

            $persen = round(($terpenuhi / $rule["total_conditions"]) * 100);

            $resultAkhir[] = [
                "id_platform" => $rule["id_platform"],
                "nama_platform" => $rule["nama_platform"],
                "terpenuhi" => $terpenuhi,
                "total_kondisi" => $rule["total_conditions"],
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
            "INSERT INTO {$this->tableHasil} (id_konsultasi, id_platform, terpenuhi, total_kondisi, persen) VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($top2 as $h) {
            $stmtHasil->bind_param(
                "iiiii",
                $id_konsultasi,
                $h["id_platform"],
                $h["terpenuhi"],
                $h["total_kondisi"],
                $h["persen"]
            );
            $stmtHasil->execute();
        }

        return $top2;
    }
}
