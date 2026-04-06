<?php
class Authentication {
    private $conn;
    private $table = "akun";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET BY ID
    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->table . " WHERE id_user = ?"
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
            "INSERT INTO {$this->table} (username, email, password, peran) VALUES (?, ?, ?, ?)"
        );

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt->bind_param(
            "ssss",
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['peran']
        );

        if ($stmt->execute()) {
            $id = $this->conn->insert_id;

            // ambil data yang baru dibuat
            return $this->getById($id);
        } else {
            return false;
        }
    }
}
