<?php
// create_index.php
require_once __DIR__ . '/core/Database.php';

// Coba koneksi ke database
try {
    $db = new Database();

    // Tes query sederhana
    $sql = "SELECT version()"; // Cek versi PostgreSQL
    $stmt = $db->query($sql);
    $stmt->execute();
    $result = $stmt->fetch();

    echo "<h3>Connection Successful!</h3>";
    echo "PostgreSQL Version: " . $result[0];

} catch (Exception $e) {
    echo "<h3>Connection Failed!</h3>";
    echo "Error: " . $e->getMessage();
}
