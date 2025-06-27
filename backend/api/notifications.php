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

// Log all API requests for debugging
$action = $_GET['action'] ?? $_POST['action'] ?? 'no_action';
$method = $_SERVER['REQUEST_METHOD'];
$user_role = $_SESSION['role'] ?? 'no_session';
$user_id = $_SESSION['user_id'] ?? 'no_user_id';
error_log("API Request: Method=$method, Action=$action, Role=$user_role, User=$user_id, URL=" . $_SERVER['REQUEST_URI']);

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
            
        case 'all_students_in_school':
            // For headmasters: target all students in the school
            $sql = "SELECT DISTINCT u.id FROM users u 
                    JOIN students s ON u.id = s.user_id 
                    WHERE u.status = 'active'";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $recipients[] = $row['id'];
                }
            } else {
                error_log("calculateRecipients - Error querying all_students_in_school: " . mysqli_error($conn));
            }
            break;
            
        case 'all_teachers_in_school':
            // For headmasters: target all teachers in the school
            $sql = "SELECT DISTINCT u.id FROM users u 
                    JOIN teachers t ON u.id = t.user_id 
                    WHERE u.status = 'active'";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $recipients[] = $row['id'];
                }
            } else {
                error_log("calculateRecipients - Error querying all_teachers_in_school: " . mysqli_error($conn));
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

// Deliver notification to all recipients
function deliverNotificationToRecipients($conn, $notification_id) {
    try {
        // Get notification and targeting details
        $sql = "SELECT n.*, nt.target_type, nt.target_value 
                FROM notifications n 
                LEFT JOIN notification_targets nt ON n.id = nt.notification_id 
                WHERE n.id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $notification_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$notification = mysqli_fetch_assoc($result)) {
            throw new Exception('Notification not found');
        }
        
        // Calculate recipients
        $recipients = calculateRecipients($conn, $notification['target_type'], $notification['target_value']);
        
        if (empty($recipients)) {
            return ['count' => 0, 'message' => 'No recipients found'];
        }
        
        // Create individual notification records for each recipient
        $delivery_count = 0;
        foreach ($recipients as $recipient_id) {
            $insert_sql = "INSERT INTO notifications (user_id, title, message, type, priority, requires_acknowledgment, created_by, expires_at, is_active, approval_status, created_at) 
                          SELECT ?, title, message, type, priority, requires_acknowledgment, created_by, expires_at, 1, 'published', NOW() 
                          FROM notifications WHERE id = ?";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "ii", $recipient_id, $notification_id);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $delivery_count++;
            }
        }
        
        return ['count' => $delivery_count, 'recipients' => $recipients];
        
    } catch (Exception $e) {
        error_log("Delivery error: " . $e->getMessage());
        throw $e;
    }
}

// Handle different API endpoints
switch ($method) {
    case 'GET':
        $auth = checkAuth();
        
        switch ($action) {
            case 'get_unread_count':
                $user_id = $auth['user_id']; // $auth is set at the start of 'GET' case

                $count_sql = "SELECT COUNT(n.id) as unread_count
                              FROM notifications n
                              LEFT JOIN notification_read_status nrs ON n.id = nrs.notification_id AND nrs.user_id = ?
                              WHERE n.is_active = 1 
                              AND (n.expires_at IS NULL OR n.expires_at > NOW())
                              AND nrs.user_id IS NULL";
                // Additional filtering based on user role/permissions for targeted notifications can be added here if necessary.
                
                $count_params = [$user_id];
                $count_param_types = "i";
                
                $stmt_count = mysqli_prepare($conn, $count_sql);
                if (!$stmt_count) {
                    sendNotificationResponse(false, null, 'Failed to prepare count statement: ' . mysqli_error($conn), 500);
                    exit;
                }
                mysqli_stmt_bind_param($stmt_count, $count_param_types, ...$count_params);
                mysqli_stmt_execute($stmt_count);
                $result_count = mysqli_stmt_get_result($stmt_count);
                $row_count = mysqli_fetch_assoc($result_count);
                
                if ($row_count) {
                    sendNotificationResponse(true, ['count' => (int)$row_count['unread_count']], 'Unread count fetched successfully.');
                } else {
                    sendNotificationResponse(false, ['count' => 0], 'Failed to fetch unread count. Error: ' . mysqli_error($conn), 500);
                }
                mysqli_stmt_close($stmt_count);
                break;

            case 'list':
                $user_id = $auth['user_id'];
                $user_role = $auth['role'];

                $page = (int)($_GET['page'] ?? 1);
                $limit = (int)($_GET['limit'] ?? 10); // Default limit for dropdown or general list
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit;

                $type = $_GET['type'] ?? ''; // For filtering by notification type e.g. 'alert', 'info'
                $filter = $_GET['filter'] ?? ''; // sent, received, all
                
                $status_filter = strtolower($_GET['status'] ?? ''); // e.g., 'unread', 'read', 'all'
                $unread_only = ($status_filter === 'unread');
                
                // Handle different filter types
                if ($filter === 'sent') {
                    // Get notifications created by this user
                    $sql = "
                        SELECT n.*, 
                               u.full_name as created_by_name,
                               0 as is_read,
                               0 as is_acknowledged,
                               CASE 
                                   WHEN na.status = 'approved' THEN 'Approved & Sent'
                                   WHEN na.status = 'rejected' THEN 'Rejected'
                                   WHEN na.status = 'pending' THEN 'Pending Approval'
                                   ELSE 'Unknown'
                               END as approval_status,
                               na.admin_comments,
                               na.created_at as submitted_at
                        FROM notifications n
                        LEFT JOIN users u ON n.created_by = u.id
                        LEFT JOIN notification_approvals na ON n.id = na.notification_id
                        WHERE n.created_by = ? AND n.is_active = 1
                    ";
                    
                    $params = [$user_id];
                    $param_types = "i";
                    
                } else if ($filter === 'received') {
                    // Get notifications targeted to this user - Fixed to avoid duplicates
                    $sql = "
                        SELECT DISTINCT n.*, 
                               u.full_name as created_by_name,
                               CASE WHEN nrs.user_id IS NOT NULL THEN 1 ELSE 0 END as is_read,
                               CASE WHEN na.user_id IS NOT NULL THEN 1 ELSE 0 END as is_acknowledged
                        FROM notifications n
                        LEFT JOIN users u ON n.created_by = u.id
                        LEFT JOIN notification_read_status nrs ON n.id = nrs.notification_id AND nrs.user_id = ?
                        LEFT JOIN notification_acknowledgments na ON n.id = na.notification_id AND na.user_id = ?
                        WHERE n.is_active = 1 
                        AND (n.expires_at IS NULL OR n.expires_at > NOW())
                        AND EXISTS (
                            SELECT 1 FROM notification_targets nt 
                            WHERE nt.notification_id = n.id 
                            AND (
                                nt.target_type = 'all_school'
                                OR (nt.target_type = 'role' AND nt.target_value = ?)
                                OR (nt.target_type = 'user' AND nt.target_value = ?)
                            )
                        )
                    ";
                    
                    $params = [$user_id, $user_id, $user_role, $user_id];
                    $param_types = "iisi";
                    
                } else {
                    // Default: all accessible notifications (existing logic)
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
                }
                
                if ($type) {
                    $sql .= " AND n.type = ?";
                    $params[] = $type;
                    $param_types .= "s";
                }
                
                if ($unread_only && $filter !== 'sent') { // Only apply unread filter for received notifications
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
                
            case 'get_pending_approvals':
                try {
                    // Only admins can view pending approvals (not headmasters)
                    if ($auth['role'] !== 'admin') {
                        sendNotificationResponse(false, null, 'Only admins can view pending approvals', 403);
                    }
                    
                    $sql = "SELECT 
                                na.id as approval_id,
                                na.notification_id,
                                na.teacher_id,
                                na.status,
                                na.teacher_request_message,
                                na.admin_comments,
                                na.submitted_at,
                                na.approved_at,
                                n.title,
                                n.message,
                                n.type,
                                n.priority,
                                n.requires_acknowledgment,
                                n.expires_at,
                                nt.target_type,
                                nt.target_value,
                                u.full_name as teacher_name,
                                u.email as teacher_email
                            FROM notification_approvals na
                            JOIN notifications n ON na.notification_id = n.id
                            LEFT JOIN notification_targets nt ON n.id = nt.notification_id
                            JOIN users u ON na.teacher_id = u.id
                            WHERE na.status = 'pending'
                            ORDER BY na.submitted_at DESC";
                    
                    $result = mysqli_query($conn, $sql);
                    $pending_approvals = [];
                    
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $pending_approvals[] = $row;
                        }
                    }
                    
                    sendNotificationResponse(true, $pending_approvals, 'Pending approvals retrieved successfully');
                    
                } catch (Exception $e) {
                    error_log("Get pending approvals error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
                break;
                
            case 'get_teacher_submissions':
                try {
                    // Teachers and headmasters can view their own submissions
                    if ($auth['role'] !== 'teacher' && $auth['role'] !== 'headmaster') {
                        sendNotificationResponse(false, null, 'Only teachers and headmasters can view their submissions', 403);
                    }
                    
                    $sql = "SELECT 
                                na.id as approval_id,
                                na.notification_id,
                                na.status,
                                na.teacher_request_message,
                                na.admin_comments,
                                na.submitted_at,
                                na.approved_at,
                                n.title,
                                n.message,
                                n.type,
                                n.priority,
                                n.approval_status,
                                nt.target_type,
                                nt.target_value,
                                CASE 
                                    WHEN au.full_name IS NOT NULL THEN au.full_name
                                    ELSE 'Pending Admin Review'
                                END as admin_name
                            FROM notification_approvals na
                            JOIN notifications n ON na.notification_id = n.id
                            LEFT JOIN notification_targets nt ON n.id = nt.notification_id
                            LEFT JOIN users au ON na.admin_id = au.id
                            WHERE na.teacher_id = ?
                            ORDER BY na.submitted_at DESC";
                    
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $auth['user_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    $submissions = [];
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $submissions[] = $row;
                        }
                    }
                    
                    sendNotificationResponse(true, $submissions, 'Teacher submissions retrieved successfully');
                    
                } catch (Exception $e) {
                    error_log("Get teacher submissions error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
                break;
                
            case 'get_approval_stats':
                try {
                    // Only admins can view approval stats
                    if ($auth['role'] !== 'admin') {
                        sendNotificationResponse(false, null, 'Only admins can view approval statistics', 403);
                    }
                    
                    $stats = [
                        'pending_count' => 0,
                        'approved_today' => 0,
                        'rejected_today' => 0
                    ];
                    
                    // Get pending approvals count
                    $pending_sql = "SELECT COUNT(*) as count FROM notification_approvals WHERE status = 'pending'";
                    $pending_result = mysqli_query($conn, $pending_sql);
                    if ($row = mysqli_fetch_assoc($pending_result)) {
                        $stats['pending_count'] = (int)$row['count'];
                    }
                    
                    // Get approved today count
                    $approved_sql = "SELECT COUNT(*) as count FROM notification_approvals 
                                   WHERE status = 'approved' AND DATE(approved_at) = CURDATE()";
                    $approved_result = mysqli_query($conn, $approved_sql);
                    if ($row = mysqli_fetch_assoc($approved_result)) {
                        $stats['approved_today'] = (int)$row['count'];
                    }
                    
                    // Get rejected today count
                    $rejected_sql = "SELECT COUNT(*) as count FROM notification_approvals 
                                   WHERE status = 'rejected' AND DATE(approved_at) = CURDATE()";
                    $rejected_result = mysqli_query($conn, $rejected_sql);
                    if ($row = mysqli_fetch_assoc($rejected_result)) {
                        $stats['rejected_today'] = (int)$row['count'];
                    }
                    
                    sendNotificationResponse(true, $stats, 'Approval statistics retrieved successfully');
                    
                } catch (Exception $e) {
                    error_log("Get approval stats error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
                break;
                
            case 'get_processed_approvals':
                try {
                    // Only admins can view processed approvals
                    if ($auth['role'] !== 'admin') {
                        sendNotificationResponse(false, null, 'Only admins can view processed approvals', 403);
                    }
                    
                    $status = $_GET['status'] ?? 'approved'; // approved or rejected
                    $limit = (int)($_GET['limit'] ?? 50);
                    
                    if (!in_array($status, ['approved', 'rejected'])) {
                        sendNotificationResponse(false, null, 'Invalid status parameter', 400);
                    }
                    
                    $sql = "SELECT na.*, n.title, n.message, n.type, n.priority, n.created_at,
                                   u.full_name as teacher_name, u.role as teacher_role,
                                   admin.full_name as admin_name
                            FROM notification_approvals na
                            JOIN notifications n ON na.notification_id = n.id
                            JOIN users u ON na.teacher_id = u.id
                            LEFT JOIN users admin ON na.admin_id = admin.id
                            WHERE na.status = ?
                            ORDER BY na.approved_at DESC
                            LIMIT ?";
                    
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $status, $limit);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    $approvals = [];
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $approvals[] = $row;
                        }
                    }
                    
                    sendNotificationResponse(true, $approvals, "Processed approvals retrieved successfully");
                    
                } catch (Exception $e) {
                    error_log("Get processed approvals error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
                break;
                
            default:
                sendNotificationResponse(false, null, 'Invalid action', 400);
        }
        break;
        
    case 'POST':
        $auth = checkAuth();
        
        switch ($action) {
            case 'create':
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
                    
                    // Check if user is a teacher or headmaster - redirect to approval workflow
                    if ($auth['role'] === 'teacher' || $auth['role'] === 'headmaster') {
                        // Teachers and headmasters must use the approval workflow
                        sendNotificationResponse(false, null, 'Teachers and headmasters must submit notifications for admin approval. Use the submit_for_approval endpoint instead.', 403);
                    }
                    
                    // Check user permissions (for admin/headmaster only now)
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
                    
                    // Validate headmaster-specific targeting permissions
                    if (($input['target_type'] === 'all_students_in_school' || $input['target_type'] === 'all_teachers_in_school') && !$permissions['can_target_all_school']) {
                        sendNotificationResponse(false, null, 'You do not have permission to target all students or teachers in the school', 403);
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
                
            case 'submit_for_approval':
                try {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    // Validate required fields
                    $required = ['title', 'message', 'type', 'priority', 'target_type'];
                    foreach ($required as $field) {
                        if (empty($input[$field])) {
                            sendNotificationResponse(false, null, "Field '$field' is required", 400);
                        }
                    }
                    
                    // Only teachers and headmasters can submit for approval
                    if ($auth['role'] !== 'teacher' && $auth['role'] !== 'headmaster') {
                        sendNotificationResponse(false, null, 'Only teachers and headmasters can submit notifications for approval', 403);
                    }
                    
                    // Check user permissions for targeting (even in approval workflow)
                    $permissions = getUserPermissions($conn, $auth['user_id'], $auth['role']);
                    
                    // Validate headmaster-specific targeting permissions
                    if (($input['target_type'] === 'all_students_in_school' || $input['target_type'] === 'all_teachers_in_school') && !$permissions['can_target_all_school']) {
                        sendNotificationResponse(false, null, 'You do not have permission to target all students or teachers in the school', 403);
                    }
                    
                    // Validate priority level even in approval workflow
                    $priority_levels = ['normal', 'important', 'urgent'];
                    $max_priority_index = array_search($permissions['max_priority_level'], $priority_levels);
                    $requested_priority_index = array_search($input['priority'], $priority_levels);
                    
                    if ($requested_priority_index > $max_priority_index) {
                        sendNotificationResponse(false, null, "You can only submit notifications up to '{$permissions['max_priority_level']}' priority", 403);
                    }
                    
                    mysqli_begin_transaction($conn);
                    
                    // Create notification in draft status
                    $sql = "INSERT INTO notifications (title, message, type, priority, requires_acknowledgment, created_by, expires_at, user_id, is_active, approval_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, 'pending_approval')";
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
                    
                    // Create approval request
                    $approval_sql = "INSERT INTO notification_approvals (notification_id, teacher_id, teacher_request_message, status) VALUES (?, ?, ?, 'pending')";
                    $approval_stmt = mysqli_prepare($conn, $approval_sql);
                    
                    if (!$approval_stmt) {
                        throw new Exception('Failed to prepare approval statement: ' . mysqli_error($conn));
                    }
                    
                    $request_message = $input['approval_message'] ?? 'Teacher requesting approval for notification';
                    mysqli_stmt_bind_param($approval_stmt, "iis", $notification_id, $auth['user_id'], $request_message);
                    
                    if (!mysqli_stmt_execute($approval_stmt)) {
                        throw new Exception('Failed to create approval request: ' . mysqli_error($conn));
                    }
                    
                    mysqli_commit($conn);
                    
                    sendNotificationResponse(true, [
                        'notification_id' => $notification_id,
                        'status' => 'pending_approval'
                    ], 'Notification submitted for admin approval');
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    error_log("Submit for approval error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
                break;
                
            case 'approve_notification':
                try {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    // Only admins can approve notifications (not headmasters)
                    if ($auth['role'] !== 'admin') {
                        sendNotificationResponse(false, null, 'Only admins can approve notifications', 403);
                    }
                    
                    $approval_id = $input['approval_id'] ?? 0;
                    $admin_comments = $input['admin_comments'] ?? '';
                    
                    if (!$approval_id) {
                        sendNotificationResponse(false, null, 'Approval ID is required', 400);
                    }
                    
                    mysqli_begin_transaction($conn);
                    
                    // Get the notification details
                    $get_sql = "SELECT na.notification_id, na.teacher_id, na.status 
                               FROM notification_approvals na 
                               WHERE na.id = ? AND na.status = 'pending'";
                    $get_stmt = mysqli_prepare($conn, $get_sql);
                    mysqli_stmt_bind_param($get_stmt, "i", $approval_id);
                    mysqli_stmt_execute($get_stmt);
                    $result = mysqli_stmt_get_result($get_stmt);
                    
                    if (!$approval_row = mysqli_fetch_assoc($result)) {
                        throw new Exception('Approval request not found or already processed');
                    }
                    
                    // Update approval status
                    $update_approval_sql = "UPDATE notification_approvals 
                                          SET status = 'approved', 
                                              admin_id = ?, 
                                              admin_comments = ?, 
                                              approved_at = NOW() 
                                          WHERE id = ?";
                    $update_approval_stmt = mysqli_prepare($conn, $update_approval_sql);
                    mysqli_stmt_bind_param($update_approval_stmt, "isi", $auth['user_id'], $admin_comments, $approval_id);
                    
                    if (!mysqli_stmt_execute($update_approval_stmt)) {
                        throw new Exception('Failed to update approval status: ' . mysqli_error($conn));
                    }
                    
                    // Update notification status and make it active
                    $update_notification_sql = "UPDATE notifications 
                                              SET approval_status = 'approved', 
                                                  is_active = 1 
                                              WHERE id = ?";
                    $update_notification_stmt = mysqli_prepare($conn, $update_notification_sql);
                    mysqli_stmt_bind_param($update_notification_stmt, "i", $approval_row['notification_id']);
                    
                    if (!mysqli_stmt_execute($update_notification_stmt)) {
                        throw new Exception('Failed to update notification status: ' . mysqli_error($conn));
                    }
                    
                    // Now deliver the notification to recipients
                    $delivery_result = deliverNotificationToRecipients($conn, $approval_row['notification_id']);
                    
                    mysqli_commit($conn);
                    
                    sendNotificationResponse(true, [
                        'notification_id' => $approval_row['notification_id'],
                        'delivered_to' => $delivery_result['count'] ?? 0
                    ], 'Notification approved and delivered successfully');
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    error_log("Approve notification error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
                break;
                
            case 'reject_notification':
                try {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (!$input) {
                        $input = $_POST;
                    }
                    
                    // Only admins can reject notifications (not headmasters)
                    if ($auth['role'] !== 'admin') {
                        sendNotificationResponse(false, null, 'Only admins can reject notifications', 403);
                    }
                    
                    $approval_id = $input['approval_id'] ?? 0;
                    $admin_comments = $input['admin_comments'] ?? 'Notification rejected by admin';
                    
                    if (!$approval_id) {
                        sendNotificationResponse(false, null, 'Approval ID is required', 400);
                    }
                    
                    mysqli_begin_transaction($conn);
                    
                    // Get the notification details
                    $get_sql = "SELECT na.notification_id, na.teacher_id, na.status 
                               FROM notification_approvals na 
                               WHERE na.id = ? AND na.status = 'pending'";
                    $get_stmt = mysqli_prepare($conn, $get_sql);
                    mysqli_stmt_bind_param($get_stmt, "i", $approval_id);
                    mysqli_stmt_execute($get_stmt);
                    $result = mysqli_stmt_get_result($get_stmt);
                    
                    if (!$approval_row = mysqli_fetch_assoc($result)) {
                        throw new Exception('Approval request not found or already processed');
                    }
                    
                    // Update approval status
                    $update_approval_sql = "UPDATE notification_approvals 
                                          SET status = 'rejected', 
                                              admin_id = ?, 
                                              admin_comments = ? 
                                          WHERE id = ?";
                    $update_approval_stmt = mysqli_prepare($conn, $update_approval_sql);
                    mysqli_stmt_bind_param($update_approval_stmt, "isi", $auth['user_id'], $admin_comments, $approval_id);
                    
                    if (!mysqli_stmt_execute($update_approval_stmt)) {
                        throw new Exception('Failed to update approval status: ' . mysqli_error($conn));
                    }
                    
                    // Update notification status
                    $update_notification_sql = "UPDATE notifications 
                                              SET approval_status = 'rejected' 
                                              WHERE id = ?";
                    $update_notification_stmt = mysqli_prepare($conn, $update_notification_sql);
                    mysqli_stmt_bind_param($update_notification_stmt, "i", $approval_row['notification_id']);
                    
                    if (!mysqli_stmt_execute($update_notification_stmt)) {
                        throw new Exception('Failed to update notification status: ' . mysqli_error($conn));
                    }
                    
                    mysqli_commit($conn);
                    
                    sendNotificationResponse(true, [
                        'notification_id' => $approval_row['notification_id']
                    ], 'Notification rejected successfully');
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    error_log("Reject notification error: " . $e->getMessage());
                    sendNotificationResponse(false, null, $e->getMessage(), 500);
                }
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
