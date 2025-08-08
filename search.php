<?php
// File: /search.php
require_once __DIR__ . '/includes/functions.php';

$search_query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
$page_title = "Hasil Pencarian untuk: " . htmlspecialchars($search_query);

// Logika Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

$status_published = 'published';
$search_term = "%{$search_query}%";

// Hitung total hasil pencarian
$total_stmt = $conn->prepare("SELECT COUNT(id) FROM posts WHERE status = ? AND (judul LIKE ? OR konten LIKE ?)");
$total_stmt->bind_param("sss", $status_published, $search_term, $search_term);
$total_stmt->execute();
$total_posts = $total_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_posts / $perPage);
$total_stmt->close();

// Ambil hasil pencarian
$posts_sql = "SELECT p.*, c.nama_kategori, c.slug as kategori_slug, u.nama_lengkap as penulis
              FROM posts p
              JOIN categories c ON p.kategori_id = c.id
              JOIN users u ON p.penulis_id = u.id
              WHERE p.status = ? AND (p.judul LIKE ? OR p.konten LIKE ?)
              ORDER BY p.created_at DESC
              LIMIT ?, ?";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->bind_param("sssii", $status_published, $search_term, $search_term, $start, $perPage);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

include __DIR__ . '/includes/header.php';
?>

<main class="container">
    <div class="main-content">
        <h1 class="page-title">Hasil Pencarian untuk: "<?= htmlspecialchars($search_query) ?>"</h1>

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
            <p>Tidak ada hasil yang ditemukan untuk pencarian Anda.</p>
        <?php endif; ?>

        <!-- Navigasi Halaman -->
        <nav class="pagination">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/personal_blog/search.php?q=<?= htmlspecialchars($search_query) ?>&page=<?= $i ?>" class="<?= ($page === $i) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    </div>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
