<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../../logs/notification_errors.log');

session_start();

// Set content type to JSON
header('Content-Type: application/json');

try {
    include 'con.php';

    // Check if user is logged in and is a teacher
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'headmaster'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    switch ($action) {
    case 'get_notifications':
        try {
            $limit = $_GET['limit'] ?? 10;
            $offset = $_GET['offset'] ?? 0;
            
            $query = "SELECT id, title, message, type, priority, created_at, is_read, created_by
                      FROM notifications 
                      WHERE user_id = ? AND is_active = 1
                      ORDER BY created_at DESC 
                      LIMIT ? OFFSET ?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("iii", $user_id, $limit, $offset);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $notifications[] = [
                    'id' => $row['id'],
                    'title' => $row['title'] ?: 'Notification',
                    'message' => $row['message'],
                    'type' => $row['type'],
                    'priority' => $row['priority'],
                    'created_at' => $row['created_at'],
                    'is_read' => (bool)$row['is_read'],
                    'created_by' => $row['created_by'],
                    'time_ago' => getTimeAgo($row['created_at'])
                ];
            }
            
            echo json_encode(['notifications' => $notifications]);
        } catch (Exception $e) {
            error_log("Notification API Error (get_notifications): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'get_unread_count':
        try {
            $query = "SELECT COUNT(*) as count FROM notifications 
                      WHERE user_id = ? AND is_read = 0 AND is_active = 1";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            echo json_encode(['count' => (int)$row['count']]);
        } catch (Exception $e) {
            error_log("Notification API Error (get_unread_count): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'mark_as_read':
        $notification_id = $_POST['notification_id'] ?? null;
        
        if (!$notification_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Notification ID required']);
            exit();
        }
        
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $notification_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to mark as read']);
        }
        break;
        
    case 'mark_all_as_read':
        try {
            $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Notification API Error (mark_all_as_read): " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

} catch (Exception $e) {
    error_log("Notification API Critical Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time / 60) . ' min ago';
    if ($time < 86400) return floor($time / 3600) . ' hrs ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>
