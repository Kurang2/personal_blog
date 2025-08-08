<?php
require_once 'includes/check_auth.php';

// Logika untuk menentukan mode 'tambah' atau 'edit'
$is_edit = false;
$post = [];
if (isset($_GET['id'])) {
    check_permission(['Admin', 'Redaktur', 'Editor', 'Penulis']);
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $is_edit = true;
        $post = $result->fetch_assoc();
        
        // Proteksi: Penulis hanya bisa edit post miliknya sendiri
        if ($_SESSION['user_role'] === 'Penulis' && $post['penulis_id'] !== $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Anda hanya bisa mengedit artikel Anda sendiri.";
            header('Location: manage_posts.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Postingan tidak ditemukan.";
        header('Location: manage_posts.php');
        exit();
    }
} else {
    check_permission(['Admin', 'Redaktur', 'Editor', 'Penulis']);
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "CSRF token tidak valid. Silakan coba lagi.";
    } else {
        // Sanitasi & Validasi Input
        $judul = sanitize_input($_POST['judul']);
        $slug = isset($_POST['slug']) && !empty($_POST['slug']) ? create_slug($_POST['slug']) : create_slug($judul);
        $meta_description = sanitize_input($_POST['meta_description']);
        $kata_kunci = sanitize_input($_POST['kata_kunci']);
        $kategori_id = (int)$_POST['kategori_id'];
        $konten = $_POST['konten']; 
        
        $penulis_id = ($is_edit) ? $post['penulis_id'] : $_SESSION['user_id'];
        if (in_array($_SESSION['user_role'], ['Admin', 'Redaktur'])) {
            $penulis_id = (int)$_POST['penulis_id'];
        }
        $editor_id = (in_array($_SESSION['user_role'], ['Admin', 'Redaktur', 'Editor'])) ? $_SESSION['user_id'] : null;
        
        $status = $is_edit ? $post['status'] : 'pending';
        if (isset($_POST['status']) && in_array($_SESSION['user_role'], ['Admin', 'Redaktur', 'Editor'])) {
            $status = in_array($_POST['status'], ['published', 'draft', 'pending']) ? $_POST['status'] : 'pending';
        }

        if (empty($judul)) $errors[] = "Judul tidak boleh kosong.";
        if (strlen($meta_description) > 160) $errors[] = "Meta description tidak boleh lebih dari 160 karakter.";
        
        // Validasi Upload File
        $gambar_nama = $is_edit ? $post['gambar'] : null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['gambar'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 2 * 1024 * 1024; // 2MB

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);

            if (!in_array($mime_type, $allowed_types)) {
                $errors[] = "Format file tidak diizinkan. Hanya .jpg, .png, .webp.";
            } elseif ($file['size'] > $max_size) {
                $errors[] = "Ukuran file maksimal adalah 2MB.";
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $gambar_nama = uniqid('post_', true) . '.' . $ext;
                $upload_path = __DIR__ . '/../uploads/' . $gambar_nama;
                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $errors[] = "Gagal mengupload gambar.";
                    $gambar_nama = $is_edit ? $post['gambar'] : null;
                }
            }
        }

        if (empty($errors)) {
            if ($is_edit) {
                $stmt = $conn->prepare("UPDATE posts SET judul=?, slug=?, meta_description=?, kata_kunci=?, kategori_id=?, editor_id=?, konten=?, gambar=?, status=? WHERE id=?");
                $stmt->bind_param("ssssiiissi", $judul, $slug, $meta_description, $kata_kunci, $kategori_id, $editor_id, $konten, $gambar_nama, $status, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO posts (judul, slug, meta_description, kata_kunci, kategori_id, penulis_id, editor_id, konten, gambar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssiiisss", $judul, $slug, $meta_description, $kata_kunci, $kategori_id, $penulis_id, $editor_id, $konten, $gambar_nama, $status);
            }

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Postingan berhasil disimpan.";
                header('Location: manage_posts.php');
                exit();
            } else {
                $errors[] = "Gagal menyimpan ke database: " . $stmt->error;
            }
        }
    }
}

$page_title = $is_edit ? "Edit Postingan" : "Tambah Postingan Baru";
include 'includes/admin_header.php';
$categories = $conn->query("SELECT id, nama_kategori FROM categories ORDER BY nama_kategori");
$users = $conn->query("SELECT id, nama_lengkap FROM users ORDER BY nama_lengkap");
?>

<div class="admin-container">
    <h1><?= $page_title ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data" class="post-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="form-grid">
            <div class="main-content-form">
                <div class="form-group"><label for="judul">Judul Artikel</label><input type="text" id="judul" name="judul" value="<?= htmlspecialchars($post['judul'] ?? '') ?>" required></div>
                <div class="form-group"><label for="konten">Isi Artikel</label><textarea id="konten" name="konten" rows="20"><?= htmlspecialchars($post['konten'] ?? '') ?></textarea></div>
            </div>
            <div class="sidebar-form">
                <div class="form-group"><label for="slug">Permalink (Slug)</label><input type="text" id="slug" name="slug" value="<?= htmlspecialchars($post['slug'] ?? '') ?>"><small>Biarkan kosong untuk generate otomatis.</small></div>
                <div class="form-group"><label for="kategori_id">Kategori</label><select name="kategori_id" id="kategori_id" required><option value="">Pilih Kategori</option><?php while($cat = $categories->fetch_assoc()): ?><option value="<?= $cat['id'] ?>" <?= (isset($post['kategori_id']) && $post['kategori_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nama_kategori']) ?></option><?php endwhile; ?></select></div>
                <?php if (in_array($_SESSION['user_role'], ['Admin', 'Redaktur'])): ?>
                <div class="form-group"><label for="penulis_id">Penulis</label><select name="penulis_id" id="penulis_id" required><?php mysqli_data_seek($users, 0); while($user = $users->fetch_assoc()): ?><option value="<?= $user['id'] ?>" <?= (isset($post['penulis_id']) && $post['penulis_id'] == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['nama_lengkap']) ?></option><?php endwhile; ?></select></div>
                <?php endif; ?>
                <div class="form-group"><label for="status">Status</label>
                <?php if (in_array($_SESSION['user_role'], ['Admin', 'Redaktur', 'Editor'])): ?>
                    <select name="status" id="status"><option value="draft" <?= (isset($post['status']) && $post['status'] == 'draft') ? 'selected' : '' ?>>Draft</option><option value="pending" <?= (isset($post['status']) && $post['status'] == 'pending') ? 'selected' : '' ?>>Menunggu Review</option><option value="published" <?= (isset($post['status']) && $post['status'] == 'published') ? 'selected' : '' ?>>Publish</option></select>
                <?php else: ?><p><strong>Menunggu Review</strong></p><input type="hidden" name="status" value="pending"><?php endif; ?></div>
                <div class="form-group"><label for="gambar">Gambar Utama</label><input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp"><?php if ($is_edit && !empty($post['gambar'])): ?><img src="../uploads/<?= htmlspecialchars($post['gambar']) ?>" alt="Gambar saat ini" style="max-width: 100%; margin-top: 10px;"><?php endif; ?></div>
                <div class="form-group"><label for="meta_description">Meta Description (max 160)</label><textarea id="meta_description" name="meta_description" rows="3"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea></div>
                <div class="form-group"><label for="kata_kunci">Kata Kunci</label><input type="text" id="kata_kunci" name="kata_kunci" value="<?= htmlspecialchars($post['kata_kunci'] ?? '') ?>"><small>Pisahkan dengan koma.</small></div>
                <button type="submit" class="form-button"><?= $is_edit ? 'Update' : 'Simpan' ?></button>
            </div>
        </div>
    </form>
</div>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>tinymce.init({selector: '#konten',plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',});</script>
<?php include 'includes/admin_footer.php'; ?>