<?php
/**
 * DEBUG MySQL Local Connection
 * Upload ke root project
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'tests' => []
];

// TEST 1: Check PDO Extensions
$results['tests']['extensions'] = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'pdo_pgsql' => extension_loaded('pdo_pgsql'),
];

if (!$results['tests']['extensions']['pdo']) {
    $results['tests']['extensions']['error'] = 'PDO extension not loaded!';
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

if (!$results['tests']['extensions']['pdo_mysql']) {
    $results['tests']['extensions']['error'] = 'PDO_MYSQL extension not loaded! Enable it in php.ini';
    $results['tests']['extensions']['solution'] = 'Edit php.ini: extension=pdo_mysql';
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

$results['tests']['extensions']['status'] = 'success';

// TEST 2: Load Config
try {
    require_once __DIR__ . '/config/database.php';
    
    // Force local environment
    putenv('VERCEL=');
    unset($_ENV['VERCEL']);
    unset($_SERVER['VERCEL']);
    
    $config = getDatabaseConfig();
    
    $results['tests']['config'] = [
        'status' => 'success',
        'driver' => $config['driver'],
        'host' => $config['host'],
        'port' => $config['port'],
        'dbname' => $config['dbname'],
        'username' => $config['username'],
        'password_length' => strlen($config['password'] ?? ''),
        'password_set' => !empty($config['password']),
    ];
    
    if ($config['driver'] !== 'mysql') {
        $results['tests']['config']['error'] = 'Expected MySQL driver for local, got: ' . $config['driver'];
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }
    
} catch (Exception $e) {
    $results['tests']['config'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ];
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// TEST 3: Check if MySQL/MariaDB is Running
try {
    $results['tests']['mysql_service'] = [
        'status' => 'checking',
    ];
    
    // Try to connect to MySQL port
    $socket = @fsockopen($config['host'], $config['port'], $errno, $errstr, 5);
    
    if ($socket) {
        fclose($socket);
        $results['tests']['mysql_service']['status'] = 'success';
        $results['tests']['mysql_service']['message'] = 'MySQL port is open';
    } else {
        $results['tests']['mysql_service']['status'] = 'error';
        $results['tests']['mysql_service']['error_code'] = $errno;
        $results['tests']['mysql_service']['error_message'] = $errstr;
        $results['tests']['mysql_service']['solution'] = 'Start MySQL/MariaDB service in Laragon';
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }
    
} catch (Exception $e) {
    $results['tests']['mysql_service'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

// TEST 4: Raw PDO Connection Test
try {
    $dsn = sprintf(
        "mysql:host=%s;port=%d;charset=utf8mb4",
        $config['host'],
        $config['port']
    );
    
    $results['tests']['raw_connection'] = [
        'status' => 'attempting',
        'dsn' => $dsn,
        'username' => $config['username'],
    ];
    
    // Try without database first
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    $results['tests']['raw_connection']['status'] = 'success';
    $results['tests']['raw_connection']['message'] = 'Connected to MySQL server';
    
    // Get MySQL version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    $results['tests']['raw_connection']['mysql_version'] = $version['version'];
    
} catch (PDOException $e) {
    $results['tests']['raw_connection']['status'] = 'error';
    $results['tests']['raw_connection']['error_code'] = $e->getCode();
    $results['tests']['raw_connection']['error_message'] = $e->getMessage();
    
    // Specific error hints
    if ($e->getCode() == 1045) {
        $results['tests']['raw_connection']['hint'] = 'Wrong username or password';
        $results['tests']['raw_connection']['solution'] = [
            '1. Check password in config/database.php',
            '2. Try empty password for root',
            '3. Reset MySQL root password in Laragon'
        ];
    } elseif ($e->getCode() == 2002) {
        $results['tests']['raw_connection']['hint'] = 'MySQL service not running';
        $results['tests']['raw_connection']['solution'] = 'Start MySQL in Laragon: Menu → MySQL → Start';
    }
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// TEST 5: Check if Database Exists
try {
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['dbname']}'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        $results['tests']['database'] = [
            'status' => 'success',
            'message' => "Database '{$config['dbname']}' exists",
        ];
        
        // Connect to specific database
        $pdo->exec("USE {$config['dbname']}");
        
        // Get tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $results['tests']['database']['tables'] = $tables;
        $results['tests']['database']['table_count'] = count($tables);
        
    } else {
        $results['tests']['database'] = [
            'status' => 'error',
            'message' => "Database '{$config['dbname']}' NOT FOUND!",
            'solution' => "Create database in Laragon → HeidiSQL or MySQL console"
        ];
        
        // Show available databases
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $results['tests']['database']['available_databases'] = $databases;
        
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }
    
} catch (PDOException $e) {
    $results['tests']['database'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

// TEST 6: Test Database Class
try {
    require_once __DIR__ . '/core/Database.php';
    $db = new Database();
    
    $results['tests']['database_class'] = [
        'status' => 'success',
        'message' => 'Database class initialized',
        'driver' => $db->getDriver(),
        'is_connected' => $db->isConnected(),
    ];
    
    // Test query
    $stmt = $db->query("SELECT DATABASE() as dbname, USER() as user");
    $info = $stmt->fetch();
    
    $results['tests']['database_class']['current_database'] = $info['dbname'];
    $results['tests']['database_class']['current_user'] = $info['user'];
    
} catch (Exception $e) {
    $results['tests']['database_class'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ];
}

// Summary
$results['summary'] = [
    'all_tests_passed' => true,
    'recommendations' => []
];

foreach ($results['tests'] as $testName => $test) {
    if (isset($test['status']) && $test['status'] === 'error') {
        $results['summary']['all_tests_passed'] = false;
        $results['summary']['failed_test'] = $testName;
        break;
    }
}

if ($results['summary']['all_tests_passed']) {
    $results['summary']['message'] = '✅ All tests passed! MySQL connection is working!';
} else {
    $results['summary']['message'] = '❌ Some tests failed. Check details above.';
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);