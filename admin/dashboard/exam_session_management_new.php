<?php
/**
 * Simplified Exam Session Management System
 * Hierarchical Card Navigation: Sessions → Classes → Subjects
 */

// Start session before any output
session_start();

require_once 'con.php';
require_once '../../includes/functions.php';

// Check if user has permission to manage exams
if (!hasRole(['admin', 'headmaster'])) {
    header('Location: ../../login.php');
    exit();
}

// Get current user
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Session Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/schedule.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Exam Session Management</h1>
            <span class="header-path">Dashboard > Exams > Session Management</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="sessionSearch" class="search-input" placeholder="Search sessions...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="newSessionBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Session
                    </button>
                    
                    <!-- Workflow Helper Buttons -->
                    <div class="workflow-buttons" style="margin-left: auto; display: flex; gap: 0.5rem;">
                        <button class="btn btn-outline btn-sm" onclick="window.location.href='schedule.php'">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Schedule Exams
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="window.location.href='view_exam_marks.php'">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            View Marks
                        </button>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-nav" id="breadcrumbNav">
                    <button class="breadcrumb-item active">All Sessions</button>
                </nav>

                <!-- Sessions View -->
                <div id="sessionsView" class="view-container">
                    <div class="view-header">
                        <h1 class="view-title">📊 Exam Sessions Management</h1>
                        <button class="btn btn-primary" id="newSessionBtn2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create New Session
                        </button>
                    </div>
                    <div class="cards-grid" id="sessions-grid">
                        <!-- Sessions cards will be loaded here -->
                    </div>
                </div>

                <!-- Classes View -->
                <div id="classesView" class="view-container" style="display: none;">
                    <div class="view-header">
                        <h1 class="view-title" id="classesViewTitle">Classes in Session</h1>
                    </div>
                    <div class="cards-grid" id="classes-grid">
                        <!-- Classes cards will be loaded here -->
                    </div>
                </div>

                <!-- Subjects View -->
                <div id="subjectsView" class="view-container" style="display: none;">
                    <div class="view-header">
                        <h1 class="view-title" id="subjectsViewTitle">Subjects in Class</h1>
                        <button class="btn btn-primary" onclick="showAddSubjectForm()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Subject
                        </button>
                    </div>
                    <div class="cards-grid" id="subjects-grid">
                        <!-- Subject cards will be loaded here -->
                    </div>
                </div>

                <!-- Create/Edit Session Form -->
                <div class="exam-form-container" id="sessionForm" style="display: none;">
                    <h2 class="form-title">Create New Exam Session</h2>
                    <form id="createSessionForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="sessionName">Session Name</label>
                                <input type="text" class="form-input" id="sessionName" name="sessionName" placeholder="e.g. Mid Term Examination 2025" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="sessionType">Session Type</label>
                                <select class="form-select" id="sessionType" name="sessionType" required>
                                    <option value="">Select Type</option>
                                    <option value="SA">SA (Summative Assessment)</option>
                                    <option value="FA">FA (Formative Assessment)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="classId">Class</label>
                                <select class="form-select" id="classId" name="classId" required>
                                    <option value="">Select Class</option>
                                    <!-- Dynamic options will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="sectionId">Section</label>
                                <select class="form-select" id="sectionId" name="sectionId">
                                    <option value="">Select Section</option>
                                    <!-- Dynamic options will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="startDate">Start Date</label>
                                <input type="date" class="form-input" id="startDate" name="startDate" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="endDate">End Date</label>
                                <input type="date" class="form-input" id="endDate" name="endDate" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="academicYear">Academic Year</label>
                                <input type="text" class="form-input" id="academicYear" name="academicYear" value="2024-25" placeholder="2024-25" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-textarea" id="description" name="description" placeholder="Enter session description or instructions..."></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelSessionBtn">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Session</button>
                        </div>
                    </form>
                </div>

                <!-- Add Subject Form -->
                <div class="exam-form-container" id="addSubjectForm" style="display: none;">
                    <h2 class="form-title">Add Subject to Class</h2>
                    <form id="addSubjectToClassForm">
                        <input type="hidden" id="currentSessionId" name="sessionId">
                        <input type="hidden" id="currentClassId" name="classId">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="subjectId">Subject</label>
                                <select class="form-select" id="subjectId" name="subjectId" required>
                                    <option value="">Select Subject</option>
                                    <!-- Dynamic options will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="assessmentId">Assessment</label>
                                <select class="form-select" id="assessmentId" name="assessmentId" required>
                                    <option value="">Select Assessment</option>
                                    <!-- Dynamic options will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="examDate">Exam Date</label>
                                <input type="date" class="form-input" id="examDate" name="examDate" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="examTime">Exam Time</label>
                                <input type="time" class="form-input" id="examTime" name="examTime" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="totalMarks">Total Marks</label>
                                <input type="number" class="form-input" id="totalMarks" name="totalMarks" min="1" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="durationMinutes">Duration (minutes)</label>
                                <input type="number" class="form-input" id="durationMinutes" name="durationMinutes" min="15" step="15" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="hideAddSubjectForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </div>
                    </form>
                </div>
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
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSessionsHierarchy();
            loadDropdownData();
            
            // Create New Session Form Toggles
            const newSessionBtn = document.getElementById('newSessionBtn');
            const newSessionBtn2 = document.getElementById('newSessionBtn2');
            const sessionForm = document.getElementById('sessionForm');
            const cancelSessionBtn = document.getElementById('cancelSessionBtn');
            
            [newSessionBtn, newSessionBtn2].forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', function() {
                        document.getElementById('createSessionForm').reset();
                        document.querySelector('.form-title').textContent = 'Create New Exam Session';
                        document.querySelector('.form-actions .btn-primary').textContent = 'Create Session';
                        sessionForm.style.display = 'block';
                        sessionForm.scrollIntoView({ behavior: 'smooth' });
                    });
                }
            });
            
            cancelSessionBtn.addEventListener('click', function() {
                sessionForm.style.display = 'none';
            });
            
            // Form Submission
            const createSessionForm = document.getElementById('createSessionForm');
            createSessionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Saving...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                formData.append('action', 'create_session');
                
                fetch('exam_session_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Session created successfully!');
                        sessionForm.style.display = 'none';
                        this.reset();
                        loadSessionsHierarchy();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to create session'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while creating the session');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });

            // Add Subject Form Submission
            const addSubjectForm = document.getElementById('addSubjectToClassForm');
            if (addSubjectForm) {
                addSubjectForm.addEventListener('submit', function(e) {
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
                            hideAddSubjectForm();
                            showSubjectsInClass(currentClass.id, currentClass.name);
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
            }
        });
        
        function loadDropdownData() {
            Promise.all([
                fetch('exam_session_actions.php?action=get_classes'),
                fetch('exam_session_actions.php?action=get_academic_years')
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(data => {
                const [classes, years] = data;
                
                if (classes.success) {
                    populateSelect('classId', classes.data, 'class_id', 'class_name');
                }
                if (years.success) {
                    populateSelect('academicYear', years.data, 'academic_year_id', 'academic_year_name');
                }
            })
            .catch(error => console.error('Error loading dropdown data:', error));
        }
        
        function populateSelect(selectId, data, valueField, textField) {
            const select = document.getElementById(selectId);
            if (!select) return;
            
            const defaultOption = select.querySelector('option[value=""]');
            select.innerHTML = '';
            if (defaultOption) {
                select.appendChild(defaultOption);
            }
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item[valueField];
                option.textContent = item[textField];
                select.appendChild(option);
            });
        }

        // Hierarchical Navigation Functions
        let currentSession = null;
        let currentClass = null;

        function loadSessionsHierarchy() {
            fetch('exam_session_actions.php?action=get_sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySessionCards(data.sessions);
                    }
                })
                .catch(error => console.error('Error loading sessions:', error));
        }

        function displaySessionCards(sessions) {
            const container = document.getElementById('sessions-grid');
            if (!sessions || sessions.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H3a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z" />
                        </svg>
                        <p>No exam sessions found</p>
                        <p>Create a new session to get started</p>
                    </div>
                `;
                return;
            }

            const html = sessions.map(session => `
                <div class="hierarchy-card session-card" onclick="showClassesInSession(${session.id}, '${session.session_name}')">
                    <div class="card-header">
                        <h4 class="card-title">${session.session_name}</h4>
                        <span class="badge badge-${session.session_type.toLowerCase()}">${session.session_type}</span>
                    </div>
                    <div class="card-content">
                        <div class="card-meta">
                            <p><strong>Duration:</strong> ${new Date(session.start_date).toLocaleDateString()} - ${new Date(session.end_date).toLocaleDateString()}</p>
                            <p><strong>Academic Year:</strong> ${session.academic_year}</p>
                            <p><strong>Status:</strong> <span class="status-${session.status}">${session.status}</span></p>
                        </div>
                        ${session.description ? `<p class="card-description">${session.description}</p>` : ''}
                    </div>
                    <div class="card-footer">
                        <div class="card-stats">
                            <span class="stat-item">Classes: <strong id="classes-count-${session.id}">Loading...</strong></span>
                            <span class="stat-item">Subjects: <strong id="subjects-count-${session.id}">Loading...</strong></span>
                        </div>
                        <div class="card-actions" onclick="event.stopPropagation()">
                            <button class="btn-icon" onclick="editSession(${session.id})" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="btn-icon" onclick="deleteSession(${session.id})" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

            // Load counts for each session
            sessions.forEach(session => {
                loadSessionCounts(session.id);
            });
        }

        function loadSessionCounts(sessionId) {
            fetch(`exam_session_actions.php?action=get_session_counts&session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const classesElement = document.getElementById(`classes-count-${sessionId}`);
                        const subjectsElement = document.getElementById(`subjects-count-${sessionId}`);
                        if (classesElement) classesElement.textContent = data.classes_count || 0;
                        if (subjectsElement) subjectsElement.textContent = data.subjects_count || 0;
                    }
                })
                .catch(error => console.error('Error loading session counts:', error));
        }

        function showClassesInSession(sessionId, sessionName) {
            currentSession = { id: sessionId, name: sessionName };
            
            // Update breadcrumb
            updateBreadcrumb([
                { text: 'All Sessions', onclick: 'showSessionsView()' },
                { text: sessionName, active: true }
            ]);

            // Hide sessions view, show classes view
            document.getElementById('sessionsView').style.display = 'none';
            document.getElementById('classesView').style.display = 'block';
            document.getElementById('subjectsView').style.display = 'none';
            
            // Update title
            document.getElementById('classesViewTitle').textContent = `Classes in ${sessionName}`;

            // Load classes for this session
            fetch(`exam_session_actions.php?action=get_classes_in_session&session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayClassCards(data.classes);
                    }
                })
                .catch(error => console.error('Error loading classes:', error));
        }

        function displayClassCards(classes) {
            const container = document.getElementById('classes-grid');
            if (!classes || classes.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H3a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z" />
                        </svg>
                        <p>No classes found in this session</p>
                        <p>Add subjects to classes to see them here</p>
                    </div>
                `;
                return;
            }

            const html = classes.map(classItem => `
                <div class="hierarchy-card class-card" onclick="showSubjectsInClass(${classItem.class_id}, '${classItem.class_name}')">
                    <div class="card-header">
                        <h4 class="card-title">${classItem.class_name}</h4>
                        ${classItem.section_name ? `<span class="badge badge-secondary">${classItem.section_name}</span>` : ''}
                    </div>
                    <div class="card-content">
                        <div class="card-meta">
                            <p><strong>Subjects:</strong> ${classItem.subjects_count || 0}</p>
                            <p><strong>Students:</strong> ${classItem.students_count || 0}</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="card-actions" onclick="event.stopPropagation()">
                            <button class="btn btn-outline btn-sm" onclick="addSubjectToClass(${classItem.class_id}, '${classItem.class_name}')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Subject
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;
        }

        function showSubjectsInClass(classId, className) {
            currentClass = { id: classId, name: className };
            
            // Update breadcrumb
            updateBreadcrumb([
                { text: 'All Sessions', onclick: 'showSessionsView()' },
                { text: currentSession.name, onclick: 'showClassesView()' },
                { text: className, active: true }
            ]);

            // Hide other views, show subjects view
            document.getElementById('sessionsView').style.display = 'none';
            document.getElementById('classesView').style.display = 'none';
            document.getElementById('subjectsView').style.display = 'block';
            
            // Update title
            document.getElementById('subjectsViewTitle').textContent = `Subjects in ${className}`;

            // Set hidden form fields
            document.getElementById('currentSessionId').value = currentSession.id;
            document.getElementById('currentClassId').value = classId;

            // Load subjects for this class in the session
            fetch(`exam_session_actions.php?action=get_subjects_in_class&session_id=${currentSession.id}&class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySubjectCards(data.subjects);
                    }
                })
                .catch(error => console.error('Error loading subjects:', error));
        }

        function displaySubjectCards(subjects) {
            const container = document.getElementById('subjects-grid');
            if (!subjects || subjects.length === 0) {
                container.innerHTML = `
                    <div class="no-data">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <p>No subjects added to this class yet</p>
                        <p>Click "Add Subject" to get started</p>
                    </div>
                `;
                return;
            }

            const html = subjects.map(subject => {
                const hasMarks = subject.marks_count > 0;
                const statusClass = hasMarks ? 'completed' : 'pending';
                const statusText = hasMarks ? 'Completed' : 'Pending';
                
                return `
                    <div class="hierarchy-card subject-card">
                        <div class="card-header">
                            <h4 class="card-title">${subject.subject_name}</h4>
                            <span class="badge badge-${statusClass}">${statusText}</span>
                        </div>
                        <div class="card-content">
                            <div class="card-meta">
                                <p><strong>Assessment:</strong> ${subject.assessment_name}</p>
                                <p><strong>Date:</strong> ${new Date(subject.exam_date).toLocaleDateString()}</p>
                                <p><strong>Max Marks:</strong> ${subject.total_marks}</p>
                                <p><strong>Students with Marks:</strong> ${subject.marks_count || 0}</p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="card-actions">
                                <button class="btn btn-outline btn-sm" onclick="viewSubjectMarks(${subject.id})">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    View Marks
                                </button>
                                ${!hasMarks ? `
                                    <button class="btn btn-outline btn-sm" onclick="editSubject(${subject.id})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                    <button class="btn btn-outline btn-sm btn-danger" onclick="deleteSubject(${subject.id})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        }

        function updateBreadcrumb(items) {
            const container = document.getElementById('breadcrumbNav');
            const html = items.map((item, index) => `
                <button class="breadcrumb-item ${item.active ? 'active' : ''}" 
                        ${item.onclick ? `onclick="${item.onclick}"` : ''}>
                    ${index > 0 ? `
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    ` : ''}
                    ${item.text}
                </button>
            `).join('');
            container.innerHTML = html;
        }

        function showSessionsView() {
            document.getElementById('sessionsView').style.display = 'block';
            document.getElementById('classesView').style.display = 'none';
            document.getElementById('subjectsView').style.display = 'none';
            updateBreadcrumb([{ text: 'All Sessions', active: true }]);
            currentSession = null;
            currentClass = null;
        }

        function showClassesView() {
            if (!currentSession) {
                showSessionsView();
                return;
            }
            document.getElementById('sessionsView').style.display = 'none';
            document.getElementById('classesView').style.display = 'block';
            document.getElementById('subjectsView').style.display = 'none';
            updateBreadcrumb([
                { text: 'All Sessions', onclick: 'showSessionsView()' },
                { text: currentSession.name, active: true }
            ]);
            currentClass = null;
        }

        function showAddSubjectForm() {
            document.getElementById('addSubjectForm').style.display = 'block';
            loadSubjectFormData();
        }

        function hideAddSubjectForm() {
            document.getElementById('addSubjectForm').style.display = 'none';
        }

        function loadSubjectFormData() {
            // Load available subjects and assessments
            Promise.all([
                fetch('exam_session_actions.php?action=get_all_subjects'),
                fetch(`exam_session_actions.php?action=get_assessments&session_type=${currentSession.name.includes('SA') ? 'SA' : 'FA'}`)
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(data => {
                const [subjects, assessments] = data;
                
                if (subjects.success) {
                    populateSelect('subjectId', subjects.data, 'id', 'name');
                }
                if (assessments.success) {
                    populateSelect('assessmentId', assessments.data, 'id', 'title');
                }
            })
            .catch(error => console.error('Error loading form data:', error));
        }

        function addSubjectToClass(classId, className) {
            currentClass = { id: classId, name: className };
            document.getElementById('currentClassId').value = classId;
            showAddSubjectForm();
        }

        function viewSubjectMarks(subjectId) {
            window.location.href = `view_exam_marks.php?exam_subject_id=${subjectId}`;
        }

        function editSession(sessionId) {
            // Implement edit functionality
            console.log('Edit session:', sessionId);
            // Could redirect to edit page or open modal
        }

        function deleteSession(sessionId) {
            if (confirm('Are you sure you want to delete this session?')) {
                const formData = new FormData();
                formData.append('action', 'delete_session');
                formData.append('session_id', sessionId);
                
                fetch('exam_session_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Session deleted successfully!');
                        loadSessionsHierarchy();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete session'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the session');
                });
            }
        }

        function editSubject(subjectId) {
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
                        showSubjectsInClass(currentClass.id, currentClass.name);
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
                        showSubjectsInClass(currentClass.id, currentClass.name);
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
    </script>

    <style>
        /* Hierarchical Card Styles */
        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: none;
            border: none;
            border-radius: 4px;
            color: #6b7280;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .breadcrumb-item:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .breadcrumb-item.active {
            background: #3b82f6;
            color: white;
        }

        .breadcrumb-item svg {
            width: 1rem;
            height: 1rem;
        }

        .view-container {
            margin-bottom: 2rem;
        }

        .view-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .view-title {
            margin: 0;
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .hierarchy-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .hierarchy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            border-color: #3b82f6;
        }

        .hierarchy-card.session-card {
            border-left: 4px solid #3b82f6;
        }

        .hierarchy-card.class-card {
            border-left: 4px solid #10b981;
        }

        .hierarchy-card.subject-card {
            border-left: 4px solid #f59e0b;
            cursor: default;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .card-title {
            margin: 0;
            color: #1f2937;
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .card-content {
            margin-bottom: 1rem;
        }

        .card-meta {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .card-meta p {
            margin: 0.25rem 0;
        }

        .card-description {
            margin-top: 0.75rem;
            color: #6b7280;
            font-style: italic;
            font-size: 0.875rem;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .card-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            background: none;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
            color: #374151;
        }

        .btn-icon svg {
            width: 1rem;
            height: 1rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 9999px;
            white-space: nowrap;
        }

        .badge-sa {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-fa {
            background: #fdf4ff;
            color: #a21caf;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-active {
            color: #059669;
            font-weight: 600;
        }

        .status-draft {
            color: #d97706;
            font-weight: 600;
        }

        .status-completed {
            color: #2563eb;
            font-weight: 600;
        }

        .status-cancelled {
            color: #dc2626;
            font-weight: 600;
        }

        .btn-danger {
            border-color: #fca5a5 !important;
            color: #dc2626 !important;
        }

        .btn-danger:hover {
            background: #fee2e2 !important;
            border-color: #f87171 !important;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
            grid-column: 1 / -1;
        }

        .no-data svg {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            opacity: 0.5;
        }

        .no-data p {
            margin: 0.5rem 0;
        }

        .no-data p:first-of-type {
            font-weight: 600;
            font-size: 1.125rem;
        }

        @media (max-width: 768px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
            
            .view-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .card-footer {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start;
            }
            
            .card-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</body>
</html>
