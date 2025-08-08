<?php
// File: /admin/manage_posts.php
require_once __DIR__ . '/includes/check_auth.php';

// Logika untuk menghapus postingan
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (verify_csrf_token($_GET['token'])) {
        $post_id_to_delete = (int)$_GET['id'];
        
        // Cek kepemilikan jika bukan admin/redaktur
        if (!in_array($_SESSION['user_role'], ['Admin', 'Redaktur'])) {
            $stmt_check = $conn->prepare("SELECT penulis_id FROM posts WHERE id = ?");
            $stmt_check->bind_param("i", $post_id_to_delete);
            $stmt_check->execute();
            $post_owner = $stmt_check->get_result()->fetch_assoc();
            if ($post_owner['penulis_id'] !== $_SESSION['user_id']) {
                 $_SESSION['error_message'] = "Anda tidak memiliki izin untuk menghapus postingan ini.";
                 header('Location: manage_posts.php');
                 exit();
            }
        }

        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id_to_delete);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Postingan berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus postingan.";
        }
        $stmt->close();

    } else {
        $_SESSION['error_message'] = "Token tidak valid.";
    }
    header('Location: manage_posts.php');
    exit();
}

$page_title = "Kelola Postingan";
include __DIR__ . '/includes/admin_header.php';

// Logika untuk menampilkan postingan berdasarkan role
$sql = "SELECT p.id, p.judul, p.status, u.nama_lengkap as penulis, p.created_at 
        FROM posts p JOIN users u ON p.penulis_id = u.id";

if ($_SESSION['user_role'] === 'Penulis' || $_SESSION['user_role'] === 'Editor') {
    $sql .= " WHERE p.penulis_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$posts_result = $stmt->get_result();
?>
<div class="admin-container">
    <h1>Kelola Postingan</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <a href="post_form.php" class="button button-primary">Tulis Postingan Baru</a>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($posts_result->num_rows > 0): ?>
                <?php while ($post = $posts_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['judul']) ?></td>
                        <td><?= htmlspecialchars($post['penulis']) ?></td>
                        <td><span class="status-badge status-<?= htmlspecialchars($post['status']) ?>"><?= htmlspecialchars($post['status']) ?></span></td>
                        <td><?= date('d M Y', strtotime($post['created_at'])) ?></td>
                        <td>
                            <a href="post_form.php?id=<?= $post['id'] ?>" class="button button-edit">Edit</a>
                            <a href="manage_posts.php?action=delete&id=<?= $post['id'] ?>&token=<?= $csrf_token ?>" 
                               class="button button-delete" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus postingan ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Belum ada postingan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
