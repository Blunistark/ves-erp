<?php
// Start session first to avoid header conflicts
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Enable error reporting for debugging (but don't display for API requests)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

// Include database connection - fix the path
try {
    require_once __DIR__ . '/../../teachers/dashboard/con.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to include database connection: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Check database connection
if (!$conn || mysqli_connect_error()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Response helper function
function sendNotificationResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Authentication check
function checkAuth() {
    // Session should already be started
    if (!isset($_SESSION['user_id'])) {
        sendNotificationResponse(false, null, 'Authentication required', 401);
    }
    return [
        'user_id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'] ?? 'user'
    ];
}

// Get user permissions
function getUserPermissions($conn, $user_id, $user_type) {
    $sql = "SELECT * FROM notification_permissions WHERE user_id = ? AND user_type = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $user_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row;
    }
      // Default permissions for user types
    $defaults = [
        'admin' => [
            'can_create_notifications' => 1,
            'can_target_all_school' => 1,
            'can_target_other_classes' => 1,
            'can_schedule_notifications' => 1,
            'can_require_acknowledgment' => 1,
            'max_priority_level' => 'urgent'
        ],
        'headmaster' => [
            'can_create_notifications' => 1,
            'can_target_all_school' => 1,
            'can_target_other_classes' => 1,
            'can_schedule_notifications' => 1,
            'can_require_acknowledgment' => 1,
            'max_priority_level' => 'urgent'
        ],
        'teacher' => [
            'can_create_notifications' => 1,
            'can_target_all_school' => 0,
            'can_target_other_classes' => 0,
            'can_schedule_notifications' => 1,
            'can_require_acknowledgment' => 1,
            'max_priority_level' => 'important'
        ],
        'student' => [
            'can_create_notifications' => 0,
            'can_target_all_school' => 0,
            'can_target_other_classes' => 0,
            'can_schedule_notifications' => 0,
            'can_require_acknowledgment' => 0,
            'max_priority_level' => 'normal'
        ]
    ];
    
    return $defaults[$user_type] ?? $defaults['student'];
}

// Calculate notification recipients
function calculateRecipients($conn, $target_type, $target_value) {
    $recipients = [];
    
    error_log("calculateRecipients - Type: $target_type, Value: $target_value");
    
    switch ($target_type) {
        case 'all_school':
            $sql = "SELECT id FROM users WHERE status = 'active'";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $recipients[] = $row['id'];
                }
            } else {
                error_log("calculateRecipients - Error querying all_school: " . mysqli_error($conn));
            }
            break;
              case 'role':
        case 'role_based':
            // Handle multiple roles separated by comma
            $roles = explode(',', $target_value);
            $union_queries = [];
              foreach ($roles as $role) {
                $role = trim($role);
                if ($role === 'teachers') {
                    $union_queries[] = "SELECT u.id FROM users u JOIN teachers t ON u.id = t.user_id WHERE u.status = 'active'";
                } elseif ($role === 'students') {
                    $union_queries[] = "SELECT u.id FROM users u JOIN students s ON u.id = s.user_id WHERE u.status = 'active'";
                } elseif ($role === 'staff' || $role === 'admin') {
                    $union_queries[] = "SELECT u.id FROM users u JOIN admins a ON u.id = a.id WHERE u.status = 'active'";
                } else {
                    error_log("calculateRecipients - Unknown role: $role");
                }
            }
            
            if (!empty($union_queries)) {
                $sql = implode(' UNION ', $union_queries);
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $recipients[] = $row['id'];
                    }
                } else {
                    error_log("calculateRecipients - Error querying roles: " . mysqli_error($conn));
                }
            }
            break;
            
        case 'class':
            $class_id = str_replace('class_', '', $target_value);
            $sql = "SELECT DISTINCT u.id FROM users u 
                    JOIN students s ON u.id = s.user_id 
                    JOIN enrollments e ON s.user_id = e.student_id 
                    WHERE e.class_id = ? AND u.status = 'active'";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $class_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $recipients[] = $row['id'];
                }
            } else {
                error_log("calculateRecipients - Error preparing class query: " . mysqli_error($conn));
            }
            break;
            
        case 'section':
            $section_id = str_replace('section_', '', $target_value);
            $sql = "SELECT DISTINCT u.id FROM users u 
                    JOIN students s ON u.id = s.user_id 
                    JOIN enrollments e ON s.user_id = e.student_id 
                    WHERE e.section_id = ? AND u.status = 'active'";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $section_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $recipients[] = $row['id'];
                }
            } else {
                error_log("calculateRecipients - Error preparing section query: " . mysqli_error($conn));
            }
            break;
              case 'individual':
            // Handle comma-separated list of user IDs
            $user_ids = explode(',', $target_value);
            $valid_ids = [];
            
            foreach ($user_ids as $user_id) {
                $user_id = (int)trim($user_id);
                if ($user_id > 0) {
                    $valid_ids[] = $user_id;
                }
            }
            
            if (!empty($valid_ids)) {
                $placeholders = str_repeat('?,', count($valid_ids) - 1) . '?';
                $sql = "SELECT id FROM users WHERE id IN ($placeholders) AND status = 'active'";
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    $types = str_repeat('i', count($valid_ids));
                    mysqli_stmt_bind_param($stmt, $types, ...$valid_ids);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $recipients[] = $row['id'];
                    }
                } else {
                    error_log("calculateRecipients - Error preparing individual query: " . mysqli_error($conn));
                }
            }
            break;
            
        case 'multiple_classes':
            // Handle multiple class/section combinations
            $class_sections = explode(',', $target_value);
            foreach ($class_sections as $class_section) {
                $class_section = trim($class_section);
                if (preg_match('/class_(\d+)_section_(\d+)/', $class_section, $matches)) {
                    $class_id = $matches[1];
                    $section_id = $matches[2];
                    
                    $sql = "SELECT DISTINCT u.id FROM users u 
                            JOIN students s ON u.id = s.user_id 
                            JOIN enrollments e ON s.user_id = e.student_id 
                            WHERE e.class_id = ? AND e.section_id = ? AND u.status = 'active'";
                    $stmt = mysqli_prepare($conn, $sql);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "ii", $class_id, $section_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $recipients[] = $row['id'];
                        }
                    } else {
                        error_log("calculateRecipients - Error preparing multiple_classes query: " . mysqli_error($conn));
                    }
                } else {
                    error_log("calculateRecipients - Invalid class_section format: $class_section");
                }
            }
            break;
            
        default:
            error_log("calculateRecipients - Unknown target_type: $target_type");
            break;
    }
    
    $unique_recipients = array_unique($recipients);
    error_log("calculateRecipients - Found " . count($unique_recipients) . " recipients");
    
    return $unique_recipients;
}

// Update notification analytics
function updateNotificationAnalytics($conn, $notification_id) {
    $sql = "
        INSERT INTO notification_analytics (notification_id, total_recipients, total_read, total_acknowledged)
        SELECT 
            nt.notification_id,
            COUNT(DISTINCT nt.id) as total_recipients,
            COUNT(DISTINCT nrs.user_id) as total_read,
            COUNT(DISTINCT na.user_id) as total_acknowledged
        FROM notification_targets nt
        LEFT JOIN notification_read_status nrs ON nt.notification_id = nrs.notification_id
        LEFT JOIN notification_acknowledgments na ON nt.notification_id = na.notification_id
        WHERE nt.notification_id = ?
        GROUP BY nt.notification_id
        ON DUPLICATE KEY UPDATE
            total_read = VALUES(total_read),
            total_acknowledged = VALUES(total_acknowledged),
            last_updated = CURRENT_TIMESTAMP
    ";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $notification_id);
    return mysqli_stmt_execute($stmt);
}

// Handle different API endpoints
switch ($method) {
    case 'GET':
        $auth = checkAuth();
        
        switch ($action) {
            case 'list':
                $user_id = $auth['user_id'];
                $limit = (int)($_GET['limit'] ?? 50);
                $offset = (int)($_GET['offset'] ?? 0);
                $type = $_GET['type'] ?? '';
                $unread_only = $_GET['unread_only'] ?? false;
                
                $sql = "
                    SELECT n.*, 
                           u.full_name as created_by_name,
                           CASE WHEN nrs.user_id IS NOT NULL THEN 1 ELSE 0 END as is_read,
                           CASE WHEN na.user_id IS NOT NULL THEN 1 ELSE 0 END as is_acknowledged
                    FROM notifications n
                    LEFT JOIN users u ON n.created_by = u.id
                    LEFT JOIN notification_read_status nrs ON n.id = nrs.notification_id AND nrs.user_id = ?
                    LEFT JOIN notification_acknowledgments na ON n.id = na.notification_id AND na.user_id = ?
                    WHERE n.is_active = 1 
                    AND (n.expires_at IS NULL OR n.expires_at > NOW())
                ";
                
                $params = [$user_id, $user_id];
                $param_types = "ii";
                
                if ($type) {
                    $sql .= " AND n.type = ?";
                    $params[] = $type;
                    $param_types .= "s";
                }
                
                if ($unread_only) {
                    $sql .= " AND nrs.user_id IS NULL";
                }
                
                $sql .= " ORDER BY n.priority DESC, n.created_at DESC LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                $param_types .= "ii";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, $param_types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                $notifications = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $notifications[] = $row;
                }
                
                sendNotificationResponse(true, $notifications);
                break;
                
            case 'count':
                $user_id = $auth['user_id'];
                $sql = "
                    SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN nrs.user_id IS NULL THEN 1 END) as unread
                    FROM notifications n
                    LEFT JOIN notification_read_status nrs ON n.id = nrs.notification_id AND nrs.user_id = ?
                    WHERE n.is_active = 1 
                    AND (n.expires_at IS NULL OR n.expires_at > NOW())
                ";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $counts = mysqli_fetch_assoc($result);
                
                sendNotificationResponse(true, $counts);
                break;
                
            case 'permissions':
                $user_id = $auth['user_id'];
                $user_type = $auth['role'];
                $permissions = getUserPermissions($conn, $user_id, $user_type);
                sendNotificationResponse(true, $permissions);
                break;
                
            case 'analytics':
                if ($auth['role'] !== 'admin') {
                    sendNotificationResponse(false, null, 'Admin access required', 403);
                }
                
                $notification_id = (int)($_GET['notification_id'] ?? 0);
                if ($notification_id) {
                    $sql = "SELECT * FROM notification_analytics WHERE notification_id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $notification_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $analytics = mysqli_fetch_assoc($result);
                    sendNotificationResponse(true, $analytics);
                } else {
                    // Overall analytics
                    $sql = "
                        SELECT 
                            COUNT(*) as total_notifications,
                            AVG(read_rate) as avg_read_rate,
                            AVG(acknowledgment_rate) as avg_ack_rate,
                            COUNT(CASE WHEN n.priority = 'urgent' THEN 1 END) as urgent_count
                        FROM notification_analytics na
                        JOIN notifications n ON na.notification_id = n.id
                        WHERE n.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ";
                    $result = mysqli_query($conn, $sql);
                    $analytics = mysqli_fetch_assoc($result);
                    sendNotificationResponse(true, $analytics);
                }
                break;
                
            default:
                sendNotificationResponse(false, null, 'Invalid action', 400);
        }
        break;
        
    case 'POST':
        $auth = checkAuth();
        
        switch ($action) {            case 'create':
                try {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    // Log the input for debugging
                    error_log("Notification API - Input received: " . json_encode($input));
                    
                    // Validate required fields
                    $required = ['title', 'message', 'type', 'priority', 'target_type'];
                    foreach ($required as $field) {
                        if (empty($input[$field])) {
                            sendNotificationResponse(false, null, "Field '$field' is required", 400);
                        }
                    }
                    
                    // Check user permissions
                    $permissions = getUserPermissions($conn, $auth['user_id'], $auth['role']);
                    if (!$permissions['can_create_notifications']) {
                        sendNotificationResponse(false, null, 'You do not have permission to create notifications', 403);
                    }
                    
                    // Validate priority level
                    $priority_levels = ['normal', 'important', 'urgent'];
                    $max_priority_index = array_search($permissions['max_priority_level'], $priority_levels);
                    $requested_priority_index = array_search($input['priority'], $priority_levels);
                    
                    if ($requested_priority_index > $max_priority_index) {
                        sendNotificationResponse(false, null, "You can only create notifications up to '{$permissions['max_priority_level']}' priority", 403);
                    }
                    
                    // Validate targeting permissions
                    if ($input['target_type'] === 'all_school' && !$permissions['can_target_all_school']) {
                        sendNotificationResponse(false, null, 'You do not have permission to target the entire school', 403);
                    }
                    
                    error_log("Notification API - Starting database transaction");
                    
                    mysqli_begin_transaction($conn);
                    
                    // Insert notification
                    $sql = "INSERT INTO notifications (title, message, type, priority, requires_acknowledgment, created_by, expires_at, user_id, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)";
                    $stmt = mysqli_prepare($conn, $sql);
                    
                    if (!$stmt) {
                        throw new Exception('Failed to prepare notification statement: ' . mysqli_error($conn));
                    }
                    
                    $requires_ack = $input['requires_acknowledgment'] ?? 0;
                    $expires_at = !empty($input['expires_at']) ? $input['expires_at'] : null;
                    
                    mysqli_stmt_bind_param($stmt, "ssssiss", 
                        $input['title'], 
                        $input['message'], 
                        $input['type'], 
                        $input['priority'], 
                        $requires_ack, 
                        $auth['user_id'], 
                        $expires_at
                    );
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception('Failed to create notification: ' . mysqli_error($conn));
                    }
                    
                    $notification_id = mysqli_insert_id($conn);
                    error_log("Notification API - Created notification ID: $notification_id");
                    
                    // Insert targeting
                    $target_sql = "INSERT INTO notification_targets (notification_id, target_type, target_value) VALUES (?, ?, ?)";
                    $target_stmt = mysqli_prepare($conn, $target_sql);
                    
                    if (!$target_stmt) {
                        throw new Exception('Failed to prepare target statement: ' . mysqli_error($conn));
                    }
                    
                    $target_value = $input['target_value'] ?? '';
                    mysqli_stmt_bind_param($target_stmt, "iss", $notification_id, $input['target_type'], $target_value);
                    
                    if (!mysqli_stmt_execute($target_stmt)) {
                        throw new Exception('Failed to set notification targets: ' . mysqli_error($conn));
                    }
                    
                    // Calculate and store initial analytics
                    $recipients = calculateRecipients($conn, $input['target_type'], $target_value);
                    $recipient_count = count($recipients);
                    
                    error_log("Notification API - Calculated $recipient_count recipients");
                    
                    $analytics_sql = "INSERT INTO notification_analytics (notification_id, total_recipients) VALUES (?, ?)";
                    $analytics_stmt = mysqli_prepare($conn, $analytics_sql);
                    
                    if (!$analytics_stmt) {
                        throw new Exception('Failed to prepare analytics statement: ' . mysqli_error($conn));
                    }
                    
                    mysqli_stmt_bind_param($analytics_stmt, "ii", $notification_id, $recipient_count);
                    
                    if (!mysqli_stmt_execute($analytics_stmt)) {
                        throw new Exception('Failed to insert analytics: ' . mysqli_error($conn));
                    }
                    
                    // Handle scheduling if specified
                    if (!empty($input['scheduled_for'])) {
                        $queue_sql = "INSERT INTO notification_queue (notification_id, scheduled_for) VALUES (?, ?)";
                        $queue_stmt = mysqli_prepare($conn, $queue_sql);
                        
                        if (!$queue_stmt) {
                            throw new Exception('Failed to prepare queue statement: ' . mysqli_error($conn));
                        }
                        
                        mysqli_stmt_bind_param($queue_stmt, "is", $notification_id, $input['scheduled_for']);
                        
                        if (!mysqli_stmt_execute($queue_stmt)) {
                            throw new Exception('Failed to insert into queue: ' . mysqli_error($conn));
                        }
                    }
                    
                    mysqli_commit($conn);
                    error_log("Notification API - Transaction committed successfully");
                    
                    sendNotificationResponse(true, [
                        'notification_id' => $notification_id,
                        'recipients_count' => $recipient_count
                    ], 'Notification created successfully');
                    
                } catch (Exception $e) {
                    error_log("Notification API - Exception caught: " . $e->getMessage());
                    error_log("Notification API - Stack trace: " . $e->getTraceAsString());
                    
                    if (isset($conn)) {
                        mysqli_rollback($conn);
                    }
                    
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                } catch (Error $e) {
                    error_log("Notification API - Fatal error: " . $e->getMessage());
                    error_log("Notification API - Stack trace: " . $e->getTraceAsString());
                    
                    if (isset($conn)) {
                        mysqli_rollback($conn);
                    }
                    
                    sendNotificationResponse(false, null, 'Internal server error: ' . $e->getMessage(), 500);
                }
                break;
                
            case 'mark_read':
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    $input = $_POST;
                }
                
                $notification_id = (int)($input['notification_id'] ?? 0);
                if (!$notification_id) {
                    sendNotificationResponse(false, null, 'Notification ID is required', 400);
                }
                
                $sql = "INSERT IGNORE INTO notification_read_status (user_id, notification_type, notification_id) 
                        VALUES (?, 'announcement', ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $auth['user_id'], $notification_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    updateNotificationAnalytics($conn, $notification_id);
                    sendNotificationResponse(true, null, 'Notification marked as read');
                } else {
                    sendNotificationResponse(false, null, 'Failed to mark notification as read', 500);
                }
                break;
                
            case 'acknowledge':
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    $input = $_POST;
                }
                
                $notification_id = (int)($input['notification_id'] ?? 0);
                if (!$notification_id) {
                    sendNotificationResponse(false, null, 'Notification ID is required', 400);
                }
                  $sql = "INSERT IGNORE INTO notification_acknowledgments (notification_id, user_id) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $notification_id, $auth['user_id']);
                
                if (mysqli_stmt_execute($stmt)) {
                    updateNotificationAnalytics($conn, $notification_id);
                    sendNotificationResponse(true, null, 'Notification acknowledged');
                } else {
                    sendNotificationResponse(false, null, 'Failed to acknowledge notification', 500);
                }
                break;
                
            case 'search_users':
                // Only allow headmasters/admins to search users
                if ($auth['role'] !== 'headmaster' && $auth['role'] !== 'admin') {
                    sendNotificationResponse(false, null, 'Admin access required', 403);
                }
                  $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    $input = $_POST;
                }
                
                // Handle query parameter from both JSON body and query string
                $query = '';
                if (isset($input['query']) && is_string($input['query'])) {
                    $query = trim($input['query']);
                } elseif (isset($_GET['query']) && is_string($_GET['query'])) {
                    $query = trim($_GET['query']);
                } elseif (isset($_POST['query']) && is_string($_POST['query'])) {
                    $query = trim($_POST['query']);
                }
                
                if (strlen($query) < 2) {
                    sendNotificationResponse(false, null, 'Query must be at least 2 characters', 400);
                }
                
                // Search for users by name or email
                $searchQuery = '%' . $query . '%';
                $sql = "
                    SELECT DISTINCT u.id, u.full_name as name, u.email, 
                           CASE 
                               WHEN t.user_id IS NOT NULL THEN 'teacher'
                               WHEN s.user_id IS NOT NULL THEN 'student'
                               WHEN a.id IS NOT NULL THEN 'admin'
                               ELSE 'unknown'
                           END as role
                    FROM users u
                    LEFT JOIN teachers t ON u.id = t.user_id
                    LEFT JOIN students s ON u.id = s.user_id
                    LEFT JOIN admins a ON u.id = a.id
                    WHERE u.status = 'active' 
                    AND (u.full_name LIKE ? OR u.email LIKE ?)
                    ORDER BY u.full_name
                    LIMIT 20
                ";
                
                $stmt = mysqli_prepare($conn, $sql);
                if (!$stmt) {
                    sendNotificationResponse(false, null, 'Failed to prepare search query', 500);
                }
                
                mysqli_stmt_bind_param($stmt, "ss", $searchQuery, $searchQuery);
                if (!mysqli_stmt_execute($stmt)) {
                    sendNotificationResponse(false, null, 'Failed to execute search query', 500);
                }
                
                $result = mysqli_stmt_get_result($stmt);
                $users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }
                
                sendNotificationResponse(true, $users);
                break;
                
            default:
                sendNotificationResponse(false, null, 'Invalid action', 400);
        }
        break;
        
    case 'PUT':
        $auth = checkAuth();
        
        switch ($action) {
            case 'update':
                if ($auth['role'] !== 'admin') {
                    sendNotificationResponse(false, null, 'Admin access required', 403);
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $notification_id = (int)($input['notification_id'] ?? 0);
                
                if (!$notification_id) {
                    sendNotificationResponse(false, null, 'Notification ID is required', 400);
                }
                
                $updates = [];
                $params = [];
                $param_types = "";
                
                $allowed_fields = ['title', 'message', 'priority', 'expires_at', 'is_active'];
                foreach ($allowed_fields as $field) {
                    if (isset($input[$field])) {
                        $updates[] = "$field = ?";
                        $params[] = $input[$field];
                        $param_types .= "s";
                    }
                }
                
                if (empty($updates)) {
                    sendNotificationResponse(false, null, 'No valid fields to update', 400);
                }
                
                $params[] = $notification_id;
                $param_types .= "i";
                
                $sql = "UPDATE notifications SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, $param_types, ...$params);
                
                if (mysqli_stmt_execute($stmt)) {
                    sendNotificationResponse(true, null, 'Notification updated successfully');
                } else {
                    sendNotificationResponse(false, null, 'Failed to update notification', 500);
                }
                break;
                
            default:
                sendNotificationResponse(false, null, 'Invalid action', 400);
        }
        break;
        
    case 'DELETE':
        $auth = checkAuth();
        
        switch ($action) {
            case 'delete':
                if ($auth['role'] !== 'admin') {
                    sendNotificationResponse(false, null, 'Admin access required', 403);
                }
                
                $notification_id = (int)($_GET['notification_id'] ?? 0);
                if (!$notification_id) {
                    sendNotificationResponse(false, null, 'Notification ID is required', 400);
                }
                
                $sql = "UPDATE notifications SET is_active = 0 WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $notification_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    sendNotificationResponse(true, null, 'Notification deleted successfully');
                } else {
                    sendNotificationResponse(false, null, 'Failed to delete notification', 500);
                }
                break;
                
            default:
                sendNotificationResponse(false, null, 'Invalid action', 400);
        }
        break;
        
    default:
        sendNotificationResponse(false, null, 'Method not allowed', 405);
}
?>
