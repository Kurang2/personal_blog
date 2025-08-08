<?php
// /includes/sidebar.php

// Kategori
$cat_sidebar_sql = "SELECT nama_kategori, slug FROM categories ORDER BY nama_kategori ASC";
$cat_sidebar_result = $conn->query($cat_sidebar_sql);

// Populer
$pop_sql = "SELECT judul, slug FROM posts WHERE status='published' ORDER BY views DESC LIMIT 5";
$pop_result = $conn->query($pop_sql);

// Arsip
$arc_sql = "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month_val, DATE_FORMAT(created_at, '%M %Y') as month_name FROM posts WHERE status='published' ORDER BY month_val DESC LIMIT 6";
$arc_result = $conn->query($arc_sql);
?>
<aside class="sidebar">
    <div class="widget">
        <h3>Kategori</h3>
        <ul class="widget-list">
            <?php if ($cat_sidebar_result && $cat_sidebar_result->num_rows > 0): ?>
                <?php while($cat = $cat_sidebar_result->fetch_assoc()): ?>
                <li><a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></a></li>
                <?php endwhile; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="widget">
        <h3>Artikel Populer</h3>
        <ul class="widget-list">
            <?php if ($pop_result && $pop_result->num_rows > 0): ?>
                <?php while($pop = $pop_result->fetch_assoc()): ?>
                <li><a href="article.php?slug=<?= htmlspecialchars($pop['slug']) ?>"><?= htmlspecialchars($pop['judul']) ?></a></li>
                <?php endwhile; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="widget">
        <h3>Arsip</h3>
        <ul class="widget-list">
             <?php if ($arc_result && $arc_result->num_rows > 0): ?>
                <?php while($arc = $arc_result->fetch_assoc()): ?>
                <li><a href="search.php?archive=<?= htmlspecialchars($arc['month_val']) ?>"><?= htmlspecialchars($arc['month_name']) ?></a></li>
                <?php endwhile; ?>
            <?php endif; ?>
        </ul>
    </div>
</aside>