<?php
// Include necessary files
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/con.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../login.php");
    exit;
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = "Invalid request. Please try again.";
    header("Location: users.php");
    exit;
}

// Get action and user ID
$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

// Validate user ID
if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: users.php");
    exit;
}

// Get current user info
$user_query = "SELECT id, full_name, role, status FROM users WHERE id = ?";
$user = executeQuery($user_query, "i", [$user_id]);

if (empty($user)) {
    $_SESSION['error'] = "User not found.";
    header("Location: users.php");
    exit;
}

$user = $user[0];

// Check if trying to modify admin
if ($user['role'] === 'admin' && $_SESSION['role'] === 'admin' && $_SESSION['user_id'] !== $user_id) {
    // Only allow admin to modify other admins if they are super admin or themselves
    if (!isset($_SESSION['is_super_admin']) || $_SESSION['is_super_admin'] !== true) {
        $_SESSION['error'] = "You don't have permission to modify other administrators.";
        header("Location: users.php");
        exit;
    }
}

// Process actions
switch ($action) {
    case 'activate':
        if ($user['status'] !== 'inactive') {
            $_SESSION['error'] = "User is already active.";
            header("Location: users.php");
            exit;
        }
        
        $result = executeQuery("UPDATE users SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?", "i", [$user_id]);
        
        if ($result && isset($result['affected_rows']) && $result['affected_rows'] > 0) {
            // Log audit
            logAudit('users', $user_id, 'UPDATE');
            $_SESSION['success'] = "User " . htmlspecialchars($user['full_name']) . " has been activated.";
        } else {
            $_SESSION['error'] = "Failed to activate user.";
        }
        break;
        
    case 'deactivate':
        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "User is already inactive.";
            header("Location: users.php");
            exit;
        }
        
        // Don't allow deactivating your own account
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot deactivate your own account.";
            header("Location: users.php");
            exit;
        }
        
        $result = executeQuery("UPDATE users SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = ?", "i", [$user_id]);
        
        if ($result && isset($result['affected_rows']) && $result['affected_rows'] > 0) {
            // Log audit
            logAudit('users', $user_id, 'UPDATE');
            $_SESSION['success'] = "User " . htmlspecialchars($user['full_name']) . " has been deactivated.";
        } else {
            $_SESSION['error'] = "Failed to deactivate user.";
        }
        break;
        
    case 'reset_password':
        // Generate a random password
        $new_password = generateRandomPassword();
        
        // Hash the password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $result = executeQuery("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", "si", [$password_hash, $user_id]);
        
        if ($result && isset($result['affected_rows']) && $result['affected_rows'] > 0) {
            // Log audit
            logAudit('users', $user_id, 'UPDATE');
            $_SESSION['success'] = "Password reset successful for " . htmlspecialchars($user['full_name']) . ". Temporary password: " . $new_password;
            
            // In a production environment, you would email the password instead of displaying it
            // sendPasswordResetEmail($user_id, $new_password);
        } else {
            $_SESSION['error'] = "Failed to reset password.";
        }
        break;
        
    default:
        $_SESSION['error'] = "Invalid action.";
}

// Redirect back to users page
header("Location: users.php");
exit;

/**
 * Generate a random password
 * @param int $length Length of the password
 * @return string Random password
 */
function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($chars) - 1);
        $password .= $chars[$index];
    }
    
    return $password;
} 