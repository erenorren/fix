<?php
require_once __DIR__ . '/../core/Database.php';
// layanan
class Layanan 
{
    private $db;

    public function __construct() { $this->db = new Database(); }

    /** 
     * Ambil semua data layanan (READ)
     * PostgreSQL compatible
     */
    public function getAll()
    {
        $sql = "SELECT * FROM layanan ORDER BY nama_layanan";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /** 
     * Ambil data layanan berdasarkan ID (READ)
     * PostgreSQL compatible, menggunakan named parameter
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM layanan WHERE id_layanan = :id";
        $stmt = $this->db->query($sql, ["id" => $id]);
        return $stmt->fetch();
    }
    
    /**
     * CREATE layanan baru
     * PostgreSQL compatible, menggunakan named parameter
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO layanan (nama_layanan, harga, deskripsi) 
                    VALUES (:nama_layanan, :harga, :deskripsi)";
            return $this->db->execute($sql, [
                "nama_layanan" => $data['nama_layanan'],
                "harga" => $data['harga'],
                "deskripsi" => $data['deskripsi'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error create layanan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE data layanan
     * PostgreSQL compatible, menggunakan named parameter
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE layanan 
                    SET nama_layanan = :nama_layanan, 
                        harga = :harga, 
                        deskripsi = :deskripsi 
                    WHERE id_layanan = :id";
            return $this->db->execute($sql, [
                "nama_layanan" => $data['nama_layanan'],
                "harga" => $data['harga'],
                "deskripsi" => $data['deskripsi'] ?? null,
                "id" => $id
            ]);
        } catch (Exception $e) {
            error_log("Error update layanan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE layanan
     * PostgreSQL compatible, menggunakan named parameter
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM layanan WHERE id_layanan = :id";
            return $this->db->execute($sql, ["id" => $id]);
        } catch (Exception $e) {
            error_log("Error delete layanan: " . $e->getMessage());
            return false;
        }
    }
}
?>
