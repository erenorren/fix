<?php
// debug_session.php
session_start();

echo "<h2>Session Debug Info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Cookie Params:\n";
print_r(session_get_cookie_params());
echo "\nSession Data:\n";
print_r($_SESSION);
echo "\nEnvironment:\n";
echo "VERCEL: " . (getenv('VERCEL') ? 'YES' : 'NO') . "\n";
echo "Session Path: " . session_save_path() . "\n";
echo "</pre>";

// Test database connection
try {
    require_once 'config/database.php';
    $config = getDatabaseConfig();
    echo "<h3>Database Config:</h3>";
    echo "<pre>";
    print_r($config);
    echo "</pre>";
} catch(Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>