<?php
session_start();
include 'con.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get the JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate input
if (!isset($data['type']) || !isset($data['ids']) || !is_array($data['ids']) || empty($data['ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

// Sanitize and validate the type
$valid_types = ['announcement', 'notice', 'message'];
$type = in_array($data['type'], $valid_types) ? $data['type'] : null;

if ($type === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid notification type']);
    exit();
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Prepare placeholders for the SQL query
$placeholders = implode(',', array_fill(0, count($data['ids']), '?'));

// Create insert statement for batch insertion
$query = "INSERT IGNORE INTO notification_read_status 
          (user_id, notification_type, notification_id, read_at) 
          VALUES ";

$values = [];
$params = [];
$types = '';

foreach ($data['ids'] as $id) {
    // Skip invalid IDs
    if (!is_numeric($id)) {
        continue;
    }
    
    $values[] = "(?, ?, ?, NOW())";
    $params[] = $user_id;
    $params[] = $type;
    $params[] = $id;
    $types .= "isi";
}

// If we have valid IDs to insert
if (!empty($values)) {
    $query .= implode(', ', $values);
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'marked' => count($values)]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark as read', 'details' => $conn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No valid notification IDs provided']);
}
?> 