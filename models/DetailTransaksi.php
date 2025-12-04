<?php
require_once __DIR__ . '/../core/Database.php';

/**
 * Class DetailTransaksi
 * 
 * Model untuk mengelola detail transaksi.
 * Semua fungsi kompatibel dengan PostgreSQL Supabase.
 * 
 * Dampak perubahan:
 * - Semua query memakai prepared statement PostgreSQL agar aman.
 * - Tidak ada fungsi yang dihapus atau ditambah.
 * - Semua fitur CRUD tetap ada.
 */
class DetailTransaksi
{
    private $db;
    private $table = 'detail_transaksi';

    public function __construct()
    {
        // Pastikan Database.php menginisialisasi PDO dengan Supabase
        $this->db = new Database();
    }

    /**
     * Tambah detail transaksi (CREATE)
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (id_transaksi, kode_layanan, nama_layanan, harga, quantity, subtotal) 
                    VALUES 
                    (:id_transaksi, :kode_layanan, :nama_layanan, :harga, :quantity, :subtotal)";

            return $this->db->execute($sql, [
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
     * Ambil detail transaksi berdasarkan ID transaksi (READ)
     */
    public function getByTransaksiId($id_transaksi)
    {
        // Gunakan parameter binding agar kompatibel Supabase
        $sql = "SELECT * FROM {$this->table} WHERE id_transaksi = :id_transaksi ORDER BY id";

        $stmt = $this->db->query($sql, ['id_transaksi' => $id_transaksi]);
        return $stmt->fetchAll();
    }

    /**
     * Hapus detail transaksi berdasarkan ID transaksi (DELETE)
     */
    public function deleteByTransaksiId($id_transaksi)
    {
        $sql = "DELETE FROM {$this->table} WHERE id_transaksi = :id_transaksi";
        return $this->db->execute($sql, ['id_transaksi' => $id_transaksi]);
    }

    /**
     * Hitung total layanan tambahan untuk suatu transaksi
     */
    public function getTotalLayananTambahan($id_transaksi)
    {
        $sql = "SELECT SUM(subtotal) as total FROM {$this->table} WHERE id_transaksi = :id_transaksi";
        $stmt = $this->db->query($sql, ['id_transaksi' => $id_transaksi]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
