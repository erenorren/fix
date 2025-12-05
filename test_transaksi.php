<?php
/**
 * TEST SCRIPT - Debug Koneksi Database
 * Upload ke root project, akses via browser
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'step1_file_check' => null,
    'step2_config_load' => null,
    'step3_pdo_check' => null,
    'step4_raw_connection' => null,
    'step5_database_class' => null,
];

// STEP 1: Check File Exists
$files = [
    'config/database.php' => __DIR__ . '/config/database.php',
    'core/Database.php' => __DIR__ . '/core/Database.php',
    'models/Transaksi.php' => __DIR__ . '/models/Transaksi.php',
];

$results['step1_file_check'] = [
    'status' => 'checking',
    'files' => [],
];

foreach ($files as $name => $path) {
    $results['step1_file_check']['files'][$name] = [
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'path' => $path,
    ];
}

$allFilesExist = true;
foreach ($results['step1_file_check']['files'] as $file) {
    if (!$file['exists'] || !$file['readable']) {
        $allFilesExist = false;
        break;
    }
}

$results['step1_file_check']['status'] = $allFilesExist ? 'success' : 'error';

if (!$allFilesExist) {
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// STEP 2: Load Config
try {
    require_once __DIR__ . '/config/database.php';
    
    if (!function_exists('getDatabaseConfig')) {
        throw new Exception('Function getDatabaseConfig() not found in config/database.php');
    }
    
    $config = getDatabaseConfig();
    
    $results['step2_config_load'] = [
        'status' => 'success',
        'config' => [
            'driver' => $config['driver'] ?? 'NOT SET',
            'host' => $config['host'] ?? 'NOT SET',
            'port' => $config['port'] ?? 'NOT SET',
            'dbname' => $config['dbname'] ?? 'NOT SET',
            'username' => $config['username'] ?? 'NOT SET',
            'password' => isset($config['password']) ? '***SET***' : 'NOT SET',
            'charset' => $config['charset'] ?? 'NOT SET',
        ],
    ];
    
    // Validate required fields
    $required = ['driver', 'host', 'port', 'dbname', 'username', 'password'];
    $missing = [];
    foreach ($required as $field) {
        if (empty($config[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        $results['step2_config_load']['status'] = 'error';
        $results['step2_config_load']['missing_fields'] = $missing;
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }
    
} catch (Exception $e) {
    $results['step2_config_load'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ];
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// STEP 3: Check PDO Extension
$results['step3_pdo_check'] = [
    'pdo_available' => extension_loaded('pdo'),
    'pdo_pgsql_available' => extension_loaded('pdo_pgsql'),
    'loaded_extensions' => get_loaded_extensions(),
];

if (!extension_loaded('pdo') || !extension_loaded('pdo_pgsql')) {
    $results['step3_pdo_check']['status'] = 'error';
    $results['step3_pdo_check']['message'] = 'PDO or PDO_PGSQL extension not loaded';
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

$results['step3_pdo_check']['status'] = 'success';

// STEP 4: Raw PDO Connection Test
try {
    $dsn = sprintf(
        "%s:host=%s;port=%d;dbname=%s",
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['dbname']
    );
    
    $results['step4_raw_connection'] = [
        'dsn' => str_replace($config['password'], '***', $dsn),
        'attempting' => true,
    ];
    
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetch();
    
    $results['step4_raw_connection']['status'] = 'success';
    $results['step4_raw_connection']['postgresql_version'] = $version['version'];
    $results['step4_raw_connection']['connection_status'] = 'Connected successfully';
    
    $pdo = null; // Close connection
    
} catch (PDOException $e) {
    $results['step4_raw_connection']['status'] = 'error';
    $results['step4_raw_connection']['error_code'] = $e->getCode();
    $results['step4_raw_connection']['error_message'] = $e->getMessage();
    $results['step4_raw_connection']['suggestion'] = 'Check: host, port, dbname, username, password, firewall, PostgreSQL service';
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// STEP 5: Test Database Class
try {
    require_once __DIR__ . '/core/Database.php';
    
    $db = new Database();
    
    // Test query
    $stmt = $db->query("SELECT current_database(), current_user");
    $info = $stmt->fetch();
    
    $results['step5_database_class'] = [
        'status' => 'success',
        'message' => 'Database class working',
        'current_database' => $info['current_database'],
        'current_user' => $info['current_user'],
        'driver' => $db->getDriver(),
    ];
    
    // Check if tables exist
    $tables = ['transaksi', 'pelanggan', 'hewan', 'kandang'];
    $tableCheck = [];
    
    foreach ($tables as $table) {
        $stmt = $db->query(
            "SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = :table
            )",
            [':table' => $table]
        );
        $result = $stmt->fetch();
        $tableCheck[$table] = ($result['exists'] === true || $result['exists'] === 't');
    }
    
    $results['step5_database_class']['tables'] = $tableCheck;
    
} catch (Exception $e) {
    $results['step5_database_class'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ];
}

// Output
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>