<?php
class Platform {
    private $conn;
    private $table = "platform";

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

    // GET BY ID
    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->table . " WHERE id_platform = ?"
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
            "INSERT INTO {$this->table} (kode_platform, nama_platform, deskripsi, link_toko) VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $data['kode_platform'],
            $data['nama_platform'],
            $data['deskripsi'],
            $data['link_toko']
        );

        if ($stmt->execute()) {
            $id = $this->conn->insert_id;

            // ambil data yang baru dibuat
            return $this->getById($id);
        } else {
            return false;
        }
    }

    // UPDATE
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " 
            SET kode_platform = ?, 
                nama_platform = ?, 
                deskripsi = ? ,
                link_toko = ?
            WHERE id_platform = ?"
        );

        $stmt->bind_param(
            "ssssi",
            $data['kode_platform'],
            $data['nama_platform'],
            $data['deskripsi'],
            $data['link_toko'],
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
            "DELETE FROM " . $this->table . " WHERE id_platform = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
