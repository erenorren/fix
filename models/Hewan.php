<?php
require_once __DIR__ . '/../core/Database.php';

/**
 * Class Hewan
 * 
 * Model untuk mengelola data hewan dalam sistem.
 * Menyediakan operasi CRUD, pencarian, filter, dan statistik.
 */
class Hewan 
{
    /** @var Database $db Instance database untuk eksekusi query */
    private $db;

    /**
     * Constructor
     * Inisialisasi koneksi database melalui class Database
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ambil semua data hewan
     * 
     * @return array Array berisi semua data hewan beserta nama pemilik
     */
    /**
 * Ambil semua data hewan
 * 
 * @return array Array berisi semua data hewan beserta nama pemilik
 */
public function getAll()
{
    try {
        $sql = "SELECT 
                    h.id_hewan,
                    h.nama_hewan,
                    h.jenis,
                    h.ras,
                    h.ukuran,
                    h.warna,
                    h.catatan,
                    h.status,
                    p.id_pelanggan,
                    p.nama_pelanggan,
                    p.no_hp
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                ORDER BY h.nama_hewan";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error getAll hewan: " . $e->getMessage());
        return [];
    }
}

public function create($data) {
    try {
        $sql = "INSERT INTO hewan (id_pelanggan, nama_hewan, jenis, ras, ukuran, warna, catatan, status) 
                VALUES (:id_pelanggan, :nama_hewan, :jenis, :ras, :ukuran, :warna, :catatan, :status) 
                RETURNING id_hewan";
        
        $params = [
            ":id_pelanggan" => $data["id_pelanggan"],
            ":nama_hewan" => $data["nama_hewan"],
            ":jenis" => $data["jenis"],
            ":ras" => $data["ras"] ?? null,
            ":ukuran" => $data["ukuran"] ?? null,
            ":warna" => $data["warna"] ?? null,
            ":catatan" => $data["catatan"] ?? null,
            ":status" => $data["status"] ?? 'tersedia'
        ];
        
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        
        return $result['id_hewan'] ?? false;
        
    } catch (Exception $e) {
        error_log("Error create hewan: " . $e->getMessage());
        return false;
    }
}

    /**
     * Update data hewan
     * 
     * @param int $id ID hewan yang akan diupdate
     * @param array $data Data hewan baru
     * @return bool True jika berhasil, false jika gagal
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE hewan SET 
                        id_pelanggan = :id_pelanggan, 
                        nama_hewan = :nama_hewan, 
                        jenis = :jenis, 
                        ras = :ras, 
                        ukuran = :ukuran, 
                        warna = :warna, 
                        catatan = :catatan, 
                        status = :status 
                    WHERE id_hewan = :id";

            $params = [
                "id" => $id,
                "id_pelanggan" => $data['id_pelanggan'],
                "nama_hewan" => $data['nama_hewan'],
                "jenis" => $data['jenis'],
                "ras" => $data['ras'] ?? '',
                "ukuran" => $data['ukuran'] ?? '',
                "warna" => $data['warna'] ?? '',
                "catatan" => $data['catatan'] ?? '',
                "status" => $data['status'] ?? 'tersedia'
            ];

            return $this->db->execute($sql, $params);

        } catch (Exception $e) {
            error_log("Error update hewan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cari hewan berdasarkan keyword
     * 
     * @param string $keyword Keyword untuk pencarian nama, jenis, ras, atau nama pemilik
     * @return array Array hasil pencarian
     */
    public function search($keyword)
    {
        $sql = "SELECT h.id_hewan as id, h.nama_hewan as nama, h.jenis, h.ras, 
                       p.nama_pelanggan as pemilik, p.no_hp as no_telp, h.catatan 
                FROM hewan h 
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                WHERE h.nama_hewan ILIKE :key 
                   OR h.jenis ILIKE :key 
                   OR h.ras ILIKE :key 
                   OR p.nama_pelanggan ILIKE :key 
                ORDER BY h.created_at DESC";
        $stmt = $this->db->query($sql, ["key" => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil data hewan berdasarkan jenis
     * 
     * @param string $jenis Jenis hewan
     * @return array Array hewan dengan jenis tertentu
     */
    public function getByJenis($jenis)
    {
        $sql = "SELECT h.id_hewan as id, h.nama_hewan as nama, h.jenis, h.ras, 
                       p.nama_pelanggan as pemilik, p.no_hp as no_telp, h.keterangan as catatan 
                FROM hewan h 
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                WHERE h.jenis = :jenis 
                ORDER BY h.nama_hewan";
        $stmt = $this->db->query($sql, ["jenis" => $jenis]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil data hewan berdasarkan pemilik
     * 
     * @param int $id_pelanggan ID pemilik
     * @return array Array hewan milik pemilik tertentu
     */
    public function getByPemilik($id_pelanggan)
    {
        $sql = "SELECT h.id_hewan as id, h.nama_hewan as nama, h.jenis, h.ras, 
                       p.nama_pelanggan as pemilik, p.no_hp as no_telp, h.keterangan as catatan 
                FROM hewan h 
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                WHERE h.id_pelanggan = :id_pelanggan 
                ORDER BY h.nama_hewan";
        $stmt = $this->db->query($sql, ["id_pelanggan" => $id_pelanggan]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil hewan yang tersedia
     * 
     * @return array Array hewan dengan status "tersedia"
     */
    public function getAvailable()
    {
        $sql = "SELECT h.id_hewan as id, h.nama_hewan as nama, h.jenis, h.ras, 
                       p.nama_pelanggan as pemilik, p.no_hp as no_telp, h.keterangan as catatan 
                FROM hewan h 
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                WHERE h.status = 'tersedia' 
                ORDER BY h.nama_hewan";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Ambil hewan yang sedang dititipkan
     * 
     * @return array Array hewan dengan status "sedang_dititipkan"
     */
    public function getInCare()
    {
        $sql = "SELECT h.id_hewan as id, h.nama_hewan as nama, h.jenis, h.ras, 
                       p.nama_pelanggan as pemilik, p.no_hp as no_telp, h.keterangan as catatan 
                FROM hewan h 
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                WHERE h.status = 'sedang_dititipkan' 
                ORDER BY h.nama_hewan";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total hewan berdasarkan status
     * 
     * @param string $status Status hewan
     * @return int Total hewan dengan status tertentu
     */
    public function countByStatus($status)
    {
        $sql = "SELECT COUNT(*) as total FROM hewan WHERE status = :status";
        $stmt = $this->db->query($sql, ["status" => $status]);
        return $stmt->fetch()['total'] ?? 0;
    }

    /**
     * Ambil data hewan dengan pagination
     * 
     * @param int $limit Jumlah data per halaman
     * @param int $offset Posisi offset
     * @return array Array data hewan
     */
    public function getWithPagination($limit = 10, $offset = 0)
    {
        $sql = "SELECT h.id_hewan as id, h.nama_hewan as nama, h.jenis, h.ras, 
                       p.nama_pelanggan as pemilik, p.no_hp as no_telp, h.keterangan as catatan 
                FROM hewan h 
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan 
                ORDER BY h.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->query($sql, ["limit" => (int)$limit, "offset" => (int)$offset]);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total hewan untuk pagination
     * 
     * @return int Total seluruh hewan
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM hewan";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'] ?? 0;
    }

    /**
     * Update status hewan
     * 
     * @param int $id ID hewan
     * @param string $status Status baru (tersedia/sedang_dititipkan/sudah_diambil)
     * @return bool True jika berhasil
     */
    public function updateStatus($id, $status) {
        $allowed = ["tersedia", "sedang_dititipkan", "sudah_diambil"];
        if (!in_array($status, $allowed)) { $status = "tersedia"; }

        $sql = "UPDATE hewan SET status = :status WHERE id_hewan = :id";
        return $this->db->execute($sql, ["id" => $id, "status" => $status ]);
    }

    /**
     * Ambil ID insert terakhir
     * 
     * @return int ID terakhir yang dimasukkan
     */
    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}
