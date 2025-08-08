<?php
// File: /admin/manage_users.php
require_once __DIR__ . '/includes/check_auth.php';

// Hanya Admin yang bisa mengakses halaman ini
check_permission(['Admin']);

// Logika untuk menghapus pengguna
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'])) {
        $user_id_to_delete = (int)$_GET['id'];
        
        // Mencegah admin menghapus akunnya sendiri
        if ($user_id_to_delete === $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Anda tidak dapat menghapus akun Anda sendiri.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id_to_delete);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Pengguna berhasil dihapus.";
            } else {
                $_SESSION['error_message'] = "Gagal menghapus pengguna.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Token tidak valid.";
    }
    header('Location: manage_users.php');
    exit();
}

$page_title = "Kelola Pengguna";
include __DIR__ . '/includes/admin_header.php';

// Ambil semua pengguna dari database
$users_result = $conn->query("SELECT id, nama_lengkap, username, role FROM users ORDER BY nama_lengkap ASC");
?>
<div class="admin-container">
    <h1>Kelola Pengguna</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <a href="user_form.php" class="button button-primary">Tambah Pengguna Baru</a>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users_result->num_rows > 0): ?>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <a href="user_form.php?id=<?= $user['id'] ?>" class="button button-edit">Edit</a>
                            <?php if ($user['id'] !== $_SESSION['user_id']): // Tombol hapus tidak muncul untuk diri sendiri ?>
                                <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>&token=<?= $csrf_token ?>" 
                                   class="button button-delete" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua postingan oleh pengguna ini juga akan terhapus.')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Tidak ada pengguna.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
