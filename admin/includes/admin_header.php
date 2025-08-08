<?php
// File: /admin/includes/admin_header.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel' ?> - <?= htmlspecialchars(get_setting('site_title')) ?></title>
    <link rel="stylesheet" href="/personal_blog/assets/css/style.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="container">
            <div class="admin-header-logo">
                <a href="/personal_blog/admin/index.php">Admin Panel</a>
            </div>
            <div class="admin-header-user">
                <span>Selamat datang, <strong><?= htmlspecialchars($_SESSION['user_nama_lengkap']) ?></strong></span>
                <a href="/personal_blog/logout.php" class="button button-logout">Keluar</a>
            </div>
        </div>
    </header>
    <div class="admin-wrapper">
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_posts.php">Kelola Postingan</a></li>
                <li><a href="post_form.php">Tulis Baru</a></li>
                
                <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                    <li class="nav-separator"></li>
                    <li><a href="manage_users.php">Kelola Pengguna</a></li>
                    <li><a href="manage_categories.php">Kelola Kategori</a></li>
                    <li><a href="settings.php">Pengaturan Website</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <main class="admin-main-content">