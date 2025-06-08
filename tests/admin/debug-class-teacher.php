<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Test the API endpoint directly
echo "<h1>Debug Class Teacher Assignment</h1>";

// Test 1: Check if the API file exists
if (file_exists('teachersassign_class_action.php')) {
    echo "<p style='color: green;'>✓ teachersassign_class_action.php exists</p>";
} else {
    echo "<p style='color: red;'>✗ teachersassign_class_action.php NOT found</p>";
}

// Test 2: Test GET request for teachers
echo "<h2>Testing GET requests:</h2>";

$url = 'http://localhost/ves/admin/dashboard/teachersassign_class_action.php?type=teachers';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . session_name() . '=' . session_id()
    ]
]);

$response = file_get_contents($url, false, $context);
echo "<h3>Teachers API Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test 3: Test POST request
echo "<h2>Testing POST request:</h2>";

$postData = http_build_query([
    'teacher' => 522,  // John Doe
    'class' => 3,      // Class II
    'section' => 3     // Section A
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                   'Cookie: ' . session_name() . '=' . session_id() . "\r\n",
        'content' => $postData
    ]
]);

$response = file_get_contents('http://localhost/ves/admin/dashboard/teachersassign_class_action.php', false, $context);
echo "<h3>Assignment POST Response:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test 4: Check what data is being sent
echo "<h2>Test Data Being Sent:</h2>";
echo "<pre>";
echo "teacher: 522 (John Doe)\n";
echo "class: 3 (Class II)\n";
echo "section: 3 (Section A)\n";
echo "</pre>";
?> 