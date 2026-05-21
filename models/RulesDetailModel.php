<?php
class RulesDetail {
    private $conn;
    private $gejalaTable = "gejala";
    private $penyebabTable = "penyebab";
    private $rulesTable = "rules";
    private $kondisiTable = "kondisi";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET BY RULES ID
    public function getByRulesId($idRules) {
        $stmt = $this->conn->prepare("
            SELECT
                r.id_rules,
                r.kode_rules,
                r.total_kondisi,

                p.nama_penyebab,
                p.deskripsi,

                rc.id_kondisi,
                rc.jawaban,

                k.kode_gejala,
                k.nama_gejala,
                k.pertanyaan
            FROM {$this->rulesTable} r
            JOIN {$this->penyebabTable} p ON r.id_penyebab = p.id_penyebab
            JOIN {$this->kondisiTable} rc ON r.id_rules = rc.id_rules
            JOIN {$this->gejalaTable} k ON rc.id_gejala = k.id_gejala
            WHERE r.id_rules = ?
            ORDER BY kode_gejala ASC
        ");

        $stmt->bind_param("i", $idRules);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // GET BY ID
    public function getByKondisiId($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->kondisiTable . " WHERE id_kondisi = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    // CREATE
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->kondisiTable} (id_rules, id_gejala, jawaban) VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iii",
            $data['id_rules'],
            $data['id_gejala'],
            $data['jawaban']
        );

        if ($stmt->execute()) {
            $id = $this->conn->insert_id;

            // ambil data yang baru dibuat
            return $this->getByKondisiId($id);
        } else {
            return false;
        }
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM " . $this->kondisiTable . " WHERE id_kondisi = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }    
}
