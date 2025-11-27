<?php
require_once __DIR__ . '/../config/database.php'; // penerapan Konsep OOP Class Hewan, Layanan, Pelanggan, dan Transaksi sama-sama menggunakan DB


class Layanan // Menggunakan Encapsulation private $db dan CRUD
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Ambil semua data layanan
     */
    public function getAll()
    {
        $sql = "SELECT * FROM layanan ORDER BY nama_layanan";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil data layanan berdasarkan ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM layanan WHERE id_layanan = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * CREATE layanan baru
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO layanan (nama_layanan, harga, deskripsi) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['nama_layanan'],
                $data['harga'],
                $data['deskripsi'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error create layanan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE data layanan 
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE layanan SET nama_layanan = ?, harga = ?, deskripsi = ? WHERE id_layanan = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['nama_layanan'],
                $data['harga'],
                $data['deskripsi'] ?? null,
                $id
            ]);
        } catch (Exception $e) {
            error_log("Error update layanan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE layanan
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM layanan WHERE id_layanan = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error delete layanan: " . $e->getMessage());
            return false;
        }
    }
}
