<?php
// core /database.php
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    private $port;
    private $charset = 'utf8mb4'; // Ditambahkan untuk koneksi yang lebih stabil
    
    public function __construct() {
        // 1. Include konfigurasi database yang SANGAT SEDERHANA
        require_once __DIR__ . '/../config/database.php';
        
        // 2. Ambil konfigurasi (Hanya fungsi getDatabaseConfig() yang tersisa di file config)
        $config = getDatabaseConfig();
        
        // 3. Simpan properti
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->database = $config['dbname']; // KOREKSI: Menggunakan key 'dbname' dari config
        $this->port = $config['port'];
        
        $this->connect();
    }
    
    private function connect() {
        try {
            // KOREKSI: Gunakan port dalam DSN
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Tambahkan opsi yang lebih baik dari file config lama
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Tampilkan error koneksi yang lebih informatif
            die("Connection failed: " . $e->getMessage() . 
                "<br>Host: {$this->host}, DB: {$this->database}, Port: {$this->port}");
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage() . " - SQL: " . $sql);
        }
    }
    
    // ... (metode execute, lastInsertId, transaction methods tetap sama) ...
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Execute failed: " . $e->getMessage() . " - SQL: " . $sql);
        }
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