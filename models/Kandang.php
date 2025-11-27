<?php
require_once __DIR__ . '/../config/database.php';

class Kandang
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total kandang berdasarkan tipe
     */
    public function countByType($type)
    {
        $sql = "SELECT COUNT(*) as total FROM kandang WHERE tipe = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Ambil kandang yang tersedia berdasarkan jenis dan ukuran hewan
     */
    public function getAvailableKandang($jenisHewan, $ukuranHewan)
    {
        // Tentukan tipe kandang berdasarkan jenis dan ukuran hewan
        $tipeKandang = 'Kecil'; // default
        
        if ($jenisHewan === 'Anjing' || $ukuranHewan === 'Besar') {
            $tipeKandang = 'Besar';
        } elseif ($ukuranHewan === 'Sedang') {
            $tipeKandang = 'Besar'; // Sedang juga pakai kandang besar
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tipe' => $tipeKandang]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil data kandang berdasarkan ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM kandang WHERE id_kandang = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }


public function updateStatus($id, $status) {
    $allowed = ["tersedia", "terpakai", "maintenance"];

    if (!in_array($status, $allowed)) {
        $status = "tersedia";
    }

    $sql = "UPDATE kandang SET status = :status WHERE id_kandang = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        "id" => $id,
        "status" => $status
    ]);
}
}