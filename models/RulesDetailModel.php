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
}
