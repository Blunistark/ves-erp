<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Include timezone utilities
require_once __DIR__ . '/../../includes/timezone_fix.php';

// Start output buffering to catch any accidental output
ob_start();

// Include necessary files - but prevent any HTML output
if (file_exists('con.php')) {
    include 'con.php'; // Database connection
}

// Check if session is needed and include necessary authentication
$is_ajax = true; // Flag to tell sidebar.php this is an AJAX request
if (file_exists('sidebar.php')) {
    include 'sidebar.php'; // For authentication and session management
}

// Get the action parameter
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Get teacher user ID from session
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Default response
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different actions
switch ($action) {
    case 'get_dashboard_stats':
        // Get dashboard statistics
        getDashboardStats($conn, $teacher_user_id);
        break;
        
    case 'get_students_for_attendance':
        // Get students for a specific class and section
        getStudentsForAttendance($conn, $teacher_user_id);
        break;
        
    case 'save_attendance':
        // Save attendance records
        saveAttendance($conn, $teacher_user_id);
        break;
        
    case 'get_attendance_history':
        // Get attendance history
        getAttendanceHistory($conn, $teacher_user_id);
        break;
        
    case 'get_attendance_report':
        // Get attendance report
        getAttendanceReport($conn, $teacher_user_id);
        break;
        
    default:
        // Invalid action
        echo json_encode($response);
        break;
}

// Discard any buffered output that might interfere with our JSON
ob_end_clean();

/** * Get dashboard statistics for the teacher */function getDashboardStats($conn, $teacher_user_id) {
    // Always define these variables at the very top
    $classIds = [];
    $sectionIds = [];
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    // Get classes assigned to this teacher
    $classQuery = "SELECT DISTINCT c.id, s.id AS section_id                   FROM classes c                    JOIN sections s ON c.id = s.class_id                    LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ?                    LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id                   WHERE s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL";
    $stmt = $conn->prepare($classQuery);
    $stmt->bind_param("ii", $teacher_user_id, $teacher_user_id);
    $stmt->execute();
    $classResult = $stmt->get_result();
    
    while ($row = $classResult->fetch_assoc()) {
        $classIds[] = $row['id'];
        $sectionIds[] = $row['section_id'];
    }
    
    // If no classes assigned, return empty stats
    if (empty($classIds)) {
        $response = [
            'success' => true,
            'today' => [
                'percentage' => 0,
                'trend' => 'No data available',
                'trend_direction' => 0
            ],
            'weekly' => [
                'percentage' => 0,
                'trend' => 'No data available',
                'trend_direction' => 0
            ],
            'monthly' => [
                'percentage' => 0,
                'trend' => 'No data available',
                'trend_direction' => 0
            ],
            'absent' => [
                'count' => 0,
                'no_reason' => 0,
                'percentage' => 0
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Ensure $classIds and $sectionIds are always arrays and not empty
    if (!is_array($classIds) || empty($classIds)) {
        $classIds = [0];
    }
    if (!is_array($sectionIds) || empty($sectionIds)) {
        $sectionIds = [0];
    }
    $classIdStr = implode(',', $classIds);
    $sectionIdStr = implode(',', $sectionIds);
    
    // Get today's attendance
    $dates = getAttendanceDateRange();
    $today = $dates['today'];
    $yesterday = $dates['yesterday'];
    
    $todayQuery = "SELECT 
                      COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                      COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                      COUNT(*) as total_count
                   FROM attendance 
                   WHERE class_id IN ($classIdStr) 
                   AND section_id IN ($sectionIdStr)
                   AND (DATE(date) = ? OR DATE(date) = ?)";
    
    $stmt = $conn->prepare($todayQuery);
    $stmt->bind_param("ss", $today, $yesterday);
    $stmt->execute();
    $todayResult = $stmt->get_result();
    $todayData = $todayResult->fetch_assoc();
    
    // Get yesterday's attendance (now using day before yesterday due to timezone fix)
    $dayBeforeYesterday = date('Y-m-d', strtotime('-2 days'));
    $yesterdayQuery = "SELECT 
                         COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                         COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                         COUNT(*) as total_count
                      FROM attendance 
                      WHERE class_id IN ($classIdStr) 
                      AND section_id IN ($sectionIdStr)
                      AND (DATE(date) = ? OR DATE(date) = ?)";
    
    $stmt = $conn->prepare($yesterdayQuery);
    $stmt->bind_param("ss", $yesterday, $dayBeforeYesterday);
    $stmt->execute();
    $yesterdayResult = $stmt->get_result();
    $yesterdayData = $yesterdayResult->fetch_assoc();
    
    // Get weekly attendance (last 7 days)
    $weekStart = date('Y-m-d', strtotime('-7 days'));
    $weeklyQuery = "SELECT 
                       COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                       COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                       COUNT(*) as total_count
                    FROM attendance 
                    WHERE class_id IN ($classIdStr) 
                    AND section_id IN ($sectionIdStr)
                    AND date >= ? AND date <= ?";
    
    $stmt = $conn->prepare($weeklyQuery);
    $stmt->bind_param("ss", $weekStart, $today);
    $stmt->execute();
    $weeklyResult = $stmt->get_result();
    $weeklyData = $weeklyResult->fetch_assoc();
    
    // Get previous week's attendance
    $prevWeekStart = date('Y-m-d', strtotime('-14 days'));
    $prevWeekEnd = date('Y-m-d', strtotime('-8 days'));
    $prevWeekQuery = "SELECT 
                        COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                        COUNT(*) as total_count
                     FROM attendance 
                     WHERE class_id IN ($classIdStr) 
                     AND section_id IN ($sectionIdStr)
                     AND date >= ? AND date <= ?";
    
    $stmt = $conn->prepare($prevWeekQuery);
    $stmt->bind_param("ss", $prevWeekStart, $prevWeekEnd);
    $stmt->execute();
    $prevWeekResult = $stmt->get_result();
    $prevWeekData = $prevWeekResult->fetch_assoc();
    
    // Get monthly attendance (current month)
    $monthStart = date('Y-m-01');
    $monthlyQuery = "SELECT 
                        COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                        COUNT(*) as total_count
                     FROM attendance 
                     WHERE class_id IN ($classIdStr) 
                     AND section_id IN ($sectionIdStr)
                     AND date >= ? AND date <= ?";
    
    $stmt = $conn->prepare($monthlyQuery);
    $stmt->bind_param("ss", $monthStart, $today);
    $stmt->execute();
    $monthlyResult = $stmt->get_result();
    $monthlyData = $monthlyResult->fetch_assoc();
    
    // Get previous month's attendance
    $prevMonthStart = date('Y-m-01', strtotime('-1 month'));
    $prevMonthEnd = date('Y-m-t', strtotime('-1 month'));
    $prevMonthQuery = "SELECT 
                         COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                         COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
                         COUNT(*) as total_count
                      FROM attendance 
                      WHERE class_id IN ($classIdStr) 
                      AND section_id IN ($sectionIdStr)
                      AND date >= ? AND date <= ?";
    
    $stmt = $conn->prepare($prevMonthQuery);
    $stmt->bind_param("ss", $prevMonthStart, $prevMonthEnd);
    $stmt->execute();
    $prevMonthResult = $stmt->get_result();
    $prevMonthData = $prevMonthResult->fetch_assoc();
    
    // Get absent students with no reason today
    $absentNoReasonQuery = "SELECT COUNT(*) as no_reason_count
                           FROM attendance 
                           WHERE class_id IN ($classIdStr) 
                           AND section_id IN ($sectionIdStr)
                           AND date = ? 
                           AND status = 'absent' 
                           AND (remark IS NULL OR remark = '')";
    
    $stmt = $conn->prepare($absentNoReasonQuery);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $absentNoReasonResult = $stmt->get_result();
    $absentNoReasonData = $absentNoReasonResult->fetch_assoc();
    
    // Calculate percentages
    $todayPercentage = 0;
    $yesterdayPercentage = 0;
    $weeklyPercentage = 0;
    $prevWeekPercentage = 0;
    $monthlyPercentage = 0;
    $prevMonthPercentage = 0;
    
    if ($todayData['total_count'] > 0) {
        $todayPercentage = round(($todayData['present_count'] / $todayData['total_count']) * 100, 1);
    }
    
    if ($yesterdayData['total_count'] > 0) {
        $yesterdayPercentage = round(($yesterdayData['present_count'] / $yesterdayData['total_count']) * 100, 1);
    }
    
    if ($weeklyData['total_count'] > 0) {
        $weeklyPercentage = round(($weeklyData['present_count'] / $weeklyData['total_count']) * 100, 1);
    }
    
    if ($prevWeekData['total_count'] > 0) {
        $prevWeekPercentage = round(($prevWeekData['present_count'] / $prevWeekData['total_count']) * 100, 1);
    }
    
    if ($monthlyData['total_count'] > 0) {
        $monthlyPercentage = round(($monthlyData['present_count'] / $monthlyData['total_count']) * 100, 1);
    }
    
    if ($prevMonthData['total_count'] > 0) {
        $prevMonthPercentage = round(($prevMonthData['present_count'] / $prevMonthData['total_count']) * 100, 1);
    }
    
    // Calculate trends
    $todayTrend = '';
    $todayTrendDirection = 0;
    if ($yesterdayData['total_count'] > 0) {
        $diff = $todayPercentage - $yesterdayPercentage;
        $todayTrendDirection = $diff >= 0 ? 1 : -1;
        $todayTrend = abs($diff) . '% from yesterday';
    } else {
        $todayTrend = 'No data for comparison';
    }
    
    $weeklyTrend = '';
    $weeklyTrendDirection = 0;
    if ($prevWeekData['total_count'] > 0) {
        $diff = $weeklyPercentage - $prevWeekPercentage;
        $weeklyTrendDirection = $diff >= 0 ? 1 : -1;
        $weeklyTrend = abs($diff) . '% from last week';
    } else {
        $weeklyTrend = 'No data for comparison';
    }
    
    $monthlyTrend = '';
    $monthlyTrendDirection = 0;
    if ($prevMonthData['total_count'] > 0) {
        $diff = $monthlyPercentage - $prevMonthPercentage;
        $monthlyTrendDirection = $diff >= 0 ? 1 : -1;
        $monthlyTrend = abs($diff) . '% from last month';
    } else {
        $monthlyTrend = 'No data for comparison';
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'today' => [
            'percentage' => $todayPercentage,
            'trend' => $todayTrend,
            'trend_direction' => $todayTrendDirection
        ],
        'weekly' => [
            'percentage' => $weeklyPercentage,
            'trend' => $weeklyTrend,
            'trend_direction' => $weeklyTrendDirection
        ],
        'monthly' => [
            'percentage' => $monthlyPercentage,
            'trend' => $monthlyTrend,
            'trend_direction' => $monthlyTrendDirection
        ],
        'absent' => [
            'count' => $todayData['absent_count'] ?? 0,
            'no_reason' => $absentNoReasonData['no_reason_count'] ?? 0,
            'percentage' => $todayData['total_count'] > 0 ? round(($todayData['absent_count'] / $todayData['total_count']) * 100) : 0
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Get students for a specific class and section
 */
function getStudentsForAttendance($conn, $teacher_user_id) {
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $include_existing = isset($_GET['include_existing']) && $_GET['include_existing'] === 'true';
    
    // Basic validation
    if (!$class_id || !$section_id) {
        $response = [
            'success' => false,
            'message' => 'Class ID and Section ID are required'
        ];
        echo json_encode($response);
        return;
    }
    
    try {
        // Check if attendance already exists for this date, class and section
        $attendanceCheckQuery = "SELECT COUNT(*) as count 
                                FROM attendance 
                                WHERE class_id = ? 
                                AND section_id = ? 
                                AND date = ?";
        
        $stmt = $conn->prepare($attendanceCheckQuery);
        $stmt->bind_param("iis", $class_id, $section_id, $date);
        $stmt->execute();
        $attendanceCheckResult = $stmt->get_result();
        $attendanceCheckData = $attendanceCheckResult->fetch_assoc();
        
        $attendance_exists = ($attendanceCheckData['count'] > 0);
        
        // If attendance exists and we're not including existing records, return early
        if ($attendance_exists && !$include_existing) {
            $response = [
                'success' => true,
                'attendance_exists' => true,
                'message' => 'Attendance for this date already exists'
            ];
            echo json_encode($response);
            return;
        }
        
        // Get students for this class and section
        $studentsQuery = "SELECT s.user_id, s.admission_number, s.full_name, s.roll_number
                         FROM students s
                         WHERE s.class_id = ? AND s.section_id = ?
                         ORDER BY s.roll_number";
        
        $stmt = $conn->prepare($studentsQuery);
        $stmt->bind_param("ii", $class_id, $section_id);
        $stmt->execute();
        $studentsResult = $stmt->get_result();
        
        if (!$studentsResult) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $students = [];
        while ($row = $studentsResult->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Get existing attendance records if needed
        $existing_attendance = [];
        if ($include_existing && $attendance_exists) {
            $existingQuery = "SELECT student_user_id, status, remark
                             FROM attendance
                             WHERE class_id = ? AND section_id = ? AND date = ?";
            
            $stmt = $conn->prepare($existingQuery);
            $stmt->bind_param("iis", $class_id, $section_id, $date);
            $stmt->execute();
            $existingResult = $stmt->get_result();
            
            if (!$existingResult) {
                throw new Exception("Database error while fetching existing attendance: " . $conn->error);
            }
            
            while ($row = $existingResult->fetch_assoc()) {
                $existing_attendance[$row['student_user_id']] = [
                    'status' => $row['status'],
                    'remark' => $row['remark']
                ];
            }
        }
        
        // Build response
        $response = [
            'success' => true,
            'attendance_exists' => $attendance_exists,
            'students' => $students
        ];
        
        if ($include_existing && $attendance_exists) {
            $response['existing_attendance'] = $existing_attendance;
        }
        
        // Ensure proper JSON output
        header('Content-Type: application/json');
        echo json_encode($response);
        exit; // Exit to prevent further output
        
    } catch (Exception $e) {
        // Return JSON error response
        header('Content-Type: application/json');
        $response = [
            'success' => false,
            'message' => 'Error loading students: ' . $e->getMessage()
        ];
        echo json_encode($response);
        exit; // Exit to prevent further output
    }
}

/**
 * Save attendance records
 */
function saveAttendance($conn, $teacher_user_id) {
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : getCurrentDateIST();
    $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === 'true';
    
    $statuses = isset($_POST['status']) ? $_POST['status'] : [];
    $remarks = isset($_POST['remark']) ? $_POST['remark'] : [];
    
    // Basic validation
    if (!$class_id || !$section_id || !$date) {
        $response = [
            'success' => false,
            'message' => 'Class ID, Section ID, and Date are required'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    if (empty($statuses)) {
        $response = [
            'success' => false,
            'message' => 'No attendance data provided'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    try {
        // Check if attendance already exists for this date
        $attendanceCheckQuery = "SELECT COUNT(*) as count 
                                FROM attendance 
                                WHERE class_id = ? 
                                AND section_id = ? 
                                AND date = ?";
        
        $stmt = $conn->prepare($attendanceCheckQuery);
        $stmt->bind_param("iis", $class_id, $section_id, $date);
        $stmt->execute();
        $attendanceCheckResult = $stmt->get_result();
        
        if (!$attendanceCheckResult) {
            throw new Exception("Database error checking attendance: " . $conn->error);
        }
        
        $attendanceCheckData = $attendanceCheckResult->fetch_assoc();
        $attendance_exists = ($attendanceCheckData['count'] > 0);
        
        // If attendance exists and we're not allowed to update, return error
        if ($attendance_exists && !$update_existing) {
            $response = [
                'success' => false,
                'message' => 'Attendance for this date already exists. Use the update function instead.'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Begin transaction
        $conn->begin_transaction();
        
        // If updating existing attendance, first delete the old records
        if ($attendance_exists) {
            $deleteQuery = "DELETE FROM attendance 
                           WHERE class_id = ? 
                           AND section_id = ? 
                           AND date = ?";
            
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("iis", $class_id, $section_id, $date);
            $result = $deleteStmt->execute();
            
            if (!$result) {
                throw new Exception("Error deleting existing attendance: " . $conn->error);
            }
        }
        
        // Prepare insert statement
        $insertQuery = "INSERT INTO attendance (student_user_id, class_id, section_id, date, status, remark, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $insertStmt = $conn->prepare($insertQuery);
        
        if (!$insertStmt) {
            throw new Exception("Error preparing insert statement: " . $conn->error);
        }
        
        $inserted = 0;
        
        // Insert attendance for each student
        foreach ($statuses as $student_id => $status) {
            $remark = $remarks[$student_id] ?? '';
            $student_id = intval($student_id);
            
            $insertStmt->bind_param("iiisss", $student_id, $class_id, $section_id, $date, $status, $remark);
            $result = $insertStmt->execute();
            
            if (!$result) {
                throw new Exception("Error inserting attendance for student $student_id: " . $conn->error);
            }
            
            $inserted++;
        }
        
        // Commit transaction if successful
        $conn->commit();
        
        $action = $attendance_exists ? "updated" : "saved";
        
        $response = [
            'success' => true,
            'message' => "Attendance $action successfully for $inserted students.",
            'inserted' => $inserted,
            'updated' => $attendance_exists
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        
        $response = [
            'success' => false,
            'message' => 'Error saving attendance: ' . $e->getMessage()
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

/**
 * Get attendance history
 */
function getAttendanceHistory($conn, $teacher_user_id) {
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    
    // Format dates for query
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    // Check if teacher has access to this class/section
    if ($class_id && $section_id) {
        $accessQuery = "SELECT COUNT(*) as count
                       FROM classes c 
                       JOIN sections s ON c.id = s.class_id 
                       LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
                       LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
                       WHERE c.id = ? AND s.id = ? AND (s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL)";
        
        $stmt = $conn->prepare($accessQuery);
        $stmt->bind_param("iiii", $teacher_user_id, $class_id, $section_id, $teacher_user_id);
        $stmt->execute();
        $accessResult = $stmt->get_result();
        $accessData = $accessResult->fetch_assoc();
        
        if ($accessData['count'] == 0) {
            $response = [
                'success' => false,
                'message' => 'You do not have access to this class/section'
            ];
            echo json_encode($response);
            return;
        }
    }
    
    // Build query based on whether class and section are provided
    if ($class_id && $section_id) {
        // Get attendance for specific class/section
        $historyQuery = "SELECT a.date, c.name as class_name, s.name as section_name, c.id as class_id, s.id as section_id,
                            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
                         FROM attendance a
                         JOIN classes c ON a.class_id = c.id
                         JOIN sections s ON a.section_id = s.id
                         WHERE a.class_id = ? AND a.section_id = ? AND a.date BETWEEN ? AND ?
                         GROUP BY a.date
                         ORDER BY a.date DESC";
        
        $stmt = $conn->prepare($historyQuery);
        $stmt->bind_param("iiss", $class_id, $section_id, $start_date, $end_date);
    } else {
        // Get attendance for all classes/sections this teacher has access to
        $historyQuery = "SELECT a.date, c.name as class_name, s.name as section_name, c.id as class_id, s.id as section_id,
                            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent,
                         FROM attendance a
                         JOIN classes c ON a.class_id = c.id
                         JOIN sections s ON a.section_id = s.id
                         WHERE a.date BETWEEN ? AND ? 
                         AND a.class_id IN (
                             SELECT DISTINCT c.id
                             FROM classes c 
                             JOIN sections s ON c.id = s.class_id 
                             LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
                             LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
                             WHERE s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL
                         )
                         GROUP BY a.date, a.class_id, a.section_id
                         ORDER BY a.date DESC, c.name, s.name";
        
        $stmt = $conn->prepare($historyQuery);
        $stmt->bind_param("ssii", $start_date, $end_date, $teacher_user_id, $teacher_user_id);
    }
    
    $stmt->execute();
    $historyResult = $stmt->get_result();
    
    if (!$historyResult) {
        $response = [
            'success' => false,
            'message' => 'Error fetching attendance history: ' . $conn->error
        ];
        echo json_encode($response);
        return;
    }
    
    $history = [];
    while ($row = $historyResult->fetch_assoc()) {
        // Convert number strings to integers for consistent JSON output
        $row['present'] = intval($row['present']);
        $row['absent'] = intval($row['absent']);
        $row['class_id'] = intval($row['class_id']);
        $row['section_id'] = intval($row['section_id']);
        
        $history[] = $row;
    }
    
    $response = [
        'success' => true,
        'history' => $history,
        'month' => $month,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    
    // Return JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Get attendance report
 */
function getAttendanceReport($conn, $teacher_user_id) {
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    $period = isset($_GET['period']) ? $_GET['period'] : 'month';
    
    if (!$class_id || !$section_id) {
        $response = [
            'success' => false,
            'message' => 'Class and section are required'
        ];
        echo json_encode($response);
        return;
    }
    
    // Check if teacher has access to this class/section
    $accessQuery = "SELECT COUNT(*) as count, c.name as class_name, s.name as section_name
                   FROM classes c 
                   JOIN sections s ON c.id = s.class_id 
                   LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
                   LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
                   WHERE c.id = ? AND s.id = ? AND (s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL)";
    
    $stmt = $conn->prepare($accessQuery);
    $stmt->bind_param("iiii", $teacher_user_id, $class_id, $section_id, $teacher_user_id);
    $stmt->execute();
    $accessResult = $stmt->get_result();
    $accessData = $accessResult->fetch_assoc();
    
    if ($accessData['count'] == 0) {
        $response = [
            'success' => false,
            'message' => 'You do not have access to this class/section'
        ];
        echo json_encode($response);
        return;
    }
    
    // Determine date range based on period
    $end_date = date('Y-m-d');
    $start_date = '';
    $period_label = '';
    
    switch ($period) {
        case 'week':
            $start_date = date('Y-m-d', strtotime('-7 days'));
            $period_label = 'Last 7 Days';
            break;
        case 'month':
            $start_date = date('Y-m-01');
            $period_label = 'This Month';
            break;
        case 'term':
            // Get the current term dates
            $termQuery = "SELECT t.start_date, t.end_date, t.name
                         FROM terms t
                         JOIN academic_years ay ON t.academic_year_id = ay.id
                         WHERE ? BETWEEN t.start_date AND t.end_date";
            
            $stmt = $conn->prepare($termQuery);
            $stmt->bind_param("s", $end_date);
            $stmt->execute();
            $termResult = $stmt->get_result();
            
            if ($termResult->num_rows > 0) {
                $termData = $termResult->fetch_assoc();
                $start_date = $termData['start_date'];
                $end_date = min($end_date, $termData['end_date']);
                $period_label = 'Term: ' . $termData['name'];
            } else {
                // Fall back to this month if term not found
                $start_date = date('Y-m-01');
                $period_label = 'This Month (No active term)';
            }
            break;
        default:
            $start_date = date('Y-m-01');
            $period_label = 'This Month';
    }
    
    // Get total days with attendance records
    $daysQuery = "SELECT COUNT(DISTINCT date) as total_days
                 FROM attendance
                 WHERE class_id = ? AND section_id = ? AND date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($daysQuery);
    $stmt->bind_param("iiss", $class_id, $section_id, $start_date, $end_date);
    $stmt->execute();
    $daysResult = $stmt->get_result();
    
    if (!$daysResult) {
        $response = [
            'success' => false,
            'message' => 'Error fetching attendance days: ' . $conn->error
        ];
        echo json_encode($response);
        return;
    }
    
    $daysData = $daysResult->fetch_assoc();
    $total_days = intval($daysData['total_days']);
    
    // If no attendance days found, return early with empty data
    if ($total_days === 0) {
        $response = [
            'success' => true,
            'class_name' => $accessData['class_name'],
            'section_name' => $accessData['section_name'],
            'period_label' => $period_label,
            'report' => [
                'total_days' => 0,
                'average_percentage' => 0,
                'total_students' => 0,
                'students' => []
            ],
            'message' => 'No attendance data found for the selected period'
        ];
        echo json_encode($response);
        return;
    }
    
    // Get students in this class/section
    $studentsQuery = "SELECT s.user_id, s.full_name, s.roll_number
                     FROM students s
                     WHERE s.class_id = ? AND s.section_id = ?
                     ORDER BY s.roll_number";
    
    $stmt = $conn->prepare($studentsQuery);
    $stmt->bind_param("ii", $class_id, $section_id);
    $stmt->execute();
    $studentsResult = $stmt->get_result();
    
    if (!$studentsResult) {
        $response = [
            'success' => false,
            'message' => 'Error fetching students: ' . $conn->error
        ];
        echo json_encode($response);
        return;
    }
    
    $students = [];
    $total_attendance_percentage = 0;
    $student_count = 0;
    
    while ($student = $studentsResult->fetch_assoc()) {
        $student_id = $student['user_id'];
        
        // Get attendance for this student
        $attendanceQuery = "SELECT 
                             COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                             COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
                             COUNT(*) as total_days
                           FROM attendance
                           WHERE student_user_id = ? AND class_id = ? AND section_id = ? AND date BETWEEN ? AND ?";
        
        $stmt = $conn->prepare($attendanceQuery);
        $stmt->bind_param("iiiss", $student_id, $class_id, $section_id, $start_date, $end_date);
        $stmt->execute();
        $attendanceResult = $stmt->get_result();
        
        if (!$attendanceResult) {
            continue; // Skip this student if there's an error
        }
        
        $attendanceData = $attendanceResult->fetch_assoc();
        
        // Calculate attendance percentage
        $attendance_percentage = 0;
        $present_days = intval($attendanceData['present_days'] ?? 0);
        $absent_days = intval($attendanceData['absent_days'] ?? 0);
        $student_total_days = intval($attendanceData['total_days'] ?? 0);
        
        if ($student_total_days > 0) {
            $attendance_percentage = round(($present_days / $student_total_days) * 100, 1);
        }
        
        $student['present_days'] = $present_days;
        $student['absent_days'] = $absent_days;
        $student['attendance_percentage'] = $attendance_percentage;
        
        $total_attendance_percentage += $attendance_percentage;
        $student_count++;
        
        $students[] = $student;
    }
    
    // Calculate average attendance
    $average_percentage = $student_count > 0 ? round($total_attendance_percentage / $student_count, 1) : 0;
    
    $response = [
        'success' => true,
        'class_name' => $accessData['class_name'],
        'section_name' => $accessData['section_name'],
        'period_label' => $period_label,
        'report' => [
            'total_days' => $total_days,
            'average_percentage' => $average_percentage,
            'total_students' => $student_count,
            'students' => $students
        ]
    ];
    
    // Return JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 