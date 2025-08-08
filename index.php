<?php
// File: /index.php (Halaman Depan untuk Pengunjung)

// Memuat file fungsi dan koneksi database
require_once __DIR__ . '/includes/functions.php';

// --- Logika Pagination ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5; // Jumlah artikel per halaman
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Menghitung total artikel yang sudah 'published' untuk pagination
$total_stmt = $conn->prepare("SELECT COUNT(id) FROM posts WHERE status = ?");
$status_published = 'published';
$total_stmt->bind_param("s", $status_published);
$total_stmt->execute();
$total_posts = $total_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_posts / $perPage);
$total_stmt->close();

// --- Mengambil Artikel dari Database ---
$posts_sql = "SELECT p.*, c.nama_kategori, c.slug as kategori_slug, u.nama_lengkap as penulis
              FROM posts p
              JOIN categories c ON p.kategori_id = c.id
              JOIN users u ON p.penulis_id = u.id
              WHERE p.status = ?
              ORDER BY p.created_at DESC
              LIMIT ?, ?";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->bind_param("sii", $status_published, $start, $perPage);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

// Mengatur judul halaman dan memuat header
$page_title = get_setting('site_title');
include __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="main-content">
        <h1 class="page-title">Berita Terbaru</h1>

        <?php if ($posts_result->num_rows > 0): ?>
            <?php while($post = $posts_result->fetch_assoc()): ?>
                <article class="post-item">
                    <?php if(!empty($post['gambar'])): ?>
                        <a href="/personal_blog/article.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                            <img src="/personal_blog/uploads/<?= htmlspecialchars($post['gambar']) ?>" alt="<?= htmlspecialchars($post['judul']) ?>" class="post-item-image">
                        </a>
                    <?php endif; ?>
                    <div class="post-item-content">
                        <a href="/personal_blog/category.php?slug=<?= htmlspecialchars($post['kategori_slug']) ?>" class="post-category"><?= htmlspecialchars($post['nama_kategori']) ?></a>
                        <h2><a href="/personal_blog/article.php?slug=<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['judul']) ?></a></h2>
                        <p class="post-meta">Oleh <?= htmlspecialchars($post['penulis']) ?> pada <?= date('d M Y', strtotime($post['created_at'])) ?></p>
                        <p><?= get_excerpt($post['konten']) ?></p>
                        <a href="/personal_blog/article.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="read-more">Baca Selengkapnya &rarr;</a>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada postingan yang dipublikasikan.</p>
        <?php endif; ?>

        <!-- Navigasi Halaman -->
        <nav class="pagination">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/personal_blog/?page=<?= $i ?>" class="<?= ($page === $i) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    </div>

    <?php // Memuat sidebar
    include __DIR__ . '/includes/sidebar.php'; ?>
</main>

<?php // Memuat footer
include __DIR__ . '/includes/footer.php'; ?>
