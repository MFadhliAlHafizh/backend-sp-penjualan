<?php
class Rules {
    private $conn;
    private $rulestable = "rules";
    private $platformtable = "platform";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL
    public function getAll() {
        $query = "
            SELECT 
                r.*,
                p.nama_platform
            FROM {$this->rulestable} r
            JOIN {$this->platformtable} p
            ON r.id_platform = p.id_platform
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
                p.nama_platform
            FROM {$this->rulestable} r
            JOIN {$this->platformtable} p
            ON r.id_platform = p.id_platform
            WHERE r.id_rule = ?
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
            "INSERT INTO {$this->rulestable} (kode_rule, id_platform, total_conditions) VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sii",
            $data['kode_rule'],
            $data['id_platform'],
            $data['total_conditions']
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
            "DELETE FROM " . $this->rulestable . " WHERE id_rule = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
