<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load(); // load semua di file .env ke $_ENV, $_SERVER, dan setenv()
// var_dump(getenv('TEST') ?: 'tidak ada');
// die();
/**
 * Helper function - Mengembalikan array konfigurasi database lokal
 * @return array
 */
function getDatabaseConfig() {
    return [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'sslmode' => getenv('DB_SSLMODE') ?: 'disable',
        'dbname' => getenv('DB_NAME') ?: 'penitipan_hewan',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '', 
    ];
}
