<?php
// teachers/dashboard/homework_actions.php
// Backend endpoint for homework management (assign, list, submit, grade)

require_once 'con.php'; // Database connection
require_once '../../includes/timezone_fix.php'; // Add timezone utilities

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Debug logging
$debug_file = __DIR__ . '/debug.log';
function debug_log($message) {
    global $debug_file;
    file_put_contents($debug_file, date('Y-m-d H:i:s') . ' - ' . print_r($message, true) . "\n", FILE_APPEND);
}

debug_log('Request started');
debug_log('Session: ' . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    debug_log('Unauthorized access');
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get action from POST, GET, or JSON body
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (!$action) {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $action = $json_data['action'] ?? '';
}

debug_log('Action: ' . $action);
debug_log('POST data: ' . print_r($_POST, true));
debug_log('GET data: ' . print_r($_GET, true));
debug_log('JSON data: ' . print_r($json_data ?? [], true));

switch ($action) {
    case 'list_homework':
        $teacher_id = $_SESSION['user_id'];
        debug_log('Teacher ID: ' . $teacher_id);
        
        // Get filter values from POST data or JSON body
        $json_data = json_decode(file_get_contents('php://input'), true);
        $class_id = $json_data['class'] ?? $_POST['class'] ?? '';
        $section_id = $json_data['section'] ?? $_POST['section'] ?? '';
        $subject_id = $json_data['subject'] ?? $_POST['subject'] ?? '';
        $search = $json_data['search'] ?? $_POST['search'] ?? '';
        $tab = $json_data['tab'] ?? $_POST['tab'] ?? 'all';
        
        debug_log('Filters: ' . print_r([
            'class_id' => $class_id,
            'section_id' => $section_id,
            'subject_id' => $subject_id,
            'search' => $search,
            'tab' => $tab
        ], true));
        
        // Build the SQL query with filters
        $sql = "SELECT h.id, h.title, h.description, h.due_date, h.attachment, h.submission_type, h.created_at,
                       h.class_id, h.section_id, h.subject_id,
                       c.name AS class_name, s.name AS section_name, sub.name AS subject_name,
                       EXISTS(SELECT 1 FROM homework_submissions hs WHERE hs.homework_id = h.id) as has_submissions
                FROM homework h
                JOIN classes c ON h.class_id = c.id
                JOIN sections s ON h.section_id = s.id
                JOIN subjects sub ON h.subject_id = sub.id
                WHERE h.teacher_user_id = ?";
        
        $params = [$teacher_id];
        $types = 'i';
        
        // Add tab-specific filters
        $today = getCurrentDateIST();
        switch ($tab) {
            case 'pending':
                $sql .= " AND h.due_date >= ?";
                $params[] = $today;
                $types .= 's';
                break;
            case 'grading':
                $sql .= " AND EXISTS(SELECT 1 FROM homework_submissions hs WHERE hs.homework_id = h.id)";
                break;
            case 'completed':
                $sql .= " AND h.due_date < ?";
                $params[] = $today;
                $types .= 's';
                break;
        }
        
        if ($class_id) {
            $sql .= " AND h.class_id = ?";
            $params[] = $class_id;
            $types .= 'i';
        }
        if ($section_id) {
            $sql .= " AND h.section_id = ?";
            $params[] = $section_id;
            $types .= 'i';
        }
        if ($subject_id) {
            $sql .= " AND h.subject_id = ?";
            $params[] = $subject_id;
            $types .= 'i';
        }
        if ($search) {
            $sql .= " AND (h.title LIKE ? OR h.description LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'ss';
        }
        
        $sql .= " ORDER BY h.due_date DESC, h.created_at DESC";
        
        debug_log('SQL: ' . $sql);
        debug_log('Params: ' . print_r($params, true));
        
        $conn = getDbConnection();
        if (!$conn) {
            debug_log('Database connection failed');
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
            break;
        }
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            debug_log('SQL prepare failed: ' . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare query']);
            $conn->close();
            break;
        }
        
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            debug_log('SQL execute failed: ' . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute query']);
            $stmt->close();
            $conn->close();
            break;
        }
        
        $result = $stmt->get_result();
        $homework = [];
        while ($row = $result->fetch_assoc()) {
            $homework[] = $row;
        }
        
        debug_log('Found ' . count($homework) . ' homework assignments');
        
        $stmt->close();
        $conn->close();
        echo json_encode(['status' => 'success', 'debug_action' => 'list_homework', 'homework' => $homework]);
        break;
    case 'add_homework':
        $response = ['status' => 'error', 'message' => 'Unknown error'];
        $teacher_id = $_SESSION['user_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $class_id = $_POST['class_id'] ?? '';
        $section_id = $_POST['section_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $due_date = $_POST['due_date'] ?? '';
        $submission_type = $_POST['submission_type'] ?? 'online';
        $attachment_path = NULL;
        $total_marks = $_POST['total_marks'] ?? NULL; // Get total marks

        // Handle file upload if present
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/homework/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true);
            }
            $file_tmp = $_FILES['attachment']['tmp_name'];
            $file_name = basename($_FILES['attachment']['name']);
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('hw_', true) . '.' . $file_ext;
            $target_path = $upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $target_path)) {
                $attachment_path = 'uploads/homework/' . $new_file_name;
            } else {
                $response['message'] = 'File upload failed.';
                echo json_encode($response);
                break;
            }
        }

        // Validate required fields
        if (!$title || !$description || !$class_id || !$section_id || !$subject_id || !$due_date || $total_marks === NULL || $total_marks < 0) {
            $response['message'] = 'Missing required fields or invalid total marks.';
            echo json_encode($response);
            break;
        }

        $conn = getDbConnection();
        $sql = "INSERT INTO homework (class_id, section_id, subject_id, teacher_user_id, title, description, due_date, attachment, submission_type, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiiisssssi', $class_id, $section_id, $subject_id, $teacher_id, $title, $description, $due_date, $attachment_path, $submission_type, $total_marks);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'homework_id' => $stmt->insert_id];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        break;
    case 'submit_homework':
        // Only allow students to submit homework
        if ($_SESSION['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['error' => 'Only students can submit homework.']);
            break;
        }
        $response = ['status' => 'error', 'message' => 'Unknown error'];
        $student_id = $_SESSION['user_id'];
        $homework_id = $_POST['homework_id'] ?? '';
        $file_path = NULL;

        // Handle file upload if present
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/homework_submissions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true);
            }
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_name = basename($_FILES['file']['name']);
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('hws_', true) . '.' . $file_ext;
            $target_path = $upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $target_path)) {
                $file_path = 'uploads/homework_submissions/' . $new_file_name;
            } else {
                $response['message'] = 'File upload failed.';
                echo json_encode($response);
                break;
            }
        }

        // Validate required fields
        if (!$homework_id) {
            $response['message'] = 'Missing homework_id.';
            echo json_encode($response);
            break;
        }

        $conn = getDbConnection();
        $sql = "INSERT INTO homework_submissions (homework_id, student_user_id, file_path) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iis', $homework_id, $student_id, $file_path);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'submission_id' => $stmt->insert_id];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        echo json_encode($response);
        break;
    case 'grade_homework':
        // Only allow teachers to grade homework
        if ($_SESSION['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Only teachers can grade homework.']);
            break;
        }
        $response = ['status' => 'error', 'message' => 'Unknown error'];
        // Get data from JSON body or POST
        // Log incoming data for debugging
        $grade_debug_file = __DIR__ . '/grade_debug.log';
        file_put_contents($grade_debug_file, "\n---\nRequest Start: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        $raw_post_body = file_get_contents('php://input');
        file_put_contents($grade_debug_file, "Raw POST Body: " . $raw_post_body . "\n", FILE_APPEND);
        $json_data = json_decode($raw_post_body, true);
        file_put_contents($grade_debug_file, "Decoded JSON: " . print_r($json_data, true) . "\n", FILE_APPEND);

        $submission_id = $json_data['submission_id'] ?? $_POST['submission_id'] ?? '';
        $marks_obtained = $json_data['marks_obtained'] ?? $_POST['marks_obtained'] ?? ''; // Get marks obtained
        $feedback = $json_data['feedback'] ?? $_POST['feedback'] ?? '';

        file_put_contents($grade_debug_file, "Submission ID (after assignment): " . $submission_id . "\n", FILE_APPEND);
        file_put_contents($grade_debug_file, "Marks Obtained (after assignment): " . $marks_obtained . "\n", FILE_APPEND);
        file_put_contents($grade_debug_file, "Feedback (after assignment): " . $feedback . "\n", FILE_APPEND);

        // Ensure submission_id and marks_obtained are integers
        $submission_id = (int)$submission_id;
        $marks_obtained = (int)$marks_obtained;

        // Validate required fields
        // marks_obtained must be provided and non-negative
        if (!$submission_id || $marks_obtained < 0) {
            // Log validation failure for debugging
            file_put_contents($grade_debug_file, "Validation failed: submission_id=".$submission_id.", marks_obtained=".$marks_obtained."\n", FILE_APPEND);
            $response['message'] = 'Missing required fields or invalid marks obtained.';
            echo json_encode($response);
            break;
        }

        $conn = getDbConnection();
        if (!$conn) {
             file_put_contents($grade_debug_file, "Database connection failed\n", FILE_APPEND);
             $response['message'] = 'Database connection failed.';
             echo json_encode($response);
             break;
        }

        // 1. Fetch total_marks for the homework
        $sql_total_marks = "SELECT h.total_marks FROM homework_submissions hs JOIN homework h ON hs.homework_id = h.id WHERE hs.id = ?";
        $stmt_total_marks = $conn->prepare($sql_total_marks);
        if (!$stmt_total_marks) {
             file_put_contents($grade_debug_file, "SQL total_marks prepare failed: " . $conn->error . "\n", FILE_APPEND);
             $response['message'] = 'Failed to prepare total marks query: ' . $conn->error;
             $conn->close();
             echo json_encode($response);
             break;
        }
        $stmt_total_marks->bind_param('i', $submission_id);
        $stmt_total_marks->execute();
        $result_total_marks = $stmt_total_marks->get_result();
        $homework_data = $result_total_marks->fetch_assoc();
        $stmt_total_marks->close();

        if (!$homework_data || $homework_data['total_marks'] === NULL || $homework_data['total_marks'] <= 0) {
            file_put_contents($grade_debug_file, "Total marks not found or invalid for homework related to submission " . $submission_id . "\n", FILE_APPEND);
            $response['message'] = 'Total marks for this homework are not set or invalid. Cannot calculate grade.';
            $conn->close();
            echo json_encode($response);
            break;
        }
        $total_marks = $homework_data['total_marks'];

        // 2. Fetch grading scale
        $sql_grades = "SELECT code, min_percentage FROM grades ORDER BY min_percentage DESC";
        $result_grades = $conn->query($sql_grades);
        $grades = [];
        while ($row = $result_grades->fetch_assoc()) {
            $grades[] = $row;
        }

        // 3. Calculate percentage
        $percentage = ($total_marks > 0) ? ($marks_obtained / $total_marks) * 100 : 0;

        // 4. Determine grade code
        $calculated_grade_code = NULL;
        foreach ($grades as $grade) {
            if ($percentage >= $grade['min_percentage']) {
                $calculated_grade_code = $grade['code'];
                break;
            }
        }

        if ($calculated_grade_code === NULL) {
             // Handle case where no grade matches (e.g., percentage is below the lowest min_percentage)
             // Assign the lowest grade code, or handle as an error if preferred
             // For now, let's assign the lowest grade code from the sorted list (which is the last one)
             if (!empty($grades)) {
                 $calculated_grade_code = end($grades)['code'];
             } else {
                  file_put_contents($grade_debug_file, "No grades defined in the grades table.\n", FILE_APPEND);
                  $response['message'] = 'Grading scale not defined.';
                  $conn->close();
                  echo json_encode($response);
                  break;
             }
        }

        file_put_contents($grade_debug_file, "Calculated Percentage: " . $percentage . "\n", FILE_APPEND);
        file_put_contents($grade_debug_file, "Calculated Grade Code: " . $calculated_grade_code . "\n", FILE_APPEND);

        // 5. Update homework_submissions with marks_obtained and calculated grade_code
        $sql_update = "UPDATE homework_submissions SET marks_obtained = ?, grade_code = ?, feedback = ?, status = 'graded', graded_at = NOW() WHERE id = ?";
        
        // Log SQL query and parameters before prepare
        file_put_contents($grade_debug_file, "Update SQL: " . $sql_update . "\n", FILE_APPEND);
        file_put_contents($grade_debug_file, "Update Params (issi): " . print_r([$marks_obtained, $calculated_grade_code, $feedback, $submission_id], true) . "\n", FILE_APPEND);

        $stmt_update = $conn->prepare($sql_update);
        
        if (!$stmt_update) {
            // Log prepare error
            file_put_contents($grade_debug_file, "SQL update prepare failed: " . $conn->error . "\n", FILE_APPEND);
            $response['message'] = 'Failed to prepare update query: ' . $conn->error;
            $conn->close();
            echo json_encode($response);
            break;
        }

        $stmt_update->bind_param('issi', $marks_obtained, $calculated_grade_code, $feedback, $submission_id);
        
        // Log after bind_param
        file_put_contents($grade_debug_file, "After update bind_param\n", FILE_APPEND);

        // Log immediately before execute
        file_put_contents($grade_debug_file, "Before update execute\n", FILE_APPEND);

        try {
            if ($stmt_update->execute()) {
                // Log success
                file_put_contents($grade_debug_file, "SQL update execute success\n", FILE_APPEND);
                $response = ['status' => 'success', 'marks_obtained' => $marks_obtained, 'grade_code' => $calculated_grade_code]; // Return updated data
            } else {
                // Log execute error
                file_put_contents($grade_debug_file, "SQL update execute failed: " . $stmt_update->error . "\n", FILE_APPEND);
                $response['message'] = 'Database update error: ' . $stmt_update->error;
            }
        } catch (mysqli_sql_exception $e) {
            // Log caught exception
            file_put_contents($grade_debug_file, "Caught SQL Update Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            $response['message'] = 'SQL Update Exception: ' . $e->getMessage();
        } catch (Exception $e) {
            // Log other caught exceptions
            file_put_contents($grade_debug_file, "Caught Update Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            $response['message'] = 'An unexpected error occurred during update: ' . $e->getMessage();
        }

        $stmt_update->close();
        $conn->close();
        echo json_encode($response);
        break;
    case 'get_submissions':
        // --- Debugging start for get_submissions ---
        $get_submissions_debug_file = __DIR__ . '/get_submissions_debug.log';
        file_put_contents($get_submissions_debug_file, "\n---\nRequest Start: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        file_put_contents($get_submissions_debug_file, "GET Data: " . print_r($_GET, true) . "\n", FILE_APPEND);
        file_put_contents($get_submissions_debug_file, "POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);
        // --- Debugging end for get_submissions ---

        // Only allow teachers to fetch submissions
        if ($_SESSION['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Only teachers can view submissions.']);
            break;
        }
        // Allow fetching by homework_id (for list) or submission_id (for modal)
        $homework_id = $_POST['homework_id'] ?? $_GET['homework_id'] ?? '';
        $submission_id = $_POST['submission_id'] ?? $_GET['submission_id'] ?? '';

        if (!$homework_id && !$submission_id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing homework_id or submission_id.']);
            break;
        }

        $conn = getDbConnection();
        // Join with homework table to get total_marks
        $sql = "SELECT hs.id, hs.file_path, hs.submitted_at, hs.status, hs.grade_code, hs.feedback, hs.marks_obtained, s.full_name AS student_name, h.total_marks
                FROM homework_submissions hs
                JOIN students s ON hs.student_user_id = s.user_id
                JOIN homework h ON hs.homework_id = h.id";
        
        $params = [];
        $types = '';

        if ($submission_id) {
            $sql .= " WHERE hs.id = ?";
            $params[] = $submission_id;
            $types .= 'i';
        } else if ($homework_id) {
            $sql .= " WHERE hs.homework_id = ?";
            $params[] = $homework_id;
            $types .= 'i';
        }

        $sql .= " ORDER BY hs.submitted_at DESC";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare query: ' . $conn->error]);
            $conn->close();
            break;
        }
        
        if (!empty($params)) {
             $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        $stmt->close();
        $conn->close();
        echo json_encode(['status' => 'success', 'submissions' => $submissions]);
        break;
    case 'get_classes':
        if ($_SESSION['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            break;
        }
        $conn = getDbConnection();
        $sql = "SELECT id, name FROM classes ORDER BY name";
        $result = $conn->query($sql);
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        $conn->close();
        echo json_encode(['status' => 'success', 'classes' => $classes]);
        break;
    case 'get_sections':
        if ($_SESSION['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            break;
        }
        $class_id = $_POST['class_id'] ?? $_GET['class_id'] ?? 0;
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, name, class_id FROM sections WHERE class_id = ? ORDER BY name");
        $stmt->bind_param('i', $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $sections = [];
        while ($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
        $stmt->close();
        $conn->close();
        echo json_encode(['status' => 'success', 'sections' => $sections]);
        break;
    case 'get_subjects':
        if ($_SESSION['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            break;
        }
        $conn = getDbConnection();
        $sql = "SELECT id, name FROM subjects ORDER BY name";
        $result = $conn->query($sql);
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        $conn->close();
        echo json_encode(['status' => 'success', 'subjects' => $subjects]);
        break;
    case 'delete_homework':
        $data = json_decode(file_get_contents('php://input'), true);
        $homework_id = $data['homework_id'] ?? 0;
        $teacher_id = $_SESSION['user_id'];
        
        if (!$homework_id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing homework ID']);
            break;
        }
        
        $conn = getDbConnection();
        
        // First verify the homework belongs to this teacher
        $sql = "SELECT id FROM homework WHERE id = ? AND teacher_user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $homework_id, $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Homework not found or unauthorized']);
            $stmt->close();
            $conn->close();
            break;
        }
        
        // Delete associated submissions first
        $sql = "DELETE FROM homework_submissions WHERE homework_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $homework_id);
        $stmt->execute();
        
        // Then delete the homework
        $sql = "DELETE FROM homework WHERE id = ? AND teacher_user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $homework_id, $teacher_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete homework']);
        }
        
        $stmt->close();
        $conn->close();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
} 