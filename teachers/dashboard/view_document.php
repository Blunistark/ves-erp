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
    $stmt->bind_param("ii", $document_id, $user_id);    $stmt->execute();
    $result = $stmt->get_result();
      if ($row = $result->fetch_assoc()) {
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
            // Fallback MIME type detection based on extension
            $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'pdf':
                    $mime_type = 'application/pdf';
                    break;
                case 'doc':
                    $mime_type = 'application/msword';
                    break;
                case 'docx':
                    $mime_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                    break;
                case 'jpg':
                case 'jpeg':
                    $mime_type = 'image/jpeg';
                    break;
                case 'png':
                    $mime_type = 'image/png';
                    break;
                case 'gif':
                    $mime_type = 'image/gif';
                    break;
                default:
                    $mime_type = 'application/octet-stream';
            }
        }
          // Set appropriate headers for viewing documents/images
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        
        // For images, set additional headers to help with browser display
        if (strpos($mime_type, 'image/') === 0) {
            header('Content-Disposition: inline; filename="' . $original_filename . '"');
            header('Cache-Control: public, max-age=3600');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        } else {
            header('Content-Disposition: inline; filename="' . $original_filename . '"');
            header('Cache-Control: private, max-age=3600');
        }
        
        // Prevent any output before the file content
        ob_clean();
        flush();
        
        // Output file
        readfile($file_path);
        exit;
        
    } else {
        header('HTTP/1.0 404 Not Found');
        exit('Document not found or access denied');
    }
    
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Error retrieving document');
}
?>
