<?php
require_once __DIR__ . '/../config/database.php'; // penerapan Konsep OOP Class Hewan, Layanan, Pelanggan, dan Transaksi sama-sama menggunakan DB

class Pelanggan // Menggunakan Encapsulation private $db dan CRUD
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Ambil semua data pelanggan
     */
    public function getAll()
    {
        $sql = "SELECT 
                    p.id_pelanggan as id,
                    p.kode_pelanggan as kode,
                    p.nama_pelanggan as nama,
                    p.no_hp as hp,
                    p.alamat,
                    p.created_at
                FROM pelanggan p
                ORDER BY p.nama_pelanggan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cari pelanggan untuk autocomplete - VERSI FIX
     */
    public function searchForAutocomplete($keyword)
    {
        // Gunakan query langsung tanpa prepared statement untuk simplicity
        $sql = "SELECT 
                    p.id_pelanggan as id,
                    p.kode_pelanggan as kode,
                    p.nama_pelanggan as nama,
                    p.no_hp as hp,
                    p.alamat
                FROM pelanggan p
                WHERE p.nama_pelanggan LIKE '%" . $keyword . "%'
                OR p.no_hp LIKE '%" . $keyword . "%'
                OR p.kode_pelanggan LIKE '%" . $keyword . "%'
                ORDER BY p.nama_pelanggan
                LIMIT 10";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }


    /**
     * Tambah pelanggan baru CREATE
     */
    public function create($data)
    {
        try {
            // Generate kode pelanggan otomatis
            $kode = $this->generateKodePelanggan();

            $sql = "INSERT INTO pelanggan 
                    (kode_pelanggan, nama_pelanggan, no_hp, alamat)
                    VALUES 
                    (:kode_pelanggan, :nama_pelanggan, :no_hp, :alamat)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                "kode_pelanggan" => $kode,
                "nama_pelanggan" => $data["nama_pelanggan"],
                "no_hp" => $data["no_hp"],
                "alamat" => $data["alamat"] ?? null,
            ]);
            if ($result) {
            return $this->db->lastInsertId(); // Return the inserted ID
        } else {
            return false;
        }
        

        } catch (Exception $e) {
            error_log("Error create pelanggan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update data pelanggan UPDATE
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE pelanggan SET 
                        nama_pelanggan = :nama_pelanggan,
                        no_hp = :no_hp,
                        alamat = :alamat
                    WHERE id_pelanggan = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                "id" => $id,
                "nama_pelanggan" => $data["nama_pelanggan"],
                "no_hp" => $data["no_hp"],
                "alamat" => $data["alamat"] ?? null,
            ]);

        } catch (Exception $e) {
            error_log("Error update pelanggan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus pelanggan DELETE
     */
    public function delete($id)
    {
        try {
            // Cek apakah pelanggan punya hewan
            $sqlCheck = "SELECT COUNT(*) as total FROM hewan WHERE id_pelanggan = :id";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute(["id" => $id]);
            $result = $stmtCheck->fetch();

            if ($result['total'] > 0) {
                // Jika punya hewan, tidak bisa dihapus
                return false;
            }

            $sql = "DELETE FROM pelanggan WHERE id_pelanggan = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(["id" => $id]);

        } catch (Exception $e) {
            error_log("Error delete pelanggan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate kode pelanggan otomatis (PLG001, PLG002, dst)
     */
    private function generateKodePelanggan()
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(kode_pelanggan, 4) AS UNSIGNED)) as max_number 
                FROM pelanggan 
                WHERE kode_pelanggan LIKE 'PLG%'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        $nextNumber = ($result['max_number'] ?? 0) + 1;
        return 'PLG' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Cari pelanggan berdasarkan nama/no HP
     */
    public function search($keyword)
    {
        $sql = "SELECT 
                    p.id_pelanggan as id,
                    p.kode_pelanggan as kode,
                    p.nama_pelanggan as nama,
                    p.no_hp as hp,
                    p.alamat
                FROM pelanggan p
                WHERE p.nama_pelanggan LIKE :keyword
                OR p.no_hp LIKE :keyword
                OR p.kode_pelanggan LIKE :keyword
                ORDER BY p.nama_pelanggan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["keyword" => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total pelanggan
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM pelanggan";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Ambil data untuk dropdown
     */
    public function getForDropdown()
    {
        $sql = "SELECT 
                    p.id_pelanggan as id,
                    p.kode_pelanggan as kode,
                    p.nama_pelanggan as nama
                FROM pelanggan p
                ORDER BY p.nama_pelanggan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cek apakah no HP sudah terdaftar
     */
    public function isPhoneExists($no_hp, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM pelanggan 
                WHERE no_hp = :no_hp";
        
        $params = ["no_hp" => $no_hp];

        if ($exclude_id) {
            $sql .= " AND id_pelanggan != :exclude_id";
            $params["exclude_id"] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return ($result['total'] ?? 0) > 0;
    }
    // Tambahkan method ini di class Pelanggan
public function getLastInsertId() {
    return $this->db->lastInsertId();
}

}
