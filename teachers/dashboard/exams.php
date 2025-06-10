<?php
// Build $sections_by_class for JS (must be before any HTML output)
require_once 'con.php';
$sections_by_class = [];
$sections_res = mysqli_query($conn, "SELECT id, class_id, name FROM sections ORDER BY class_id, name");
if ($sections_res) {
    while ($row = mysqli_fetch_assoc($sections_res)) {
        $sections_by_class[$row['class_id']][] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
}
?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Exam & Test Schedules</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/exams.css">
    <style>
    @media print {
        body, html {
            background: #fff !important;
        }
        .sidebar-overlay, .sidebar, .dashboard-header, .quick-actions, .filters-container, .tab, .modal, .modal-header, .modal-footer, .hamburger-btn, .print-link, .btn, .action-card, .exam-details-modal, #studentListModal {
            display: none !important;
        }
        .dashboard-container {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        .tab-content {
            display: none !important;
        }
        .tab-content.active {
            display: block !important;
        }
        table.exam-table {
            width: 100% !important;
            font-size: 12pt;
            border-collapse: collapse;
        }
        table.exam-table th, table.exam-table td {
            border: 1px solid #333 !important;
            padding: 6px 8px !important;
        }
        .badge, .exam-type-badge {
            color: #000 !important;
            background: none !important;
            border: none !important;
            font-weight: normal !important;
        }
        .two-col {
            display: block !important;
        }
        /* Hide Actions column in print */
        .exam-table th:last-child,
        .exam-table td.actions-cell {
            display: none !important;
        }
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
        <h1 class="header-title">Exam & Test Schedules</h1>
        <p class="header-subtitle">View and manage upcoming assessments</p>
        <div id="notificationBellContainer" style="position:absolute; top:24px; right:32px; z-index:10;">
            <button id="notificationBell" style="background:none; border:none; position:relative; cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="notificationBadge" style="display:none; position:absolute; top:2px; right:2px; background:#ff4d4d; color:#fff; border-radius:50%; font-size:0.8em; padding:2px 6px; min-width:18px; text-align:center;">0</span>
            </button>
            <div id="notificationDropdown" style="display:none; position:absolute; right:0; top:36px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.12); border-radius:8px; min-width:320px; max-width:400px; max-height:400px; overflow-y:auto; border:1px solid #eee;">
                <div id="notificationDropdownContent" style="padding:0.5rem 0;"></div>
            </div>
        </div>
    </header>

    <main class="dashboard-content">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <div class="action-title">Student List</div>
                <div class="action-description">View students for selected exam</div>
            </div>
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <div class="action-title">Print Schedule</div>
                <div class="action-description">Generate printable schedule</div>
            </div>
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div class="action-title">Sync Calendar</div>
                <div class="action-description">Export to your calendar</div>
            </div>
        </div>

        <!-- Upcoming Exam Alert -->
        <div class="upcoming-exam" id="upcomingExamAlert" style="display: none;">
            <div class="upcoming-exam-info">
                <div class="upcoming-exam-title" id="upcomingExamTitle">Loading...</div>
                <div class="upcoming-exam-details" id="upcomingExamDetails">Loading...</div>
            </div>
            <div class="upcoming-exam-countdown" id="upcomingExamCountdown">Loading...</div>
        </div>

        <!-- Main Content Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Exam & Test Schedule</h2>
                <a href="#" class="print-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="print-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Schedule
                </a>
            </div>
            <div class="card-body">
                <div class="tabs">
                    <div class="tab active" data-tab="list">List View</div>
                    <div class="tab" data-tab="calendar">Calendar View</div>
                    <div class="tab" data-tab="classes">By Class</div>
                </div>
                
                <!-- Filters -->
                <div class="filters-container" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="filter">
                        <select id="filterClass" class="form-select">
                            <option value="">All Classes</option>
                            <?php
                            require_once 'con.php';
                            $class_res = mysqli_query($conn, "SELECT id, name FROM classes ORDER BY name");
                            while ($row = mysqli_fetch_assoc($class_res)) {
                                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter">
                        <select id="filterSection" class="form-select">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select id="filterType" class="form-select">
                            <option value="">All Assessment Types</option>
                            <option value="SA">SA (Summative Assessment)</option>
                            <option value="FA">FA (Formative Assessment)</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select id="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <button id="applyFilterBtn" class="btn btn-primary" style="height: 38px;">Filter</button>
                    <button id="clearFilterBtn" class="btn btn-secondary" style="height: 38px;">Clear Filters</button>
                </div>
                
                <!-- List View Tab Content -->
                <div class="tab-content active" id="list-content">
                    <div style="overflow-x: auto;">
                        <table class="exam-table">
                            <thead>
                                <tr>
                                    <th>Exam/Test</th>
                                    <th>Class</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="examTableBody">
                                <tr><td colspan="7" style="text-align:center;">Loading exams...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Calendar View Tab Content -->
                <div class="tab-content" id="calendar-content">
                    <div id="calendarNav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;"></div>
                    <div id="calendarGrid"></div>
                </div>
                
                <!-- Classes Tab Content -->
                <div class="tab-content" id="classes-content">
                    <div id="byClassLoading" style="text-align:center; padding:2rem; display:none;">Loading...</div>
                    <div id="byClassContainer"></div>
                </div>
            </div>
        </div>
        
        <!-- Exam Details Modal -->
        <div class="exam-details-modal" id="examDetailsModal" style="display: none;">
            <div class="modal-header">
                <h3 class="modal-title">Exam Details</h3>
                <button class="modal-close" id="closeModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Title:</div>
                    <div class="exam-detail-value" id="modalExamTitle"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Class:</div>
                    <div class="exam-detail-value" id="modalExamClass"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Date:</div>
                    <div class="exam-detail-value" id="modalExamDate"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Time:</div>
                    <div class="exam-detail-value" id="modalExamTime"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Location:</div>
                    <div class="exam-detail-value">Room 203</div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Type:</div>
                    <div class="exam-detail-value" id="modalExamType"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Total Marks:</div>
                    <div class="exam-detail-value" id="modalExamTotalMarks"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Syllabus:</div>
                    <div class="exam-detail-value" id="modalExamSyllabus"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Instructions:</div>
                    <div class="exam-detail-value" id="modalExamInstructions"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Invigilators:</div>
                    <div class="exam-detail-value" id="modalExamInvigilators"></div>
                </div>
                <div class="exam-detail-item">
                    <div class="exam-detail-label">Created By:</div>
                    <div class="exam-detail-value" id="modalExamTeacher"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeModalBtn">Close</button>
                <button class="btn btn-primary">Edit Details</button>
            </div>
        </div>
        
        <!-- Student List Modal -->
        <div class="exam-details-modal" id="studentListModal" style="display: none;">
            <div class="modal-header">
                <h3 class="modal-title">Student List</h3>
                <button class="modal-close" id="closeStudentListModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:1rem;">
                    <label for="studentListExamSelect" style="font-weight:bold;">Select Exam:</label>
                    <select id="studentListExamSelect" class="form-select"></select>
                </div>
                <div id="studentListLoading" style="display:none; text-align:center; padding:1rem;">Loading...</div>
                <div id="studentListError" style="display:none; color:red; text-align:center; padding:1rem;"></div>
                <div id="studentListTableContainer"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeStudentListModalBtn">Close</button>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Show the corresponding tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(`${tabId}-content`).classList.add('active');
            });
        });
        
        // Modal functionality
        const modal = document.getElementById('examDetailsModal');
        const viewButtons = document.querySelectorAll('.view-exam-btn');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'block';
                // In a real app, you would fetch the exam details based on the data-exam-id
                const examId = this.getAttribute('data-exam-id');
                console.log('Viewing exam with ID:', examId);
            });
        });
        
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Create New Exam - REMOVED
        const actionCards = document.querySelectorAll('.action-card');
        
        // Function to toggle the sidebar (defined in the sidebar.php)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        };
        
        // Dynamic exam list fetch
        let allExams = [];
        function renderExamRow(exam) {
            // Format date and time
            const date = new Date(exam.date);
            const dateStr = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            const timeStr = exam.start_time && exam.end_time ? `${exam.start_time} - ${exam.end_time}` : '';
            // Status badge
            let status = exam.status || 'Upcoming';
            let badgeClass = 'badge-upcoming';
            if (status.toLowerCase() === 'completed') badgeClass = 'badge-completed';
            else if (status.toLowerCase() === 'draft') badgeClass = 'badge-draft';
            // Type badge
            let typeClass = 'exam-type-' + (exam.type ? exam.type.toLowerCase().replace(/\s/g, '') : '');
            let typeLabel = exam.type || '';
            return `<tr>
                <td>${exam.title}</td>
                <td>${exam.class_name} - ${exam.section_name}</td>
                <td>${dateStr}${timeStr ? '<br>' + timeStr : ''}</td>
                <td><span class="exam-type-badge ${typeClass}">${typeLabel}</span></td>
                <td><span class="badge ${badgeClass}">${status}</span></td>
                <td class="actions-cell">
                    <button class="btn btn-secondary btn-sm view-exam-btn" data-exam-id="${exam.id}">View</button>
                    <button class="btn btn-primary btn-sm edit-exam-btn" data-exam-id="${exam.id}">Edit</button>
                    <button class="btn btn-danger btn-sm delete-exam-btn" data-exam-id="${exam.id}">Delete</button>
                </td>
            </tr>`;
        }
        function renderExamTable(exams) {
            const tbody = document.getElementById('examTableBody');
            if (!exams.length) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No exams found.</td></tr>`;
                return;
            }
            tbody.innerHTML = exams.map(renderExamRow).join('');
        }
        function applyFilters() {
            const classVal = document.getElementById('filterClass').value;
            const sectionVal = document.getElementById('filterSection').value;
            const typeVal = document.getElementById('filterType').value;
            const statusVal = document.getElementById('filterStatus').value;
            let filtered = allExams;
            if (classVal) filtered = filtered.filter(e => e.class_id == classVal);
            if (sectionVal) filtered = filtered.filter(e => e.section_id == sectionVal);
            if (typeVal) filtered = filtered.filter(e => (e.assessment_type || e.type || '').toLowerCase() === typeVal.toLowerCase());
            if (statusVal) filtered = filtered.filter(e => (e.status || '').toLowerCase() === statusVal.toLowerCase());
            renderExamTable(filtered);
            bindViewButtons();
        }
        function fetchExams() {
            const tbody = document.getElementById('examTableBody');
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Loading exams...</td></tr>`;
            
            // Fetch exams from admin exam session management system
            fetch('../../admin/dashboard/exam_session_actions.php?action=get_teacher_exam_sessions', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    // Transform admin exam session data to match expected format
                    const transformedExams = data.data.map(exam => ({
                        id: exam.id,
                        title: exam.session_name || exam.subject_name || 'Untitled Exam',
                        class_name: exam.class_name || 'Unknown Class',
                        section_name: exam.section_name || 'All Sections',
                        date: exam.exam_date || exam.start_date,
                        start_time: exam.exam_time || exam.start_time || '',
                        end_time: exam.end_time || '',
                        location: exam.location || 'TBA',
                        assessment_type: exam.session_type || 'SA',
                        type: exam.session_type || 'SA',
                        status: exam.status || 'active',
                        total_marks: exam.total_marks || '',
                        duration: exam.duration_minutes || '',
                        instructions: exam.instructions || '',
                        subject_name: exam.subject_name || '',
                        teacher_name: exam.teacher_name || ''
                    }));
                    
                    allExams = transformedExams;
                    applyFilters();
                } else {
                    allExams = [];
                    renderExamTable([]);
                }
                bindViewButtons();
            })
            .catch(error => {
                console.error('Error fetching exams from admin system:', error);
                allExams = [];
                renderExamTable([]);
            });
        }
        function bindViewButtons() {
            const modal = document.getElementById('examDetailsModal');
            const closeModal = document.getElementById('closeModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            document.querySelectorAll('.view-exam-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const examId = this.getAttribute('data-exam-id');
                    // Find exam in already loaded data
                    const exam = allExams.find(e => e.id == examId);
                    
                    if (exam) {
                        document.getElementById('modalExamTitle').textContent = exam.title;
                        document.getElementById('modalExamClass').textContent = exam.class_name + ' - ' + exam.section_name;
                        document.getElementById('modalExamDate').textContent = exam.date;
                        document.getElementById('modalExamTime').textContent = (exam.start_time || '') + (exam.end_time ? ' - ' + exam.end_time : '');
                        document.getElementById('modalExamType').textContent = exam.type || exam.assessment_type;
                        document.getElementById('modalExamTotalMarks').textContent = exam.total_marks || 'TBA';
                        document.getElementById('modalExamSyllabus').textContent = exam.subject_name || '';
                        document.getElementById('modalExamInstructions').textContent = exam.instructions || '';
                        document.getElementById('modalExamInvigilators').textContent = ''; // Not available in new system
                        document.getElementById('modalExamTeacher').textContent = exam.teacher_name || '';
                        modal.style.display = 'block';
                    } else {
                        alert('Failed to load exam details.');
                    }
                });
            });
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            closeModalBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            document.querySelectorAll('.delete-exam-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    alert('Delete functionality has been disabled. Please contact administrator to modify exams.');
                });
            });
            document.querySelectorAll('.edit-exam-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    alert('Edit functionality has been disabled. Please contact administrator.');
                });
            });
        }
        fetchExams();

        // Add event listeners to filters
        document.getElementById('applyFilterBtn').addEventListener('click', applyFilters);

        // Section filter population
        const filterClass = document.getElementById('filterClass');
        const filterSection = document.getElementById('filterSection');
        filterClass.addEventListener('change', function() {
            filterSection.innerHTML = '<option value="">All Sections</option>';
            const classVal = this.value;
            if (!classVal || !sectionsByClass[classVal]) return;
            sectionsByClass[classVal].forEach(function(s) {
                filterSection.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });

        document.getElementById('clearFilterBtn').addEventListener('click', function() {
            document.getElementById('filterClass').value = '';
            document.getElementById('filterSection').innerHTML = '<option value="">All Sections</option>';
            document.getElementById('filterType').value = '';
            document.getElementById('filterStatus').value = '';
            applyFilters();
        });

        // Calendar View logic
        const calendarContent = document.getElementById('calendar-content');
        const calendarNav = document.createElement('div');
        calendarNav.id = 'calendarNav';
        calendarNav.style.display = 'flex';
        calendarNav.style.justifyContent = 'space-between';
        calendarNav.style.alignItems = 'center';
        calendarNav.style.marginBottom = '1rem';
        const calendarGrid = document.createElement('div');
        calendarGrid.id = 'calendarGrid';
        // Add a legend for assessment types/colors
        const calendarLegend = document.createElement('div');
        calendarLegend.id = 'calendarLegend';
        calendarLegend.style.display = 'flex';
        calendarLegend.style.gap = '1rem';
        calendarLegend.style.marginBottom = '0.5rem';
        calendarLegend.innerHTML = `
            <span style="display:inline-flex;align-items:center;"><span style="width:16px;height:16px;background:#4da6ff;border-radius:4px;display:inline-block;margin-right:4px;"></span>Quiz</span>
            <span style="display:inline-flex;align-items:center;"><span style="width:16px;height:16px;background:#ffb84d;border-radius:4px;display:inline-block;margin-right:4px;"></span>Unit Test</span>
            <span style="display:inline-flex;align-items:center;"><span style="width:16px;height:16px;background:#a259e6;border-radius:4px;display:inline-block;margin-right:4px;"></span>Mid-Term</span>
            <span style="display:inline-flex;align-items:center;"><span style="width:16px;height:16px;background:#ff4d4d;border-radius:4px;display:inline-block;margin-right:4px;"></span>Final</span>
        `;
        calendarContent.innerHTML = '';
        calendarContent.appendChild(calendarLegend);
        calendarContent.appendChild(calendarNav);
        calendarContent.appendChild(calendarGrid);

        let calendarMonth = new Date().getMonth();
        let calendarYear = new Date().getFullYear();

        function getTypeColor(type) {
            switch ((type||'').toLowerCase()) {
                case 'quiz': return '#4da6ff';
                case 'test': return '#ffb84d';
                case 'midterm': return '#a259e6';
                case 'final': return '#ff4d4d';
                default: return '#bdbdbd';
            }
        }

        function renderCalendar() {
            // Month navigation
            const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            calendarNav.innerHTML = `
                <button id="prevMonthBtn" class="btn btn-secondary btn-sm">&lt;</button>
                <span style="font-weight: bold;">${monthNames[calendarMonth]} ${calendarYear}</span>
                <button id="nextMonthBtn" class="btn btn-secondary btn-sm">&gt;</button>
            `;
            document.getElementById('prevMonthBtn').onclick = () => { changeMonth(-1); };
            document.getElementById('nextMonthBtn').onclick = () => { changeMonth(1); };

            // Calendar grid
            const firstDay = new Date(calendarYear, calendarMonth, 1);
            const lastDay = new Date(calendarYear, calendarMonth + 1, 0);
            const startDay = firstDay.getDay();
            const daysInMonth = lastDay.getDate();
            let html = '<div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; gap: 6px;">';
            ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
                html += `<div style="font-weight:bold; padding:8px 0; background:#f0f0f0; border-radius:6px;">${d}</div>`;
            });
            let day = 1;
            for (let i = 0; i < 42; i++) {
                if (i < startDay || day > daysInMonth) {
                    html += '<div style="min-height:120px; background:#f8f9fa; border-radius:8px;"></div>';
                } else {
                    const dateStr = `${calendarYear}-${String(calendarMonth+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
                    const exams = allExams.filter(e => e.date === dateStr);
                    html += `<div style="min-height:120px; background:#fff; border:1px solid #eee; border-radius:8px; position:relative; padding:6px 4px 4px 4px; display:flex; flex-direction:column; align-items:flex-start; overflow-y:auto; max-height:180px;">
                        <div style="font-size:1em; font-weight:bold; margin-bottom:2px; color:#333;">${day}</div>`;
                    exams.forEach(exam => {
                        const classSection = `${exam.class_name} - ${exam.section_name}`;
                        const time = (exam.start_time && exam.end_time) ? `${exam.start_time} - ${exam.end_time}` : '';
                        const color = getTypeColor(exam.type);
                        // Compose tooltip with all details
                        const tooltip = [
                            `Title: ${exam.title}`,
                            `Class: ${classSection}`,
                            `Date: ${exam.date}`,
                            time ? `Time: ${time}` : '',
                            exam.location ? `Location: ${exam.location}` : '',
                            exam.type ? `Type: ${exam.type}` : '',
                            exam.status ? `Status: ${exam.status}` : '',
                            exam.total_marks ? `Total Marks: ${exam.total_marks}` : '',
                            exam.invigilators ? `Invigilators: ${exam.invigilators}` : '',
                            exam.teacher_name ? `Teacher: ${exam.teacher_name}` : '',
                            exam.syllabus ? `Syllabus: ${exam.syllabus}` : '',
                            exam.instructions ? `Instructions: ${exam.instructions}` : ''
                        ].filter(Boolean).join('\n');
                        html += `<div style="background:${color}; color:#fff; border-radius:6px; margin:2px 0; padding:4px 6px; font-size:0.85em; width:100%; box-sizing:border-box; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.07); margin-bottom:2px;" title="${tooltip.replace(/\"/g, '&quot;')}">
                            <div style="font-weight:bold;">${exam.title}</div>
                            <div style="font-size:0.8em;">${classSection}</div>
                            <div style="font-size:0.8em;">${time}</div>
                        </div>`;
                    });
                    html += '</div>';
                    day++;
                }
            }
            html += '</div>';
            calendarGrid.innerHTML = html;
        }
        function changeMonth(delta) {
            calendarMonth += delta;
            if (calendarMonth < 0) { calendarMonth = 11; calendarYear--; }
            if (calendarMonth > 11) { calendarMonth = 0; calendarYear++; }
            renderCalendar();
        }
        // When Calendar View tab is clicked, render the calendar
        const calendarTab = document.querySelector('.tab[data-tab="calendar"]');
        calendarTab.addEventListener('click', function() {
            renderCalendar();
        });
        // Also re-render calendar when exams are fetched
        // ... in fetchExams, after allExams = data.data; ...
        // if (document.querySelector('.tab.active').dataset.tab === 'calendar') renderCalendar();
        // ... existing code ...

        // By Class Tab logic
        const byClassTab = document.querySelector('.tab[data-tab="classes"]');
        const byClassContainer = document.getElementById('byClassContainer');
        const byClassLoading = document.getElementById('byClassLoading');
        byClassTab.addEventListener('click', function() {
            byClassContainer.innerHTML = '';
            byClassLoading.style.display = 'block';
            fetch('../../admin/dashboard/exam_session_actions.php?action=get_teacher_exam_sessions', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                byClassLoading.style.display = 'none';
                if (data.success && data.data && data.data.length > 0) {
                    // Group exams by class-section
                    const grouped = {};
                    data.data.forEach(exam => {
                        const key = `${exam.class_name}-${exam.section_name || 'All Sections'}`;
                        if (!grouped[key]) {
                            grouped[key] = {
                                class_name: exam.class_name,
                                section_name: exam.section_name || 'All Sections',
                                assessments: []
                            };
                        }
                        grouped[key].assessments.push({
                            id: exam.id,
                            title: exam.session_name + (exam.subject_name ? ` - ${exam.subject_name}` : ''),
                            date: exam.exam_date || exam.start_date,
                            start_time: exam.exam_time || '',
                            end_time: '',
                            type: exam.session_type || 'SA',
                            status: exam.status || 'active'
                        });
                    });
                    
                    // Render each class-section card
                    let html = '<div class="two-col">';
                    Object.values(grouped).forEach(group => {
                        html += `<div class="card">
                            <div class="card-header">
                                <h3 class="card-title">${group.class_name} - ${group.section_name}</h3>
                            </div>
                            <div class="card-body">
                                <table class="exam-table">
                                    <thead>
                                        <tr>
                                            <th>Exam/Test</th>
                                            <th>Date & Time</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                        group.assessments.forEach(exam => {
                            const date = new Date(exam.date);
                            const dateStr = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                            const timeStr = exam.start_time && exam.end_time ? `${exam.start_time} - ${exam.end_time}` : exam.start_time || '';
                            let status = exam.status || 'active';
                            let badgeClass = 'badge-upcoming';
                            if (status.toLowerCase() === 'completed') badgeClass = 'badge-completed';
                            else if (status.toLowerCase() === 'draft') badgeClass = 'badge-draft';
                            let typeClass = 'exam-type-' + (exam.type ? exam.type.toLowerCase().replace(/\s/g, '') : '');
                            let typeLabel = exam.type || '';
                            html += `<tr>
                                <td>${exam.title}</td>
                                <td>${dateStr}${timeStr ? '<br>' + timeStr : ''}</td>
                                <td><span class="exam-type-badge ${typeClass}">${typeLabel}</span></td>
                                <td><span class="badge ${badgeClass}">${status}</span></td>
                            </tr>`;
                        });
                        html += `</tbody></table></div></div>`;
                    });
                    html += '</div>';
                    byClassContainer.innerHTML = html;
                } else {
                    byClassContainer.innerHTML = '<div style="text-align:center; padding:2rem;">No exams found for any class-section.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading exams by class:', error);
                byClassLoading.style.display = 'none';
                byClassContainer.innerHTML = '<div style="text-align:center; padding:2rem; color:red;">Failed to load data.</div>';
            });
        });

        // Student List Modal logic
        const studentListModal = document.getElementById('studentListModal');
        const closeStudentListModal = document.getElementById('closeStudentListModal');
        const closeStudentListModalBtn = document.getElementById('closeStudentListModalBtn');
        const studentListExamSelect = document.getElementById('studentListExamSelect');
        const studentListLoading = document.getElementById('studentListLoading');
        const studentListError = document.getElementById('studentListError');
        const studentListTableContainer = document.getElementById('studentListTableContainer');

        function openStudentListModal() {
            // Populate exam dropdown
            studentListExamSelect.innerHTML = '';
            if (!allExams || allExams.length === 0) {
                studentListExamSelect.innerHTML = '<option value="">No exams available</option>';
                studentListTableContainer.innerHTML = '';
                return;
            }
            allExams.forEach(exam => {
                const date = new Date(exam.date);
                const dateStr = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                const label = `${exam.title} (${exam.class_name} - ${exam.section_name}, ${dateStr})`;
                const opt = document.createElement('option');
                opt.value = exam.id;
                opt.textContent = label;
                studentListExamSelect.appendChild(opt);
            });
            // Show modal
            studentListModal.style.display = 'block';
            // Load for first exam
            if (studentListExamSelect.value) fetchStudentList(studentListExamSelect.value);
        }

        function closeStudentList() {
            studentListModal.style.display = 'none';
            studentListTableContainer.innerHTML = '';
            studentListError.style.display = 'none';
            studentListLoading.style.display = 'none';
        }

        closeStudentListModal.addEventListener('click', closeStudentList);
        closeStudentListModalBtn.addEventListener('click', closeStudentList);
        studentListExamSelect.addEventListener('change', function() {
            if (this.value) fetchStudentList(this.value);
        });

        function fetchStudentList(assessmentId) {
            studentListLoading.style.display = 'block';
            studentListError.style.display = 'none';
            studentListTableContainer.innerHTML = '';
            fetch('../../admin/dashboard/exam_session_actions.php?action=get_student_list_for_exam&exam_subject_id=' + assessmentId, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                studentListLoading.style.display = 'none';
                if (data.success && data.data && data.data.length > 0) {
                    let html = `<div style=\"overflow-x:auto;\"><table class=\"exam-table\"><thead><tr><th>Roll No</th><th>Name</th><th>Class</th><th>Marks</th><th>Grade</th><th>Remark</th></tr></thead><tbody>`;
                    data.data.forEach(stu => {
                        html += `<tr>
                            <td>${stu.roll_number || ''}</td>
                            <td>${stu.full_name || ''}</td>
                            <td>${stu.class_name} - ${stu.section_name || 'All'}</td>
                            <td>${stu.marks_obtained !== null && stu.marks_obtained !== undefined ? stu.marks_obtained : 'Not graded'}</td>
                            <td>${stu.grade_code || ''}</td>
                            <td>${stu.remark || ''}</td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                    studentListTableContainer.innerHTML = html;
                } else if (data.success && data.data && data.data.length === 0) {
                    studentListTableContainer.innerHTML = '<div style="text-align:center; padding:2rem; color:#888;">No students found for this exam.</div>';
                } else {
                    studentListError.style.display = 'block';
                    studentListError.textContent = data.message || 'Failed to load student list.';
                }
            })
            .catch(error => {
                console.error('Error loading student list:', error);
                studentListLoading.style.display = 'none';
                studentListError.style.display = 'block';
                studentListError.textContent = 'Failed to load student list.';
            });
        }

        // Quick Action: Student List
        const quickActions = document.querySelectorAll('.action-card');
        if (quickActions[1]) {
            quickActions[1].addEventListener('click', function() {
                openStudentListModal();
            });
        }

        // Print Schedule logic
        function printSchedule() {
            window.print();
        }
        // Print Quick Action
        if (quickActions[2]) {
            quickActions[2].addEventListener('click', printSchedule);
        }
        // Print icon link
        const printLink = document.querySelector('.print-link');
        if (printLink) {
            printLink.addEventListener('click', function(e) {
                e.preventDefault();
                printSchedule();
            });
        }

        // Notification Bell logic
        const notificationBell = document.getElementById('notificationBell');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationDropdownContent = document.getElementById('notificationDropdownContent');
        const notificationBadge = document.getElementById('notificationBadge');
        let notifications = [];
        let unreadCount = 0;

        function fetchNotifications() {
            fetch('notifications_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'list_notifications' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    notifications = data.data;
                    unreadCount = notifications.filter(n => n.is_read == 0).length;
                    renderNotifications();
                }
            });
        }

        function renderNotifications() {
            if (unreadCount > 0) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = 'inline-block';
            } else {
                notificationBadge.style.display = 'none';
            }
            if (!notifications.length) {
                notificationDropdownContent.innerHTML = '<div style="padding:1rem; text-align:center; color:#888;">No notifications.</div>';
                return;
            }
            let html = '';
            notifications.forEach(n => {
                html += `<div style="padding:0.75rem 1rem; border-bottom:1px solid #f0f0f0; background:${n.is_read==0?'#f7faff':'#fff'}; font-size:0.98em;">
                    <div>${n.message}</div>
                    <div style=\"font-size:0.85em; color:#888; margin-top:2px;\">${new Date(n.created_at).toLocaleString()}</div>
                </div>`;
            });
            notificationDropdownContent.innerHTML = html;
        }

        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            if (notificationDropdown.style.display === 'block') {
                notificationDropdown.style.display = 'none';
            } else {
                fetchNotifications();
                notificationDropdown.style.display = 'block';
            }
        });
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && e.target !== notificationBell) {
                notificationDropdown.style.display = 'none';
            }
        });

        // Dynamic Upcoming Exam Alert
        function fetchUpcomingExam() {
            fetch('../../admin/dashboard/exam_session_actions.php?action=get_teacher_exam_sessions', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                console.log('Upcoming exam data:', data); // Debug output
                const alertDiv = document.getElementById('upcomingExamAlert');
                alertDiv.style.display = ''; // Always show for debugging
                
                if (data.success && data.data && data.data.length > 0) {
                    // Find the next upcoming exam
                    const today = new Date();
                    const upcomingExams = data.data.filter(exam => {
                        const examDate = new Date(exam.exam_date || exam.start_date);
                        return examDate >= today;
                    }).sort((a, b) => {
                        const dateA = new Date(a.exam_date || a.start_date);
                        const dateB = new Date(b.exam_date || b.start_date);
                        return dateA - dateB;
                    });
                    
                    if (upcomingExams.length > 0) {
                        const exam = upcomingExams[0];
                        // Transform to expected format
                        const examData = {
                            title: exam.session_name + (exam.subject_name ? ` - ${exam.subject_name}` : ''),
                            date: exam.exam_date || exam.start_date,
                            class_name: exam.class_name || 'Unknown Class',
                            section_name: exam.section_name || 'All Sections'
                        };
                        
                        // Make the title a link if possible
                        let titleHtml = 'Upcoming: ';
                        let examId = null;
                        if (window.allExams && Array.isArray(window.allExams)) {
                            const found = allExams.find(e => e.title === examData.title && e.date === examData.date);
                            if (found) examId = found.id;
                        }
                        if (examId) {
                            titleHtml += `<a href="#" class="upcoming-exam-link" data-exam-id="${examId}" style="color:#2a4cff; text-decoration:underline;">${examData.title}</a>`;
                        } else {
                            titleHtml += examData.title;
                        }
                        document.getElementById('upcomingExamTitle').innerHTML = titleHtml;
                        const dateObj = new Date(examData.date);
                        const dateStr = dateObj.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        document.getElementById('upcomingExamDetails').textContent = `${examData.class_name} - ${examData.section_name} - ${dateStr}`;
                        // Countdown (by date only)
                        const now = new Date();
                        const diffMs = dateObj.setHours(0,0,0,0) - now.setHours(0,0,0,0);
                        let countdown = '';
                        if (diffMs > 0) {
                            const days = Math.floor(diffMs / (1000*60*60*24));
                            countdown = days + ' day' + (days>1?'s':'') + ' remaining';
                        } else if (diffMs === 0) {
                            countdown = 'Today';
                        } else {
                            countdown = 'Ongoing or passed';
                        }
                        document.getElementById('upcomingExamCountdown').textContent = countdown;
                        alertDiv.style.display = '';
                        setTimeout(() => {
                            const link = document.querySelector('.upcoming-exam-link');
                            if (link) {
                                link.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    // Edit functionality disabled - form removed
                                    console.log('Edit functionality disabled');
                                });
                            }
                        }, 100);
                    } else {
                        alertDiv.innerHTML = '<div style="padding:1rem; color:#888;">No upcoming exams scheduled.</div>';
                    }
                } else {
                    alertDiv.innerHTML = '<div style="padding:1rem; color:#888;">No upcoming exams found.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching upcoming exam:', error);
                const alertDiv = document.getElementById('upcomingExamAlert');
                alertDiv.innerHTML = '<div style="padding:1rem; color:red;">Error loading upcoming exam data.</div>';
            });
        }
        fetchUpcomingExam();
    });

</script>
</body>
</html>