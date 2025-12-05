<?php
namespace Models;

use Core\Database;
use PDO;
use PDOException;

class DetailTransaksi
{
    private $db;
    private $table = 'detail_transaksi';

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function create($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (id_transaksi, kode_layanan, nama_layanan, harga, quantity, subtotal)
                    VALUES 
                    (:id_transaksi, :kode_layanan, :nama_layanan, :harga, :quantity, :subtotal)";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ":id_transaksi"   => $data["id_transaksi"],
                ":kode_layanan"   => $data["kode_layanan"],
                ":nama_layanan"   => $data["nama_layanan"],
                ":harga"          => $data["harga"],
                ":quantity"       => $data["quantity"] ?? 1,
                ":subtotal"       => $data["subtotal"]
            ]);

        } catch (PDOException $e) {
            error_log("Error create detail transaksi: " . $e->getMessage());
            return false;
        }
    }

    public function getByTransaksiId($id_transaksi)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_transaksi = :id_transaksi ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_transaksi' => $id_transaksi]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByTransaksiId($id_transaksi)
    {
        $sql = "DELETE FROM {$this->table} WHERE id_transaksi = :id_transaksi";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id_transaksi' => $id_transaksi]);
    }

    public function getTotalLayananTambahan($id_transaksi)
    {
        $sql = "SELECT SUM(subtotal) as total FROM {$this->table} WHERE id_transaksi = :id_transaksi";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_transaksi' => $id_transaksi]);
        return $stmt->fetchColumn() ?: 0;
    }
}
