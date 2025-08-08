<?php
// File: /login.php

// Baris ini adalah kunci perbaikannya.
// __DIR__ akan mengambil path absolut ke direktori tempat file ini berada (yaitu /personal_blog).
// Kemudian, ia akan mencari folder /includes/ di dalamnya.
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, langsung arahkan ke dashboard admin
if (is_logged_in()) {
    header('Location: /personal_blog/index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inisialisasi session untuk proteksi brute-force jika belum ada
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    if (!isset($_SESSION['last_login_attempt'])) {
        $_SESSION['last_login_attempt'] = 0;
    }

    // Cek jika user diblok sementara
    if (time() - $_SESSION['last_login_attempt'] < 300 && $_SESSION['login_attempts'] >= 5) {
        $error = "Terlalu banyak percobaan login. Silakan coba lagi setelah 5 menit.";
    } else {
        // Reset percobaan jika sudah lewat dari 5 menit
        if (time() - $_SESSION['last_login_attempt'] > 300) {
            $_SESSION['login_attempts'] = 0;
        }

        sleep(2); // Tunda eksekusi untuk memperlambat serangan brute-force
        $_SESSION['login_attempts']++;
        $_SESSION['last_login_attempt'] = time();

        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = 'Username dan password tidak boleh kosong.';
        } else {
            // Gunakan prepared statement untuk keamanan
            $stmt = $conn->prepare("SELECT id, nama_lengkap, username, password_hash, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Verifikasi password
                if (password_verify($password, $user['password_hash'])) {
                    // Jika berhasil, regenerasi session ID dan simpan data user
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_username'] = $user['username'];
                    $_SESSION['user_nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Reset proteksi brute-force
                    unset($_SESSION['login_attempts'], $_SESSION['last_login_attempt']);
                    
                    // Arahkan ke dashboard
                    header('Location: /personal_blog/admin/index.php');
                    exit();
                }
            }
            // Jika username tidak ditemukan atau password salah
            $error = 'Username atau password salah.';
        }
    }
}

$page_title = "Login";
// Sertakan header
include __DIR__ . '/includes/header.php';
?>
<main class="container">
    <div class="form-container">
        <h2>Login ke Akun Anda</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="/personal_blog/login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="form-button">Login</button>
        </form>
        <p class="form-switch">Belum punya akun? <a href="/personal_blog/register.php">Daftar di sini</a></p>
    </div>
</main>
<?php
// Sertakan footer
include __DIR__ . '/includes/footer.php';
?>
