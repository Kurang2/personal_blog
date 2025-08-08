<?php
// File: /includes/header.php
$site_title = get_setting('site_title');
$site_desc = get_setting('site_description');
$categories_menu_sql = "SELECT nama_kategori, slug FROM categories ORDER BY nama_kategori ASC";
$categories_menu_result = $conn->query($categories_menu_sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' . htmlspecialchars($site_title) : htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?= isset($meta_description) ? htmlspecialchars($meta_description) : htmlspecialchars($site_desc); ?>">
    
    <!-- ================================================== -->
    <!-- == PERBAIKAN DI SINI: Path ke file CSS diperbaiki == -->
    <!-- ================================================== -->
    <link rel="stylesheet" href="personal_blog/assets/css/style.css">

</head>
<body>
    <header class="main-header">
        <div class="container header-flex">
            <div class="logo">
                <a href="/personal_blog/index.php"><?= htmlspecialchars($site_title) ?></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php if ($categories_menu_result && $categories_menu_result->num_rows > 0): ?>
                        <?php while($cat = $categories_menu_result->fetch_assoc()): ?>
                        <li><a href="/personal_blog/category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></a></li>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="header-actions">
                <form action="/personal_blog/search.php" method="GET" class="search-form">
                    <input type="search" name="q" placeholder="Cari artikel..." required>
                    <button type="submit">Cari</button>
                </form>
                <?php if (is_logged_in()): ?>
                    <a href="/personal_blog/admin/index.php" class="auth-button">Dashboard</a>
                    <a href="/personal_blog/logout.php" class="auth-button">Keluar</a>
                <?php else: ?>
                    <a href="/personal_blog/login.php" class="auth-button">Masuk</a>
                    <a href="/personal_blog/register.php" class="auth-button register">Daftar</a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-toggle">MENU</button>
        </div>
    </header>
