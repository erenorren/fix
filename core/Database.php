<?php
/**
 * Database Class
 * Support: MySQL (Local) & PostgreSQL (Production)
 */

class Database {
    private $connection;
    private $driver;
    private $config;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->config = getDatabaseConfig();
        $this->driver = $this->config['driver'];
        
        try {
            $dsn = $this->buildDSN($this->config);
            $options = $this->config['options'] ?? $this->getDefaultOptions();
            
            $this->connection = new PDO(
                $dsn, 
                $this->config['username'], 
                $this->config['password'], 
                $options
            );
            
            // Set timezone
            if ($this->driver === 'pgsql') {
                $this->connection->exec("SET TIME ZONE 'Asia/Jakarta'");
            } elseif ($this->driver === 'mysql') {
                $this->connection->exec("SET time_zone = '+07:00'");
            }
            
        } catch (PDOException $e) {
            $this->logError("Connection failed", $e);
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    /**
     * Build DSN berdasarkan driver
     */
    private function buildDSN($config) {
        $driver = $config['driver'];
        
        if ($driver === 'mysql') {
            $charset = $config['charset'] ?? 'utf8mb4';
            return sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['dbname'],
                $charset
            );
        }
        
        if ($driver === 'pgsql') {
            $dsn = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s",
                $config['host'],
                $config['port'],
                $config['dbname']
            );
            
            // SSL Mode untuk Supabase
            if (isset($config['sslmode'])) {
                $dsn .= ";sslmode=" . $config['sslmode'];
            }
            
            return $dsn;
        }
        
        throw new Exception("Unsupported database driver: {$driver}");
    }
    
    /**
     * Get default PDO options
     */
    private function getDefaultOptions() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // PostgreSQL specific
        if ($this->driver === 'pgsql') {
            $options[PDO::ATTR_PERSISTENT] = false;
            $options[PDO::ATTR_TIMEOUT] = 30;
        }
        
        return $options;
    }
    
    /**
     * Execute SELECT query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Query failed", $e, $sql, $params);
            throw $e;
        }
    }
    
    /**
     * Execute INSERT/UPDATE/DELETE
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
            $this->logError("Execute failed", $e, $sql, $params);
            throw new Exception("Database execute error: " . $e->getMessage());
        }
    }
    
    /**
     * Get last insert ID (cross-database)
     * MySQL: Auto-increment ID
     * PostgreSQL: Sequence atau RETURNING clause
     */
    public function lastInsertId($sequenceName = null) {
        try {
            if ($this->driver === 'pgsql' && $sequenceName !== null) {
                return $this->connection->lastInsertId($sequenceName);
            }
            
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->logError("lastInsertId failed", $e);
            return null;
        }
    }
    
    /**
     * Helper untuk INSERT dengan RETURNING (PostgreSQL) atau fallback (MySQL)
     * 
     * Usage:
     * $id = $db->insertAndGetId($sql, $params, 'id_transaksi');
     */
    public function insertAndGetId($sql, $params, $idColumn = 'id') {
        try {
            if ($this->driver === 'pgsql') {
                // PostgreSQL: Gunakan RETURNING
                if (stripos($sql, 'RETURNING') === false) {
                    $sql = rtrim($sql, ';') . " RETURNING {$idColumn}";
                }
                
                $stmt = $this->query($sql, $params);
                $result = $stmt->fetch();
                return $result[$idColumn] ?? null;
                
            } else {
                // MySQL: Execute kemudian get last insert ID
                $this->execute($sql, $params);
                return $this->lastInsertId();
            }
            
        } catch (Exception $e) {
            $this->logError("insertAndGetId failed", $e, $sql, $params);
            throw $e;
        }
    }
    
    /**
     * Transaction methods
     */
    public function beginTransaction() {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            $this->logError("Begin transaction failed", $e);
            throw $e;
        }
    }
    
    public function commit() {
        try {
            return $this->connection->commit();
        } catch (PDOException $e) {
            $this->logError("Commit failed", $e);
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
            $this->logError("Rollback failed", $e);
        }
    }
    
    /**
     * Check connection status
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
    
    /**
     * Check if using PostgreSQL
     */
    public function isPostgreSQL() {
        return $this->driver === 'pgsql';
    }
    
    /**
     * Check if using MySQL
     */
    public function isMySQL() {
        return $this->driver === 'mysql';
    }
    
    /**
     * Get raw PDO connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Error logging helper
     */
    private function logError($message, $exception, $sql = null, $params = null) {
        $logMessage = sprintf(
            "[%s] [%s] %s: %s",
            date('Y-m-d H:i:s'),
            $this->driver,
            $message,
            $exception->getMessage()
        );
        
        if ($sql) {
            $logMessage .= " | SQL: " . $sql;
        }
        
        if ($params) {
            $logMessage .= " | Params: " . json_encode($params);
        }
        
        error_log($logMessage);
    }
}
