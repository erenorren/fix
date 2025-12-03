<?php
// api/index.php - VERCEL FIXED

// Force Vercel environment
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// ==================================================
// FIX SESSION FOR VERCELL
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    // For Vercel - use files for session storage
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());
    
    // Cookie settings for Vercel
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
    
    session_start();
}

// ==================================================
// DEBUG LOGGING
// ==================================================
error_log("=== VERCEL API INDEX ===");
error_log("Session ID: " . session_id());
error_log("Vercel Env: " . (getenv('VERCEL') ?: 'NO'));

// ==================================================
// INCLUDE MAIN APP
// ==================================================
require_once __DIR__ . '/../public/index.php';
?>