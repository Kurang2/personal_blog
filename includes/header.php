<?php
// File: /admin/index.php

// PERBAIKAN: Path ini sekarang akan benar setelah Anda memindahkan file check_auth.php
require_once __DIR__ . '/includes/check_auth.php';

$page_title = "Dashboard";
include __DIR__ . '/includes/admin_header.php';

$total_posts = $conn->query("SELECT COUNT(id) FROM posts")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(id) FROM users")->fetch_row()[0];
$pending_posts = $conn->query("SELECT COUNT(id) FROM posts WHERE status = 'pending'")->fetch_row()[0];
?>

<div class="admin-container">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['user_nama_lengkap']); ?>!</h1>
    <p>Anda login sebagai: <strong><?= htmlspecialchars($_SESSION['user_role']); ?></strong></p>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h2>Total Postingan</h2>
            <p><?= $total_posts ?></p>
        </div>
        <div class="stat-card">
            <h2>Menunggu Persetujuan</h2>
            <p><?= $pending_posts ?></p>
        </div>
        <div class="stat-card">
            <h2>Total Pengguna</h2>
            <p><?= $total_users ?></p>
        </div>
    </div>

    <h2>Akses Cepat</h2>
    <div class="quick-links">
        <a href="post_form.php" class="button">Tulis Artikel Baru</a>
        <a href="manage_posts.php" class="button">Kelola Semua Postingan</a>
    </div>

</div>

<?php
include __DIR__ . '/includes/admin_footer.php';
?>
