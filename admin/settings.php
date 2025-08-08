<?php
// File: /admin/settings.php
require_once __DIR__ . '/includes/check_auth.php';
check_permission(['Admin']);

$page_title = "Pengaturan Website";
$errors = [];

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $settings_to_update = [
            'site_title' => sanitize_input($_POST['site_title']),
            'site_description' => sanitize_input($_POST['site_description']),
            'contact_email' => sanitize_input($_POST['contact_email']),
            'social_facebook' => sanitize_input($_POST['social_facebook']),
            'social_twitter' => sanitize_input($_POST['social_twitter']),
            'social_instagram' => sanitize_input($_POST['social_instagram']),
        ];

        $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE nama_setting = ?");
        foreach ($settings_to_update as $name => $value) {
            $stmt->bind_param("ss", $value, $name);
            $stmt->execute();
        }
        $stmt->close();
        
        $_SESSION['success_message'] = "Pengaturan berhasil diperbarui.";
        header('Location: settings.php');
        exit();

    } else {
        $errors[] = "Token tidak valid.";
    }
}

// Ambil semua pengaturan dari DB untuk ditampilkan di form
$all_settings = [];
$settings_result = $conn->query("SELECT nama_setting, value FROM settings");
while ($row = $settings_result->fetch_assoc()) {
    $all_settings[$row['nama_setting']] = $row['value'];
}

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-container">
    <h1>Pengaturan Website</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form action="" method="POST" class="form-container" style="max-width: 800px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <h2>Umum</h2>
        <div class="form-group">
            <label for="site_title">Judul Website</label>
            <input type="text" id="site_title" name="site_title" value="<?= htmlspecialchars($all_settings['site_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="site_description">Deskripsi Website</label>
            <textarea id="site_description" name="site_description" rows="3"><?= htmlspecialchars($all_settings['site_description'] ?? '') ?></textarea>
        </div>

        <h2>Kontak & Media Sosial</h2>
        <div class="form-group">
            <label for="contact_email">Email Kontak</label>
            <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($all_settings['contact_email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="social_facebook">URL Facebook</label>
            <input type="url" id="social_facebook" name="social_facebook" value="<?= htmlspecialchars($all_settings['social_facebook'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="social_twitter">URL Twitter</label>
            <input type="url" id="social_twitter" name="social_twitter" value="<?= htmlspecialchars($all_settings['social_twitter'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="social_instagram">URL Instagram</label>
            <input type="url" id="social_instagram" name="social_instagram" value="<?= htmlspecialchars($all_settings['social_instagram'] ?? '') ?>">
        </div>
        
        <button type="submit" class="button button-primary">Simpan Pengaturan</button>
    </form>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
