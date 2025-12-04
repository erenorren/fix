<?php
class Database {
    private $connection;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $config = getDatabaseConfig();
        
        try {
            // Build DSN berdasarkan driver
            $dsn = $this->buildDSN($config);
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO(
                $dsn, 
                $config['username'], 
                $config['password'], 
                $options
            );
            
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function buildDSN($config) {
        $driver = $config['driver'];
        
        if ($driver === 'mysql') {
            return "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        }
        
        if ($driver === 'pgsql') {
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
            if (isset($config['sslmode'])) {
                $dsn .= ";sslmode={$config['sslmode']}";
            }
            return $dsn;
        }
        
        throw new Exception("Unsupported database driver: {$driver}");
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " - SQL: " . $sql);
            throw $e;
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