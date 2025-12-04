<?php

function getDatabaseConfig() {

    // Deteksi Vercel
    $isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);

    if ($isVercel) {
        return [
            'driver'   => 'pgsql',
            'host'     => getenv('DB_HOST'),
            'port'     => getenv('DB_PORT') ?: 5432,
            'dbname'   => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'sslmode'  => getenv('DB_SSLMODE') ?: 'require'
        ];
    }

    // Local (XAMPP / Laragon)
    return [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'db_penitipan_hewan',
        'username' => 'root',
        'password' => 'Sh3Belajar!SQL',
    ];
}
