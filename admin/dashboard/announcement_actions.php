<?php
// Set JSON header first
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Include the centralized database configuration file
require_once __DIR__ . '/../../includes/config.php';

// Get database connection
$conn = getDbConnection();

// Check if connection is successful
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get the action from request
$action = $_REQUEST['action'] ?? '';

try {
    switch($action) {
        case 'list':
            // Base query
            $query = "SELECT a.*, u.full_name as created_by_name 
                      FROM announcements a 
                      JOIN users u ON a.created_by = u.id 
                      WHERE 1=1";
            
            // Add filters if provided
            $params = [];
            $types = '';
            
            if (isset($_REQUEST['status']) && $_REQUEST['status'] !== '') {
                $query .= " AND a.is_active = ?";
                $params[] = $_REQUEST['status'] === 'active' ? 1 : 0;
                $types .= 'i';
            }
            
            if (isset($_REQUEST['priority']) && $_REQUEST['priority'] !== '') {
                $query .= " AND a.priority = ?";
                $params[] = $_REQUEST['priority'];
                $types .= 's';
            }

            if (isset($_REQUEST['target_audience']) && $_REQUEST['target_audience'] !== '') {
                $query .= " AND a.target_audience = ?";
                $params[] = $_REQUEST['target_audience'];
                $types .= 's';
            }
            
            // Add ordering
            $query .= " ORDER BY a.priority DESC, a.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $announcements = [];
            
            while ($row = $result->fetch_assoc()) {
                // Format dates for JSON
                $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
                if ($row['expiry_date']) {
                    $row['expiry_date'] = date('Y-m-d', strtotime($row['expiry_date']));
                }
                $announcements[] = $row;
            }
            
            echo json_encode(['success' => true, 'announcements' => $announcements]);
            break;
            
        case 'create':
            // Validate required fields
            $required_fields = ['title', 'content', 'target_audience', 'priority'];
            $missing_fields = [];
            
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Missing required fields',
                    'fields' => $missing_fields
                ]);
                exit;
            }
            
            // Sanitize and get input data
            $title = trim($_POST['title']);
            $content = $_POST['content']; // Allow HTML content
            $target_audience = trim($_POST['target_audience']);
            $priority = trim($_POST['priority']);
            $expiry_date = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
            $created_by = $_SESSION['user_id'];
            
            // Validate target audience
            if (!in_array($target_audience, ['all', 'teachers', 'students'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid target audience']);
                exit;
            }
            
            // Validate priority
            if (!in_array($priority, ['normal', 'important', 'urgent'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid priority']);
                exit;
            }
            
            // Validate expiry date if provided
            if ($expiry_date !== null) {
                $expiry_timestamp = strtotime($expiry_date);
                if ($expiry_timestamp === false || $expiry_timestamp < strtotime('today')) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid expiry date']);
                    exit;
                }
            }
            
            // Insert announcement
            $query = "INSERT INTO announcements (title, content, created_by, target_audience, priority, expiry_date, created_at, is_active) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("ssssss", $title, $content, $created_by, $target_audience, $priority, $expiry_date);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create announcement: " . $stmt->error);
            }
            
            $announcement_id = $stmt->insert_id;
            
            // Fetch the created announcement
            $fetch_query = "SELECT a.*, u.full_name as created_by_name 
                          FROM announcements a 
                          JOIN users u ON a.created_by = u.id 
                          WHERE a.id = ?";
            
            $fetch_stmt = $conn->prepare($fetch_query);
            if (!$fetch_stmt) {
                throw new Exception("Failed to prepare fetch statement: " . $conn->error);
            }
            
            $fetch_stmt->bind_param("i", $announcement_id);
            
            if (!$fetch_stmt->execute()) {
                throw new Exception("Failed to fetch created announcement: " . $fetch_stmt->error);
            }
            
            $result = $fetch_stmt->get_result();
            $created_announcement = $result->fetch_assoc();
            
            // Format dates for JSON
            $created_announcement['created_at'] = date('Y-m-d H:i:s', strtotime($created_announcement['created_at']));
            if ($created_announcement['expiry_date']) {
                $created_announcement['expiry_date'] = date('Y-m-d', strtotime($created_announcement['expiry_date']));
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Announcement created successfully',
                'announcement' => $created_announcement
            ]);
            break;
            
        case 'delete':
            // Validate announcement ID
            if (!isset($_POST['announcement_id']) || !is_numeric($_POST['announcement_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid announcement ID']);
                exit;
            }
            
            $announcement_id = (int)$_POST['announcement_id'];
            
            // Delete the announcement
            $delete_query = "DELETE FROM announcements WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            if (!$delete_stmt) {
                throw new Exception("Failed to prepare delete statement: " . $conn->error);
            }
            
            $delete_stmt->bind_param("i", $announcement_id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to delete announcement: " . $delete_stmt->error);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);
            break;

        case 'toggle_status':
            // Validate announcement ID
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid announcement ID']);
                exit;
            }
            
            $announcement_id = (int)$_POST['id'];
            
            // Toggle the status
            $query = "UPDATE announcements SET is_active = NOT is_active WHERE id = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("i", $announcement_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to toggle announcement status: " . $stmt->error);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Announcement status updated successfully'
            ]);
            break;

        case 'get_announcement':
            // Validate announcement ID
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid announcement ID']);
                exit;
            }
            
            $announcement_id = (int)$_GET['id'];
            
            // Get the announcement
            $query = "SELECT a.*, u.full_name as created_by_name 
                     FROM announcements a 
                     JOIN users u ON a.created_by = u.id 
                     WHERE a.id = ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("i", $announcement_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to fetch announcement: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $announcement = $result->fetch_assoc();
            
            if (!$announcement) {
                http_response_code(404);
                echo json_encode(['error' => 'Announcement not found']);
                exit;
            }
            
            // Format dates for JSON
            $announcement['created_at'] = date('Y-m-d H:i:s', strtotime($announcement['created_at']));
            if ($announcement['expiry_date']) {
                $announcement['expiry_date'] = date('Y-m-d', strtotime($announcement['expiry_date']));
            }
            
            echo json_encode($announcement);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}