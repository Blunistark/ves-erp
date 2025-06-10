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
                    </div>                </div>

                <!-- Sections View -->
                <div id="sectionsView" class="view-container" style="display: none;">
                    <div class="view-header">
                        <h1 class="view-title" id="sectionsViewTitle">Sections in Class</h1>
                    </div>
                    <div class="cards-grid" id="sections-grid">
                        <!-- Sections cards will be loaded here -->
                    </div>
                </div>

                <!-- Subjects View -->
                <div id="subjectsView" class="view-container" style="display: none;">
                    <div class="view-header">
                        <h1 class="view-title" id="subjectsViewTitle">Subjects in Section</h1>
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
                        </div>                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label">Select Classes</label>
                                <div class="classes-header">
                                    <label class="select-all-label">
                                        <input type="checkbox" id="selectAllClasses" class="select-all-checkbox">
                                        <span class="select-all-text">Select All Classes</span>
                                    </label>
                                </div>
                                <div class="classes-selection" id="classesSelection">
                                    <!-- Dynamic class checkboxes will be loaded here -->
                                </div>
                                <small class="form-hint">Select one or more classes for this exam session</small>
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
                        </div>                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="academicYear">Academic Year</label>
                                <select class="form-select" id="academicYear" name="academicYear" required>
                                    <option value="">Select Academic Year</option>
                                    <!-- Options will be loaded dynamically -->
                                </select>
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
                        </div>                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="hideAddSubjectForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </div>
                    </form>
                </div>

                <!-- Edit Subject Form -->
                <div class="exam-form-container" id="editSubjectForm" style="display: none;">
                    <h2 class="form-title">Edit Subject</h2>
                    <form id="editSubjectFormData">
                        <input type="hidden" id="editSubjectId" name="subjectId">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="editSubjectName">Subject</label>
                                <input type="text" class="form-input" id="editSubjectName" readonly 
                                       style="background-color: #f5f5f5; cursor: not-allowed;">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="editAssessmentName">Assessment</label>
                                <input type="text" class="form-input" id="editAssessmentName" readonly 
                                       style="background-color: #f5f5f5; cursor: not-allowed;">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="editExamDate">Exam Date</label>
                                <input type="date" class="form-input" id="editExamDate" name="examDate" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="editExamTime">Exam Time</label>
                                <input type="time" class="form-input" id="editExamTime" name="examTime" required>
                            </div>
                        </div>
                        <div class="form-row">                            <div class="form-group">
                                <label class="form-label" for="editTotalMarks">Total Marks</label>
                                <input type="number" class="form-input" id="editTotalMarks" name="totalMarks" min="1" required
                                       oninput="validatePassingMarks()">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="editPassingMarks">Passing Marks</label>
                                <input type="number" class="form-input" id="editPassingMarks" name="passingMarks" min="0" required
                                       oninput="validatePassingMarks()">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="editDurationMinutes">Duration (minutes)</label>
                                <input type="number" class="form-input" id="editDurationMinutes" name="durationMinutes" min="15" step="15" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="editInstructions">Instructions</label>
                                <textarea class="form-textarea" id="editInstructions" name="instructions" 
                                          placeholder="Enter special instructions for this exam..." rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="hideEditSubjectForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Subject</button>
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
                        
                        // Reset form mode
                        const form = document.getElementById('createSessionForm');
                        delete form.dataset.editMode;
                        delete form.dataset.sessionId;
                        
                        // Reset class selections
                        document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);
                        document.querySelectorAll('.sections-for-class').forEach(div => div.style.display = 'none');
                        
                        // Reset select all checkbox
                        const selectAllCheckbox = document.getElementById('selectAllClasses');
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = false;
                            selectAllCheckbox.indeterminate = false;
                        }
                        
                        sessionForm.style.display = 'block';
                        sessionForm.scrollIntoView({ behavior: 'smooth' });
                    });
                }
            });
            
            cancelSessionBtn.addEventListener('click', function() {
                sessionForm.style.display = 'none';
            });
            
            // Form Submission
            const createSessionForm = document.getElementById('createSessionForm');            createSessionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate that at least one class is selected
                const selectedClasses = document.querySelectorAll('input[name="selectedClasses"]:checked');
                if (selectedClasses.length === 0) {
                    alert('Please select at least one class for this exam session.');
                    return;
                }
                  const submitBtn = this.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Saving...';
                submitBtn.disabled = true;
                
                // Check if this is edit mode
                const isEditMode = this.dataset.editMode === 'true';
                const sessionId = this.dataset.sessionId;
                
                // Collect form data
                const formData = new FormData(this);
                formData.append('action', isEditMode ? 'update_session' : 'create_session');
                
                if (isEditMode && sessionId) {
                    formData.append('session_id', sessionId);
                }
                
                // Collect selected classes and their sections
                const classesData = [];
                selectedClasses.forEach(classCheckbox => {
                    const classId = classCheckbox.value;
                    const selectedSections = document.querySelectorAll(`input[name="selectedSections"][data-class-id="${classId}"]:checked`);
                    
                    if (selectedSections.length > 0) {
                        selectedSections.forEach(sectionCheckbox => {
                            classesData.push({
                                class_id: classId,
                                section_id: sectionCheckbox.value
                            });
                        });
                    } else {
                        // No specific sections selected, include the class with all sections
                        classesData.push({
                            class_id: classId,
                            section_id: null
                        });
                    }
                });
                
                formData.append('classes_data', JSON.stringify(classesData));
                
                fetch('exam_session_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())                .then(data => {
                    if (data.success) {
                        alert(isEditMode ? 'Session updated successfully!' : 'Session created successfully!');
                        sessionForm.style.display = 'none';
                        this.reset();
                        // Reset form mode
                        delete this.dataset.editMode;
                        delete this.dataset.sessionId;
                        document.querySelector('.form-title').textContent = 'Create New Exam Session';
                        submitBtn.textContent = 'Create Session';
                          // Reset class selections
                        document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);
                        document.querySelectorAll('.sections-for-class').forEach(div => div.style.display = 'none');
                        
                        // Reset select all checkbox
                        const selectAllCheckbox = document.getElementById('selectAllClasses');
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = false;
                            selectAllCheckbox.indeterminate = false;
                        }
                        
                        loadSessionsHierarchy();
                    } else {
                        alert('Error: ' + (data.message || `Failed to ${isEditMode ? 'update' : 'create'} session`));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`An error occurred while ${isEditMode ? 'updating' : 'creating'} the session`);
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
                    })                    .finally(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }

            // Edit Subject Form Submission
            const editSubjectForm = document.getElementById('editSubjectFormData');
            if (editSubjectForm) {
                editSubjectForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = this.querySelector('.btn-primary');
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = 'Updating...';
                    submitBtn.disabled = true;                    const formData = new FormData(this);
                    const totalMarks = parseInt(formData.get('totalMarks'));
                    const passingMarks = parseInt(formData.get('passingMarks'));
                    
                    // Validate that passing marks is not greater than total marks
                    if (passingMarks > totalMarks) {
                        alert('❌ Passing marks cannot be greater than total marks');
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                        return;
                    }
                    
                    const data = {
                        action: 'update_subject',
                        subjectId: formData.get('subjectId'),
                        examDate: formData.get('examDate'),
                        examTime: formData.get('examTime'),
                        maxMarks: totalMarks,
                        passingMarks: passingMarks,
                        durationMinutes: parseInt(formData.get('durationMinutes')),
                        instructions: formData.get('instructions')
                    };
                    
                    fetch('exam_session_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('✅ Subject updated successfully!');
                            hideEditSubjectForm();
                            showSubjectsInClass(currentClass.id, currentClass.name);
                        } else {
                            alert('❌ Error: ' + result.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ An error occurred while updating the subject');
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
                    populateClassesSelection(classes.data);
                }
                if (years.success) {
                    populateAcademicYearDropdown(years.data);
                }
            })
            .catch(error => console.error('Error loading dropdown data:', error));
        }

        function populateClassesSelection(classes) {
            const container = document.getElementById('classesSelection');
            if (!container) return;
            
            let html = '';            classes.forEach(classItem => {
                html += `
                    <div class="class-checkbox-group">
                        <label class="class-checkbox-label">
                            <input type="checkbox" name="selectedClasses" value="${classItem.id}" class="class-checkbox">
                            <span class="class-name">${classItem.name}</span>
                        </label>
                        <div class="sections-for-class" id="sections-${classItem.id}" style="display: none;">
                            <!-- Sections will be loaded here -->
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;            // Add event listeners for class checkboxes
            container.querySelectorAll('.class-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const classId = this.value;
                    const sectionsDiv = document.getElementById(`sections-${classId}`);
                    
                    if (this.checked) {
                        loadSectionsForClass(classId);
                        sectionsDiv.style.display = 'block';
                    } else {
                        sectionsDiv.style.display = 'none';
                        // Uncheck all sections for this class
                        sectionsDiv.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                    }
                    
                    // Update select all checkbox state
                    updateSelectAllState();
                });
            });
            
            // Add event listener for Select All checkbox
            const selectAllCheckbox = document.getElementById('selectAllClasses');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const allClassCheckboxes = document.querySelectorAll('.class-checkbox');
                    const isChecked = this.checked;
                    
                    allClassCheckboxes.forEach(checkbox => {
                        if (checkbox.checked !== isChecked) {
                            checkbox.checked = isChecked;
                            
                            // Trigger change event for each checkbox
                            const changeEvent = new Event('change');
                            checkbox.dispatchEvent(changeEvent);
                        }
                    });
                });
            }        }
        
        function updateSelectAllState() {
            const selectAllCheckbox = document.getElementById('selectAllClasses');
            const allClassCheckboxes = document.querySelectorAll('.class-checkbox');
            const checkedClassCheckboxes = document.querySelectorAll('.class-checkbox:checked');
            
            if (selectAllCheckbox && allClassCheckboxes.length > 0) {
                if (checkedClassCheckboxes.length === 0) {
                    // No classes selected
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else if (checkedClassCheckboxes.length === allClassCheckboxes.length) {
                    // All classes selected
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    // Some classes selected
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                }
            }
        }
        
        function populateAcademicYearDropdown(years) {
            const select = document.getElementById('academicYear');
            if (!select) return;
            
            // Keep the default option
            select.innerHTML = '<option value="">Select Academic Year</option>';
            
            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year.name; // Use the name as value
                option.textContent = year.name;
                
                // Select current year if it exists
                if (year.is_current) {
                    option.selected = true;
                }
                
                select.appendChild(option);
            });
        }

        function loadSectionsForClass(classId) {
            fetch(`exam_session_actions.php?action=get_sections&class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sectionsDiv = document.getElementById(`sections-${classId}`);
                        if (sectionsDiv) {
                            let html = '<div class="sections-list">';
                            if (data.sections.length > 0) {
                                data.sections.forEach(section => {
                                    html += `
                                        <label class="section-checkbox-label">
                                            <input type="checkbox" name="selectedSections" value="${section.id}" class="section-checkbox" data-class-id="${classId}">
                                            <span class="section-name">Section ${section.name}</span>
                                        </label>
                                    `;
                                });
                            } else {
                                html += '<span class="no-sections">All sections</span>';
                            }
                            html += '</div>';
                            sectionsDiv.innerHTML = html;
                        }
                    }
                })
                .catch(error => console.error('Error loading sections:', error));
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
                            <div class="card-actions">                                <button class="btn btn-outline btn-sm" onclick="viewSubjectMarks(${subject.id})">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    View Marks
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="editSubject(${subject.id})">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                                ${!hasMarks ? `
                                    <button class="btn btn-outline btn-sm btn-danger" onclick="deleteSubject(${subject.id})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                ` : `
                                    <button class="btn btn-outline btn-sm btn-danger" onclick="deleteSubjectWithMarks(${subject.id})" 
                                            title="This subject has recorded marks. Only admins can delete it.">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                `}
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
        }        function editSession(sessionId) {
            // Get session data and populate the form
            fetch(`exam_session_actions.php?action=get_session_data&session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const session = data.session;
                        
                        // Show the form
                        document.getElementById('sessionForm').style.display = 'block';
                        document.querySelector('.form-title').textContent = 'Edit Exam Session';
                          // Populate form fields
                        document.getElementById('sessionName').value = session.session_name || '';
                        document.getElementById('sessionType').value = session.session_type || '';
                        document.getElementById('startDate').value = session.start_date || '';
                        document.getElementById('endDate').value = session.end_date || '';
                        document.getElementById('academicYear').value = session.academic_year || '';
                        document.getElementById('status').value = session.status || 'draft';
                        document.getElementById('description').value = session.description || '';
                        
                        // Set form to edit mode
                        const form = document.getElementById('createSessionForm');
                        form.dataset.editMode = 'true';
                        form.dataset.sessionId = sessionId;
                        
                        // Change button text
                        const submitBtn = form.querySelector('button[type="submit"]');
                        submitBtn.textContent = 'Update Session';
                        
                        // Load classes and select the ones for this session
                        loadClassesForEdit(sessionId);
                        
                        // Scroll to form
                        document.getElementById('sessionForm').scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Error loading session data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading session data');
                });        }
          function loadClassesForEdit(sessionId) {
            // First load all classes
            fetch('exam_session_actions.php?action=get_classes')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateClassesSelection(data.data);
                        
                        // Then get the classes assigned to this session and check them
                        fetch(`exam_session_actions.php?action=get_classes_in_session&session_id=${sessionId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const assignedClasses = data.classes;
                                    
                                    // Check the assigned classes
                                    assignedClasses.forEach(classInfo => {
                                        const checkbox = document.querySelector(`input[name="selectedClasses"][value="${classInfo.class_id}"]`);
                                        if (checkbox) {
                                            checkbox.checked = true;
                                            
                                            // Trigger change event to load sections
                                            const changeEvent = new Event('change');
                                            checkbox.dispatchEvent(changeEvent);
                                            
                                            // After sections load, check the assigned sections
                                            setTimeout(() => {
                                                if (classInfo.section_id) {
                                                    const sectionCheckbox = document.querySelector(`input[name="selectedSections"][value="${classInfo.section_id}"][data-class-id="${classInfo.class_id}"]`);
                                                    if (sectionCheckbox) {
                                                        sectionCheckbox.checked = true;
                                                    }
                                                }
                                            }, 500);                                        }
                                    });
                                    
                                    // Update select all checkbox state after loading assigned classes
                                    setTimeout(() => {
                                        updateSelectAllState();
                                    }, 1000);
                                }
                            })
                            .catch(error => console.error('Error loading assigned classes:', error));
                    }
                })
                .catch(error => console.error('Error loading classes:', error));
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
        }        function editSubject(subjectId) {
            // First, fetch the current subject data
            fetch(`exam_session_actions.php?action=get_subject_data&subjectId=${subjectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the edit form with current data
                        document.getElementById('editSubjectId').value = subjectId;
                        document.getElementById('editSubjectName').value = data.data.subject_name;
                        document.getElementById('editAssessmentName').value = data.data.assessment_name;
                        document.getElementById('editExamDate').value = data.data.exam_date;
                        document.getElementById('editExamTime').value = data.data.exam_time || '';
                        document.getElementById('editTotalMarks').value = data.data.total_marks;
                        document.getElementById('editPassingMarks').value = data.data.passing_marks;
                        document.getElementById('editDurationMinutes').value = data.data.duration_minutes || 60;
                        document.getElementById('editInstructions').value = data.data.instructions || '';
                        
                        // Show the edit form
                        showEditSubjectForm();
                    } else {
                        alert('❌ Error loading subject data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ An error occurred while loading subject data');
                });
        }

        function showEditSubjectForm() {
            document.getElementById('editSubjectForm').style.display = 'block';
            document.getElementById('sessionsView').style.display = 'none';
            document.getElementById('classesView').style.display = 'none';
            document.getElementById('subjectsView').style.display = 'none';
        }        function hideEditSubjectForm() {
            document.getElementById('editSubjectForm').style.display = 'none';
            document.getElementById('subjectsView').style.display = 'block';
        }

        function validatePassingMarks() {
            const totalMarksInput = document.getElementById('editTotalMarks');
            const passingMarksInput = document.getElementById('editPassingMarks');
            const totalMarks = parseInt(totalMarksInput.value) || 0;
            const passingMarks = parseInt(passingMarksInput.value) || 0;
            
            if (passingMarks > totalMarks && totalMarks > 0) {
                passingMarksInput.setCustomValidity('Passing marks cannot be greater than total marks');
                passingMarksInput.setAttribute('max', totalMarks);
            } else {
                passingMarksInput.setCustomValidity('');
                passingMarksInput.setAttribute('max', totalMarks || 999);
            }
        }        function deleteSubject(subjectId) {
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

        function deleteSubjectWithMarks(subjectId) {
            const message = 'This subject has recorded marks. Are you sure you want to delete it?\n\n' +
                          'WARNING: This action will permanently delete:\n' +
                          '• The subject from the exam session\n' +
                          '• All recorded marks for this subject\n' +
                          '• All associated data\n\n' +
                          'This action cannot be undone.\n\n' +
                          'Only proceed if you have administrative permission.';
            
            if (confirm(message)) {
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
        }        /* Class Selection Styles */
        .classes-header {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .select-all-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            color: #374151;
            padding: 0.5rem;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        
        .select-all-label:hover {
            background-color: #f3f4f6;
        }
        
        .select-all-checkbox {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: #3b82f6;
        }
        
        .select-all-text {
            font-size: 0.9375rem;
        }
        
        .classes-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            max-height: 300px;
            overflow-y: auto;
        }

        .class-checkbox-group {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 0.75rem;
            transition: all 0.2s;
        }

        .class-checkbox-group:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .class-checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            color: #1f2937;
        }

        .class-checkbox {
            width: 1rem;
            height: 1rem;
            accent-color: #3b82f6;
        }

        .class-name {
            font-size: 0.875rem;
        }

        .sections-for-class {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .sections-list {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .section-checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .section-checkbox-label:hover {
            background: #f3f4f6;
        }

        .section-checkbox {
            width: 0.875rem;
            height: 0.875rem;
            accent-color: #10b981;
        }

        .section-name {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .no-sections {
            font-size: 0.75rem;
            color: #9ca3af;
            font-style: italic;
        }

        .form-hint {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }
    </style>
</body>
</html>
