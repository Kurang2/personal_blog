<?php
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: admin/index.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validasi
    if (empty($nama_lengkap)) $errors[] = "Nama Lengkap wajib diisi.";
    if (empty($username)) $errors[] = "Username wajib diisi.";
    if (strlen($username) < 4) $errors[] = "Username minimal 4 karakter.";
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = "Username hanya boleh berisi huruf, angka, dan underscore.";
    if (empty($password)) $errors[] = "Password wajib diisi.";
    if (strlen($password) < 6) $errors[] = "Password minimal 6 karakter.";
    if ($password !== $password_confirm) $errors[] = "Konfirmasi password tidak cocok.";

    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Username sudah digunakan. Silakan pilih yang lain.";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        // Default role untuk registrasi publik adalah 'Penulis'
        $role = 'Penulis'; 
        
        $stmt_insert = $conn->prepare("INSERT INTO users (nama_lengkap, username, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $nama_lengkap, $username, $password_hash, $role);
        
        if ($stmt_insert->execute()) {
            $success = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
        } else {
            $errors[] = "Terjadi kesalahan. Gagal mendaftar.";
        }
    }
}

$page_title = "Registrasi Penulis";
include 'includes/header.php';
?>
<main class="container">
    <div class="form-container">
        <h2>Daftar Sebagai Penulis</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="form-button">Daftar</button>
        </form>
        <?php endif; ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>