<?php
require_once 'includes/functions.php';

if (!isset($_GET['slug'])) {
    header('Location: index.php');
    exit();
}

$slug = $conn->real_escape_string($_GET['slug']);

// Get post detail
$sql = "SELECT p.*, c.nama_kategori, c.slug as kategori_slug, u_penulis.nama_lengkap as penulis, u_editor.nama_lengkap as editor 
        FROM posts p
        JOIN categories c ON p.kategori_id = c.id
        JOIN users u_penulis ON p.penulis_id = u_penulis.id
        LEFT JOIN users u_editor ON p.editor_id = u_editor.id
        WHERE p.slug = ? AND p.status = 'published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    $page_title = "404 Not Found";
    include 'includes/header.php';
    echo "<main class='container'><p>Artikel tidak ditemukan.</p></main>";
    include 'includes/footer.php';
    exit();
}

$post = $result->fetch_assoc();
$page_title = htmlspecialchars($post['judul']);
$meta_description = htmlspecialchars($post['meta_description']);

// Update view count
$conn->query("UPDATE posts SET views = views + 1 WHERE id = " . (int)$post['id']);

include 'includes/header.php';
?>

<main class="container">
    <div class="main-content article-page">
        <article class="full-article">
            <header class="article-header">
                <a href="category.php?slug=<?= htmlspecialchars($post['kategori_slug']) ?>" class="post-category"><?= htmlspecialchars($post['nama_kategori']) ?></a>
                <h1><?= htmlspecialchars($post['judul']) ?></h1>
                <p class="post-meta">
                    Ditulis oleh: <strong><?= htmlspecialchars($post['penulis']) ?></strong> | 
                    <?php if(!empty($post['editor'])): ?>
                        Disunting oleh: <strong><?= htmlspecialchars($post['editor']) ?></strong> |
                    <?php endif; ?>
                    Tanggal: <?= date('d F Y', strtotime($post['created_at'])) ?>
                </p>
            </header>

            <?php if(!empty($post['gambar'])): ?>
                <img src="uploads/<?= htmlspecialchars($post['gambar']) ?>" alt="<?= htmlspecialchars($post['judul']) ?>" class="article-image">
            <?php endif; ?>
            
            <div class="article-content">
                <?= $post['konten'] // Don't escape, WYSIWYG content is considered trusted from admin ?>
            </div>
        </article>
        
        <hr>
        
        <section class="related-posts">
            <h3>Artikel Terkait</h3>
            <div class="related-grid">
                <?php
                $related_sql = "SELECT judul, slug, gambar FROM posts WHERE kategori_id = ? AND id != ? AND status = 'published' ORDER BY RAND() LIMIT 3";
                $stmt_related = $conn->prepare($related_sql);
                $stmt_related->bind_param("ii", $post['kategori_id'], $post['id']);
                $stmt_related->execute();
                $related_result = $stmt_related->get_result();
                if($related_result->num_rows > 0):
                    while($related = $related_result->fetch_assoc()):
                ?>
                <div class="related-item">
                    <a href="article.php?slug=<?= htmlspecialchars($related['slug']) ?>">
                        <?php if(!empty($related['gambar'])): ?>
                           <img src="uploads/<?= htmlspecialchars($related['gambar']) ?>" alt="<?= htmlspecialchars($related['judul']) ?>">
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($related['judul']) ?></h4>
                    </a>
                </div>
                <?php 
                    endwhile;
                else:
                    echo "<p>Tidak ada artikel terkait.</p>";
                endif;
                ?>
            </div>
        </section>
    </div>
    
    <?php include 'includes/sidebar.php'; ?>
</main>

<?php include 'includes/footer.php'; ?>