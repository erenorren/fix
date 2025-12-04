<?php

function auth_required() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Jika user belum login → paksa ke halaman login
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}
