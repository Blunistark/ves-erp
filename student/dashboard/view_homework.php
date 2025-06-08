<?php
require_once 'con.php';
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit;
}

$student_id = $_SESSION['user_id'];
$homework_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no homework ID is provided, redirect to homework list
if ($homework_id <= 0) {
    header('Location: homework.php');
    exit;
}

// Get student details
$conn = getDbConnection();
$sql = "SELECT s.*, c.name AS class_name, sec.name AS section_name
        FROM students s
        JOIN classes c ON s.class_id = c.id
        JOIN sections sec ON s.section_id = sec.id
        WHERE s.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header('Location: ../index.php');
    exit;
}

// Get homework details
$sql = "SELECT h.*, s.name AS subject_name, u.full_name AS teacher_name,
        hs.id AS submission_id, hs.file_path, hs.status AS submission_status,
        hs.grade_code, hs.feedback, hs.submitted_at
        FROM homework h
        JOIN subjects s ON h.subject_id = s.id
        JOIN teachers t ON h.teacher_user_id = t.user_id
        JOIN users u ON t.user_id = u.id
        LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_user_id = ?
        WHERE h.id = ? AND h.class_id = ? AND h.section_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiii', $student_id, $homework_id, $student['class_id'], $student['section_id']);
$stmt->execute();
$homework = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

// If homework doesn't exist or doesn't belong to student's class/section
if (!$homework) {
    header('Location: homework.php');
    exit;
}

// Format submission data
$submission = null;
if ($homework['submission_id']) {
    $submission = [
        'id' => $homework['submission_id'],
        'file_path' => $homework['file_path'],
        'status' => $homework['submission_status'],
        'grade_code' => $homework['grade_code'],
        'feedback' => $homework['feedback'],
        'submitted_at' => $homework['submitted_at']
    ];
}

// Helper functions
function formatDate($dateStr) {
    $date = new DateTime($dateStr);
    return $date->format('M d, Y');
}

function isOverdue($dateStr) {
    $dueDate = new DateTime($dateStr);
    $now = new DateTime();
    return $dueDate < $now;
}

function getStatusText($homework, $submission) {
    if (!$submission) {
        return isOverdue($homework['due_date']) ? 'Overdue' : 'Pending';
    }
    return ucfirst($submission['status']);
}

function getStatusClass($homework, $submission) {
    if (!$submission) {
        return isOverdue($homework['due_date']) ? 'status-overdue' : 'status-pending';
    }
    return 'status-' . $submission['status'];
}

?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Details - <?php echo htmlspecialchars($homework['title']); ?></title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/homework.css">
    <style>
        .homework-detail-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .homework-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eaeaea;
        }
        
        .homework-title {
            font-size: 1.8rem;
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .homework-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .meta-label {
            font-weight: 600;
            color: #666;
        }
        
        .meta-value {
            color: #333;
        }
        
        .homework-status {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            min-width: 100px;
        }
        
        .status-pending {
            background-color: #f0f9ff;
            color: #0369a1;
        }
        
        .status-submitted {
            background-color: #f0fdf4;
            color: #15803d;
        }
        
        .status-graded {
            background-color: #faf5ff;
            color: #7e22ce;
        }
        
        .status-overdue {
            background-color: #fef2f2;
            color: #b91c1c;
        }
        
        .homework-description {
            margin-bottom: 2rem;
            line-height: 1.6;
            color: #444;
        }
        
        .attachment-container {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 6px;
        }
        
        .submission-container {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eaeaea;
        }
        
        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .submission-title {
            font-size: 1.5rem;
            margin: 0;
        }
        
        .submission-form {
            padding: 1.5rem;
            background-color: #f9fafb;
            border-radius: 6px;
        }
        
        .submission-details {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background-color: #f9fafb;
            border-radius: 6px;
        }
        
        .file-upload {
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .file-upload:hover {
            border-color: #9ca3af;
        }
        
        .upload-icon {
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
            color: #6b7280;
        }
        
        .upload-text {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .upload-hint {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .offline-submission {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #374151;
        }
        
        .feedback-container {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background-color: #f0f9ff;
            border-radius: 6px;
            border-left: 4px solid #0369a1;
        }
        
        .feedback-header {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #0369a1;
        }
        
        .grade-container {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #0369a1;
            color: white;
            border-radius: 4px;
            margin-left: 1rem;
            font-weight: 600;
        }
        
        .back-button {
            margin-bottom: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .back-button:hover {
            color: #111827;
        }
        
        .btn-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        /* Ensure dashboard container behaves like attendance page */
        .dashboard-container {
            margin-left: 280px;
            transition: all 0.3s ease;
            position: relative;
            height: 100vh;
            overflow-y: auto;
            padding: 2rem;
        }
        
        /* Hide body scrollbar */
        body {
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay"></div>
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Homework Details</h1>
            <p class="header-subtitle">View assignment details and submit your work</p>
        </header>

        <main class="dashboard-content">
            <a href="homework.php" class="back-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to All Assignments
            </a>
            
            <div class="homework-detail-container">
                <div class="homework-header">
                    <div>
                        <h2 class="homework-title"><?php echo htmlspecialchars($homework['title']); ?></h2>
                        <div class="homework-subject"><?php echo htmlspecialchars($homework['subject_name']); ?></div>
                    </div>
                    <div class="homework-status <?php echo getStatusClass($homework, $submission); ?>">
                        <?php echo getStatusText($homework, $submission); ?>
                    </div>
                </div>
                
                <div class="homework-meta">
                    <div class="meta-item">
                        <span class="meta-label">Teacher:</span>
                        <span class="meta-value"><?php echo htmlspecialchars($homework['teacher_name']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Due Date:</span>
                        <span class="meta-value"><?php echo formatDate($homework['due_date']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Submission Type:</span>
                        <span class="meta-value">
                            <?php
                            $submissionTypeText = '';
                            switch($homework['submission_type']) {
                                case 'online':
                                    $submissionTypeText = 'Online Submission';
                                    break;
                                case 'physical':
                                    $submissionTypeText = 'Physical Submission';
                                    break;
                                case 'both':
                                    $submissionTypeText = 'Online or Physical Submission';
                                    break;
                                default:
                                    $submissionTypeText = 'Online Submission';
                            }
                            echo $submissionTypeText;
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="homework-description">
                    <?php echo nl2br(htmlspecialchars($homework['description'])); ?>
                </div>
                
                <?php if ($homework['attachment']): ?>
                <div class="attachment-container">
                    <h3>Attachment</h3>
                    <a href="<?php echo htmlspecialchars($homework['attachment']); ?>" target="_blank" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download Attachment
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="submission-container">
                    <div class="submission-header">
                        <h3 class="submission-title">Submission</h3>
                    </div>
                    
                    <?php if ($submission): ?>
                        <!-- Submission details if already submitted -->
                        <div class="submission-details">
                            <p><strong>Submitted on:</strong> <?php echo formatDate($submission['submitted_at']); ?></p>
                            
                            <?php if ($submission['file_path']): ?>
                            <p>
                                <strong>Submitted File:</strong> 
                                <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">
                                    View Submission
                                </a>
                            </p>
                            <?php else: ?>
                            <p><strong>Submission Type:</strong> Marked as Submitted Offline</p>
                            <?php endif; ?>
                            
                            <?php if ($submission['status'] === 'graded'): ?>
                            <div class="feedback-container">
                                <div class="feedback-header">
                                    Teacher Feedback
                                    <?php if ($submission['grade_code']): ?>
                                    <span class="grade-container">Grade: <?php echo htmlspecialchars($submission['grade_code']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($submission['feedback']): ?>
                                <div class="feedback-content">
                                    <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                                </div>
                                <?php else: ?>
                                <div class="feedback-content">No detailed feedback provided.</div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Submission form if not yet submitted -->
                        <form id="submitForm" class="submission-form" enctype="multipart/form-data">
                            <input type="hidden" name="homework_id" value="<?php echo $homework_id; ?>">
                            
                            <?php if ($homework['submission_type'] !== 'physical'): ?>
                            <div class="form-group">
                                <label for="submissionFile" class="form-label">Upload Your Work</label>
                                <div class="file-upload" id="fileUploadContainer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <span class="upload-text">Click to upload or drag and drop</span>
                                    <span class="upload-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG</span>
                                    <input type="file" id="submissionFile" name="file" class="file-upload-input" style="display: none;">
                                </div>
                                <div id="selectedFileName" style="margin-top: 0.5rem; font-size: 0.9rem;"></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($homework['submission_type'] !== 'online'): ?>
                            <div class="offline-submission">
                                <input type="checkbox" id="offlineSubmission" name="offline_submission" value="1">
                                <label for="offlineSubmission" class="checkbox-label">
                                    I have submitted this assignment offline
                                </label>
                            </div>
                            <?php endif; ?>
                            
                            <div class="btn-container">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    Submit Assignment
                                </button>
                            </div>
                        </form>
                        
                        <div id="submissionResult" style="margin-top: 1rem; display: none;"></div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File upload interaction
            const fileUploadContainer = document.getElementById('fileUploadContainer');
            const submissionFile = document.getElementById('submissionFile');
            const selectedFileName = document.getElementById('selectedFileName');
            
            if (fileUploadContainer && submissionFile) {
                fileUploadContainer.addEventListener('click', function() {
                    submissionFile.click();
                });
                
                submissionFile.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        selectedFileName.textContent = 'Selected file: ' + this.files[0].name;
                    } else {
                        selectedFileName.textContent = '';
                    }
                });
            }
            
            // Form submission
            const submitForm = document.getElementById('submitForm');
            const submitBtn = document.getElementById('submitBtn');
            const submissionResult = document.getElementById('submissionResult');
            const offlineSubmission = document.getElementById('offlineSubmission');
            
            if (submitForm) {
                submitForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate submission
                    if (!offlineSubmission && (!submissionFile || submissionFile.files.length === 0)) {
                        alert('Please select a file to upload or mark as submitted offline.');
                        return;
                    }
                    
                    if (offlineSubmission && offlineSubmission.checked && submissionFile && submissionFile.files.length > 0) {
                        if (!confirm('You have both selected a file and marked as submitted offline. Do you want to continue with both?')) {
                            return;
                        }
                    }
                    
                    // Disable submit button and show loading state
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = 'Submitting...';
                    }
                    
                    // Create FormData and append form data
                    const formData = new FormData(submitForm);
                    formData.append('action', 'submit_homework');
                    
                    // Submit via AJAX
                    fetch('homework_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (submissionResult) {
                            submissionResult.style.display = 'block';
                            
                            if (data.status === 'success') {
                                submissionResult.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                                // Reload page after 2 seconds to show updated submission status
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                submissionResult.innerHTML = '<div class="alert alert-danger">' + (data.message || 'An error occurred.') + '</div>';
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Submit Assignment';
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (submissionResult) {
                            submissionResult.style.display = 'block';
                            submissionResult.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                        }
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Submit Assignment';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html> 