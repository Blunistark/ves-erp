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
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$user_id = $_SESSION['user_id'];
$document_id = (int)($_GET['id'] ?? 0);

if (!$document_id) {
    header('HTTP/1.0 400 Bad Request');
    exit('Document ID is required');
}

try {
    // Get document details
    $query = "SELECT file_path, original_filename, document_name FROM teacher_documents WHERE id = ? AND teacher_user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $document_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();    if ($row = $result->fetch_assoc()) {
        // Use absolute path instead of relative path
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/erp/' . $row['file_path'];
        $original_filename = $row['original_filename'];
        $document_name = $row['document_name'];
        
        // Check if file exists
        if (!file_exists($file_path)) {
            header('HTTP/1.0 404 Not Found');
            exit('File not found');
        }
        
        // Get MIME type
        $mime_type = mime_content_type($file_path);
        if (!$mime_type) {
            $mime_type = 'application/octet-stream';
        }
        
        // Set headers for download
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: attachment; filename="' . $original_filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Output file
        readfile($file_path);
        exit;
        
    } else {
        header('HTTP/1.0 404 Not Found');
        exit('Document not found or access denied');
    }
    
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Error downloading document');
}
?>
