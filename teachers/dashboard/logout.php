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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Logging Out</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/logout.css">
</head>
<body>
    <div class="logout-container">
        <svg class="logout-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <h1 class="logout-title">Logging Out</h1>
        <p class="logout-message">Thank you for using the School Management System. You are being logged out securely.</p>
        <div class="redirect-text">
            <div class="spinner"></div>
            <span>Redirecting to login page...</span>
        </div>
    </div>
    
    <script>
        // Countdown for redirection (in case the PHP redirect doesn't work)
        setTimeout(function() {
            window.location.href = "../index.php";
        }, 2000);
    </script>
</body>
</html>

<!-- 
    ADD THIS JAVASCRIPT FUNCTION TO YOUR MAIN script.js FILE:

    // Logout function to be called when the logout link is clicked
    function logoutUser() {
        // Show a confirmation dialog
        if (confirm('Are you sure you want to log out?')) {
            // Add a fade-out animation to the body before redirecting
            document.body.classList.add('fade-out');
            
            // Wait for the animation to complete before redirecting
            setTimeout(function() {
                // Redirect to the logout page
                window.location.href = 'logout.php';
            }, 500);
        }
    }

    AND REPLACE YOUR SIDEBAR LOGOUT LINK WITH:

    <a href="javascript:void(0);" onclick="logoutUser()" class="sidebar-link">
        <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span>Logout</span>
    </a>

    AND ADD THIS TO YOUR login.php FILE (after "Please enter your details" paragraph):

    <?php
    // Check if there's a logout message in cookies
    $logout_message = '';
    if (isset($_COOKIE['logout_message'])) {
        $logout_message = $_COOKIE['logout_message'];
        // Clear the cookie
        setcookie("logout_message", "", time() - 3600, "/");
    }
    ?>

    <?php if(!empty($logout_message)): ?>
    <div class="alert alert-success" style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
        <?php echo htmlspecialchars($logout_message); ?>
    </div>
    <?php endif; ?>
-->