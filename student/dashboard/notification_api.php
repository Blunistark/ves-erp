<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Include database connection
require_once 'con.php';

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_notifications':
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;
        
        $stmt = $conn->prepare("
            SELECT id, title, message, type, priority, is_read, created_at 
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        
        // Format the created_at field for better display
        foreach($notifications as &$notification) {
            $notification['created_at_formatted'] = date('M j, Y g:i A', strtotime($notification['created_at']));
            $notification['time_ago'] = timeAgo($notification['created_at']);
        }
        
        echo json_encode(['notifications' => $notifications]);
        break;
        
    case 'get_unread_count':
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo json_encode(['unread_count' => $row['count']]);
        break;
        
    case 'mark_as_read':
        $notification_id = $_POST['notification_id'] ?? 0;
        
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $notification_id, $user_id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
        break;
        
    case 'mark_all_as_read':
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hr ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>
