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
$subjects_sql = "SELECT id, name, code FROM subjects ORDER BY name";
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

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exam Subjects - <?= htmlspecialchars($session['session_name']) ?></title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/schedule.css">
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress"></div>
    
    <!-- Quick Navigation Panel -->
    <div class="quick-nav" id="quickNav">
        <div class="quick-nav-item" data-tooltip="Search & Filters" onclick="scrollToSection('action-bar')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <div class="quick-nav-item" data-tooltip="Add New Subject" onclick="scrollToSection('form-container')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </div>
        <div class="quick-nav-item" data-tooltip="Current Subjects" onclick="scrollToSection('subjects-container')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
        </div>
    </div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>
    
    <!-- Keyboard Hints -->
    <div class="keyboard-hint" id="keyboardHint">
        Press Ctrl+Home for top, Ctrl+End for bottom
    </div>

    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Manage Exam Subjects</h1>
            <span class="header-path">Dashboard > Exams > <?= htmlspecialchars($session['session_name']) ?> > Subjects</span>
        </header>

        <main class="dashboard-content">
            <!-- Session Information Header -->
            <div class="exam-card session-info-card">
                <div class="session-header">
                    <div class="session-details">
                        <h2><?= htmlspecialchars($session['session_name']) ?> 
                            <span class="session-type <?= $session['session_type'] ?>"><?= $session['session_type'] ?></span>
                        </h2>
                        <p class="session-meta">
                            <strong>Duration:</strong> <?= date('M d', strtotime($session['start_date'])) ?> - <?= date('M d, Y', strtotime($session['end_date'])) ?> |
                            <strong>Academic Year:</strong> <?= htmlspecialchars($session['academic_year']) ?>
                        </p>
                    </div>
                    <div class="session-actions">
                        <a href="exam_session_management.php" class="btn btn-outline btn-sm">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Sessions
                        </a>
                        <a href="schedule.php" class="btn btn-outline btn-sm">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Schedule Exams
                        </a>
                        <a href="view_exam_marks.php?session_id=<?= $session_id ?>" class="btn btn-outline btn-sm">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            View Marks
                        </a>
                    </div>
                </div>

            <!-- Action Bar -->
            <div class="action-bar" id="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="subjectSearch" class="search-input" placeholder="Search subjects...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" id="addSubjectBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Subject
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Subjects</h3>
                <form class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Subject</label>
                        <select class="filter-select" id="subjectFilter">
                            <option value="">All Subjects</option>
                            <?php 
                            $subjects_result->data_seek(0);
                            while ($subject = $subjects_result->fetch_assoc()): 
                            ?>
                                <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="filter-input" id="startDateFilter" value="<?= $session['start_date'] ?>" style="flex: 1;">
                            <span style="align-self: center;">to</span>
                            <input type="date" class="filter-input" id="endDateFilter" value="<?= $session['end_date'] ?>" style="flex: 1;">
                        </div>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>
            
            <!-- Statistics Overview -->
            <div class="exam-card">
                <h3 class="section-title">📊 Statistics Overview</h3>
                <div class="stats-grid">
                    <?php
                    // Calculate statistics
                    $subjects_count = $current_subjects_result->num_rows;
                    $total_max_marks = 0;
                    $completed_count = 0;
                    
                    // Reset result pointer and calculate stats
                    $current_subjects_result->data_seek(0);
                    while ($subject = $current_subjects_result->fetch_assoc()) {
                        $total_max_marks += $subject['total_marks'];
                        
                        // Check if exam is completed by seeing if results exist for this exam subject
                        $results_check_sql = "SELECT COUNT(*) as result_count FROM exam_results WHERE assessment_id = ? AND subject_id = ?";
                        $results_check_stmt = $conn->prepare($results_check_sql);
                        $results_check_stmt->bind_param('ii', $subject['assessment_id'], $subject['subject_id']);
                        $results_check_stmt->execute();
                        $results_result = $results_check_stmt->get_result()->fetch_assoc();
                        
                        if ($results_result['result_count'] > 0) {
                            $completed_count++;
                        }
                    }
                    $current_subjects_result->data_seek(0); // Reset again for later use
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
            </div>

            <!-- Add New Subject Form -->
            <div class="exam-form-container" id="form-container">
                <h3 class="form-title">Add Subject to Exam Session</h3>
                <form id="addSubjectForm">
                    <input type="hidden" name="sessionId" value="<?= $session_id ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="subjectId">Subject</label>
                            <select class="form-select" id="subjectId" name="subjectId" required>
                                <option value="">Select Subject</option>
                                <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                    <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?> (<?= htmlspecialchars($subject['code']) ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="assessmentId">Assessment</label>
                            <select class="form-select" id="assessmentId" name="assessmentId" required>
                                <option value="">Select Assessment</option>
                                <?php while ($assessment = $assessments_result->fetch_assoc()): ?>
                                    <option value="<?= $assessment['id'] ?>"><?= htmlspecialchars($assessment['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examDate">Exam Date</label>
                            <input type="date" class="form-input" id="examDate" name="examDate" required 
                                   min="<?= $session['start_date'] ?>" max="<?= $session['end_date'] ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="maxMarks">Maximum Marks</label>
                            <input type="number" class="form-input" id="maxMarks" name="maxMarks" required min="1" max="1000" value="100">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn btn-outline">Reset Form</button>
                        <button type="submit" class="btn btn-primary">Add Subject</button>
                    </div>
                </form>
            </div>

            <!-- Current Subjects List -->
            <div class="exam-card" id="subjects-container">
                <h3 class="section-title">📋 Current Exam Subjects</h3>
                
                <?php if ($current_subjects_result->num_rows > 0): ?>
                    <div class="subjects-grid">
                        <?php while ($subject = $current_subjects_result->fetch_assoc()): 
                            // Check if this subject has marks (completed) or not (pending)
                            $marks_check_sql = "SELECT COUNT(*) as mark_count FROM exam_results WHERE assessment_id = ? AND subject_id = ?";
                            $marks_check_stmt = $conn->prepare($marks_check_sql);
                            $marks_check_stmt->bind_param('ii', $subject['assessment_id'], $subject['subject_id']);
                            $marks_check_stmt->execute();
                            $marks_result = $marks_check_stmt->get_result()->fetch_assoc();
                            $subject_status = ($marks_result['mark_count'] > 0) ? 'completed' : 'pending';
                        ?>
                            <div class="subject-card">
                                <div class="subject-info">
                                    <div class="subject-details">
                                        <h4><?= htmlspecialchars($subject['subject_name']) ?> 
                                            <small>(<?= htmlspecialchars($subject['subject_code']) ?>)</small>
                                        </h4>
                                        <p><strong>Assessment:</strong> <?= htmlspecialchars($subject['assessment_name']) ?></p>
                                        <p><strong>Date:</strong> <?= date('M d, Y', strtotime($subject['exam_date'])) ?></p>
                                        <p><strong>Max Marks:</strong> <?= $subject['total_marks'] ?></p>
                                        <p><strong>Status:</strong>
                                            <span class="badge badge-<?= $subject_status === 'completed' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($subject_status) ?>
                                            </span>
                                        </p>
                                    </div>
                                    
                                    <div class="subject-actions">
                                        <a href="view_exam_marks.php?exam_subject_id=<?= $subject['id'] ?>" 
                                           class="btn btn-success">View Marks</a>
                                        
                                        <?php if ($subject_status !== 'completed'): ?>
                                            <button onclick="editSubject(<?= $subject['id'] ?>)" 
                                                    class="btn btn-warning">Edit</button>
                                            <button onclick="deleteSubject(<?= $subject['id'] ?>)" 
                                                    class="btn btn-danger">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <p>No subjects added to this exam session yet.</p>
                        <p>Add subjects using the form above to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Filter Panel Toggle
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterPanel = document.getElementById('filterPanel');
            
            filterToggleBtn.addEventListener('click', function() {
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            });
            
            // Add Subject Button
            const addSubjectBtn = document.getElementById('addSubjectBtn');
            
            addSubjectBtn.addEventListener('click', function() {
                const formContainer = document.getElementById('form-container');
                if (formContainer) {
                    formContainer.scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Scroll to add subject form
                    const addForm = document.querySelector('.subject-card h3');
                    if (addForm && addForm.textContent.includes('Add Subject')) {
                        addForm.parentElement.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
            
            // Search functionality
            const searchInput = document.getElementById('subjectSearch');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                filterSubjects(searchTerm);
            });
            
            // Filter functionality
            const filterApplyBtn = document.querySelector('.filter-btn-apply');
            const filterResetBtn = document.querySelector('.filter-btn-reset');
            
            if (filterApplyBtn) {
                filterApplyBtn.addEventListener('click', applyFilters);
            }
            if (filterResetBtn) {
                filterResetBtn.addEventListener('click', resetFilters);
            }
        });
        
        function filterSubjects(searchTerm) {
            const subjectCards = document.querySelectorAll('.subject-info');
            
            subjectCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const parentCard = card.closest('.subject-card');
                if (parentCard && !parentCard.querySelector('h3')) { // Not the add form or stats
                    card.style.display = text.includes(searchTerm) ? '' : 'none';
                }
            });
        }
        
        function applyFilters() {
            const subjectFilter = document.getElementById('subjectFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const startDate = document.getElementById('startDateFilter').value;
            const endDate = document.getElementById('endDateFilter').value;
            
            const subjectCards = document.querySelectorAll('.subject-info');
            
            subjectCards.forEach(card => {
                let shouldShow = true;
                
                // Apply subject filter
                if (subjectFilter) {
                    const subjectName = card.querySelector('h4').textContent;
                    // You could implement more specific filtering logic here
                }
                
                // Apply status filter
                if (statusFilter) {
                    const statusElement = card.querySelector('.badge');
                    if (statusElement && !statusElement.textContent.toLowerCase().includes(statusFilter)) {
                        shouldShow = false;
                    }
                }
                
                const parentCard = card.closest('.subject-card');
                if (parentCard && !parentCard.querySelector('h3')) { // Not the add form or stats
                    card.style.display = shouldShow ? '' : 'none';
                }
            });
            
            // Hide filter panel
            document.getElementById('filterPanel').style.display = 'none';
        }
        
        function resetFilters() {
            document.querySelector('.filter-form').reset();
            const subjectCards = document.querySelectorAll('.subject-info');
            subjectCards.forEach(card => {
                card.style.display = '';
            });
        }
        
        // Form submission
        document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.btn-primary');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Adding...';
            submitBtn.disabled = true;
            
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
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
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

        // Navigation helper functions
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Set default exam date to tomorrow
        document.addEventListener('DOMContentLoaded', function() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('examDate').value = tomorrow.toISOString().split('T')[0];
        });
    </script>

    <style>
        .session-info-card {
            border-left: 4px solid var(--primary-color);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .session-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .session-details h2 {
            margin: 0 0 0.5rem 0;
        }
        
        .session-meta {
            margin: 0;
            opacity: 0.9;
        }
        
        .session-type {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .session-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .subject-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .subject-header h4 {
            margin: 0;
            color: var(--text-color);
        }
        
        .subject-details p {
            margin: 0.5rem 0;
            color: var(--text-light);
        }
        
        .subject-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }
        
        .no-data svg {
            width: 4rem;
            height: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .no-data p {
            margin: 0.5rem 0;
        }
    </style>
</body>
</html>
