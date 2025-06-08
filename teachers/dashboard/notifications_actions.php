<?php
require_once 'con.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

switch ($action) {
    case 'list_notifications':
        listNotifications();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        exit();
}

function listNotifications() {
    global $conn, $user_id;
    try {
        $stmt = $conn->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $notifications]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
