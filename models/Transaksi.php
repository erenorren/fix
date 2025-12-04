<?php
require_once __DIR__ . '/../core/Database.php';

class Transaksi 
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAll()
    {
        $sql = "SELECT t.*, p.nama_pelanggan, h.nama_hewan, l.nama_layanan, k.kode_kandang
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN layanan l ON t.id_layanan = l.id_layanan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                ORDER BY t.tanggal_masuk DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getActiveTransactions()
    {
        $sql = "SELECT t.id_transaksi, t.kode_transaksi, p.nama_pelanggan, h.nama_hewan, 
                       h.jenis AS jenis_hewan, k.kode_kandang, t.tanggal_masuk, 
                       t.durasi, t.total_biaya
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                WHERE t.status = 'active'
                ORDER BY t.tanggal_masuk DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function create($data)
    {
        try {
            $kodeTransaksi = $this->generateKodeTransaksi();

            $sql = "INSERT INTO transaksi
                    (kode_transaksi, id_pelanggan, id_hewan, id_kandang, id_layanan,
                     biaya_paket, tanggal_masuk, durasi, total_biaya, status_pembayaran)
                    VALUES
                    (:kode_transaksi, :id_pelanggan, :id_hewan, :id_kandang, :id_layanan,
                     :biaya_paket, :tanggal_masuk, :durasi, :total_biaya, 'belum_lunas')
                    RETURNING id_transaksi";

            $stmt = $this->db->query($sql, [
                'kode_transaksi' => $kodeTransaksi,
                'id_pelanggan' => $data['id_pelanggan'],
                'id_hewan' => $data['id_hewan'],
                'id_kandang' => $data['id_kandang'],
                'id_layanan' => $data['id_layanan'],
                'biaya_paket' => $data['biaya_paket'],
                'tanggal_masuk' => $data['tanggal_masuk'],
                'durasi' => $data['durasi'],
                'total_biaya' => $data['total_biaya']
            ]);

            $result = $stmt->fetch();
            return $result['id_transaksi'] ?? false;

        } catch (Exception $e) {
            error_log("MODEL ERROR create transaksi: ".$e->getMessage());
            return false;
        }
    }

    public function checkout($id)
    {
        try {
            $sql = "UPDATE transaksi
                    SET status = 'completed', tanggal_keluar = CURRENT_DATE
                    WHERE id_transaksi = :id
                    RETURNING id_transaksi";

            $stmt = $this->db->query($sql, ['id' => $id]);
            return $stmt->fetch() ? true : false;

        } catch (Exception $e) {
            error_log("Error checkout:".$e->getMessage());
            return false;
        }
    }

    public function getById($id)
    {
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat,
                       h.nama_hewan, h.jenis, h.ras, h.ukuran, h.warna,
                       l.nama_layanan, l.harga AS harga_layanan,
                       k.kode_kandang, k.tipe AS tipe_kandang
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN layanan l ON t.id_layanan = l.id_layanan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                WHERE t.id_transaksi = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    public function getTotalHewanAktif()
    {
        $sql = "SELECT COUNT(*) total FROM transaksi WHERE status='active'";
        return $this->db->query($sql)->fetch()['total'] ?? 0;
    }

    public function getTotalHewanAktifByJenis($jenis)
    {
        $sql = "SELECT COUNT(*) total
                FROM transaksi t
                JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.status='active' AND h.jenis=:jenis";
        return $this->db->query($sql, ['jenis'=>$jenis])->fetch()['total'] ?? 0;
    }

    private function generateKodeTransaksi()
    {
        $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(kode_transaksi FROM 4) AS INTEGER)),0) max_number
                FROM transaksi WHERE kode_transaksi LIKE 'TRX%'";
        $res = $this->db->query($sql)->fetch();
        return 'TRX' . str_pad(($res['max_number']+1), 3, '0', STR_PAD_LEFT);
    }

    public function getByNomor($kodeTransaksi)
    {
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp,
                       u.nama_lengkap AS nama_kasir
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN \"user\" u ON t.id_user = u.id_user
                WHERE t.kode_transaksi = :kode";
        
        $trx = $this->db->query($sql, ['kode'=>$kodeTransaksi])->fetch();
        if ($trx) {
            $trx['detail_layanan'] = $this->getDetailLayanan($trx['id_transaksi']);
        }
        return $trx;
    }

    public function getDetailLayanan($idTransaksi)
    {
        $sql = "SELECT dt.*, l.nama_layanan
                FROM detail_transaksi dt
                LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
                WHERE dt.id_transaksi = :id";
        return $this->db->query($sql, ['id'=>$idTransaksi])->fetchAll();
    }

    public function search($keyword)
    {
        $sql = "SELECT t.*, p.nama_pelanggan, h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.kode_transaksi LIKE :keyword
                   OR p.nama_pelanggan LIKE :keyword
                   OR h.nama_hewan LIKE :keyword
                ORDER BY t.created_at DESC";
        return $this->db->query($sql, ['keyword'=>"%$keyword%"])->fetchAll();
    }

    public function updateCheckout($id, $data)
    {
        try {
            $this->db->beginTransaction();

            $trx = $this->getById($id);

            $sql = "UPDATE transaksi
                    SET tanggal_keluar_aktual = :tgl_keluar,
                        jam_keluar_aktual = :jam_keluar,
                        durasi_hari = :durasi,
                        diskon = :diskon,
                        total_biaya = :total,
                        metode_pembayaran = :metode,
                        status = 'selesai',
                        status_pembayaran = 'lunas'
                    WHERE id_transaksi = :id";
            $this->db->execute($sql, [
                'id'=>$id,
                'tgl_keluar'=>$data['tanggal_keluar_aktual'],
                'jam_keluar'=>$data['jam_keluar_aktual'] ?? date('H:i:s'),
                'durasi'=>$data['durasi_hari'],
                'diskon'=>$data['diskon'] ?? 0,
                'total'=>$data['total_biaya'],
                'metode'=>$data['metode_pembayaran']
            ]);

            $this->db->execute(
                "UPDATE hewan SET status='sudah_diambil' WHERE id_hewan = :id",
                ['id'=>$trx['id_hewan']]
            );

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updateCheckout:".$e->getMessage());
            return false;
        }
    }

    public function getSedangDititipkan()
    {
        $sql = "SELECT t.*, p.nama_pelanggan, h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.status = 'sedang_dititipkan'
                ORDER BY t.tanggal_masuk DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function hitungPendapatan($mulai, $akhir)
    {
        $sql = "SELECT SUM(total_biaya) total
                FROM transaksi
                WHERE tanggal_masuk BETWEEN :mulai AND :akhir
                AND status_pembayaran='lunas'";
        return $this->db->query($sql, ['mulai'=>$mulai,'akhir'=>$akhir])->fetch()['total'] ?? 0;
    }

    public function calculateTotalFromInputs(int $durasiHari, array $detailLayanan, float $paketPerHari=0.0, float $diskon=0.0)
    {
        $subtotalLayanan = 0;
        foreach($detailLayanan as $d) {
            $harga = $d['harga'] ?? $d['harga_satuan'] ?? 0;
            $qty = $d['quantity'] ?? $d['jumlah'] ?? 1;
            $subtotalLayanan += $harga * $qty;
        }
        $biayaPaket = $paketPerHari * max(1,$durasiHari);
        $subtotal = $biayaPaket + $subtotalLayanan;
        return [
            'biaya_paket'=>$biayaPaket,
            'subtotal_layanan'=>$subtotalLayanan,
            'subtotal'=>$subtotal,
            'diskon'=>$diskon,
            'total_biaya'=>$subtotal - $diskon
        ];
    }
}
