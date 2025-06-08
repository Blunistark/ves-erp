<?php
// ----------------- Database Configuration -----------------
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'u786183242_ves_recep');
define('DB_PASSWORD', '@Tinauto500');
define('DB_NAME', 'u786183242_ves_recep');

// ----------------- Application Constants ------------------
define('APP_NAME', 'School Admin System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/school-admin/'); // Change if hosted in subdirectory

// ----------------- Timezone Setup -------------------------
date_default_timezone_set('Asia/Kolkata'); // Set PHP timezone to IST

// ----------------- Error Handling -------------------------
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ----------------- Session Configuration ------------------
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------- Security Headers -----------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// ----------------- Path Constants -------------------------
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('PAGES_PATH', ROOT_PATH . 'pages/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('LOGS_PATH', ROOT_PATH . 'logs/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');

// ----------------- Directory Creation ---------------------
$dirs = [LOGS_PATH, UPLOADS_PATH];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ----------------- Role Permissions -----------------------
define('ROLES', [
    'admin' => [
        'view_dashboard',
        'manage_admissions',
        'manage_visitors',
        'manage_users',
        'export_data',
        'backup_database',
        'view_logs'
    ],
    'staff' => [
        'view_dashboard',
        'manage_admissions',
        'manage_visitors',
        'export_data'
    ],
    'viewer' => [
        'view_dashboard'
    ]
]);

// ----------------- MySQL Connection ------------------------
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// ----------------- MySQL Timezone Sync ---------------------
function syncMySQLTimezone($conn) {
    // Try setting timezone to UTC offset first, as it's more universally supported
    if (!$conn->query("SET time_zone = '+05:30'")) {
        // As a fallback, try the named timezone if the offset fails (less likely)
        // Suppress errors for this fallback attempt as it might also fail
        @$conn->query("SET time_zone = 'Asia/Kolkata'");
    }
}
syncMySQLTimezone($mysqli);

// ----------------- Global Connection -----------------------
$conn = $mysqli;
?>
