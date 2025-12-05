<?php
class Database {
    private $connection;
    private $driver;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $config = getDatabaseConfig();
        $this->driver = $config['driver'];
        
        try {
            $dsn = $this->buildDSN($config);
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Tambahan untuk PostgreSQL/Supabase
            if ($this->driver === 'pgsql') {
                $options[PDO::ATTR_PERSISTENT] = false;
                $options[PDO::ATTR_TIMEOUT] = 30;
            }
            
            $this->connection = new PDO(
                $dsn, 
                $config['username'], 
                $config['password'], 
                $options
            );
            
            // Set timezone untuk PostgreSQL
            if ($this->driver === 'pgsql') {
                $this->connection->exec("SET TIME ZONE 'Asia/Jakarta'");
            }
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    private function buildDSN($config) {
        $driver = $config['driver'];
        
        if ($driver === 'mysql') {
            return "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        }
        
        if ($driver === 'pgsql') {
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
            
            // SSL Mode WAJIB untuk Supabase
            $sslmode = $config['sslmode'] ?? 'require';
            $dsn .= ";sslmode={$sslmode}";
            
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
            error_log("Query error: " . $e->getMessage() . " - SQL: " . $sql . " - Params: " . json_encode($params));
            throw $e;
        }
    }
    
    /**
     * WRAPPER untuk CUD (CREATE, UPDATE, DELETE)
     * Dengan error handling yang lebih baik
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new PDOException("Execute failed: " . $errorInfo[2]);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Execute failed: " . $e->getMessage() . " - SQL: " . $sql . " - Params: " . json_encode($params));
            throw new Exception("Database execute error: " . $e->getMessage());
        }
    }
    
    /**
     * Get last insert ID dengan support PostgreSQL sequence
     * Untuk PostgreSQL, bisa pass nama sequence atau table
     */
    public function lastInsertId($sequenceName = null) {
        try {
            if ($this->driver === 'pgsql' && $sequenceName !== null) {
                // Untuk PostgreSQL dengan explicit sequence
                return $this->connection->lastInsertId($sequenceName);
            }
            
            // Untuk MySQL atau PostgreSQL dengan SERIAL
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("lastInsertId error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Helper untuk get last insert ID dari tabel tertentu (PostgreSQL)
     */
    public function getLastInsertId($tableName, $idColumn = 'id') {
        if ($this->driver === 'pgsql') {
            try {
                $sql = "SELECT currval(pg_get_serial_sequence(:table, :column))";
                $stmt = $this->query($sql, [
                    ':table' => $tableName,
                    ':column' => $idColumn
                ]);
                $result = $stmt->fetch();
                return $result['currval'] ?? null;
            } catch (PDOException $e) {
                // Fallback: query langsung ID terakhir
                $sql = "SELECT {$idColumn} FROM {$tableName} ORDER BY {$idColumn} DESC LIMIT 1";
                $stmt = $this->query($sql);
                $result = $stmt->fetch();
                return $result[$idColumn] ?? null;
            }
        }
        
        return $this->lastInsertId();
    }
    
    public function beginTransaction() {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            error_log("Begin transaction failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function commit() {
        try {
            return $this->connection->commit();
        } catch (PDOException $e) {
            error_log("Commit failed: " . $e->getMessage());
            $this->rollBack();
            throw $e;
        }
    }
    
    public function rollBack() {
        try {
            if ($this->connection->inTransaction()) {
                return $this->connection->rollBack();
            }
        } catch (PDOException $e) {
            error_log("Rollback failed: " . $e->getMessage());
        }
    }
    
    /**
     * Helper untuk check koneksi
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get driver name
     */
    public function getDriver() {
        return $this->driver;
    }
}
?>