<?php
require_once __DIR__ . '/../models/Layanan.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../config/database.php';

// Ada CRUD

class LayananController
{
    private $layanan;

    public function __construct()
    {
        $db = getDB();
        $this->layanan = new Layanan($db);
    }

    public function index()
    {
        $data['layanan'] = $this->layanan->getAllLayanan();
        Helper::view('layanan/index', $data);
    }

    public function create()
    {
        Helper::view('layanan/create');
    }

    public function store()
    {
        if (!isset($_POST['submit'])) {
            Helper::redirect('?page=layanan');
        }

        $nama  = clean($_POST['nama']);
        $harga = clean($_POST['harga']);
        $jenis = clean($_POST['jenis']);

        $this->layanan->tambahLayanan($nama, $harga, $jenis);

        Helper::redirect('?page=layanan&msg=added');
    }

    public function edit()
    {
        if (!isset($_GET['id'])) {
            Helper::redirect('?page=layanan');
        }

        $id = intval($_GET['id']);

        $data['layanan'] = $this->layanan->getLayananById($id);

        if (!$data['layanan']) {
            Helper::redirect('?page=layanan&msg=notfound');
        }

        Helper::view('layanan/edit', $data);
    }

    public function update()
    {
        if (!isset($_POST['submit'])) {
            Helper::redirect('?page=layanan');
        }

        $id    = intval($_POST['id']);
        $nama  = clean($_POST['nama']);
        $harga = clean($_POST['harga']);
        $jenis = clean($_POST['jenis']);

        $this->layanan->updateLayanan($id, $nama, $harga, $jenis);

        Helper::redirect('?page=layanan&msg=updated');
    }

    public function delete()
    {
        if (!isset($_GET['id'])) {
            Helper::redirect('?page=layanan');
        }

        $id = intval($_GET['id']);
        $this->layanan->deleteLayanan($id);

        Helper::redirect('?page=layanan&msg=deleted');
    }
}
