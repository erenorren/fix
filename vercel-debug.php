<?php
// vercel-debug.php
echo "<h1>Vercel Debug Page</h1>";

echo "<h2>1. Environment Variables:</h2>";
echo "<pre>";
echo "VERCEL: " . (getenv('VERCEL') ?: 'NOT SET') . "\n";
echo "DATABASE_URL: " . (getenv('DATABASE_URL') ?: 'NOT SET') . "\n";
print_r($_ENV);
echo "</pre>";

echo "<h2>2. PHP Info:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Status: " . session_status() . "<br>";

echo "<h2>3. Database Test:</h2>";
try {
    require_once 'config/database.php';
    $config = getDatabaseConfig();
    
    echo "<pre>";
    print_r($config);
    echo "</pre>";
    
    if ($config['driver'] === 'pgsql') {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};sslmode={$config['sslmode']}";
    } else {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
    }
    
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    echo "<p style='color:green'>✓ Database Connected</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pelanggan");
    $result = $stmt->fetch();
    echo "Total Pelanggan: " . ($result['total'] ?? 0) . "<br>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database Error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Session Test:</h2>";
session_start();
$_SESSION['test'] = 'vercel_test';
echo "Session ID: " . session_id() . "<br>";
echo "Session Data: ";
print_r($_SESSION);
?>