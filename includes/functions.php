<?php
// /includes/functions.php
require_once 'db.php';

// --- SESSION SECURITY ---
function secure_session_start() {
    $session_name = 'secure_session_id';
    $secure = false; // Set ke true jika menggunakan HTTPS
    $httponly = true;

    ini_set('session.use_only_cookies', 1);
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly
    );

    session_name($session_name);
    session_start();
    
    // Regenerate session ID untuk mencegah session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 menit
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// --- SECURITY & SANITIZATION ---
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// --- USER & AUTHENTICATION ---
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function check_role($allowed_roles = []) {
    if (!is_logged_in() || !in_array($_SESSION['user_role'], $allowed_roles)) {
        // Alihkan ke halaman login atau halaman error
        header('Location: /login.php?error=access_denied');
        exit();
    }
}

// --- WEBSITE SETTINGS ---
$settings_cache = null;
function get_setting($nama_setting) {
    global $conn, $settings_cache;

    if ($settings_cache === null) {
        $settings_cache = [];
        $result = $conn->query("SELECT nama_setting, value FROM settings");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings_cache[$row['nama_setting']] = $row['value'];
            }
        }
    }
    
    return isset($settings_cache[$nama_setting]) ? $settings_cache[$nama_setting] : null;
}

// --- UTILITIES ---
function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', ' ', $string);
    $string = preg_replace('/[\s]/', '-', $string);
    return trim($string, '-');
}

function get_excerpt($content, $length = 150) {
    $content = strip_tags($content);
    if (strlen($content) > $length) {
        $excerpt = substr($content, 0, $length);
        $last_space = strrpos($excerpt, ' ');
        return substr($excerpt, 0, $last_space) . '...';
    }
    return $content;
}

// Jalankan session aman di setiap halaman yang menyertakan file ini
secure_session_start();
$csrf_token = generate_csrf_token();
?>