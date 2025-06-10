<?php
/**
 * Manage Exam Subjects for a Specific Exam Session
 * Handles adding/editing subjects for SA/FA examination sessions
 */

// Start session before any output
session_start();

require_once 'con.php';
require_once '../../includes/functions.php';

// Check if user has permission
if (!hasRole(['admin', 'headmaster', 'teacher'])) {
    header('Location: ../../login.php');
    exit();
}

$session_id = $_GET['session_id'] ?? 0;
if (!$session_id) {
    header('Location: exam_session_management.php');
    exit();
}

// Get session details
$session_sql = "SELECT * FROM exam_sessions WHERE id = ?";
$session_stmt = $conn->prepare($session_sql);
$session_stmt->bind_param('i', $session_id);
$session_stmt->execute();
$session = $session_stmt->get_result()->fetch_assoc();

if (!$session) {
    header('Location: exam_session_management.php');
    exit();
}

// Get all available subjects
$subjects_sql = "SELECT id, name, code FROM subjects WHERE status = 'active' ORDER BY name";
$subjects_result = $conn->query($subjects_sql);

// Get assessments matching session type
$assessments_sql = "SELECT id, title, assessment_type FROM assessments WHERE assessment_type = ? ORDER BY title";
$assessments_stmt = $conn->prepare($assessments_sql);
$assessments_stmt->bind_param('s', $session['session_type']);
$assessments_stmt->execute();
$assessments_result = $assessments_stmt->get_result();

// Get current exam subjects for this session
$current_subjects_sql = "
    SELECT es.*, s.name as subject_name, s.code as subject_code, 
           a.title as assessment_name
    FROM exam_subjects es
    JOIN subjects s ON es.subject_id = s.id
    JOIN assessments a ON es.assessment_id = a.id
    WHERE es.exam_session_id = ?
    ORDER BY es.exam_date, s.name
";
$current_subjects_stmt = $conn->prepare($current_subjects_sql);
$current_subjects_stmt->bind_param('i', $session_id);
$current_subjects_stmt->execute();
$current_subjects_result = $current_subjects_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exam Subjects - <?= htmlspecialchars($session['session_name']) ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .exam-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .session-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        .subject-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .subject-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .subject-details {
            flex: 1;
        }
        .subject-actions {
            display: flex;
            gap: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { opacity: 0.9; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Session Header -->
        <div class="exam-header">
            <h1>📚 Manage Exam Subjects</h1>
            <h2><?= htmlspecialchars($session['session_name']) ?><span class="session-badge"><?= $session['session_type'] ?></span></h2>
            <p>📅 <?= date('M d, Y', strtotime($session['start_date'])) ?> - <?= date('M d, Y', strtotime($session['end_date'])) ?></p>
            <a href="exam_session_management.php" class="btn btn-secondary">← Back to Sessions</a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <?php
            $subjects_count = $current_subjects_result->num_rows;
            $current_subjects_result->data_seek(0);
            
            $completed_count = 0;            $total_max_marks = 0;
            while ($subject = $current_subjects_result->fetch_assoc()) {
                if ($subject['status'] === 'completed') $completed_count++;
                $total_max_marks += $subject['total_marks'];
            }
            $current_subjects_result->data_seek(0);
            ?>
            <div class="stat-item">
                <div class="stat-value"><?= $subjects_count ?></div>
                <div class="stat-label">Total Subjects</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $completed_count ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $subjects_count - $completed_count ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $total_max_marks ?></div>
                <div class="stat-label">Total Max Marks</div>
            </div>
        </div>

        <!-- Add New Subject Form -->
        <div class="subject-card">
            <h3>➕ Add Subject to Exam Session</h3>
            <form id="addSubjectForm" class="form-grid">
                <input type="hidden" name="sessionId" value="<?= $session_id ?>">
                
                <div class="form-group">
                    <label for="subjectId">Subject*</label>
                    <select id="subjectId" name="subjectId" required>
                        <option value="">Select Subject</option>
                        <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                            <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?> (<?= htmlspecialchars($subject['code']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="assessmentId">Assessment*</label>                <select id="assessmentId" name="assessmentId" required>
                        <option value="">Select Assessment</option>
                        <?php while ($assessment = $assessments_result->fetch_assoc()): ?>
                            <option value="<?= $assessment['id'] ?>"><?= htmlspecialchars($assessment['title']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="examDate">Exam Date*</label>
                    <input type="date" id="examDate" name="examDate" required 
                           min="<?= $session['start_date'] ?>" max="<?= $session['end_date'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="maxMarks">Maximum Marks*</label>
                    <input type="number" id="maxMarks" name="maxMarks" required min="1" max="1000" value="100">
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                    <button type="reset" class="btn btn-secondary">Reset Form</button>
                </div>
            </form>
        </div>

        <!-- Current Subjects List -->
        <div class="subject-card">
            <h3>📋 Current Exam Subjects</h3>
            
            <?php if ($current_subjects_result->num_rows > 0): ?>
                <?php while ($subject = $current_subjects_result->fetch_assoc()): ?>
                    <div class="subject-info">
                        <div class="subject-details">                            <h4><?= htmlspecialchars($subject['subject_name']) ?> 
                                <small>(<?= htmlspecialchars($subject['subject_code']) ?>)</small></h4>
                            <p><strong>Assessment:</strong> <?= htmlspecialchars($subject['assessment_name']) ?></p>
                            <p><strong>Date:</strong> <?= date('M d, Y', strtotime($subject['exam_date'])) ?></p>
                            <p><strong>Max Marks:</strong> <?= $subject['total_marks'] ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge badge-<?= $subject['status'] === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($subject['status']) ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="subject-actions">
                            <a href="view_exam_marks.php?exam_subject_id=<?= $subject['id'] ?>" 
                               class="btn btn-success">View Marks</a>
                            
                            <?php if ($subject['status'] !== 'completed'): ?>
                                <button onclick="editSubject(<?= $subject['id'] ?>)" 
                                        class="btn btn-warning">Edit</button>
                                <button onclick="deleteSubject(<?= $subject['id'] ?>)" 
                                        class="btn btn-danger">Delete</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No subjects added to this exam session yet. Add subjects using the form above.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Form submission
        document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('exam_session_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_subject',
                    ...data
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ Subject added successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred while adding the subject');
            });
        });

        // Edit subject function
        function editSubject(subjectId) {
            // For now, show a simple prompt - can be enhanced with modal
            const newMaxMarks = prompt('Enter new maximum marks:');
            if (newMaxMarks && !isNaN(newMaxMarks) && newMaxMarks > 0) {
                fetch('exam_session_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_subject',
                        subjectId: subjectId,
                        maxMarks: parseInt(newMaxMarks)
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('✅ Subject updated successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ An error occurred while updating the subject');
                });
            }
        }

        // Delete subject function
        function deleteSubject(subjectId) {
            if (confirm('Are you sure you want to delete this subject from the exam session? This action cannot be undone.')) {
                fetch('exam_session_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_subject',
                        subjectId: subjectId
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('✅ Subject deleted successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ An error occurred while deleting the subject');
                });
            }
        }

        // Set default exam date to tomorrow
        document.addEventListener('DOMContentLoaded', function() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('examDate').value = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
