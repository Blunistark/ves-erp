<?php
require_once 'con.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$homework_id = $_GET['id'] ?? 0;
if (!$homework_id) {
    header('Location: homework.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Get homework details
$conn = getDbConnection();
$sql = "SELECT h.*, c.name AS class_name, s.name AS section_name, sub.name AS subject_name
        FROM homework h
        JOIN classes c ON h.class_id = c.id
        JOIN sections s ON h.section_id = s.id
        JOIN subjects sub ON h.subject_id = sub.id
        WHERE h.id = ? AND h.teacher_user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $homework_id, $teacher_id);
$stmt->execute();
$homework = $stmt->get_result()->fetch_assoc();

if (!$homework) {
    header('Location: homework.php');
    exit;
}

// Get submissions
$sql = "SELECT hs.*, s.full_name AS student_name, s.roll_number
        FROM homework_submissions hs
        JOIN students s ON hs.student_user_id = s.user_id
        WHERE hs.homework_id = ?
        ORDER BY hs.submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $homework_id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total students in class/section
$sql = "SELECT COUNT(*) as total_students
        FROM students
        WHERE class_id = ? AND section_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $homework['class_id'], $homework['section_id']);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total_students'];

$stmt->close();
$conn->close();
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Submissions</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/homework.css">
    <style>
        /* Ensure dashboard container behaves correctly */
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%; /* Ensure html and body take full height */
            overflow: hidden; /* Prevent scrolling on html/body */
        }

        .dashboard-container {
            margin-left: 280px;
            transition: all 0.3s ease;
            position: relative;
            height: 100vh; /* Or adjust as needed to fit below header */
            overflow-y: auto; /* Make content area scrollable */
            width: auto; /* Ensure it takes remaining width */
            padding: 2rem; /* Re-add padding for content */
        }

        /* Add styles for modal if needed, or check homework.css */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 100; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px; /* Location of the box */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            background: none;
            border: none;
            cursor: pointer;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .form-actions {
            margin-top: 20px;
            text-align: right;
        }

        .form-actions button {
            margin-left: 10px;
        }

        /* Add styles for status badge if not in homework.css */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-badge.submitted {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-badge.graded {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-badge.not_submitted {
             background-color: #fee2e2;
            color: #b91c1c;
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
            <h1 class="header-title">Homework Submissions</h1>
            <p class="header-subtitle">View and grade student submissions</p>
        </header>

        <main class="dashboard-content">
            <!-- Homework Details -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= htmlspecialchars($homework['title']) ?></h2>
                    <div class="homework-meta">
                        <span class="class-info">
                            <?= htmlspecialchars($homework['class_name']) ?> |
                            <?= htmlspecialchars($homework['section_name']) ?> |
                            <?= htmlspecialchars($homework['subject_name']) ?>
                        </span>
                        <span class="due-date">Due: <?= date('M j, Y', strtotime($homework['due_date'])) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="description"><?= nl2br(htmlspecialchars($homework['description'])) ?></p>
                    <?php if ($homework['attachment']): ?>
                        <div class="attachment">
                            <a href="<?= htmlspecialchars($homework['attachment']) ?>" target="_blank" class="btn btn-secondary">
                                View Attachment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submission Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-title">Total Submissions</div>
                    <div class="stat-value"><?= count($submissions) ?></div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= ($total_students > 0 ? (count($submissions) / $total_students * 100) : 0) ?>%"></div>
                    </div>
                    <div class="stat-subtitle"><?= count($submissions) ?> of <?= $total_students ?> students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Graded</div>
                    <div class="stat-value"><?= count(array_filter($submissions, fn($s) => $s['status'] === 'graded')) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Pending</div>
                    <div class="stat-value"><?= count(array_filter($submissions, fn($s) => $s['status'] !== 'graded')) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Average Grade</div>
                    <?php
                    $graded = array_filter($submissions, fn($s) => $s['status'] === 'graded');
                    $avg_grade = count($graded) > 0 ? array_sum(array_column($graded, 'grade_code')) / count($graded) : 0;
                    ?>
                    <div class="stat-value"><?= number_format($avg_grade, 1) ?></div>
                </div>
            </div>

            <!-- Submissions List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Student Submissions</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($submissions)): ?>
                        <div class="no-data">No submissions yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Roll Number</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr data-submission-id="<?= $submission['id'] ?>">
                                            <td><?= htmlspecialchars($submission['student_name']) ?></td>
                                            <td><?= htmlspecialchars($submission['roll_number']) ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></td>
                                            <td>
                                                <span class="status-badge <?= $submission['status'] ?>">
                                                    <?= ucfirst($submission['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= $submission['grade_code'] ?? '-' ?></td>
                                            <td>
                                                <?php if ($submission['file_path']): ?>
                                                    <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                        View Submission
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-primary btn-sm" onclick="gradeSubmission(<?= $submission['id'] ?>)">
                                                    Grade
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Grade Submission Modal -->
    <div id="gradeModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Grade Submission</h3>
                <button class="close-btn" onclick="closeGradeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="gradeForm">
                    <input type="hidden" id="submissionId" name="submission_id">
                    <div class="form-group">
                        <label for="totalMarks" class="form-label">Total Marks:</label>
                        <span id="totalMarks" class="form-output"></span>
                    </div>
                    <div class="form-group">
                        <label for="marksObtained" class="form-label">Marks Obtained</label>
                        <input type="number" id="marksObtained" name="marks_obtained" class="form-input" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="feedback" class="form-label">Feedback</label>
                        <textarea id="feedback" name="feedback" class="form-textarea" rows="4"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeGradeModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Grade submission modal
        function gradeSubmission(submissionId) {
            // Fetch submission details including total marks
            fetch('homework_actions.php?action=get_submissions&submission_id=' + submissionId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.submissions.length > 0) {
                        const submission = data.submissions[0]; // Assuming only one submission per student per homework
                        document.getElementById('submissionId').value = submission.id;
                        document.getElementById('totalMarks').textContent = submission.total_marks;
                        document.getElementById('marksObtained').value = submission.marks_obtained ?? ''; // Populate if already graded
                        document.getElementById('feedback').value = submission.feedback ?? ''; // Populate feedback
                        document.getElementById('gradeModal').style.display = 'block';
                    } else {
                        alert('Error fetching submission details: ' + (data.message || 'Submission not found.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching submission details. Please try again.');
                });
        }

        function closeGradeModal() {
            document.getElementById('gradeModal').style.display = 'none';
            document.getElementById('gradeForm').reset();
            // Clear total marks display
            document.getElementById('totalMarks').textContent = '';
        }

        // Handle grade form submission
        document.getElementById('gradeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = {
                action: 'grade_homework',
                submission_id: document.getElementById('submissionId').value,
                marks_obtained: document.getElementById('marksObtained').value, // Send marks obtained
                feedback: document.getElementById('feedback').value
            };

            fetch('homework_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update the specific row in the table instead of reloading the whole page
                    const submissionId = data.submission_id; // Get submission ID from response if needed, or use the one we have
                    const marksObtained = data.marks_obtained; // Get updated marks from response
                    const gradeCode = data.grade_code; // Get calculated grade from response

                    // Find the row in the table for this submission using the data-submission-id attribute
                    const row = document.querySelector(`tr[data-submission-id='${submissionId}']`);
                    if (row) {
                        // Update Grade and Marks Obtained columns
                        const cells = row.querySelectorAll('td');
                        // Assuming Grade is the 5th column (index 4) and Status is the 4th (index 3)
                        cells[4].textContent = gradeCode; // Update Grade column
                        
                         // If you want to display marks obtained in the table, you'll need to add a column for it
                         // and update the corresponding cell here.

                        const statusBadge = cells[3].querySelector('.status-badge'); 
                         if (statusBadge) {
                             statusBadge.textContent = 'Graded';
                             statusBadge.className = 'status-badge graded';
                         }
                    }

                    closeGradeModal();
                    // No need to reload: window.location.reload();
                } else {
                    alert('Error saving grade: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving grade. Please try again.');
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('gradeModal');
            if (event.target === modal) {
                closeGradeModal();
            }
        }
    </script>
</body>
</html> 