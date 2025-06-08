<?php
require_once 'con.php';
header('Content-Type: application/json');

// Enable output compression
if (extension_loaded('zlib') && !ob_get_level()) {
    ob_start('ob_gzhandler');
}

try {
    // Check cache first
    $cache_file = __DIR__ . '/cache/class_teacher_assignments.json';
    $cache_duration = 300; // 5 minutes
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        echo file_get_contents($cache_file);
        exit;
    }
    
    // Optimized query with proper indexing
    $sql = "SELECT 
                s.id, 
                s.name AS section_name, 
                c.name AS class_name, 
                s.capacity,
                u.full_name AS teacher_name,
                s.class_teacher_user_id,
                COUNT(st.id) as student_count,
                CASE 
                    WHEN s.class_teacher_user_id IS NOT NULL THEN 'assigned'
                    ELSE 'unassigned'
                END as status
            FROM sections s
            INNER JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON s.class_teacher_user_id = u.id AND u.role = 'teacher'
            LEFT JOIN students st ON st.section_id = s.id AND st.status = 'active'
            GROUP BY s.id, s.name, c.name, s.capacity, u.full_name, s.class_teacher_user_id
            ORDER BY c.id ASC, s.name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    $stmt->close();
    
    $response = [
        'success' => true, 
        'assignments' => $assignments,
        'total' => count($assignments),
        'cache_time' => time()
    ];
    
    // Cache the response
    $cache_dir = dirname($cache_file);
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    file_put_contents($cache_file, json_encode($response));
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error fetching class teacher assignments: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch assignments: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 