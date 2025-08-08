<?php
// File: /category.php
require_once __DIR__ . '/includes/functions.php';

// Cek apakah slug kategori ada di URL
if (!isset($_GET['slug'])) {
    header('Location: /personal_blog/index.php');
    exit();
}

$slug = $_GET['slug'];

// Ambil informasi kategori dari database
$cat_stmt = $conn->prepare("SELECT id, nama_kategori FROM categories WHERE slug = ?");
$cat_stmt->bind_param("s", $slug);
$cat_stmt->execute();
$category = $cat_stmt->get_result()->fetch_assoc();
$cat_stmt->close();

// Jika kategori tidak ditemukan, tampilkan halaman 404
if (!$category) {
    http_response_code(404);
    $page_title = "404 Kategori Tidak Ditemukan";
    include __DIR__ . '/includes/header.php';
    echo "<main class='container'><p>Maaf, kategori yang Anda cari tidak ditemukan.</p></main>";
    include __DIR__ . '/includes/footer.php';
    exit();
}

$kategori_id = $category['id'];
$page_title = "Kategori: " . htmlspecialchars($category['nama_kategori']);

// Logika Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

$status_published = 'published';
$total_stmt = $conn->prepare("SELECT COUNT(id) FROM posts WHERE status = ? AND kategori_id = ?");
$total_stmt->bind_param("si", $status_published, $kategori_id);
$total_stmt->execute();
$total_posts = $total_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_posts / $perPage);
$total_stmt->close();

// Ambil artikel untuk kategori ini
$posts_sql = "SELECT p.*, u.nama_lengkap as penulis
              FROM posts p
              JOIN users u ON p.penulis_id = u.id
              WHERE p.status = ? AND p.kategori_id = ?
              ORDER BY p.created_at DESC
              LIMIT ?, ?";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->bind_param("siii", $status_published, $kategori_id, $start, $perPage);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

include __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="main-content">
        <h1 class="page-title">Kategori: <?= htmlspecialchars($category['nama_kategori']) ?></h1>

        <?php if ($posts_result->num_rows > 0): ?>
            <?php while($post = $posts_result->fetch_assoc()): ?>
                <article class="post-item">
                    <?php if(!empty($post['gambar'])): ?>
                        <a href="/personal_blog/article.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                            <img src="/personal_blog/uploads/<?= htmlspecialchars($post['gambar']) ?>" alt="<?= htmlspecialchars($post['judul']) ?>" class="post-item-image">
                        </a>
                    <?php endif; ?>
                    <div class="post-item-content">
                        <!-- Kategori tidak perlu ditampilkan lagi di sini -->
                        <h2><a href="/personal_blog/article.php?slug=<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['judul']) ?></a></h2>
                        <p class="post-meta">Oleh <?= htmlspecialchars($post['penulis']) ?> pada <?= date('d M Y', strtotime($post['created_at'])) ?></p>
                        <p><?= get_excerpt($post['konten']) ?></p>
                        <a href="/personal_blog/article.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="read-more">Baca Selengkapnya &rarr;</a>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada artikel dalam kategori ini.</p>
        <?php endif; ?>

        <!-- Navigasi Halaman -->
        <nav class="pagination">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/personal_blog/category.php?slug=<?= htmlspecialchars($slug) ?>&page=<?= $i ?>" class="<?= ($page === $i) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    </div>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
