<?php
// Prevent direct access except with specific checks
if (!isset($_GET['check'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Set content type to plain text
header('Content-Type: text/plain');

// Start collecting diagnostic information
$diagnostics = [];

// Check which diagnostic to run
$check = $_GET['check'];

// Basic server information
$diagnostics['php_version'] = phpversion();
$diagnostics['memory_limit'] = ini_get('memory_limit');
$diagnostics['post_max_size'] = ini_get('post_max_size');
$diagnostics['upload_max_filesize'] = ini_get('upload_max_filesize');
$diagnostics['max_execution_time'] = ini_get('max_execution_time');

// Check upload directory permissions
if ($check === 'upload_permissions') {
    $upload_dirs = [
        '../../uploads/',
        '../../uploads/payment_proofs/',
        '../../uploads/homework_attachments/',
        '../../uploads/homework_submissions/'
    ];
    
    foreach ($upload_dirs as $dir) {
        if (!file_exists($dir)) {
            $diagnostics['dir_' . $dir] = 'Directory does not exist';
            
            // Try to create it
            if (@mkdir($dir, 0755, true)) {
                $diagnostics['dir_' . $dir] = 'Directory created successfully';
            } else {
                $diagnostics['dir_' . $dir] = 'Failed to create directory: ' . error_get_last()['message'];
            }
        } else {
            $diagnostics['dir_' . $dir] = 'Directory exists';
            
            // Check if it's writable
            if (is_writable($dir)) {
                $diagnostics['dir_' . $dir] .= ' and is writable';
                
                // Try to create a test file
                $test_file = $dir . 'test_' . time() . '.txt';
                if (@file_put_contents($test_file, 'Test file')) {
                    $diagnostics['dir_' . $dir] .= ', test file created';
                    @unlink($test_file); // Delete the test file
                } else {
                    $diagnostics['dir_' . $dir] .= ', but test file creation failed: ' . error_get_last()['message'];
                }
            } else {
                $diagnostics['dir_' . $dir] .= ' but is NOT writable';
            }
        }
    }
}

// Database connectivity check
if ($check === 'database' || $check === 'all') {
    require_once '../../includes/config.php';
    
    try {
        $conn = getDbConnection();
        if ($conn) {
            $diagnostics['database'] = 'Database connection successful';
            
            // Check if fee_structures table exists
            $result = $conn->query("SHOW TABLES LIKE 'fee_structures'");
            $diagnostics['fee_structures_table'] = ($result->num_rows > 0) ? 'Exists' : 'Does not exist';
            
            // Check if fee_payment_proofs table exists
            $result = $conn->query("SHOW TABLES LIKE 'fee_payment_proofs'");
            $diagnostics['fee_payment_proofs_table'] = ($result->num_rows > 0) ? 'Exists' : 'Does not exist';
        } else {
            $diagnostics['database'] = 'Database connection failed';
        }
    } catch (Exception $e) {
        $diagnostics['database'] = 'Database error: ' . $e->getMessage();
    }
}

// Output the diagnostics
echo "Server Diagnostics:\n";
echo "==================\n\n";

foreach ($diagnostics as $key => $value) {
    echo $key . ': ' . $value . "\n";
}

// Try connecting to a test script to verify HTTP connections
if ($check === 'http' || $check === 'all') {
    $test_url = 'http://' . $_SERVER['HTTP_HOST'] . '/ves/student/dashboard/test-connection.php';
    echo "\nTesting HTTP connection to: $test_url\n";
    
    $ch = curl_init($test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Test Result: Status code $http_code\n";
    echo "Response: $result\n";
}

// Output session information
echo "\nSession Information:\n";
echo "===================\n";
echo "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "\n";
echo "Session ID: " . session_id() . "\n";

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session Data:\n";
    foreach ($_SESSION as $key => $value) {
        if ($key === 'user_id' || $key === 'role') {
            echo "  $key: $value\n";
        } else {
            echo "  $key: [" . gettype($value) . "]\n";
        }
    }
}
?> 