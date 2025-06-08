<?php
session_start();
include '../includes/config.php';
include 'con.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit();
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Create a new notice
    if ($action === 'create') {
        $title = sanitize_input($_POST['title']);
        $content = $_POST['content']; // Allow HTML content for rich text
        $teacher_id = $_POST['teacher_id'];
        $class_section = explode('_', $_POST['class_section']);
        $class_id = $class_section[0];
        $section_id = isset($class_section[1]) ? $class_section[1] : null;
        $expiry_date = !empty($_POST['expiry_date']) ? sanitize_input($_POST['expiry_date']) : NULL;
        
        // Validate inputs
        $errors = [];
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        if (empty($content)) {
            $errors[] = "Content is required";
        }
        if (empty($class_id)) {
            $errors[] = "Class is required";
        }
        
        // If no errors, insert notice
        if (empty($errors)) {
            $query = "INSERT INTO class_notices (title, content, teacher_id, class_id, section_id, expiry_date) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssiiss", $title, $content, $teacher_id, $class_id, $section_id, $expiry_date);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Notice created successfully";
                header('Location: notice.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Error creating notice: " . $conn->error;
                header('Location: notice.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = implode(", ", $errors);
            header('Location: notice.php');
            exit();
        }
    }
    
    // Update an existing notice
    else if ($action === 'update') {
        $id = sanitize_input($_POST['id']);
        $title = sanitize_input($_POST['title']);
        $content = $_POST['content']; // Allow HTML content for rich text
        $class_section = explode('_', $_POST['class_section']);
        $class_id = $class_section[0];
        $section_id = isset($class_section[1]) ? $class_section[1] : null;
        $expiry_date = !empty($_POST['expiry_date']) ? sanitize_input($_POST['expiry_date']) : NULL;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate inputs
        $errors = [];
        if (empty($id) || !is_numeric($id)) {
            $errors[] = "Invalid notice ID";
        }
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        if (empty($content)) {
            $errors[] = "Content is required";
        }
        if (empty($class_id)) {
            $errors[] = "Class is required";
        }
        
        // Verify that the teacher owns this notice
        $verify_query = "SELECT * FROM class_notices WHERE id = ? AND teacher_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            $_SESSION['error_message'] = "You don't have permission to edit this notice";
            header('Location: notice.php');
            exit();
        }
        
        // If no errors, update notice
        if (empty($errors)) {
            $query = "UPDATE class_notices 
                      SET title = ?, content = ?, class_id = ?, section_id = ?, 
                          expiry_date = ?, is_active = ? 
                      WHERE id = ? AND teacher_id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssiissii", $title, $content, $class_id, $section_id, $expiry_date, $is_active, $id, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Notice updated successfully";
                header('Location: notice.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Error updating notice: " . $conn->error;
                header('Location: notice.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = implode(", ", $errors);
            header('Location: notice.php');
            exit();
        }
    }
    
    // Delete a notice
    else if ($action === 'delete') {
        $id = sanitize_input($_POST['id']);
        
        // Validate input
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['error_message'] = "Invalid notice ID";
            header('Location: notice.php');
            exit();
        }
        
        // Verify that the teacher owns this notice
        $verify_query = "SELECT * FROM class_notices WHERE id = ? AND teacher_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            $_SESSION['error_message'] = "You don't have permission to delete this notice";
            header('Location: notice.php');
            exit();
        }
        
        // Delete the notice
        $query = "DELETE FROM class_notices WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Also delete read status records for this notice
            $read_query = "DELETE FROM notification_read_status 
                           WHERE notification_type = 'notice' AND notification_id = ?";
            $read_stmt = $conn->prepare($read_query);
            $read_stmt->bind_param("i", $id);
            $read_stmt->execute();
            
            $_SESSION['success_message'] = "Notice deleted successfully";
            header('Location: notice.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Error deleting notice: " . $conn->error;
            header('Location: notice.php');
            exit();
        }
    }
    
    // If action is not recognized
    else {
        $_SESSION['error_message'] = "Invalid action";
        header('Location: notice.php');
        exit();
    }
}

// Handle GET request for AJAX operations
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Get a single notice for editing
    if ($action === 'get_notice') {
        $id = sanitize_input($_GET['id']);
        
        // Validate input
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['error' => 'Invalid notice ID']);
            exit();
        }
        
        // Get the notice (ensure it belongs to this teacher)
        $query = "SELECT * FROM class_notices WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $notice = $result->fetch_assoc();
            echo json_encode($notice);
            exit();
        } else {
            echo json_encode(['error' => 'Notice not found or you do not have permission to edit it']);
            exit();
        }
    }
    
    // If action is not recognized
    else {
        echo json_encode(['error' => 'Invalid action']);
        exit();
    }
}

// If neither POST nor GET request with valid action
header('Location: notice.php');
exit();
?> 