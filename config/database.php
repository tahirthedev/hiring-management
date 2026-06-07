<?php
function _env(string $key, string $default = ''): string {
    return getenv($key) ?: ($_ENV[$key] ?? ($_SERVER[$key] ?? $default));
}

define('DB_HOST', _env('MYSQLHOST', 'localhost'));
define('DB_NAME', _env('MYSQLDATABASE', 'wp_screening'));
define('DB_USER', _env('MYSQLUSER', 'root'));
define('DB_PASS', _env('MYSQLPASSWORD'));
define('DB_PORT', _env('MYSQLPORT', '3306'));

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host;

define('SITE_URL', $baseUrl . '/public');
define('ADMIN_URL', $baseUrl . '/admin');
define('UPLOAD_DIR', __DIR__ . '/../uploads/cv/');
define('UPLOAD_URL', SITE_URL . '/../uploads/cv/');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please check your environment variables or config/database.php settings.");
}
