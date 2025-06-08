<?php
// Include functions file
require_once __DIR__ . '/../../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Log the logout action if the user was logged in
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    logAudit('users', $user_id, 'LOGOUT');
}

// Clear all session variables
$_SESSION = array();

// If session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../index.php");
exit;
?>