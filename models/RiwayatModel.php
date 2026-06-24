<?php
class Riwayat {
    private $conn;
    private $konsultasiTable = "konsultasi";
    private $akunTable = "akun";
    private $gejalaTable = "gejala";
    private $penyebabTable = "penyebab";
    private $jawabanKonsultasiTable = "jawaban_konsultasi";
    private $hasilKonsultasiTable = "hasil_konsultasi";

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
                    ON k.id_user = a.id_user
                    ORDER BY k.tanggal DESC";

            $stmt = $this->conn->prepare($query);
        } else {
            $query = "SELECT 
                        k.*,
                        a.username,
                        a.email
                    FROM {$this->konsultasiTable} k
                    JOIN {$this->akunTable} a 
                    ON k.id_user = a.id_user
                    WHERE k.id_user = ?
                    ORDER BY k.tanggal DESC";

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

    public function getProfileByConsultationId($id) {
        $stmt = $this->conn->prepare(
            "SELECT
                a.username,
                a.email,
                k.tanggal
            FROM {$this->konsultasiTable} k
            JOIN {$this->akunTable} a ON k.id_user = a.id_user
            WHERE k.id_konsultasi = ?"
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

    public function getResponsesByConsultationId($id) {
        $stmt = $this->conn->prepare(
            "SELECT
                kr.pertanyaan,
                jk.jawaban_user
            FROM {$this->jawabanKonsultasiTable} jk
            JOIN {$this->gejalaTable} kr ON jk.id_gejala = kr.id_gejala
            WHERE jk.id_konsultasi = ?
            ORDER BY kr.id_gejala ASC"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getResultsByConsultationId($id) {
        $stmt = $this->conn->prepare(
            "SELECT
                p.nama_penyebab,
                p.deskripsi,
                p.solusi,
                hk.rule_terpenuhi,
                hk.total_rule,
                hk.persen
            FROM {$this->hasilKonsultasiTable} hk
            JOIN {$this->penyebabTable} p ON hk.id_penyebab = p.id_penyebab
            WHERE hk.id_konsultasi = ?
            ORDER BY hk.persen DESC"
        );

        $stmt->bind_param("i", $id);
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
