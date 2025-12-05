<?php
/**
 * TEST DUAL DATABASE SETUP
 * Local: MySQL | Production: PostgreSQL
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// TEST 1: Load Config
try {
    require_once __DIR__ . '/config/database.php';
    $config = getDatabaseConfig();
    
    $results['environment'] = getEnvironment();
    $results['tests']['config'] = [
        'status' => 'success',
        'driver' => $config['driver'],
        'host' => $config['host'],
        'port' => $config['port'],
        'dbname' => $config['dbname'],
        'username' => $config['username'],
    ];
    
    // Show different message based on driver
    if ($config['driver'] === 'mysql') {
        $results['tests']['config']['note'] = '✅ Using MySQL (Local Development)';
    } else {
        $results['tests']['config']['note'] = '✅ Using PostgreSQL (Production/Vercel)';
        $results['tests']['config']['sslmode'] = $config['sslmode'] ?? 'not set';
    }
    
} catch (Exception $e) {
    $results['tests']['config'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// TEST 2: Database Connection
try {
    require_once __DIR__ . '/core/Database.php';
    $db = new Database();
    
    // Get database info
    if ($db->isMySQL()) {
        $stmt = $db->query("SELECT VERSION() as version, DATABASE() as dbname");
    } else {
        $stmt = $db->query("SELECT version(), current_database() as dbname");
    }
    
    $info = $stmt->fetch();
    
    $results['tests']['connection'] = [
        'status' => 'success',
        'driver' => $db->getDriver(),
        'is_mysql' => $db->isMySQL(),
        'is_postgresql' => $db->isPostgreSQL(),
        'version' => $info['version'],
        'database' => $info['dbname'],
        'is_connected' => $db->isConnected(),
    ];
    
} catch (Exception $e) {
    $results['tests']['connection'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// TEST 3: Check Required Tables
try {
    $tables = ['transaksi', 'pelanggan', 'hewan', 'kandang'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        if ($db->isMySQL()) {
            $sql = "SHOW TABLES LIKE :table";
        } else {
            $sql = "SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = :table
            )";
        }
        
        $stmt = $db->query($sql, [':table' => $table]);
        $result = $stmt->fetch();
        
        if ($db->isMySQL()) {
            $tableStatus[$table] = !empty($result);
        } else {
            $tableStatus[$table] = ($result['exists'] === true || $result['exists'] === 't');
        }
    }
    
    $results['tests']['tables'] = [
        'status' => 'success',
        'tables' => $tableStatus,
        'all_exist' => !in_array(false, $tableStatus),
    ];
    
    if (!$results['tests']['tables']['all_exist']) {
        $missing = array_keys(array_filter($tableStatus, function($v) { return !$v; }));
        $results['tests']['tables']['warning'] = 'Missing tables: ' . implode(', ', $missing);
    }
    
} catch (Exception $e) {
    $results['tests']['tables'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

// TEST 4: Test CRUD Operations
try {
    // Test INSERT
    $testName = 'TEST_' . time();
    
    if ($db->isMySQL()) {
        $sql = "INSERT INTO pelanggan (nama, alamat, nomor_telepon) 
                VALUES (:nama, :alamat, :nomor_telepon)";
    } else {
        $sql = "INSERT INTO pelanggan (nama, alamat, nomor_telepon) 
                VALUES (:nama, :alamat, :nomor_telepon) 
                RETURNING id_pelanggan";
    }
    
    $params = [
        ':nama' => $testName,
        ':alamat' => 'Test Address',
        ':nomor_telepon' => '081234567890'
    ];
    
    $insertedId = $db->insertAndGetId($sql, $params, 'id_pelanggan');
    
    if ($insertedId) {
        // Test SELECT
        $selectSql = "SELECT * FROM pelanggan WHERE id_pelanggan = :id";
        $stmt = $db->query($selectSql, [':id' => $insertedId]);
        $insertedData = $stmt->fetch();
        
        // Test DELETE
        $deleteSql = "DELETE FROM pelanggan WHERE id_pelanggan = :id";
        $db->execute($deleteSql, [':id' => $insertedId]);
        
        $results['tests']['crud'] = [
            'status' => 'success',
            'message' => '✅ INSERT, SELECT, DELETE berhasil!',
            'test_id' => $insertedId,
            'inserted_data' => $insertedData,
        ];
    } else {
        $results['tests']['crud'] = [
            'status' => 'error',
            'message' => 'Insert returned no ID',
        ];
    }
    
} catch (Exception $e) {
    $results['tests']['crud'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
    ];
}

// TEST 5: Test Transaksi Model (jika tabel ada)
if ($results['tests']['tables']['all_exist']) {
    try {
        require_once __DIR__ . '/models/Transaksi.php';
        $transaksi = new Transaksi();
        
        // Coba get all
        $allTransaksi = $transaksi->getAll(['limit' => 5]);
        
        $results['tests']['transaksi_model'] = [
            'status' => 'success',
            'message' => '✅ Model Transaksi berfungsi!',
            'total_records' => count($allTransaksi),
            'sample_data' => array_slice($allTransaksi, 0, 2), // Ambil 2 data pertama
        ];
        
    } catch (Exception $e) {
        $results['tests']['transaksi_model'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
}

// Summary
$results['summary'] = [
    'environment' => $results['environment'],
    'database_type' => $config['driver'] === 'mysql' ? 'MySQL (Local)' : 'PostgreSQL (Vercel)',
    'all_tests_passed' => true,
];

foreach ($results['tests'] as $test) {
    if (isset($test['status']) && $test['status'] === 'error') {
        $results['summary']['all_tests_passed'] = false;
        break;
    }
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
