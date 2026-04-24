<?php
class Rules {
    private $conn;
    private $rulesTable = "rules";
    private $penyebabTable = "penyebab";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL
    public function getAll() {
        $query = "
            SELECT 
                r.*,
                p.nama_penyebab
            FROM {$this->rulesTable} r
            JOIN {$this->penyebabTable} p
            ON r.id_penyebab = p.id_penyebab
            ORDER BY kode_rules ASC
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
                r.*,
                p.nama_penyebab
            FROM {$this->rulesTable} r
            JOIN {$this->penyebabTable} p
            ON r.id_penyebab = p.id_penyebab
            WHERE r.id_rules = ?
        ");

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
            "INSERT INTO {$this->rulesTable} (kode_rules, id_penyebab, total_kondisi) VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sii",
            $data['kode_rules'],
            $data['id_penyebab'],
            $data['total_kondisi']
        );

        if ($stmt->execute()) {
            $id = $this->conn->insert_id;

            // ambil data yang baru dibuat
            return $this->getById($id);
        } else {
            return false;
        }
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
