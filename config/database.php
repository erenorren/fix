<?php
/**
 * Database Connection Configuration
 * Sistem Penitipan Hewan
 * 
 * CARA PAKAI:
 * 1. Lokal (Laragon/XAMPP): Pakai config default
 * 2. Production (PlanetScale/Railway): Uncomment bagian production
 *
 */

/**
 * Database Configuration
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port; 
    private $charset = 'utf8mb4';
    
    private function __construct() {
        
        // --- LOGIKA MENDETEKSI LINGKUNGAN (RAILWAY vs LOKAL) ---
        // Jika ENV VAR MYSQLHOST ada (berarti di Railway), gunakan itu.
        if (getenv('MYSQLHOST')) {
            $this->host = getenv('MYSQLHOST');
            $this->port = getenv('MYSQLPORT') ?: '3306'; 
            $this->dbname = getenv('MYSQLDATABASE');
            $this->username = getenv('MYSQLUSER');
            $this->password = getenv('MYSQLPASSWORD');
        } else {
            // Jika tidak ada ENV VAR (berarti di lokal), gunakan konfigurasi lokal default.
            $this->host = 'localhost';
            $this->port = '3306';
            $this->dbname = 'db_penitipan_hewan';
            $this->username = 'root';
            $this->password = 'Sh3Belajar!SQL'; // Ganti dengan password lokal Anda
        }
        
        // Tentukan DSN dengan PORT
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
        
        try {
            $options = [
                PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_PERSISTENT => false,
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Tampilkan error PDO jika debugging aktif (karena ini error fatal)
            if (ini_get('display_errors')) {
                die("Koneksi DB Gagal: " . $e->getMessage() . "<br>Host: {$this->host}, DB: {$this->dbname}");
            }
            // Pesan user-friendly di production
            die("Maaf, terjadi kesalahan koneksi database. Silakan hubungi administrator.");
        }
    }

    
    /**
     * Get Database Instance
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO Connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevent cloning
     */
    // add
    private function __clone() {}
    
    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Test koneksi database
     * 
     * @return bool
     */
    public function testConnection() {
        try {
            $this->connection->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

/**
 * Helper function - Shortcut untuk get connection
 * 
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Helper function - Test koneksi
 * 
 * @return bool
 */
function isDBConnected() {
    return Database::getInstance()->testConnection();
}

function getDatabaseConfig() {
    $db = Database::getInstance();
    return [
        'host' => 'localhost',
        'username' => 'root', 
        'password' => '', // Sesuaikan dengan password MySQL Anda
        'database' => 'db_penitipan_hewan'
    ];
}
