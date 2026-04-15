<?php
class Riwayat {
    private $conn;
    private $konsultasiTable = "konsultasi";
    private $akunTable = "akun";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL
    public function getAll($id_user, $peran) {
        if ($peran === 'admin') {
            $query = "SELECT 
                        k.*,
                        a.username,
                        a.email
                    FROM {$this->konsultasiTable} k
                    JOIN {$this->akunTable} a 
                    ON k.id_user = a.id_user";

            $stmt = $this->conn->prepare($query);
        } else {
            $query = "SELECT 
                        k.*,
                        a.username,
                        a.email
                    FROM {$this->konsultasiTable} k
                    JOIN {$this->akunTable} a 
                    ON k.id_user = a.id_user
                    WHERE k.id_user = ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id_user);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare(
            "DELETE FROM " . $this->konsultasiTable . " WHERE id_konsultasi = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
