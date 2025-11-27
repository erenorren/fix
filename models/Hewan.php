<?php
require_once __DIR__ . '/../config/database.php'; // penerapan Konsep OOP Class Hewan, Layanan, Pelanggan, dan Transaksi sama-sama menggunakan DB

class Hewan // Menggunakan Encapsulation private $db, public function getLastInsertId() {return$this->db->lastInsertId();}
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Ambil semua data hewan dengan format yang sesuai untuk view
     */
    public function getAll()
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.catatan,
                    h.ukuran,
                    h.warna,
                    h.status
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                ORDER BY h.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung statistik hewan (total, kucing, anjing)
     */
    public function getSummary()
    {
        $sql = "SELECT 
                    COUNT(*) as total_hewan,
                    SUM(CASE WHEN jenis = 'Kucing' THEN 1 ELSE 0 END) as total_kucing,
                    SUM(CASE WHEN jenis = 'Anjing' THEN 1 ELSE 0 END) as total_anjing
                FROM hewan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Untuk sementara, comment method lainnya yang tidak urgent
    // Kita fokus dulu ke method getAll() dan getSummary()

    /**
     * Tambah hewan baru
     */
    /**
 * Tambah hewan baru - RETURN LAST INSERT ID
 */
public function create($data) {
    try {
        $sql = "INSERT INTO hewan 
                (id_pelanggan, nama_hewan, jenis, ras, ukuran, warna, catatan, status)
                VALUES 
                (:id_pelanggan, :nama_hewan, :jenis, :ras, :ukuran, :warna, :catatan, :status)";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            "id_pelanggan" => $data["id_pelanggan"],
            "nama_hewan" => $data["nama_hewan"],
            "jenis" => $data["jenis"],
            "ras" => $data["ras"],
            "ukuran" => $data["ukuran"],
            "warna" => $data["warna"],
            "catatan" => $data["catatan"] ?? null,
            "status" => $data["status"] ?? "tersedia",
        ]);

        if ($result) {
            return $this->db->lastInsertId(); // Return the inserted ID
        } else {
            return false;
        }

    } catch (Exception $e) {
        error_log("Error create hewan: " . $e->getMessage());
        return false;
    }
}

    // Method lainnya bisa ditambahkan nanti setelah basic functionality work


    /**
     * Update data hewan
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
                        catatan = :catatan,  -- UBAH: keterangan -> catatan
                        status = :status
                    WHERE id_hewan = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                "id" => $id,
                "id_pelanggan" => $data["id_pelanggan"],
                "nama_hewan" => $data["nama_hewan"],
                "jenis" => $data["jenis"],
                "ras" => $data["ras"],
                "ukuran" => $data["ukuran"],
                "warna" => $data["warna"],
                "catatan" => $data["catatan"] ?? null,  // UBAH: keterangan -> catatan
                "status" => $data["status"] ?? "tersedia",
            ]);

        } catch (Exception $e) {
            error_log("Error update hewan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cari hewan berdasarkan nama/jenis/ras/pemilik
     */
    public function search($keyword)
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.catatan  -- UBAH: h.keterangan -> h.catatan
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                WHERE h.nama_hewan LIKE :key
                OR h.jenis LIKE :key
                OR h.ras LIKE :key
                OR p.nama_pelanggan LIKE :key
                ORDER BY h.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["key" => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil hewan berdasarkan jenis
     */
    public function getByJenis($jenis)
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.keterangan as catatan
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                WHERE h.jenis = :jenis
                ORDER BY h.nama_hewan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["jenis" => $jenis]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil hewan berdasarkan pemilik
     */
    public function getByPemilik($id_pelanggan)
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.keterangan as catatan
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                WHERE h.id_pelanggan = :id_pelanggan
                ORDER BY h.nama_hewan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(["id_pelanggan" => $id_pelanggan]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil hewan yang tersedia (status = tersedia)
     */
    public function getAvailable()
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.keterangan as catatan
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                WHERE h.status = 'tersedia'
                ORDER BY h.nama_hewan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil hewan yang sedang dititipkan
     */
    public function getInCare()
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.keterangan as catatan
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                WHERE h.status = 'sedang_dititipkan'
                ORDER BY h.nama_hewan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total hewan per status
     */
    public function countByStatus($status)
    {
        $sql = "SELECT COUNT(*) as total FROM hewan WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["status" => $status]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Ambil data untuk dropdown (id dan nama saja)
     */
    public function getForDropdown()
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras
                FROM hewan h
                WHERE h.status = 'tersedia'
                ORDER BY h.nama_hewan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cek apakah nama hewan sudah ada untuk pemilik tertentu
     */
    public function isNameExists($nama_hewan, $id_pelanggan, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM hewan 
                WHERE nama_hewan = :nama_hewan 
                AND id_pelanggan = :id_pelanggan";
        
        $params = [
            "nama_hewan" => $nama_hewan,
            "id_pelanggan" => $id_pelanggan
        ];

        if ($exclude_id) {
            $sql .= " AND id_hewan != :exclude_id";
            $params["exclude_id"] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return ($result['total'] ?? 0) > 0;
    }

    /**
     * Ambil data hewan dengan pagination
     */
    public function getWithPagination($limit = 10, $offset = 0)
    {
        $sql = "SELECT 
                    h.id_hewan as id,
                    h.nama_hewan as nama,
                    h.jenis,
                    h.ras,
                    p.nama_pelanggan as pemilik,
                    p.no_hp as no_telp,
                    h.keterangan as catatan
                FROM hewan h
                LEFT JOIN pelanggan p ON h.id_pelanggan = p.id_pelanggan
                ORDER BY h.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total hewan untuk pagination
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM hewan";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    /**
 * Update status hewan
 */
public function updateStatus($id, $status) {
    $allowed = ["tersedia", "sedang_dititipkan", "sudah_diambil"];

    if (!in_array($status, $allowed)) {
        $status = "tersedia";
    }

    $sql = "UPDATE hewan SET status = :status WHERE id_hewan = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        "id" => $id,
        "status" => $status
    ]);
}

// Tambahkan method ini di class Hewan  
public function getLastInsertId() {
    return $this->db->lastInsertId();
}
}
