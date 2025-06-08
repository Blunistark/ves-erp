<?php
// Enable error reporting for diagnostic purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to check user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Show the upload form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test File Upload</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; }
            .result { margin-top: 20px; padding: 10px; border: 1px solid #ddd; }
            .success { color: green; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <h1>Test File Upload</h1>
        <p>This form tests if file uploads are working correctly on the server.</p>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="test_file">Select a test file:</label>
                <input type="file" name="test_file" id="test_file" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Upload Test File</button>
            </div>
        </form>
        
        <div class="result">
            <h2>Server Information:</h2>
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>Memory Limit: <?php echo ini_get('memory_limit'); ?></p>
            <p>Post Max Size: <?php echo ini_get('post_max_size'); ?></p>
            <p>Upload Max Filesize: <?php echo ini_get('upload_max_filesize'); ?></p>
            <p>Max Execution Time: <?php echo ini_get('max_execution_time'); ?></p>
            
            <h2>Upload Directory Status:</h2>
            <?php
            $upload_dir = '../../uploads/test_uploads/';
            if (!file_exists($upload_dir)) {
                echo "<p>Directory does not exist. Attempting to create...</p>";
                if (@mkdir($upload_dir, 0755, true)) {
                    echo "<p class='success'>Directory created successfully!</p>";
                } else {
                    echo "<p class='error'>Failed to create directory: " . error_get_last()['message'] . "</p>";
                }
            } else {
                echo "<p>Directory exists.</p>";
                
                if (is_writable($upload_dir)) {
                    echo "<p class='success'>Directory is writable.</p>";
                } else {
                    echo "<p class='error'>Directory is NOT writable.</p>";
                }
            }
            ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process the file upload
try {
    // Create the upload directory if it doesn't exist
    $upload_dir = '../../uploads/test_uploads/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory: " . error_get_last()['message']);
        }
    }
    
    // Check if a file was uploaded
    if (!isset($_FILES['test_file']) || $_FILES['test_file']['error'] != 0) {
        throw new Exception("File upload error: " . ($_FILES['test_file']['error'] ?? 'No file uploaded'));
    }
    
    // Generate a unique filename
    $file_name = 'test_' . time() . '_' . basename($_FILES['test_file']['name']);
    $target_file = $upload_dir . $file_name;
    
    // Move the uploaded file
    if (!move_uploaded_file($_FILES['test_file']['tmp_name'], $target_file)) {
        throw new Exception("Failed to move uploaded file: " . error_get_last()['message']);
    }
    
    // File uploaded successfully
    $response = [
        'success' => true,
        'message' => 'File uploaded successfully',
        'details' => [
            'file_name' => $file_name,
            'file_size' => $_FILES['test_file']['size'],
            'file_type' => $_FILES['test_file']['type'],
            'target_path' => $target_file
        ]
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'details' => [
            'error_code' => $_FILES['test_file']['error'] ?? -1,
            'file_info' => $_FILES['test_file'] ?? 'No file data',
            'php_info' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]
        ]
    ];
}

// Output the result
header('Content-Type: application/json');
echo json_encode($response);
?>