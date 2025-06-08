<?php
// Include required files and start session
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? 'all';
    
    if ($type === 'all') {
        // Get current date in IST timezone - but check for yesterday's date too due to timezone storage issues
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Get all statistics in a single optimized query
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id WHERE u.status = 'active' AND u.role = 'student') as total_students,
                    (SELECT COUNT(*) FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.status = 'active' AND u.role = 'teacher') as total_teachers,
                    (SELECT COUNT(DISTINCT student_user_id) FROM attendance WHERE (DATE(date) = ? OR DATE(date) = ?) AND status = 'present') as present_today,
                    (SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id WHERE u.status = 'active' AND u.role = 'student') as total_students_for_attendance";
        
        $result = executeQuery($sql, "ss", [$today, $yesterday]);
        
        if (!empty($result)) {
            $data = $result[0];
            $attendance_percentage = 0;
            if ($data['total_students_for_attendance'] > 0) {
                $attendance_percentage = round(($data['present_today'] / $data['total_students_for_attendance']) * 100, 1);
            }
            
            $response = [
                'students' => [
                    'count' => (int)$data['total_students']
                ],
                'teachers' => [
                    'count' => (int)$data['total_teachers']
                ],
                'attendance' => [
                    'percentage' => $attendance_percentage,
                    'present' => (int)$data['present_today'],
                    'total' => (int)$data['total_students_for_attendance']
                ]
            ];
        } else {
            $response = [
                'students' => ['count' => 0],
                'teachers' => ['count' => 0],
                'attendance' => ['percentage' => 0, 'present' => 0, 'total' => 0]
            ];
        }
        
        echo json_encode($response);
        
    } else {
        // Legacy support for individual requests
        $response = ['count' => 0];

        switch ($type) {
            case 'students':
                $sql = "SELECT COUNT(*) as total FROM students s 
                        JOIN users u ON s.user_id = u.id 
                        WHERE u.status = 'active' AND u.role = 'student'";
                $result = executeQuery($sql, "", []);
                if (!empty($result)) {
                    $response['count'] = (int)$result[0]['total'];
                }
                break;

            case 'teachers':
                $sql = "SELECT COUNT(*) as total FROM teachers t 
                        JOIN users u ON t.user_id = u.id 
                        WHERE u.status = 'active' AND u.role = 'teacher'";
                $result = executeQuery($sql, "", []);
                if (!empty($result)) {
                    $response['count'] = (int)$result[0]['total'];
                }
                break;

            case 'attendance':
                // Get current date in IST timezone - but check for yesterday's date too due to timezone storage issues
                $today = date('Y-m-d');
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                
                // Get total students and present students for today
                $sql = "SELECT 
                            (SELECT COUNT(DISTINCT student_user_id) FROM attendance WHERE (DATE(date) = ? OR DATE(date) = ?) AND status = 'present') as present,
                            (SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id WHERE u.status = 'active' AND u.role = 'student') as total
                        ";
                $result = executeQuery($sql, "ss", [$today, $yesterday]);
                if (!empty($result) && $result[0]['total'] > 0) {
                    $percentage = round(($result[0]['present'] / $result[0]['total']) * 100, 1);
                    $response['percentage'] = $percentage;
                    $response['present'] = (int)$result[0]['present'];
                    $response['total'] = (int)$result[0]['total'];
                } else {
                    $response['percentage'] = 0;
                    $response['present'] = 0;
                    $response['total'] = 0;
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid type parameter']);
                exit;
        }

        echo json_encode($response);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 