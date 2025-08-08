<?php
// Pastikan path ini benar dari lokasi file admin
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
    header('Location: /nama_project/login.php?error=not_logged_in');
    exit();
}

function check_permission($allowed_roles = []) {
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
        header('Location: /nama_project/admin/index.php');
        exit();
    }
}
?>