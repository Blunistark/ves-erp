<?php
require_once 'con.php';
session_start();

// Prevent display of errors and warnings
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['user_id'];

try {
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Failed to connect to database');
    }
    
    // Get classes assigned to the teacher
    $sql = "SELECT DISTINCT c.id, c.name 
            FROM classes c 
            JOIN teacher_assignments ta ON c.id = ta.class_id 
            WHERE ta.teacher_user_id = ?
            ORDER BY c.name";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->bind_param('i', $teacher_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query');
    }
    
    $result = $stmt->get_result();
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'classes' => $classes]);
    
} catch (Exception $e) {
    error_log("Teacher classes error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'An error occurred while fetching classes'
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
} 