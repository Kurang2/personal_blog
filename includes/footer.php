<?php
// /includes/footer.php
$site_title = get_setting('site_title');
$contact_email = get_setting('contact_email');
$fb_link = get_setting('social_facebook');
$twitter_link = get_setting('social_twitter');
$ig_link = get_setting('social_instagram');
?>
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <p>Email: <a href="mailto:<?= htmlspecialchars($contact_email) ?>"><?= htmlspecialchars($contact_email) ?></a></p>
                </div>
                <div class="footer-section">
                    <h4>Media Sosial</h4>
                    <a href="<?= htmlspecialchars($fb_link) ?>" target="_blank" rel="noopener noreferrer">Facebook</a> |
                    <a href="<?= htmlspecialchars($twitter_link) ?>" target="_blank" rel="noopener noreferrer">Twitter</a> |
                    <a href="<?= htmlspecialchars($ig_link) ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($site_title) ?>. All rights reserved.
            </div>
        </div>
    </footer>
    <script src="/personal_blog/assets/js/script.js"></script>
</body>
</html>