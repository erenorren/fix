<?php
// helper/auth.php

function start_session_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        // Komentari baris path /tmp ini untuk pengujian
        // if (is_dir('/tmp') && ini_get('session.save_path') === '') {
        //    session_save_path('/tmp');
        // }
        session_start();
    }
}

/**
 * auth_required
 * - menerima session (user_id) atau cookie (user_id) sebagai fallback
 */
function auth_required() {
    start_session_safe();

    // Primary: session
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }

    // Fallback: cookie
    if (isset($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
        // optionally you can rehydrate session from cookie
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        return true;
    }

    // Not authenticated
    // Use relative redirect to avoid domain mismatch
    header("Location: index.php?page=login");
    exit;
}
