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
        'driver' => getenv('DB_DRIVER') ?: 'pgsql',
        'host' => getenv('DB_HOST') ?: 'db.techuxadxdkrjwromwrs.supabase.co',
        'port' => getenv('DB_PORT') ?: '5432',
        'dbname' => getenv('DB_NAME') ?: 'postgres',
        'username' => getenv('DB_USER') ?: 'postgres',
        'password' => getenv('DB_PASS') ?: 'uas_ppbo_fix', 
        'charset'  => 'utf8',
        'sslmode' => getenv('DB_SSLMODE') ?: 'require',
    ];
}
