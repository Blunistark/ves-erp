<?php
// Include connection and functions
require_once 'con.php';
require_once '../../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication
if (!isLoggedIn() || !hasRole(['teacher', 'headmaster'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Set content type to JSON
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_profile':
            getProfileData($conn, $user_id);
            break;
            
        case 'update_personal_info':
            updatePersonalInfo($conn, $user_id);
            break;
            
        case 'upload_profile_photo':
            uploadProfilePhoto($conn, $user_id);
            break;
            
        case 'upload_document':
            uploadDocument($conn, $user_id);
            break;
            
        case 'get_documents':
            getDocuments($conn, $user_id);
            break;
            
        case 'delete_document':
            deleteDocument($conn, $user_id);
            break;
            
        case 'add_education':
            addEducation($conn, $user_id);
            break;
            
        case 'get_education':
            getEducation($conn, $user_id);
            break;
            
        case 'delete_education':
            deleteEducation($conn, $user_id);
            break;
            
        case 'update_notification_settings':
            updateNotificationSettings($conn, $user_id);
            break;
            
        case 'get_notification_settings':
            getNotificationSettings($conn, $user_id);
            break;
            
        case 'change_password':
            changePassword($conn, $user_id);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getProfileData($conn, $user_id) {
    $query = "SELECT u.id, u.email, u.full_name, u.created_at,
                     t.employee_number, t.joined_date, t.qualification, t.date_of_birth,
                     t.profile_photo, t.address, t.city, t.phone, t.alt_email,
                     t.emergency_contact, t.gender, t.state, t.zip_code, t.country,
                     t.department, t.position, t.experience_years, t.bio
              FROM users u 
              LEFT JOIN teachers t ON u.id = t.user_id 
              WHERE u.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Split full name into first and last name
        $nameParts = explode(' ', $row['full_name'], 2);
        $row['first_name'] = $nameParts[0] ?? '';
        $row['last_name'] = $nameParts[1] ?? '';
        
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        throw new Exception('Profile not found');
    }
}

function updatePersonalInfo($conn, $user_id) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $full_name = $first_name . ' ' . $last_name;
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $alt_email = trim($_POST['alt_email'] ?? '');
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $country = $_POST['country'] ?? '';
    $department = $_POST['department'] ?? '';
    $position = trim($_POST['position'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $bio = trim($_POST['bio'] ?? '');

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        throw new Exception('First name, last name, and email are required');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update users table
        $user_query = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("ssi", $full_name, $email, $user_id);
        $user_stmt->execute();        // Check if teacher record exists
        $check_query = "SELECT user_id FROM teachers WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $teacher_result = $check_stmt->get_result();
        
        if ($teacher_result->num_rows > 0) {
            // Update existing teacher record
            $teacher_query = "UPDATE teachers SET 
                phone = ?, alt_email = ?, emergency_contact = ?, date_of_birth = ?, gender = ?,
                address = ?, city = ?, state = ?, zip_code = ?, country = ?, department = ?, 
                position = ?, experience_years = ?, bio = ?
                WHERE user_id = ?";
            
            $teacher_stmt = $conn->prepare($teacher_query);
            $teacher_stmt->bind_param(
                "ssssssssssssisi",
                $phone, $alt_email, $emergency_contact, $date_of_birth, $gender,
                $address, $city, $state, $zip_code, $country, $department,
                $position, $experience_years, $bio, $user_id
            );
            $teacher_stmt->execute();
        } else {
            // Teacher record doesn't exist - this shouldn't happen for logged-in teachers
            throw new Exception('Teacher profile not found. Please contact administration.');
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function uploadProfilePhoto($conn, $user_id) {
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['profile_photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Only JPEG, PNG, and GIF images are allowed');
    }

    if ($file['size'] > $max_size) {
        throw new Exception('File size must be less than 5MB');
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../../uploads/profile_photos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'teacher_' . $user_id . '_' . time() . '.' . $extension;
    $file_path = $upload_dir . $filename;    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Update database - only update existing teacher records
        $query = "UPDATE teachers SET profile_photo = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $db_path = 'uploads/profile_photos/' . $filename;
        $stmt->bind_param("si", $db_path, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'photo_url' => $db_path, 'message' => 'Profile photo updated successfully']);
        } else {
            throw new Exception('Teacher profile not found. Please contact administration.');
        }
    } else {
        throw new Exception('Failed to upload file');
    }
}

function uploadDocument($conn, $user_id) {
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['document'];
    $document_type = $_POST['document_type'] ?? 'other';
    $document_name = trim($_POST['document_name'] ?? '');

    if (empty($document_name)) {
        throw new Exception('Document name is required');
    }

    $allowed_types = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $max_size = 10 * 1024 * 1024; // 10MB

    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Only PDF, Word documents, and images are allowed');
    }

    if ($file['size'] > $max_size) {
        throw new Exception('File size must be less than 10MB');
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../../uploads/teacher_documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'doc_' . $user_id . '_' . time() . '.' . $extension;
    $file_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Save to database
        $query = "INSERT INTO teacher_documents (teacher_user_id, document_type, document_name, 
                  original_filename, file_path, file_size) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $db_path = 'uploads/teacher_documents/' . $filename;
        $stmt->bind_param("issssi", $user_id, $document_type, $document_name, 
                         $file['name'], $db_path, $file['size']);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Document uploaded successfully']);
    } else {
        throw new Exception('Failed to upload file');
    }
}

function getDocuments($conn, $user_id) {
    $query = "SELECT id, document_type, document_name, original_filename, file_path, 
              file_size, uploaded_at, status FROM teacher_documents 
              WHERE teacher_user_id = ? ORDER BY uploaded_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $documents = [];
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }

    echo json_encode(['success' => true, 'documents' => $documents]);
}

function deleteDocument($conn, $user_id) {
    $document_id = (int)($_POST['document_id'] ?? 0);

    if (!$document_id) {
        throw new Exception('Document ID is required');
    }

    // Get file path before deletion
    $query = "SELECT file_path FROM teacher_documents WHERE id = ? AND teacher_user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $document_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Delete file from filesystem
        $file_path = '../../' . $row['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $delete_query = "DELETE FROM teacher_documents WHERE id = ? AND teacher_user_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("ii", $document_id, $user_id);
        $delete_stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
    } else {
        throw new Exception('Document not found');
    }
}

function addEducation($conn, $user_id) {
    $institution = trim($_POST['institution'] ?? '');
    $degree = trim($_POST['degree'] ?? '');
    $field = trim($_POST['field'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $is_completed = isset($_POST['is_completed']) ? 1 : 0;
    $grade = floatval($_POST['grade'] ?? 0);

    if (empty($institution) || empty($degree) || empty($field)) {
        throw new Exception('Institution, degree, and field of study are required');
    }

    $query = "INSERT INTO teacher_education (teacher_user_id, institution_name, degree_type, 
              field_of_study, start_date, end_date, is_completed, grade_percentage) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssid", $user_id, $institution, $degree, $field, 
                     $start_date, $end_date, $is_completed, $grade);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Education added successfully']);
}

function getEducation($conn, $user_id) {
    $query = "SELECT * FROM teacher_education WHERE teacher_user_id = ? ORDER BY end_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $education = [];
    while ($row = $result->fetch_assoc()) {
        $education[] = $row;
    }

    echo json_encode(['success' => true, 'education' => $education]);
}

function deleteEducation($conn, $user_id) {
    $education_id = (int)($_POST['education_id'] ?? 0);

    if (!$education_id) {
        throw new Exception('Education ID is required');
    }

    $query = "DELETE FROM teacher_education WHERE id = ? AND teacher_user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $education_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Education record deleted successfully']);
    } else {
        throw new Exception('Education record not found');
    }
}

function updateNotificationSettings($conn, $user_id) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    $announcement_notifications = isset($_POST['announcement_notifications']) ? 1 : 0;
    $assignment_notifications = isset($_POST['assignment_notifications']) ? 1 : 0;
    $student_absence_notifications = isset($_POST['student_absence_notifications']) ? 1 : 0;
    $meeting_reminders = isset($_POST['meeting_reminders']) ? 1 : 0;
    $system_updates = isset($_POST['system_updates']) ? 1 : 0;

    $query = "INSERT INTO teacher_notification_settings (
        teacher_user_id, email_notifications, sms_notifications, push_notifications,
        announcement_notifications, assignment_notifications, student_absence_notifications,
        meeting_reminders, system_updates
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        email_notifications = VALUES(email_notifications),
        sms_notifications = VALUES(sms_notifications),
        push_notifications = VALUES(push_notifications),
        announcement_notifications = VALUES(announcement_notifications),
        assignment_notifications = VALUES(assignment_notifications),
        student_absence_notifications = VALUES(student_absence_notifications),
        meeting_reminders = VALUES(meeting_reminders),
        system_updates = VALUES(system_updates)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiiiiii", $user_id, $email_notifications, $sms_notifications,
                     $push_notifications, $announcement_notifications, $assignment_notifications,
                     $student_absence_notifications, $meeting_reminders, $system_updates);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Notification settings updated successfully']);
}

function getNotificationSettings($conn, $user_id) {
    $query = "SELECT * FROM teacher_notification_settings WHERE teacher_user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'settings' => $row]);
    } else {
        // Return default settings
        $defaults = [
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'announcement_notifications' => true,
            'assignment_notifications' => true,
            'student_absence_notifications' => true,
            'meeting_reminders' => true,
            'system_updates' => false
        ];
        echo json_encode(['success' => true, 'settings' => $defaults]);
    }
}

function changePassword($conn, $user_id) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        throw new Exception('All password fields are required');
    }

    if ($new_password !== $confirm_password) {
        throw new Exception('New passwords do not match');
    }

    if (strlen($new_password) < 8) {
        throw new Exception('New password must be at least 8 characters long');
    }

    // Verify current password
    $query = "SELECT password_hash FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!password_verify($current_password, $row['password_hash'])) {
            throw new Exception('Current password is incorrect');
        }

        // Update password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password_hash = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_hash, $user_id);
        $update_stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } else {        throw new Exception('User not found');
    }
}

function generateEmployeeNumber($conn) {
    // Get current year
    $year = date('Y');
    
    // Get next sequence number for this year
    $query = "SELECT employee_number FROM teachers WHERE employee_number LIKE ? ORDER BY employee_number DESC LIMIT 1";
    $pattern = "VES{$year}T%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Extract the sequence number from the last employee number
        $lastNumber = $row['employee_number'];
        preg_match('/VES' . $year . 'T(\d+)/', $lastNumber, $matches);
        $sequence = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
    } else {
        $sequence = 1;
    }
    
    // Format: VES2025T001, VES2025T002, etc.
    return sprintf("VES%sT%03d", $year, $sequence);
}
?>
