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
    
    // Get subjects assigned to the teacher
    $sql = "SELECT DISTINCT s.id, s.name 
            FROM subjects s 
            JOIN teacher_assignments ta ON s.id = ta.subject_id 
            WHERE ta.teacher_user_id = ?
            ORDER BY s.name";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->bind_param('i', $teacher_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query');
    }
    
    $result = $stmt->get_result();
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'subjects' => $subjects]);
    
} catch (Exception $e) {
    error_log("Teacher subjects error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'An error occurred while fetching subjects'
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
} 