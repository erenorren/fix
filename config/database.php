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
        'host' => getenv('DB_HOST') ?: 'aws-1-ap-southeast-1.pooler.supabase.com',
        'port' => getenv('DB_PORT') ?: '5432',
        'dbname' => getenv('DB_NAME') ?: 'postgres',
        'username' => getenv('DB_USER') ?: 'postgres.blmhsxcvjeafglpreuhk',
        'password' => getenv('DB_PASS') ?: '4XjAkby7NSnBg9NX', 
        'sslmode' => getenv('DB_SSLMODE') ?: 'require',
    ];
}
