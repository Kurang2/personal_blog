<?php
// File: /admin/index.php (Dashboard untuk Pengguna yang sudah login)

// Memeriksa apakah pengguna sudah login dan memiliki izin
require_once __DIR__ . '/includes/check_auth.php';

// Mengatur judul halaman dan memuat header admin
$page_title = "Dashboard";
include __DIR__ . '/includes/admin_header.php';

// --- Mengambil Statistik Sederhana ---
$total_posts = $conn->query("SELECT COUNT(id) FROM posts")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(id) FROM users")->fetch_row()[0];
$pending_posts = $conn->query("SELECT COUNT(id) FROM posts WHERE status = 'pending'")->fetch_row()[0];
?>

<div class="admin-container">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['user_nama_lengkap']); ?>!</h1>
    <p>Anda login sebagai: <strong><?= htmlspecialchars($_SESSION['user_role']); ?></strong></p>

    <!-- Menampilkan pesan sukses atau error dari session -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Kartu Statistik -->
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

    <!-- Link Cepat -->
    <h2>Akses Cepat</h2>
    <div class="quick-links">
        <a href="post_form.php" class="button">Tulis Artikel Baru</a>
        <a href="manage_posts.php" class="button">Kelola Semua Postingan</a>
    </div>

</div>

<?php // Memuat footer admin
include __DIR__ . '/includes/admin_footer.php'; ?>
