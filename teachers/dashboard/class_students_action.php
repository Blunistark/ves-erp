<?php
// Include necessary files
include 'sidebar.php'; // For authentication and session management
include 'con.php'; // Database connection

// Set the content type to JSON
header('Content-Type: application/json');

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
    case 'get_students':
        // Get students for a specific class and section
        getStudentsForClass($conn, $teacher_user_id);
        break;
        
    case 'export_students':
        // Export students list to Excel
        exportStudentsToExcel($conn, $teacher_user_id);
        break;
        
    default:
        // Invalid action
        echo json_encode($response);
        break;
}

/**
 * Get students for a specific class and section
 */
function getStudentsForClass($conn, $teacher_user_id) {
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    
    // Check if teacher has access to this class/section
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
    
    // Get students for this class and section
    $studentsQuery = "SELECT s.user_id, s.admission_number, s.full_name, s.roll_number, 
                     s.gender_code, s.dob, s.mother_name, s.father_name, s.address, 
                     s.mobile, s.contact_email, s.blood_group_code, s.nationality
                     FROM students s
                     WHERE s.class_id = ? AND s.section_id = ?
                     ORDER BY s.roll_number, s.full_name";
    
    $stmt = $conn->prepare($studentsQuery);
    $stmt->bind_param("ii", $class_id, $section_id);
    $stmt->execute();
    $studentsResult = $stmt->get_result();
    
    $students = [];
    while ($row = $studentsResult->fetch_assoc()) {
        $students[] = $row;
    }
    
    // Get class and section details
    $classQuery = "SELECT c.name as class_name, s.name as section_name
                  FROM classes c
                  JOIN sections s ON c.id = s.class_id
                  WHERE c.id = ? AND s.id = ?";
    
    $stmt = $conn->prepare($classQuery);
    $stmt->bind_param("ii", $class_id, $section_id);
    $stmt->execute();
    $classResult = $stmt->get_result();
    $classData = $classResult->fetch_assoc();
    
    $response = [
        'success' => true,
        'students' => $students,
        'class' => $classData['class_name'] ?? '',
        'section' => $classData['section_name'] ?? '',
        'total_students' => count($students)
    ];
    
    echo json_encode($response);
}

/**
 * Export students list to Excel
 */
function exportStudentsToExcel($conn, $teacher_user_id) {
    // Change content type to CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_list.csv"');
    
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
    
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
        die('You do not have access to this data');
    }
    
    // Get students for this class and section
    $studentsQuery = "SELECT s.user_id, s.admission_number, s.full_name, s.roll_number, 
                     s.gender_code, s.dob, s.mother_name, s.father_name, s.address, 
                     s.mobile, s.contact_email, s.blood_group_code, s.nationality
                     FROM students s
                     WHERE s.class_id = ? AND s.section_id = ?
                     ORDER BY s.roll_number, s.full_name";
    
    $stmt = $conn->prepare($studentsQuery);
    $stmt->bind_param("ii", $class_id, $section_id);
    $stmt->execute();
    $studentsResult = $stmt->get_result();
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers with class info
    fputcsv($output, [
        'Student List - ' . $accessData['class_name'] . ' ' . $accessData['section_name'],
        'Generated on: ' . date('Y-m-d H:i:s')
    ]);
    fputcsv($output, []); // Empty row
    
    // Add column headers
    fputcsv($output, [
        'Roll No.',
        'Admission No.',
        'Student Name',
        'Gender',
        'Date of Birth',
        'Father Name',
        'Mother Name',
        'Mobile',
        'Email',
        'Address',
        'Blood Group',
        'Nationality'
    ]);
    
    // Add student data rows
    while ($row = $studentsResult->fetch_assoc()) {
        fputcsv($output, [
            $row['roll_number'] ?? 'N/A',
            $row['admission_number'] ?? 'N/A',
            $row['full_name'] ?? 'N/A',
            $row['gender_code'] ?? 'N/A',
            $row['dob'] ?? 'N/A',
            $row['father_name'] ?? 'N/A',
            $row['mother_name'] ?? 'N/A',
            $row['mobile'] ?? 'N/A',
            $row['contact_email'] ?? 'N/A',
            $row['address'] ?? 'N/A',
            $row['blood_group_code'] ?? 'N/A',
            $row['nationality'] ?? 'N/A'
        ]);
    }
    
    // Close output stream
    fclose($output);
    exit;
} 