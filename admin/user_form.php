<?php
// File: /admin/user_form.php
require_once __DIR__ . '/includes/check_auth.php';
check_permission(['Admin']); // Hanya Admin yang bisa tambah/edit pengguna

$is_edit = false;
$user = [];
$page_title = "Tambah Pengguna Baru";

// Mode Edit: jika ada ID di URL
if (isset($_GET['id'])) {
    $is_edit = true;
    $user_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT id, nama_lengkap, username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) {
        $_SESSION['error_message'] = "Pengguna tidak ditemukan.";
        header('Location: manage_users.php');
        exit();
    }
    $page_title = "Edit Pengguna: " . htmlspecialchars($user['nama_lengkap']);
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
        $username = sanitize_input($_POST['username']);
        $role = in_array($_POST['role'], ['Admin', 'Redaktur', 'Editor', 'Penulis']) ? $_POST['role'] : 'Penulis';
        $password = $_POST['password'];
        $errors = [];

        if (empty($nama_lengkap)) $errors[] = "Nama lengkap wajib diisi.";
        if (empty($username)) $errors[] = "Username wajib diisi.";
        
        // Validasi username unik
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $current_id = $is_edit ? $user_id : 0;
        $stmt->bind_param("si", $username, $current_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Username sudah digunakan.";
        }
        $stmt->close();
        
        // Validasi password (hanya jika diisi)
        if (!$is_edit && empty($password)) {
            $errors[] = "Password wajib diisi untuk pengguna baru.";
        }
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter.";
        }

        if (empty($errors)) {
            if ($is_edit) {
                // UPDATE
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, role=?, password_hash=? WHERE id=?");
                    $stmt->bind_param("ssssi", $nama_lengkap, $username, $role, $password_hash, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, role=? WHERE id=?");
                    $stmt->bind_param("sssi", $nama_lengkap, $username, $role, $user_id);
                }
            } else {
                // INSERT
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, role, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama_lengkap, $username, $role, $password_hash);
            }

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data pengguna berhasil disimpan.";
                header('Location: manage_users.php');
                exit();
            } else {
                $errors[] = "Gagal menyimpan data ke database.";
            }
            $stmt->close();
        }
    } else {
        $errors[] = "Token tidak valid.";
    }
}

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-container">
    <h1><?= $page_title ?></h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form action="" method="POST" class="form-container" style="max-width: 600px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="form-group">
            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <?php if ($is_edit): ?>
                <small>Kosongkan jika tidak ingin mengubah password.</small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="Penulis" <?= (isset($user['role']) && $user['role'] === 'Penulis') ? 'selected' : '' ?>>Penulis</option>
                <option value="Editor" <?= (isset($user['role']) && $user['role'] === 'Editor') ? 'selected' : '' ?>>Editor</option>
                <option value="Redaktur" <?= (isset($user['role']) && $user['role'] === 'Redaktur') ? 'selected' : '' ?>>Redaktur</option>
                <option value="Admin" <?= (isset($user['role']) && $user['role'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="button button-primary"><?= $is_edit ? 'Update Pengguna' : 'Tambah Pengguna' ?></button>
    </form>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
