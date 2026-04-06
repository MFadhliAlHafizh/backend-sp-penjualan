<?php
class Validator {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🔍 cek apakah value sudah ada di tabel
    public function isExists($table, $column, $value) {
        $query = "SELECT COUNT(*) as total FROM {$table} WHERE {$column} = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("s", $value);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        return $result['total'] > 0;
    }
}