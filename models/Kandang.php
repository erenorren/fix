<?php
require_once __DIR__ . '/../core/Database.php';

class Kandang
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ambil semua data kandang
     */
    public function getAll()
    {
        $sql = "SELECT 
                    k.id_kandang as id,
                    k.kode_kandang as kode,
                    k.tipe,
                    k.catatan,
                    k.status
                FROM kandang k 
                ORDER BY k.kode_kandang";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total kandang berdasarkan tipe
     */
    public function countByType($type)
    {
        $sql = "SELECT COUNT(*) as total FROM kandang WHERE tipe = ?";
        $stmt = $this->db->query($sql, [$type]);        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Ambil kandang yang tersedia berdasarkan jenis dan ukuran hewan
     */
    public function getAvailableKandang($jenisHewan, $ukuranHewan)
    {
        // Tentukan tipe kandang berdasarkan jenis dan ukuran hewan
        $tipeKandang = 'Kecil'; 
        if ($jenisHewan === 'Anjing' || $ukuranHewan === 'Besar' || $ukuranHewan === 'Sedang') {
            $tipeKandang = 'Besar';
        }

        $sql = "SELECT 
                    k.id_kandang as id,
                    k.kode_kandang as kode,
                    k.tipe,
                    k.catatan,
                    k.status
                FROM kandang k
                WHERE k.tipe = :tipe 
                AND k.status = 'tersedia'
                ORDER BY k.kode_kandang";
        
        $stmt = $this->db->query($sql, ['tipe' => $tipeKandang]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil data kandang berdasarkan ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM kandang WHERE id_kandang = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }


public function updateStatus($id, $status) {
    $allowed = ["tersedia", "terpakai", "maintenance"];

    if (!in_array($status, $allowed)) {
        $status = "tersedia";
    }

    $sql = "UPDATE kandang SET status = :status WHERE id_kandang = :id";
    return $this->db->execute($sql, [
            "id" => $id,
            "status" => $status
        ]);
}
}