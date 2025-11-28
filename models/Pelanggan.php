<?php
require_once __DIR__ . '/../core/Database.php';

class Pelanggan
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ambil semua data pelanggan (READ)
     */
    public function getAll()
    {
        $sql = "SELECT p.id_pelanggan as id, p.kode_pelanggan as kode, p.nama_pelanggan as nama, p.no_hp as hp, p.alamat FROM pelanggan p ORDER BY p.nama_pelanggan";
        $stmt = $this->db->query($sql); // FIX: Menggunakan query()
        return $stmt->fetchAll();
    }
    
    /**
     * Tambah pelanggan baru (CREATE)
     */
    public function create($data)
    {
        try {
            $kode = $this->generateKodePelanggan();
            $sql = "INSERT INTO pelanggan (kode_pelanggan, nama_pelanggan, no_hp, alamat) VALUES (:kode_pelanggan, :nama_pelanggan, :no_hp, :alamat)";

            // FIX: Menggunakan execute() untuk INSERT
            $result = $this->db->execute($sql, [
                "kode_pelanggan" => $kode,
                "nama_pelanggan" => $data["nama_pelanggan_baru"] ?? $data["search_pemilik"],
                "no_hp" => $data["no_hp"],
                "alamat" => $data["alamat"] ?? null,
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;

        } catch (Exception $e) {
            error_log("Error create pelanggan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update data pelanggan (UPDATE)
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE pelanggan SET nama_pelanggan = :nama_pelanggan, no_hp = :no_hp, alamat = :alamat WHERE id_pelanggan = :id";
            // FIX: Menggunakan execute() untuk UPDATE
            return $this->db->execute($sql, [
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
     * Hapus pelanggan (DELETE)
     */
    public function delete($id)
    {
        try {
            // Cek hewan (READ)
            $sqlCheck = "SELECT COUNT(*) as total FROM hewan WHERE id_pelanggan = :id";
            $stmtCheck = $this->db->query($sqlCheck, ["id" => $id]); // FIX: Menggunakan query()
            $result = $stmtCheck->fetch();

            if ($result['total'] > 0) { return false; }

            $sql = "DELETE FROM pelanggan WHERE id_pelanggan = :id";
            // FIX: Menggunakan execute() untuk DELETE
            return $this->db->execute($sql, ["id" => $id]);

        } catch (Exception $e) {
            error_log("Error delete pelanggan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate kode pelanggan otomatis (Helper)
     */
    private function generateKodePelanggan()
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(kode_pelanggan, 4) AS UNSIGNED)) as max_number FROM pelanggan WHERE kode_pelanggan LIKE 'PLG%'";
        $stmt = $this->db->query($sql); // FIX: Menggunakan query()
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

        $stmt = $this->db->query($sql);
        $stmt->execute(["keyword" => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total pelanggan
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM pelanggan";
        $stmt = $this->db->query($sql);
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

        $stmt = $this->db->query($sql);
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

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return ($result['total'] ?? 0) > 0;
}
public function getLastInsertId() {
    return $this->db->lastInsertId();
}

}
