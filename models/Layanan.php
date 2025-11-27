<?php
require_once __DIR__ . '/../core/Database.php';

class Layanan // Menggunakan Encapsulation private $db dan CRUD
{
    private $db;

    public function __construct()
    {
    $this->db = new Database();
    }

    /**
     * Ambil semua data layanan (READ)
     */
    public function getAll()
    {
    $sql = "SELECT * FROM layanan ORDER BY nama_layanan";
    // FIX: query() sudah benar untuk SELECT
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
    }

    /**
     * Ambil data layanan berdasarkan ID (READ)
     */
    public function getById($id)
    {
    $sql = "SELECT * FROM layanan WHERE id_layanan = ?";
    // FIX: query() sudah benar untuk SELECT dengan parameter
    $stmt = $this->db->query($sql, [$id]);
    return $stmt->fetch();
    }

    /**
     * CREATE layanan baru
     */
    public function create($data)
    {
    try {
    $sql = "INSERT INTO layanan (nama_layanan, harga, deskripsi) VALUES (?, ?, ?)";
    // FIX: Gunakan execute() untuk CUD (CREATE)
    return $this->db->execute($sql, [
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
    // FIX: Ganti $this->db->query() dan $stmt->query() menjadi execute()
    return $this->db->execute($sql, [
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
    // FIX: Ganti $this->db->query() dan $stmt->query() menjadi execute()
    return $this->db->execute($sql, [$id]);
    } catch (Exception $e) {
    error_log("Error delete layanan: " . $e->getMessage());
    return false;
    }
    }
}