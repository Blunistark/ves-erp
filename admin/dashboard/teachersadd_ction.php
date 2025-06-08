<?php
require_once 'con.php';
require_once 'clear_cache.php'; // Include cache clearing utility
header('Content-Type: application/json');

function log_error($msg) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    file_put_contents($log_dir . '/teachersadd_debug.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
}

function response($success, $message, $errors = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'errors' => $errors
    ]);
    exit;
}

function generateEmployeeId($conn) {
    // Get the highest employee number with VES2025T format
    $stmt = $conn->prepare("SELECT employee_number FROM teachers WHERE employee_number LIKE 'VES2025T%' ORDER BY employee_number DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['employee_number'];
        // Extract the number part (e.g., VES2025T001 -> 001)
        $number = intval(substr($lastId, 8));
        $nextNumber = $number + 1;
    } else {
        $nextNumber = 1;
    }
    
    // Format as VES2025T001, VES2025T002, etc.
    return 'VES2025T' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}

$conn->begin_transaction();

try {
    // Required fields (employeeNumber is auto-generated, password is optional)
    $required = [
        'fullName', 'dateOfBirth', 'joiningDate', 'qualification', 
        'email', 'address', 'city'
    ];
    
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "$field is required.";
        }
    }

    // Password validation - only if password is provided
    $passwordProvided = !empty($_POST['password']);
    if ($passwordProvided) {
        if (empty($_POST['confirmPassword'])) {
            $errors[] = "Confirm password is required when password is provided.";
        } elseif ($_POST['password'] !== $_POST['confirmPassword']) {
            $errors[] = "Passwords do not match.";
        }
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (!empty($errors)) {
        log_error('Validation failed: ' . json_encode($errors));
        response(false, 'Validation failed.', $errors);
    }

    $email = $_POST['email'];
    
    // Generate employee ID automatically
    $employeeNumber = generateEmployeeId($conn);
    
    // Determine password to use
    if ($passwordProvided) {
        $finalPassword = $_POST['password'];
    } else {
        // Use date of birth in YYYYMMDD format as password
        $dobFormatted = str_replace('-', '', $_POST['dateOfBirth']);
        $finalPassword = $dobFormatted;
    }

    // Check unique email in users table
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    if (!$stmt) {
        log_error('Prepare failed: ' . $conn->error);
        response(false, 'Database error: ' . $conn->error);
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        log_error('Duplicate email: ' . $email);
        response(false, 'Email already exists.');
    }
    $stmt->close();

    // Handle profile photo upload
    $profilePhoto = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($ext), $allowed)) {
            log_error('Invalid file type: ' . $ext);
            response(false, 'Invalid file type for profile photo.');
        }
        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            log_error('File too large: ' . $_FILES['photo']['size']);
            response(false, 'Profile photo must be less than 2MB.');
        }
        $profilePhoto = 'uploads/teachers/' . uniqid('teacher_') . '.' . $ext;
        $upload_dir = __DIR__ . '/../../uploads/teachers';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                log_error('Failed to create uploads/teachers directory');
                response(false, 'Failed to create upload directory.');
            }
        }
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . '/' . basename($profilePhoto))) {
            log_error('Failed to move uploaded file: ' . error_get_last()['message']);
            throw new Exception('Failed to save profile photo.');
        }
    }

    // Insert into users table
    $stmt = $conn->prepare('INSERT INTO users (email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        log_error('Prepare (users insert) failed: ' . $conn->error);
        throw new Exception('Database error during user creation: ' . $conn->error);
    }
    $passwordHash = password_hash($finalPassword, PASSWORD_DEFAULT);
    $fullName = trim($_POST['fullName']);
    $userRole = 'teacher';
    $status = 'active';
    
    $stmt->bind_param('sssss', $email, $passwordHash, $fullName, $userRole, $status);
    if (!$stmt->execute()) {
        log_error('Execute (users insert) failed: ' . $stmt->error);
        throw new Exception('Database error during user creation: ' . $stmt->error);
    }
    $newUserId = $conn->insert_id;
    $stmt->close();

    // Insert into teachers table
    $stmt = $conn->prepare('INSERT INTO teachers (user_id, employee_number, joined_date, qualification, date_of_birth, profile_photo, address, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        log_error('Prepare (teachers insert) failed: ' . $conn->error);
        throw new Exception('Database error during teacher creation: ' . $conn->error);
    }
    
    $stmt->bind_param('isssssss', 
        $newUserId,
        $employeeNumber,
        $_POST['joiningDate'],
        $_POST['qualification'],
        $_POST['dateOfBirth'],
        $profilePhoto,
        $_POST['address'],
        $_POST['city']
    );

    if (!$stmt->execute()) {
        log_error('Execute (teachers insert) failed: ' . $stmt->error);
        throw new Exception('Database error during teacher creation: ' . $stmt->error);
    }
    $stmt->close();

    // Send email with credentials if requested
    if (isset($_POST['sendCredentials']) && $_POST['sendCredentials'] === 'on') {
        // Implement email sending logic here
        // For now, we'll just log it
        log_error('Should send credentials email to: ' . $email);
    }

    $conn->commit();
    
    // Clear all teacher-related cache after successful creation
    clearTeacherCache();
    
    $passwordMessage = $passwordProvided ? 
        "Teacher added successfully. Employee ID: $employeeNumber" : 
        "Teacher added successfully. Employee ID: $employeeNumber. Password set to date of birth (YYYYMMDD format).";
    
    response(true, $passwordMessage);

} catch (Exception $e) {
    $conn->rollback();
    log_error('Error in teacher creation: ' . $e->getMessage());
    response(false, 'Error creating teacher: ' . $e->getMessage());
}
?> 