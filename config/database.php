<?php

/**
 * Helper function - Mengembalikan array konfigurasi database lokal
 * @return array
 */
function getDatabaseConfig() {
    // Deteksi environment Vercel
    $isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);
    
    if ($isVercel) {
        // PostgreSQL Supabase untuk Vercel
        return [
            // kode asli kamu — AMAN
        'driver' => getenv('DB_DRIVER') ?: 'pgsql',
        'host' => getenv('DB_HOST') ?: 'aws-1-ap-southeast-1.pooler.supabase.com',
        'port' => getenv('DB_PORT') ?: '5432',
        'dbname' => getenv('DB_NAME') ?: 'postgres',
        'username' => getenv('DB_USER') ?: 'postgres.blmhsxcvjeafglpreuhk',
        'password' => getenv('DB_PASS') ?: '4XjAkby7NSnBg9NX',
        'sslmode' => getenv('DB_SSLMODE') ?: 'require',    
        ];
    } else {
        // MySQL untuk local (Laragon)
        return [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'dbname' => 'db_penitipan_hewan',
            'username' => 'root',
            'password' => 'Sh3Belajar!SQL',
        ];
    }
}
?>
