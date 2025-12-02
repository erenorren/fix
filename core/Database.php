<?php

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    private $port;
    
    public function __construct() {
        // 1. Include konfigurasi database
        require_once __DIR__ . '/../config/database.php';
        
        // 2. Ambil konfigurasi
        $config = getDatabaseConfig();
        
        // 3. Simpan properti
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->database = $config['dbname'];
        $this->port = $config['port'];
        
        try {
            // Buat DSN
            $dsn = "{$config['driver']}:host={$this->host};port={$this->port};dbname={$this->database}";
            
            // Tambahkan charset untuk MySQL
            if ($config['driver'] === 'mysql') {
                $dsn .= ";charset=utf8mb4";
            }
            
            // Tambahkan sslmode hanya jika ada dan untuk PostgreSQL
            if (isset($config['sslmode']) && $config['driver'] === 'pgsql') {
                $dsn .= ";sslmode={$config['sslmode']}";
            }
            
            // OPTIONS untuk PDO
            $options = [
                PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // ↓↓↓ INI YANG ANDA HAPUS! ↓↓↓
            // BUAT KONEKSI PDO
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage() . 
                "<br>Host: {$this->host}, DB: {$this->database}, Port: {$this->port}" .
                "<br>Driver: {$config['driver']}, DSN: {$dsn}");
        }
    }
    
    /**
     * WRAPPER untuk SELECT/READ (Menggunakan prepare dan execute secara internal)
     */
    public function query($sql, $params = []) {
        try {
            // FIX: Menggunakan prepare dan execute di sini
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage() . " - SQL: " . $sql);
        }
    }
    
    /**
     * WRAPPER untuk CUD (CREATE, UPDATE, DELETE)
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Execute failed: " . $e->getMessage() . " - SQL: " . $sql);
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollBack() {
        return $this->connection->rollBack();
    }
}

?>