<?php
// create_index.php
require_once __DIR__ . '/core/Database.php';

try {
    $db = new Database();
    echo "<h3>Connection Successful!</h3>";

    // Test simple query
    $sql = "SELECT version() AS version"; 
    $stmt = $db->query($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['version'])) {
        echo "PostgreSQL Version: " . $result['version'];
    } else {
        echo "Unable to fetch version info.";
    }

} catch (Exception $e) {
    echo "<h3>Connection Failed!</h3>";
    echo "Error: " . $e->getMessage();
}
