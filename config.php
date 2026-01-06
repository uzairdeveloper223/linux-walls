<?php
// Database Configuration
define('DB_HOST', 'hostname here or localhost');
define('DB_NAME', 'wallpaper_db');
define('DB_USER', 'username');
define('DB_PASS', '7password');

// Cloudinary Configuration
define('CLOUDINARY_CLOUD_NAME', 'cloudname here');
define('CLOUDINARY_API_KEY', 'api key here');
define('CLOUDINARY_API_SECRET', '******');

// Site Configuration
define('SITE_URL', 'update the url');
define('SITE_NAME', 'Linux Wallpapers');
define('CREATOR_NAME', 'Uzair Mughal');
define('CREATOR_DISCORD', 'mughal_x22');

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Helper Functions
function generateUniqueId($length = 8) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, $length);
}

function isOwner($discord_id) {
    return strtolower($discord_id) === strtolower(CREATOR_DISCORD);
}

function getSpecialTag($discord_id) {
    if (isOwner($discord_id)) {
        return '<span class="special-tag">A PURE LINUX NERD</span>';
    }
    return '';
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
