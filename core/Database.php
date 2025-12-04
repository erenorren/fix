<?php

require_once __DIR__ . '/../helper/helper.php';

class Database {
    private $conn;

    public function __construct() {
        $config = getDatabaseConfig();

        $driver = $config['driver'];
        $host   = $config['host'];
        $port   = $config['port'];
        $dbname = $config['dbname'];
        $user   = $config['username'];
        $pass   = $config['password'];

        if ($driver === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        }

        $this->conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function execute($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollBack() {
        return $this->conn->rollBack();
    }
}
