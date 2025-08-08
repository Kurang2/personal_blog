<?php
// File: /admin/includes/check_auth.php

// Path ini mencari dua tingkat ke atas (dari admin/includes/ ke personal_blog/)
// lalu masuk ke folder includes utama.
require_once __DIR__ . '/../../includes/functions.php';

// Jika pengguna belum login, tendang ke halaman login
if (!is_logged_in()) {
    header('Location: /personal_blog/login.php?error=not_logged_in');
    exit();
}

// Fungsi untuk memeriksa role pengguna
function check_permission($allowed_roles = []) {
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
        header('Location: /personal_blog/admin/index.php');
        exit();
    }
}