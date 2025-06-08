<?php
require_once 'con.php';
header('Content-Type: application/json');

// Enable output compression
if (extension_loaded('zlib') && !ob_get_level()) {
    ob_start('ob_gzhandler');
}

function log_error($msg) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    file_put_contents($log_dir . '/fetch_teachers_debug.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
}

try {
    // Get parameters with validation
    $search = trim($_GET['search'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = max(1, min(100, intval($_GET['per_page'] ?? 10)));
    $designation = trim($_GET['designation'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $qualification = trim($_GET['qualification'] ?? '');
    
    // Create cache key for this query
    $cache_key = md5(serialize([$search, $page, $per_page, $designation, $status, $qualification]));
    $cache_file = __DIR__ . '/cache/teachers_' . $cache_key . '.json';
    $cache_duration = 300; // 5 minutes
    
    // Check cache first
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        echo file_get_contents($cache_file);
        exit;
    }
    
    // Build optimized WHERE clause
    $where_conditions = ["u.role = 'teacher'"]; // Always filter for teachers
    $params = [];
    $types = '';
    
    // Optimized search functionality
    if (!empty($search)) {
        // Use FULLTEXT search if available, otherwise optimized LIKE
        if (strlen($search) >= 3) {
            $where_conditions[] = "(
                u.full_name LIKE ? OR 
                u.email LIKE ? OR 
                t.employee_number LIKE ? OR 
                t.qualification LIKE ?
            )";
            $search_param = "%$search%";
            $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
            $types .= 'ssss';
        } else {
            // For short searches, use prefix matching which is faster
            $where_conditions[] = "(
                u.full_name LIKE ? OR 
                t.employee_number LIKE ?
            )";
            $search_prefix = "$search%";
            $params = array_merge($params, [$search_prefix, $search_prefix]);
            $types .= 'ss';
        }
    }
    
    // Status filter (indexed)
    if (!empty($status)) {
        $where_conditions[] = "u.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Qualification filter (indexed)
    if (!empty($qualification)) {
        $where_conditions[] = "t.qualification = ?";
        $params[] = $qualification;
        $types .= 's';
    }
    
    // Build the WHERE clause
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    // Single optimized query with COUNT using window function
    $sql = "
        SELECT 
            t.user_id as id,
            u.full_name,
            u.email,
            u.status as user_status,
            t.employee_number,
            t.qualification,
            t.date_of_birth,
            t.joined_date,
            t.address,
            t.city,
            t.profile_photo,
            COUNT(*) OVER() as total_count
        FROM teachers t 
        INNER JOIN users u ON t.user_id = u.id 
        $where_sql
        ORDER BY u.full_name ASC
        LIMIT ? OFFSET ?
    ";
    
    // Add pagination parameters
    $final_params = $params;
    $final_types = $types . 'ii';
    $final_params[] = $per_page;
    $final_params[] = $offset;
    
    // Execute optimized query
    $stmt = $conn->prepare($sql);
    if (!empty($final_params)) {
        $stmt->bind_param($final_types, ...$final_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $teachers = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        if ($total === 0) {
            $total = $row['total_count'];
        }
        unset($row['total_count']); // Remove the count from individual records
        $teachers[] = $row;
    }
    
    $stmt->close();
    
    // If no results, get total count for empty state
    if (empty($teachers) && !empty($search)) {
        $count_sql = "SELECT COUNT(*) as total FROM teachers t INNER JOIN users u ON t.user_id = u.id WHERE u.role = 'teacher'";
        $count_result = $conn->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'teachers' => $teachers,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => $total > 0 ? ceil($total / $per_page) : 0,
        'has_more' => ($page * $per_page) < $total,
        'cache_time' => time()
    ];
    
    // Cache the response
    $cache_dir = dirname($cache_file);
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    file_put_contents($cache_file, json_encode($response));
    
    // Return response
    echo json_encode($response);
    
} catch (Exception $e) {
    log_error('Error in fetch_teachers: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch teachers: ' . $e->getMessage(),
        'debug' => $e->getTraceAsString()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 