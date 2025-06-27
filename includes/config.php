<?php
/**
 * Database Configuration File
 * Contains database connection parameters and utility functions
 */

// Development environment - enable error reporting for debugging
// In production, set ENVIRONMENT to 'production' or remove these lines
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' for live server
}

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    error_log('Debug mode enabled in config.php - Development environment');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1); // Still log errors in production
}

// Include timezone utilities
require_once __DIR__ . '/timezone_fix.php';

// Set timezone for PHP
date_default_timezone_set('Asia/Kolkata'); // India Standard Time (IST) +5:30

// Database connection parameters
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'u786183242_ves_db6');           // Database username
define('DB_PASS', '@Tinauto500');               // Database password
define('DB_NAME', 'u786183242_ves_db6');     // Database name

/**
 * Get database connection
 * @return mysqli|false Database connection object or false on failure
 */
function getDbConnection() {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        logError("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Set charset
    if (!$conn->set_charset("utf8mb4")) {
        logError("Failed to set charset: " . $conn->error);
        return false;
    }
    
    // Set timezone for MySQL connection to match PHP timezone
    $conn->query("SET time_zone = '+05:30'");
    
    return $conn;
}

// Create global database connection
$conn = getDbConnection();
if (!$conn) {
    if (ENVIRONMENT === 'development') {
        die("Database connection failed. Please check your database configuration.");
    } else {
        error_log("Database connection failed in config.php");
        die("Database connection error. Please try again later.");
    }
}

/**
 * Execute a prepared query with parameters
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types string (i: integer, d: double, s: string, b: blob)
 * @param array $params Array of parameters to bind
 * @return array|false Result array or false on failure
 */
function executeQuery($sql, $types = "", $params = []) {
    $conn = getDbConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        logError("Query preparation failed: " . $conn->error . " for query: " . $sql);
        $conn->close();
        return false;
    }
    
    // Bind parameters if any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute statement
    if (!$stmt->execute()) {
        $error_msg = "Query execution failed: " . $stmt->error . " for query: " . $sql;
        if (!empty($params)) {
            $error_msg .= " with parameters: " . json_encode($params);
        }
        logError($error_msg);
        $stmt->close();
        $conn->close();
        return false;
    }
    
    // Get results
    $result = $stmt->get_result();
    if (!$result) {
        // For INSERT, UPDATE, DELETE statements
        $affected = $stmt->affected_rows;
        $insert_id = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        return ['affected_rows' => $affected, 'insert_id' => $insert_id];
    }
    
    // For SELECT statements
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $data;
}

/**
 * Log errors to file
 * @param string $message Error message
 * @return void
 */
function logError($message) {
    $log_file = __DIR__ . '/../logs/error.log';
    $log_dir = dirname($log_file);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Format log entry
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message" . PHP_EOL;
    
    // Write to log file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
} 