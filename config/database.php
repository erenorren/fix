<?php
/**
 * Database Configuration
 * LOCAL: MySQL (Laragon/XAMPP)
 * PRODUCTION: PostgreSQL (Supabase via Vercel)
 */

function getDatabaseConfig() {
    // Deteksi environment Vercel
    $isVercel = getenv('VERCEL') === '1' || 
                isset($_ENV['VERCEL']) || 
                isset($_SERVER['VERCEL']);

    if ($isVercel) {
        // ===== PRODUCTION (Vercel + Supabase PostgreSQL) =====
        return [
            'driver'   => 'pgsql',
            'host'     => getenv('DB_HOST'),
            'port'     => (int)(getenv('DB_PORT') ?: 6543),
            'dbname'   => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'sslmode'  => getenv('DB_SSLMODE') ?: 'require',
            'options'  => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => 30,
            ]
        ];
    } else {
        // ===== LOCAL DEVELOPMENT (MySQL) =====
        return [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'port'     => 3306,
            'dbname'   => 'db_penitipan_hewan',
            'username' => 'root',
            'password' => 'Sh3Belajar!SQL',
            'charset'  => 'utf8mb4',
            'options'  => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
    }
}

/**
 * Helper: Check if running on Vercel
 */
function isProduction() {
    return getenv('VERCEL') === '1' || 
           isset($_ENV['VERCEL']) || 
           isset($_SERVER['VERCEL']);
}

/**
 * Helper: Get environment name
 */
function getEnvironment() {
    return isProduction() ? 'production' : 'development';
}

/**
 * Helper: Get database driver being used
 */
function getDatabaseDriver() {
    $config = getDatabaseConfig();
    return $config['driver'];
}