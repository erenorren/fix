<?php
require_once __DIR__ . '/../core/Database.php';

/**
 * Class DetailTransaksi
 * Model untuk mengelola detail transaksi
 * Kompatibel dengan PostgreSQL Supabase
 */
class DetailTransaksi
{
    private $db;
    private $table = 'detail_transaksi';

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * ✅ FINAL: Tambah detail transaksi dengan RETURNING
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table}
                    (id_transaksi, kode_layanan, nama_layanan, harga, harga_satuan, quantity, jumlah, subtotal)
                    VALUES
                    (:id_transaksi, :kode_layanan, :nama_layanan, :harga, :harga_satuan, :quantity, :jumlah, :subtotal)
                    RETURNING id_detail";

            $params = [
                ':id_transaksi' => $data['id_transaksi'],
                ':kode_layanan' => $data['kode_layanan'] ?? null,
                ':nama_layanan' => $data['nama_layanan'],
                ':harga' => floatval($data['harga'] ?? 0),
                ':harga_satuan' => floatval($data['harga_satuan'] ?? $data['harga'] ?? 0),
                ':quantity' => intval($data['quantity'] ?? 1),
                ':jumlah' => intval($data['jumlah'] ?? $data['quantity'] ?? 1),
                ':subtotal' => floatval($data['subtotal'] ?? 0)
            ];

            $stmt = $this->db->query($sql, $params);
            
            if (!$stmt) {
                return false;
            }
            
            $result = $stmt->fetch();
            return $result['id_detail'] ?? false;

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
        $sql = "SELECT * FROM {$this->table} 
                WHERE id_transaksi = :id_transaksi 
                ORDER BY id_detail";

        $stmt = $this->db->query($sql, [':id_transaksi' => $id_transaksi]);
        return $stmt->fetchAll();
    }

    /**
     * Hapus detail transaksi berdasarkan ID transaksi
     */
    public function deleteByTransaksiId($id_transaksi)
    {
        $sql = "DELETE FROM {$this->table} WHERE id_transaksi = :id_transaksi";
        return $this->db->execute($sql, [':id_transaksi' => $id_transaksi]);
    }

    /**
     * Hitung total layanan tambahan untuk suatu transaksi
     */
    public function getTotalLayananTambahan($id_transaksi)
    {
        $sql = "SELECT COALESCE(SUM(subtotal), 0) as total 
                FROM {$this->table} 
                WHERE id_transaksi = :id_transaksi";
                
        $stmt = $this->db->query($sql, [':id_transaksi' => $id_transaksi]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
?>