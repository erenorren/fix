<?php
// helper/auth.php

function start_session_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        // jika environment serverless, set save path ke /tmp (saat perlu)
        if (is_dir('/tmp') && ini_get('session.save_path') === '') {
            session_save_path('/tmp');
        }
        session_start();
    }
}

/**
 * auth_required
 * - menerima session (user_id) 
 */
function auth_required() {
    start_session_safe();

    // Primary: session
    if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}
}
