<?php
/**
 * TEST SCRIPT - Verifikasi Koneksi & Insert Transaksi
 * Upload ke root project, akses via browser
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/models/Transaksi.php';

header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'step1_config' => null,
    'step2_connection' => null,
    'step3_table_check' => null,
    'step4_enum_check' => null,
    'step5_test_insert' => null,
];

try {
    // STEP 1: Check Config
    require_once __DIR__ . '/config/database.php';
    $config = getDatabaseConfig();
    
    $results['step1_config'] = [
        'status' => 'success',
        'driver' => $config['driver'],
        'host' => $config['host'],
        'port' => $config['port'],
        'dbname' => $config['dbname'],
        'username' => $config['username'],
    ];
    
    // STEP 2: Test Connection
    try {
        $db = new Database();
        $results['step2_connection'] = [
            'status' => 'success',
            'message' => 'Connected successfully',
            'driver' => $db->getDriver(),
        ];
    } catch (Exception $e) {
        $results['step2_connection'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
        echo json_encode($results, JSON_PRETTY_PRINT);
        exit;
    }
    
    // STEP 3: Check Tables Exist
    try {
        $tables = ['transaksi', 'pelanggan', 'hewan', 'kandang', 'detail_transaksi'];
        $existingTables = [];
        
        foreach ($tables as $table) {
            $sql = "SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = :table
            )";
            $result = $db->query($sql, [':table' => $table])->fetch();
            $exists = $result['exists'] === true || $result['exists'] === 't';
            $existingTables[$table] = $exists;
        }
        
        $results['step3_table_check'] = [
            'status' => 'success',
            'tables' => $existingTables,
        ];
        
    } catch (Exception $e) {
        $results['step3_table_check'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
    
    // STEP 4: Check ENUM Types
    try {
        $sql = "SELECT enumlabel 
                FROM pg_enum 
                WHERE enumtypid = 'status_transaksi_type'::regtype 
                ORDER BY enumsortorder";
        
        $result = $db->query($sql)->fetchAll();
        $enumValues = array_column($result, 'enumlabel');
        
        $results['step4_enum_check'] = [
            'status' => 'success',
            'status_transaksi_values' => $enumValues,
            'has_active' => in_array('active', $enumValues),
        ];
        
    } catch (Exception $e) {
        $results['step4_enum_check'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }
    
    // STEP 5: Test Insert Transaksi
    try {
        $transaksi = new Transaksi();
        
        // Data test (sesuaikan dengan data yang ada di database Anda)
        $testData = [
            'id_pelanggan' => 1,  // Pastikan ID ini ada
            'id_hewan' => 1,      // Pastikan ID ini ada
            'id_kandang' => 1,    // Pastikan ID ini ada
            'id_layanan' => 1,    // Bisa null
            'biaya_paket' => 50000,
            'tanggal_masuk' => date('Y-m-d'),
            'durasi_hari' => 3,
            'total_biaya' => 150000,
            'status' => 'active'
        ];
        
        $insertedId = $transaksi->create($testData);
        
        if ($insertedId) {
            $results['step5_test_insert'] = [
                'status' => 'success',
                'message' => '✅ INSERT BERHASIL!',
                'inserted_id' => $insertedId,
                'data' => $testData,
            ];
            
            // Verify data
            $inserted = $transaksi->getById($insertedId);
            $results['step5_test_insert']['verify'] = $inserted ? 'Data verified' : 'Data not found';
            
        } else {
            $results['step5_test_insert'] = [
                'status' => 'error',
                'message' => 'Insert returned false',
                'data' => $testData,
            ];
        }
        
    } catch (Exception $e) {
        $results['step5_test_insert'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ];
    }
    
} catch (Exception $e) {
    $results['fatal_error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ];
}

// Output
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>