<?php
class Kriteria {
    private $conn;
    private $table = "kriteria";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
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
}