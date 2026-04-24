<?php
class Akun {
    private $conn;
    private $table = "akun";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY peran ASC";
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

    // UPDATE
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " 
            SET username = ?, 
                email = ?, 
                password = ?,
                peran = ? 
            WHERE id_user = ?"
        );

        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt->bind_param(
            "ssssi",
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['peran'],
            $id
        );

        if ($stmt->execute()) {
            // ambil data terbaru setelah update
            return $this->getById($id);
        } else {
            return false;
        }
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM " . $this->table . " WHERE id_user = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
