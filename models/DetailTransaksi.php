<?php
require_once __DIR__ . '/../config/database.php';

class DetailTransaksi
{
    private $db;
    private $table = 'detail_transaksi';

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Tambah detail transaksi (layanan tambahan)
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (id_transaksi, kode_layanan, nama_layanan, harga, quantity, subtotal)
                    VALUES 
                    (:id_transaksi, :kode_layanan, :nama_layanan, :harga, :quantity, :subtotal)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                "id_transaksi" => $data["id_transaksi"],
                "kode_layanan" => $data["kode_layanan"],
                "nama_layanan" => $data["nama_layanan"],
                "harga" => $data["harga"],
                "quantity" => $data["quantity"] ?? 1,
                "subtotal" => $data["subtotal"]
            ]);

        } catch (Exception $e) {
            error_log("Error create detail transaksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil detail transaksi berdasarkan ID transaksi
     */
    public function getByTransaksiId($id_transaksi)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_transaksi = ? ORDER BY created_at";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_transaksi]);
        return $stmt->fetchAll();
    }

    /**
     * Hapus detail transaksi
     */
    public function deleteByTransaksiId($id_transaksi)
    {
        $sql = "DELETE FROM {$this->table} WHERE id_transaksi = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id_transaksi]);
    }

    /**
     * Hitung total layanan tambahan untuk suatu transaksi
     */
    public function getTotalLayananTambahan($id_transaksi)
    {
        $sql = "SELECT SUM(subtotal) as total FROM {$this->table} WHERE id_transaksi = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_transaksi]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}