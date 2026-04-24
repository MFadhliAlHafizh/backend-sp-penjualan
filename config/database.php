<?php
class Database {
    private $host = "localhost";
    private $db_name = "sp_penjualan";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name
            );

            if ($this->conn->connect_error) {
                http_response_code(500);
                echo json_encode([
                    "status" => "error",
                    "message" => "Database connection failed"
                ]);
                exit();
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Connection error"
            ]);
            exit();
        }

        return $this->conn;
    }
}