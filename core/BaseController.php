<?php

/**
 * Kelas Dasar (Induk) untuk semua Controller.
 * Menerapkan Pewarisan (Inheritance) dan Reusability.
 */
class BaseController {

    /**
     * Memuat file View dan meneruskan data dari Controller.
     * * @param string $viewName Nama file view (tanpa .php)
     * @param array $data Data yang akan diekstrak menjadi variabel di view.
     * @return void
     */
    protected function view($viewName, $data = []) {
        // Kualitas Kode: Menggunakan extract() agar variabel dapat diakses langsung di View
        extract($data); 
        
        // Memuat file view yang diminta
        $viewPath = __DIR__ . '/../views/' . $viewName . '.php';

        if (!file_exists($viewPath)) {
            // Error Handling: Muat halaman 404 jika view tidak ditemukan
            // Asumsi file 404.php ada di views/404.php
            http_response_code(404);
            include __DIR__ . '/../views/404.php';
            exit;
        }

        include $viewPath; 
    }

    /**
     * Melakukan pengalihan (redirect) ke URL lain.
     * * @param string $path Jalur URL tujuan.
     * @return void
     */
    protected function redirect($path) {
        header("Location: {$path}");
        exit();
    }
}