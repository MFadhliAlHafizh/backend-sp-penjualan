<?php
class RulesDetail {
    private $conn;
    private $kriteriaTable = "kriteria";
    private $platformTable = "platform";
    private $rulesTable = "rules";
    private $conditionsTable = "rule_conditions";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET BY RULE ID
    public function getByRuleId($idRule) {
        $stmt = $this->conn->prepare("
            SELECT
                r.id_rule,
                r.kode_rule,
                r.total_conditions,

                p.nama_platform,

                rc.id_condition,
                rc.jawaban,

                k.nama_kriteria,
                k.pertanyaan
            FROM {$this->rulesTable} r
            JOIN {$this->platformTable} p ON r.id_platform = p.id_platform
            JOIN {$this->conditionsTable} rc ON r.id_rule = rc.id_rule
            JOIN {$this->kriteriaTable} k ON rc.id_kriteria = k.id_kriteria
            WHERE r.id_rule = ?
        ");

        $stmt->bind_param("i", $idRule);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // GET BY ID
    public function getByConditionId($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->conditionsTable . " WHERE id_condition = ?"
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
            "INSERT INTO {$this->conditionsTable} (id_rule, id_kriteria, jawaban) VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iii",
            $data['id_rule'],
            $data['id_kriteria'],
            $data['jawaban']
        );

        if ($stmt->execute()) {
            $id = $this->conn->insert_id;

            // ambil data yang baru dibuat
            return $this->getByConditionId($id);
        } else {
            return false;
        }
    }

    // UPDATE
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->conditionsTable . " 
            SET id_rule = ?, 
                id_kriteria = ?, 
                jawaban = ?
            WHERE id_condition = ?"
        );

        $stmt->bind_param(
            "iiii",
            $data['id_rule'],
            $data['id_kriteria'],
            $data['jawaban'],
            $id
        );

        if ($stmt->execute()) {
            // ambil data terbaru setelah update
            return $this->getByConditionId($id);
        } else {
            return false;
        }
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM " . $this->conditionsTable . " WHERE id_condition = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }    
}
