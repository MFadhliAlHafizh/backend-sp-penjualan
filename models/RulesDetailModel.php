<?php
class RulesDetail {
    private $conn;
    private $rulesTable = "rules";
    private $kondisiTable = "kondisi";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET BY RULES ID
    public function getByPenyebabId($idPenyebab) {
        $stmt = $this->conn->prepare("
            SELECT
                r.id_rules,
                r.kode_rules,
                p.kode_penyebab,
                p.nama_penyebab,
                p.deskripsi,

                GROUP_CONCAT(
                    g.kode_gejala
                    ORDER BY g.kode_gejala
                    SEPARATOR ' AND '
                ) AS gejala_list

            FROM rules r

            JOIN penyebab p
                ON r.id_penyebab = p.id_penyebab

            JOIN kondisi k
                ON r.id_rules = k.id_rules

            JOIN gejala g
                ON k.id_gejala = g.id_gejala

            WHERE p.id_penyebab = ?

            GROUP BY r.id_rules

            ORDER BY r.kode_rules
        ");

        $stmt->bind_param("i", $idPenyebab);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {

            $row['rule_text'] =
                "IF {$row['gejala_list']} THEN {$row['kode_penyebab']}";

            $data[] = $row;
        }

        return $data;
    }

    // CREATE
    public function create($data) {
        $idPenyebab = $data['id_penyebab'];
        $kodeRules = strtoupper(trim($data['kode_rules']));
        $idGejala = $data['id_gejala'];

        // CEK RULE SUDAH ADA ATAU BELUM
        $stmt = $this->conn->prepare("
            SELECT id_rules
            FROM {$this->rulesTable}
            WHERE kode_rules = ?
            AND id_penyebab = ?
        ");

        $stmt->bind_param(
            "si",
            $kodeRules,
            $idPenyebab
        );

        $stmt->execute();

        $result = $stmt->get_result();

        // RULE SUDAH ADA
        if ($result->num_rows > 0) {

            $rule = $result->fetch_assoc();

            $idRules = $rule['id_rules'];

        } else {
            // BUAT RULE BARU
            $stmtInsertRule = $this->conn->prepare("
                INSERT INTO {$this->rulesTable}
                (
                    kode_rules,
                    id_penyebab
                )
                VALUES
                (
                    ?, ?
                )
            ");

            $stmtInsertRule->bind_param(
                "si",
                $kodeRules,
                $idPenyebab
            );

            $stmtInsertRule->execute();

            $idRules = $this->conn->insert_id;
        }

        // CEK DUPLIKAT GEJALA
        $stmtCheck = $this->conn->prepare("
            SELECT id_kondisi
            FROM {$this->kondisiTable}
            WHERE id_rules = ?
            AND id_gejala = ?
        ");

        $stmtCheck->bind_param(
            "ii",
            $idRules,
            $idGejala
        );

        $stmtCheck->execute();

        $duplicate = $stmtCheck->get_result();

        if ($duplicate->num_rows > 0) {

            return [
                "error" => "Gejala sudah ada pada rules ini"
            ];
        }

        // INSERT KONDISI
        $stmtInsertKondisi = $this->conn->prepare("
            INSERT INTO {$this->kondisiTable}
            (
                id_rules,
                id_gejala,
                jawaban
            )
            VALUES
            (
                ?, ?, 1
            )
        ");

        $stmtInsertKondisi->bind_param(
            "ii",
            $idRules,
            $idGejala
        );

        if (!$stmtInsertKondisi->execute()) {
            return false;
        }

        return [
            "id_rules" => $idRules,
            "kode_rules" => $kodeRules,
            "id_gejala" => $idGejala
        ];
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->kondisiTable} WHERE id_rules = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $this->conn->prepare("
            DELETE FROM {$this->rulesTable}
            WHERE id_rules = ?
        ");

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    } 
}
