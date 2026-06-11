<?php
class Rules {
    private $conn;
    private $rulesTable = "rules";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL
    public function getAll() {
        $query = "
            SELECT
                p.id_penyebab,
                p.kode_penyebab,
                p.nama_penyebab
            FROM penyebab p
            ORDER BY p.kode_penyebab ASC
        ";
        $result = $this->conn->query($query);

        if ($result) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        } else {
            return false;
        }
    }

    // GET BY ID
    public function getById($id) {
        $stmt = $this->conn->prepare("
            SELECT
                id_penyebab,
                kode_penyebab,
                nama_penyebab
            FROM penyebab
            WHERE id_penyebab = ?
        ");
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM " . $this->rulesTable . " WHERE id_rules = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
