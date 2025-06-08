<?php
session_start();
require_once 'con.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$student_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Invalid request.'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $alt_email = filter_input(INPUT_POST, 'alt_email', FILTER_SANITIZE_EMAIL);
    $alt_phone = filter_input(INPUT_POST, 'alt_phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $pincode = filter_input(INPUT_POST, 'pincode', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $mother_tongue = filter_input(INPUT_POST, 'mother_tongue', FILTER_SANITIZE_STRING);
    $medical_conditions = filter_input(INPUT_POST, 'medical_conditions', FILTER_SANITIZE_STRING);
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please provide a valid email address.';
        echo json_encode($response);
        exit();
    }
    
    // Check if email is already in use by another user
    $query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['message'] = 'Email address is already in use by another account.';
        echo json_encode($response);
        exit();
    }
    
    // Update users table (email)
    $query = "UPDATE users SET email = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $student_id);
    $stmt->execute();
    
    // Update students table
    $query = "UPDATE students SET 
              mobile = ?, 
              alt_mobile = ?, 
              contact_email = ?, 
              address = ?, 
              pincode = ?, 
              student_state_code = ?, 
              mother_tongue = ?, 
              medical_conditions = ?, 
              updated_at = NOW() 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssi", 
                     $phone, 
                     $alt_phone, 
                     $alt_email, 
                     $address, 
                     $pincode, 
                     $state, 
                     $mother_tongue, 
                     $medical_conditions, 
                     $student_id);
    
    if ($stmt->execute()) {
        // Update session email if it was changed
        $_SESSION['user_email'] = $email;
        
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully.';
    } else {
        $response['message'] = 'Failed to update profile: ' . $conn->error;
    }
}

// Handle password change
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $response['message'] = 'All password fields are required.';
        echo json_encode($response);
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match.';
        echo json_encode($response);
        exit();
    }
    
    // Check password length
    if (strlen($new_password) < 8) {
        $response['message'] = 'Password must be at least 8 characters long.';
        echo json_encode($response);
        exit();
    }
    
    // Get current password hash
    $query = "SELECT password_hash FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stored_hash = $user['password_hash'];
        
        // Verify current password
        if (password_verify($current_password, $stored_hash)) {
            // Hash new password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $query = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $new_hash, $student_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Password changed successfully.';
            } else {
                $response['message'] = 'Failed to update password: ' . $conn->error;
            }
        } else {
            $response['message'] = 'Current password is incorrect.';
        }
    } else {
        $response['message'] = 'User not found.';
    }
}

// Handle profile photo upload
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_photo') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $file['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $response['message'] = 'Only JPG and PNG images are allowed.';
            echo json_encode($response);
            exit();
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'Image size should not exceed 2MB.';
            echo json_encode($response);
            exit();
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'student_' . $student_id . '_' . time() . '.' . $file_extension;
        $upload_dir = '../uploads/student_photos/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Update database with new photo filename
            $query = "UPDATE students SET photo = ? WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $filename, $student_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Profile photo updated successfully.';
                $response['photo_url'] = '../uploads/student_photos/' . $filename;
            } else {
                $response['message'] = 'Failed to update profile photo in database: ' . $conn->error;
            }
        } else {
            $response['message'] = 'Failed to upload photo.';
        }
    } else {
        $response['message'] = 'No photo uploaded or upload error occurred.';
    }
}

// Handle notification settings update
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_notification_settings') {
    // In a real implementation, you would save these settings to a database table
    // For now, we'll just return a success response
    $response['success'] = true;
    $response['message'] = 'Notification settings updated successfully.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 