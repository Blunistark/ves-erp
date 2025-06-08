<?php
/**
 * Common Functions File
 * Contains shared functions for authentication, validation, and utilities
 */

// Include configuration file
require_once __DIR__ . '/config.php';

/**
 * Authenticate student using SATS number and DOB
 * @param string $sats_number Student State Code (SATS number)
 * @param string $dob Date of birth in YYYY-MM-DD format
 * @return array|false Student data array or false if authentication fails
 */
function authenticateStudent($sats_number, $dob) {
    // Basic input validation
    if (empty($sats_number) || empty($dob)) {
        return false;
    }
    
    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$date || $date->format('Y-m-d') !== $dob) {
        return false;
    }
    
    // Query to get student data with user information
    $sql = "SELECT s.user_id, s.full_name, s.student_state_code, s.dob, s.class_id, s.section_id, 
                   u.email, u.role, u.status
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.student_state_code = ? AND DATE_FORMAT(s.dob, '%Y-%m-%d') = ?";
    
    $result = executeQuery($sql, "ss", [$sats_number, $dob]);
    
    // Check if student exists
    if (empty($result)) {
        // Log failed login attempt
        logLoginAttempt($sats_number, false);
        return false;
    }
    
    $student = $result[0];
    
    // Check if user is active
    if ($student['status'] !== 'active') {
        logLoginAttempt($student['user_id'], false);
        return false;
    }
    
    // Log successful login
    logLoginAttempt($student['user_id'], true);
    
    // Update last login timestamp
    updateLastLogin($student['user_id']);
    
    // Return student data
    return [
        'id' => $student['user_id'],
        'email' => $student['email'],
        'full_name' => $student['full_name'],
        'role' => $student['role'],
        'student_state_code' => $student['student_state_code'],
        'class_id' => $student['class_id'],
        'section_id' => $student['section_id']
    ];
}

/**
 * Authenticate teacher using email or employee ID
 * @param string $identifier Email or Employee ID
 * @param string $password Password
 * @return array|false User data on success, false on failure
 */
function authenticateTeacher($identifier, $password) {
    // Basic input validation
    if (empty($identifier) || empty($password)) {
        return false;
    }
    
    // Determine if identifier is email or employee ID
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    
    if ($isEmail) {
        // Use email-based authentication
        $sql = "SELECT u.id, u.email, u.password_hash, u.full_name, u.role, u.status, t.employee_number 
                FROM users u 
                INNER JOIN teachers t ON u.id = t.user_id 
                WHERE u.email = ? AND u.role = 'teacher'";
        $params = [$identifier];
        $types = "s";
    } else {
        // Use employee ID-based authentication
        $sql = "SELECT u.id, u.email, u.password_hash, u.full_name, u.role, u.status, t.employee_number 
                FROM users u 
                INNER JOIN teachers t ON u.id = t.user_id 
                WHERE t.employee_number = ? AND u.role = 'teacher'";
        $params = [$identifier];
        $types = "s";
    }
    
    // Execute query
    $result = executeQuery($sql, $types, $params);
    
    // Check if user exists
    if (empty($result)) {
        // Log failed login attempt
        logLoginAttempt($identifier, false);
        return false;
    }
    
    $user = $result[0];
    
    // Check if user is active
    if ($user['status'] !== 'active') {
        logLoginAttempt($user['id'], false);
        return false;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        logLoginAttempt($user['id'], false);
        return false;
    }
    
    // Log successful login
    logLoginAttempt($user['id'], true);
    
    // Update last login timestamp
    updateLastLogin($user['id']);
    
    // Remove password hash from result
    unset($user['password_hash']);
    
    return $user;
}

/**
 * Authenticate user
 * @param string $email User email
 * @param string $password User password
 * @param string $role User role (admin, teacher, student, parent)
 * @return array|false User data array or false if authentication fails
 */
function authenticateUser($email, $password, $role = null) {
    // Basic input validation
    if (empty($email) || empty($password)) {
        return false;
    }
    
    // Prepare SQL query
    $sql = "SELECT id, email, password_hash, full_name, role, status FROM users WHERE email = ?";
    $params = [$email];
    $types = "s";
    
    // Add role filter if specified
    if ($role !== null) {
        $sql .= " AND role = ?";
        $params[] = $role;
        $types .= "s";
    }
    
    // Execute query
    $result = executeQuery($sql, $types, $params);
    
    // Check if user exists
    if (empty($result)) {
        // Log failed login attempt
        logLoginAttempt($email, false);
        return false;
    }
    
    $user = $result[0];
    
    // Check if user is active
    if ($user['status'] !== 'active') {
        logLoginAttempt($user['id'], false);
        return false;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        logLoginAttempt($user['id'], false);
        return false;
    }
    
    // Log successful login
    logLoginAttempt($user['id'], true);
    
    // Update last login timestamp
    updateLastLogin($user['id']);
    
    // Remove password hash from result
    unset($user['password_hash']);
    
    return $user;
}

/**
 * Log login attempt
 * @param int|string $user_id User ID or email if ID not available
 * @param bool $success Whether login was successful
 * @return void
 */
function logLoginAttempt($user_id, $success) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // If user_id is an email (login failed before ID was obtained)
    if (!is_numeric($user_id)) {
        $sql = "INSERT INTO login_attempts (user_id, attempt_time, ip_address, success) 
                VALUES (NULL, CURRENT_TIMESTAMP, ?, ?)";
        executeQuery($sql, "si", [$ip, $success ? 1 : 0]);
        return;
    }
    
    // Log with user ID
    $sql = "INSERT INTO login_attempts (user_id, attempt_time, ip_address, success) 
            VALUES (?, CURRENT_TIMESTAMP, ?, ?)";
    executeQuery($sql, "isi", [(int)$user_id, $ip, $success ? 1 : 0]);
}

/**
 * Update user's last login timestamp
 * @param int $user_id User ID
 * @return bool Success status
 */
function updateLastLogin($user_id) {
    $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
    $result = executeQuery($sql, "i", [(int)$user_id]);
    return isset($result['affected_rows']) && $result['affected_rows'] > 0;
}

/**
 * Start secure session
 * @return void
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session cookie parameters
        $secure = true; // Only transmit over HTTPS
        $httponly = true; // Prevent JavaScript access
        $samesite = 'Strict'; // Prevent CSRF
        
        // PHP 7.3+ supports samesite attribute
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);
        } else {
            session_set_cookie_params(0, '/; samesite='.$samesite, '', $secure, $httponly);
        }
        
        // Set additional security headers
        if (!headers_sent()) {
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', 3600); // 1 hour
        }
        
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate session ID every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Check if user is logged in
 * @return bool Whether user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has required role
 * @param string|array $roles Required role(s)
 * @return bool Whether user has required role
 */
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    
    // Single role check
    if (is_string($roles)) {
        return $_SESSION['role'] === $roles;
    }
    
    // Multiple role check
    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    }
    
    return false;
}

/**
 * Redirect to login page if not logged in
 * @param string|array $roles Required role(s)
 * @return void
 */
function requireLogin($roles = null) {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit;
    }
    
    // Check role if specified
    if ($roles !== null && !hasRole($roles)) {
        header("Location: ../unauthorized.php");
        exit;
    }
}

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token CSRF token to validate
 * @return bool Whether token is valid
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log audit event
 * @param string $table_name Name of the table being modified
 * @param int $record_id ID of the record being modified
 * @param string $action Action being performed (INSERT, UPDATE, DELETE)
 * @return void
 */
function logAudit($table_name, $record_id, $action) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    $sql = "INSERT INTO audit_logs (user_id, table_name, record_id, action, timestamp) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
    executeQuery($sql, "isis", [$user_id, $table_name, $record_id, $action]);
}

/**
 * Get current academic year ID
 * First checks for is_current flag, then falls back to date range check
 * @return int|null Current academic year ID or null if not found
 */
function getCurrentAcademicYearId() {
    // First try to get academic year marked as current
    $sql = "SELECT id FROM academic_years WHERE is_current = 1 LIMIT 1";
    $result = executeQuery($sql);
    
    if (!empty($result)) {
        return (int)$result[0]['id'];
    }
    
    // Fall back to checking date range
    $sql = "SELECT id FROM academic_years 
            WHERE CURDATE() BETWEEN start_date AND end_date 
            ORDER BY start_date DESC LIMIT 1";
    $result = executeQuery($sql);
    
    return !empty($result) ? (int)$result[0]['id'] : null;
} 