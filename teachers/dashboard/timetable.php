<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Timetable</title>
    <?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Timetable</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/timetable.css">
    <style>
        /* Additional styles for dynamic functionality */
        .loading-message {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .error-message {
            text-align: center;
            padding: 20px;
            color: #ef4444;
            font-weight: 500;
        }
        
        .highlighted {
            position: relative;
            box-shadow: 0 0 0 2px #4f46e5;
            animation: pulse 2s infinite;
        }
        
        /* Class-specific color schemes */
        .class-block {
            border-radius: 6px;
            padding: 8px;
            margin: 4px;
            transition: all 0.3s ease;
        }

        .class-block:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Color schemes for different classes */
        .class-IIIA {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }

        .class-IIA {
            background-color: #f3e5f5;
            border-left: 4px solid #9c27b0;
        }

        .class-IA {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
        }

        .class-IVA {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }

        .class-VA {
            background-color: #fbe9e7;
            border-left: 4px solid #ff5722;
        }

        /* Existing styles */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4); }
            50% { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.8); }
            100% { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4); }
        }
        
        /* Print styles */
        @media print {
            .upcoming-class, .quick-actions, .action-bar, .card-header, .card-footer,
            .hamburger-btn, .timetable-filters, .sidebar-overlay, .sidebar {
                display: none !important;
            }
            
            .dashboard-container {
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .card {
                box-shadow: none !important;
                border: none !important;
            }
            
            .card-body {
                padding: 0 !important;
            }
            
            .timetable {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            
            .timetable th, .timetable td {
                border: 1px solid #ddd !important;
                padding: 8px !important;
            }
            
            @page {
                size: landscape;
                margin: 1cm;
            }
        }

        /* Student List Styles */
        .student-list-container {
            max-height: 400px; /* Fixed height for the container */
            overflow-y: auto; /* Enable vertical scrolling */
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .student-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .student-table thead {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
            z-index: 1;
        }
        
        .student-table th {
            background-color: #f9fafb;
            font-weight: 500;
            color: #374151;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .student-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            background-color: #ffffff;
        }
        
        .student-table tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        .student-table tbody tr:hover td {
            background-color: #f3f4f6;
        }

        /* Adjust modal size */
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 30px auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            max-height: 80vh; /* Limit modal height to 80% of viewport height */
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto; /* Enable scrolling for modal body if needed */
            flex: 1;
        }

        /* Loading and error message styles */
        .loading-message, .error-message {
            padding: 20px;
            text-align: center;
            background-color: #ffffff;
        }

        /* Scrollbar styling for better appearance */
        .student-list-container::-webkit-scrollbar {
            width: 8px;
        }

        .student-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .student-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .student-list-container::-webkit-scrollbar-thumb:hover {
            background: #666;
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
        <h1 class="header-title">Timetable</h1>
        <p class="header-subtitle">View and manage your teaching schedule</p>
    </header>

    <main class="dashboard-content">
        <!-- Upcoming Class Alert -->
        <div class="upcoming-class" id="upcomingClassAlert" style="display: none;">
            <div class="upcoming-class-info">
                <div class="upcoming-class-time">Next class in</div>
                <div class="upcoming-class-name" id="upcomingClassName">Loading...</div>
                <div class="upcoming-class-details" id="upcomingClassDetails">Loading...</div>
            </div>
            <div class="countdown">
                <div class="countdown-value" id="countdownValue">--:--</div>
                <div class="countdown-label">minutes remaining</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-card" data-action="class-notes">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <div class="action-title">Class Notes</div>
                <div class="action-description">Add notes for upcoming classes</div>
            </div>
            <div class="action-card" data-action="student-list">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <div class="action-title">Student List</div>
                <div class="action-description">View students for selected class</div>
            </div>
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                <div class="action-title">Substitute Request</div>
                <div class="action-description">Request a substitute teacher</div>
            </div>
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div class="action-title">Schedule Change</div>
                <div class="action-description">Request a schedule change</div>
            </div>
        </div>

        <!-- Timetable Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Weekly Timetable</h2>
                <div class="view-options">
                    <button class="view-option active">Week</button>
                    <button class="view-option">Day</button>
                    <button class="view-option">List</button>
                </div>
            </div>
            <div class="card-body">
                <div class="timetable-filters">
                    <div class="filter-item">
                        <label for="weekSelect" class="form-label">Select Week</label>
                        <select id="weekSelect" class="form-select">
                            <option value="current">Current Week (Mar 10 - Mar 16)</option>
                            <option value="next">Next Week (Mar 17 - Mar 23)</option>
                            <option value="after">Week After (Mar 24 - Mar 30)</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="classSelect" class="form-label">Filter by Class</label>
                        <select id="classSelect" class="form-select">
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="subjectSelect" class="form-label">Filter by Subject</label>
                        <select id="subjectSelect" class="form-select">
                            <option value="all">All Subjects</option>
                            <option value="science">Science</option>
                            <option value="math">Mathematics</option>
                            <option value="english">English</option>
                            <option value="social">Social Studies</option>
                            <option value="art">Art &amp; Craft</option>
                        </select>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th class="time-header">Time</th>
                                <th class="day-header">Monday</th>
                                <th class="day-header">Tuesday</th>
                                <th class="day-header">Wednesday</th>
                                <th class="day-header">Thursday</th>
                                <th class="day-header">Friday</th>
                                <th class="day-header">Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Table body will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="teacher-note">
                    <strong>Note:</strong> Class 9B - Science on Thursday (10:30 - 11:20) will cover chapter 8 (Electromagnetic Spectrum). Please refer to the curriculum guide for the practical demonstration requirements.
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-secondary" style="margin-right: 0.75rem;">Print Timetable</button>
                <button class="btn btn-primary">Export to Calendar</button>
            </div>
        </div>
    </main>
</div>

<!-- Class Notes Modal -->
<div id="classNotesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Class Notes</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="tabs">
                <button class="tab-btn active" data-tab="add">Add Note</button>
                <button class="tab-btn" data-tab="view">View Notes</button>
            </div>

            <!-- Add Note Form -->
            <div class="tab-content active" id="addNoteTab">
                <form id="classNoteForm">
                    <div class="form-group">
                        <label for="noteClass">Class</label>
                        <select id="noteClass" name="class_id" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="noteSubject">Subject</label>
                        <select id="noteSubject" name="subject_id" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="noteDate">Date</label>
                        <input type="date" id="noteDate" name="note_date" required>
                    </div>

                    <div class="form-group">
                        <label for="noteContent">Notes</label>
                        <textarea id="noteContent" name="note_content" rows="6" required 
                                placeholder="Enter your notes here..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeClassNotesModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>

            <!-- View Notes List -->
            <div class="tab-content" id="viewNotesTab">
                <div class="notes-filters">
                    <select id="filterClass">
                        <option value="">All Classes</option>
                    </select>
                    <select id="filterSubject">
                        <option value="">All Subjects</option>
                    </select>
                    <input type="date" id="filterDate">
                </div>
                <div class="notes-list">
                    <!-- Notes will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student List Modal -->
<div id="studentListModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Student List</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="class-selection">
                <label for="studentListClass">Select Class:</label>
                <select id="studentListClass" class="form-select">
                    <option value="">Select a Class</option>
                </select>
                <button id="loadStudentsBtn" class="btn btn-primary">Load Students</button>
            </div>
            
            <div class="student-search">
                <input type="text" id="studentSearch" placeholder="Search by name, ID or roll number" class="form-control">
            </div>
            
            <div class="student-list-container">
                <div id="studentListLoading" class="loading-message">Select a class to view students</div>
                <table id="studentTable" class="student-table">
                    <thead>
                        <tr>
                            <th>Roll No.</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Student data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div id="studentDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Student Details</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="studentDetailsContent">
                <!-- Student details will be loaded here -->
                <div class="loading-message">Loading student details...</div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        position: relative;
        background-color: #fff;
        margin: 50px auto;
        padding: 0;
        width: 90%;
        max-width: 700px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        color: #1f2937;
        font-size: 1.5rem;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
    }

    .modal-body {
        padding: 20px;
    }

    /* Tabs Styles */
    .tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 20px;
    }

    .tab-btn {
        padding: 10px 20px;
        border: none;
        background: none;
        cursor: pointer;
        color: #6b7280;
        font-weight: 500;
    }

    .tab-btn.active {
        color: #4f46e5;
        border-bottom: 2px solid #4f46e5;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #374151;
        font-weight: 500;
    }

    .form-group select,
    .form-group input[type="date"],
    .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-group textarea {
        resize: vertical;
    }

    /* Notes List Styles */
    .notes-filters {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .notes-filters select,
    .notes-filters input {
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }

    .notes-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .note-item {
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .note-item:hover {
        background-color: #f9fafb;
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .note-title {
        font-weight: 500;
        color: #1f2937;
    }

    .note-date {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .note-content {
        color: #4b5563;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #4f46e5;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: #4338ca;
    }

    .btn-secondary {
        background-color: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background-color: #f9fafb;
    }

    /* Student List Styles */
    .class-selection {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .class-selection select {
        flex: 1;
    }
    
    .student-search {
        margin-bottom: 20px;
    }
    
    .student-search input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }
    
    .student-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .student-table thead {
        position: sticky;
        top: 0;
        background-color: #f9fafb;
        z-index: 1;
    }
    
    .student-table th {
        background-color: #f9fafb;
        font-weight: 500;
        color: #374151;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .student-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        background-color: #ffffff;
    }
    
    .student-table tbody tr:hover {
        background-color: #f3f4f6;
    }
    
    .student-table tbody tr:hover td {
        background-color: #f3f4f6;
    }
    
    .student-action-btn {
        background: none;
        border: none;
        color: #4f46e5;
        cursor: pointer;
        margin-right: 8px;
    }
    
    .student-action-btn:hover {
        text-decoration: underline;
    }
    
    .student-details {
        background-color: #f9fafb;
        padding: 15px;
        border-radius: 6px;
        margin-top: 10px;
        display: none;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 8px;
    }
    
    .detail-label {
        font-weight: 500;
        width: 150px;
    }
    
    .no-results {
        text-align: center;
        padding: 20px;
        color: #6b7280;
        font-style: italic;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load teacher's timetable data
        loadTeacherTimetable();
        
        // Tab switching for view options
        const viewOptions = document.querySelectorAll('.view-option');
        
        viewOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                viewOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to clicked option
                this.classList.add('active');
                
                // Here you would normally switch the content based on the option
                // For this demo, we'll just show an alert
                if (this.textContent !== 'Week') {
                    alert('Switching to ' + this.textContent + ' view - This would show a different layout in a real application.');
                }
            });
        });
        
        // Filter handling
        const filters = document.querySelectorAll('.form-select');
        
        filters.forEach(filter => {
            filter.addEventListener('change', function() {
                // In a real app, this would filter the timetable data
                if (this.id === 'subjectSelect') { // Only subject filter affects the current view directly
                    filterTimetable();
                } else if (this.id === 'classSelect') {
                    // When the class filter changes, find and render the selected timetable
                    const selectedValue = this.value;
                    
                    if (selectedValue === '') {
                        // "All Classes" selected - show aggregated timetable
                        renderAggregatedTimetable();
                    } else {
                        // Specific class selected - show that class's timetable
                        const selectedTimetableId = parseInt(selectedValue);
                        if (!isNaN(selectedTimetableId)) {
                            selectAndRenderTimetable(selectedTimetableId);
                        }
                    }
                }
            });
        });
        
        // Week select handling
        document.getElementById('weekSelect').addEventListener('change', function() {
            // This would load a different week's timetable in a real application
            // For now, just show the same data but acknowledge the change
            console.log('Week changed to:', this.value);
            // Re-load timetable with week filter
            loadTeacherTimetable();
        });
        
        // Countdown timer simulation for upcoming class
        simulateCountdown();
        
        // Function to toggle the sidebar (defined in the sidebar.php)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        };

        // Print timetable functionality
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            window.print();
        });
        
        // Export to calendar functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            exportToCalendar();
        });

        // Tab switching functionality
        document.querySelector('[data-action="class-notes"]').addEventListener('click', function() {
            openClassNotesModal();
        });

        // Student List action card click handler
        document.querySelector('[data-action="student-list"]').addEventListener('click', function() {
            openStudentListModal();
        });

        // Tab switching within the modal
        const tabButtons = document.querySelectorAll('.tab-btn');
        console.log('Found tab buttons:', tabButtons.length);
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                console.log('Tab clicked:', tabId);
        
                // Log all tab contents for debugging
                const allTabContents = document.querySelectorAll('.tab-content');
                console.log('Available tab contents:', Array.from(allTabContents).map(el => el.id));

                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Add active class to clicked tab and its content
                this.classList.add('active');
                // Match the correct tab content ID format
                const tabContentId = tabId === 'add' ? 'addNoteTab' : 'viewNotesTab';
                console.log('Looking for tab content with ID:', tabContentId);
                
                const tabContent = document.getElementById(tabContentId);
                if (tabContent) {
                    console.log('Found tab content, activating:', tabContentId);
                    tabContent.classList.add('active');
                    if (tabId === 'view') {
                        loadNotes();
                    }
                } else {
                    console.error(`Tab content not found for ${tabContentId}`);
                    // Log all elements with IDs for debugging
                    const allElements = document.querySelectorAll('[id]');
                    console.log('All elements with IDs:', Array.from(allElements).map(el => el.id));
                }
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const classNotesModal = document.getElementById('classNotesModal');
            const studentListModal = document.getElementById('studentListModal');
            
            if (event.target === classNotesModal) {
                closeClassNotesModal();
            }
            
            if (event.target === studentListModal) {
                closeStudentListModal();
            }
        });

        // Form submission handler
        document.getElementById('classNoteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const formData = {
                    class_info: JSON.parse(document.getElementById('noteClass').value),
                    subject_id: document.getElementById('noteSubject').value,
                    note_date: document.getElementById('noteDate').value,
                    note_content: document.getElementById('noteContent').value
                };
                
                const response = await axios.post('../../backend/api/class-notes.php', formData);
                
                if (response.data.success) {
                    alert('Note saved successfully!');
                    closeClassNotesModal();
                    // Optionally refresh the view notes tab if it's open
                    if (document.getElementById('viewNotesTab').classList.contains('active')) {
                        loadNotes();
                    }
                } else {
                    // Log the full response data for inspection
                    console.error('Server responded with error:', response.data);
                    alert('Failed to save note. Server responded: ' + (response.data.message || JSON.stringify(response.data)));
                }
            } catch (error) {
                console.error('Error saving note:', error);
                alert('An error occurred while saving the note.');
            }
        });

        // Initial update of upcoming class
        setTimeout(updateUpcomingClassDisplay, 1000);
    });

    // Load teacher's timetable from API
    let allTimetables = []; // Store all timetables fetched
    let currentTimetableId = null; // Store the ID of the currently displayed timetable

    function loadTeacherTimetable() {
        // Show loading state
        const timetableBody = document.querySelector('.timetable tbody');
        timetableBody.innerHTML = '<tr><td colspan="6" class="loading-message">Loading timetable data...</td></tr>';

        // Get filter values (optional, depending on if initial load should respect filters)
        // For now, fetching all timetables for the teacher regardless of initial filter selection

        // Use explicit API endpoint for listing teacher timetables (summaries)
        const listApiUrl = '../../backend/api/timetables';
        const params = {};

        // Note: Filters for class, section, subject are applied on the frontend after loading all data.
        // Academic year filter might need backend implementation if required for the initial list.

        console.log('Requesting timetable list with params:', params);
        
        axios.get(listApiUrl, { params: params })
            .then(response => {
                console.log('API list response:', response.data);
                
                if (response.data && response.data.data && response.data.data.length > 0) {
                    const timetableSummaries = response.data.data;

                    // Now fetch details for each timetable summary
                    const detailPromises = timetableSummaries.map(summary =>
                        axios.get(`../../backend/api/timetables/${summary.id}`)
                            .then(detailResponse => detailResponse.data.data) // Extract the data object
                    );
                    
                    // Wait for all detail promises to resolve
                    Promise.all(detailPromises)
                        .then(detailedTimetables => {
                            console.log('Fetched detailed timetables:', detailedTimetables);
                            allTimetables = detailedTimetables.filter(t => t !== null); // Store valid detailed timetables

                            if (allTimetables.length > 0) {
                                // Update class and subject filter options based on available data
                                updateFilterOptions(allTimetables);

                                // Determine which timetable to render initially
                                const initialClassFilterValue = document.getElementById('classSelect').value;

                                if (initialClassFilterValue === '') {
                                    // "All Classes" is selected (default) - show aggregated timetable
                                    renderAggregatedTimetable();
                                } else {
                                    // Find the timetable that matches the initial filter value
                                    const initialTimetable = allTimetables.find(t => String(t.id) === initialClassFilterValue);
                                    if (initialTimetable) {
                                        selectAndRenderTimetable(initialTimetable.id);
                                    } else {
                                        // Fallback to aggregated view if the filter value doesn't match
                                        renderAggregatedTimetable();
                                    }
                                }
                                
                                // Update upcoming class display after timetable is loaded
                                setTimeout(updateUpcomingClassDisplay, 1000);
                            } else {
                                showTimetableError('No detailed timetable data available after fetching details.');
                            }
                        })
                        .catch(detailError => {
                            console.error('Error fetching timetable details:', detailError);
                            showTimetableError('Failed to load detailed timetable data. Please try again later.');
                        });

                } else {
                    showTimetableError('No timetable summaries found for this teacher.');
                    console.warn('Timetable summary data missing or empty:', response.data);
                     // Clear the timetable body if no data is returned
                    timetableBody.innerHTML = '<tr><td colspan="6" class="no-data-message">No timetable data found for this teacher.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading teacher timetable list:', error);
                showTimetableError('Failed to load timetable list. Please try again later.');
            });
    }
    
    // Render teacher's timetable - This function will now fetch details for a specific timetable ID
    function selectAndRenderTimetable(timetableId) {
        console.log('Selecting and rendering timetable with ID:', timetableId);
        // Find the timetable with periods from the allTimetables array
        const timetableToRender = allTimetables.find(t => t.id === timetableId && t.periods);

        if (!timetableToRender) {
            showTimetableError(`Timetable with ID ${timetableId} or its periods not found in loaded data.`);
            return;
        }

        currentTimetableId = timetableId; // Update current timetable ID

            // Update the card title with actual class/section info
            document.querySelector('.card-title').textContent = 
            `Weekly Timetable for Class ${timetableToRender.class_name}${timetableToRender.section_name}`;
            
        // Build the timetable using the already fetched periods
        if (timetableToRender.periods && timetableToRender.periods.length > 0) {
            const periodsData = timetableToRender.periods;
            console.log(`Rendering ${periodsData.length} periods for timetable ID ${timetableId}`);
            buildTimetableFromPeriods(periodsData, timetableToRender.class_name, timetableToRender.section_name);
                        } else {
             const timetableBody = document.querySelector('.timetable tbody');
                            timetableBody.innerHTML = '<tr><td colspan="6" class="error-message">No classes scheduled for this timetable.</td></tr>';
                        }
    }

    // This function is now responsible for just building the HTML table
    function renderTeacherTimetable(timetables) {
       console.log('renderTeacherTimetable called - this function is deprecated in favor of selectAndRenderTimetable and renderAggregatedTimetable');
       // This function is no longer used directly for rendering the main table
       // It might be used later if we decide to display multiple tables.
       // For now, selectAndRenderTimetable handles the rendering logic.
    }
    
    // Build timetable from period data
    function buildTimetableFromPeriods(periods, className, sectionName) {
        // Define time slots
        const timeSlots = [
            { label: '8:00 - 8:45', startTime: '08:00:00', endTime: '08:45:00' },
            { label: '8:50 - 9:35', startTime: '08:50:00', endTime: '09:35:00' },
            { label: '9:40 - 10:25', startTime: '09:40:00', endTime: '10:25:00' },
            { label: '10:25 - 10:40', startTime: '10:25:00', endTime: '10:40:00', isBreak: true, breakLabel: 'Morning Break' },
            { label: '10:40 - 11:25', startTime: '10:40:00', endTime: '11:25:00' },
            { label: '11:30 - 12:15', startTime: '11:30:00', endTime: '12:15:00' },
            { label: '12:20 - 1:05', startTime: '12:20:00', endTime: '13:05:00' },
            { label: '1:05 - 1:45', startTime: '13:05:00', endTime: '13:45:00', isBreak: true, breakLabel: 'Lunch Break' },
            { label: '1:45 - 2:30', startTime: '13:45:00', endTime: '14:30:00' },
            { label: '2:35 - 3:20', startTime: '14:35:00', endTime: '15:20:00' }
        ];

        // Map day names to columns (1-based index)
        const dayMap = {
            'monday': 1,
            'tuesday': 2,
            'wednesday': 3,
            'thursday': 4,
            'friday': 5,
            'saturday': 6
        };

        // Subject to CSS class mapping for styling
        const subjectClassMap = {
            'ENGLISH': 'english-class',
            'MATHEMATICS': 'math-class',
            'SCIENCE': 'science-class',
            'SOCIAL STUDIES': 'social-class',
            'ART': 'art-class',
            'COMPUTER': 'computer-class'
        };

        // Organize periods by time slot and day
        const timetableGrid = {};
        
        // Initialize grid with empty cells
        timeSlots.forEach(slot => {
            if (!slot.isBreak) {
                timetableGrid[slot.startTime] = {
                    timeLabel: slot.label,
                    days: {
                        'monday': null,
                        'tuesday': null,
                        'wednesday': null,
                        'thursday': null,
                        'friday': null,
                        'saturday': null
                    }
                };
            } else {
                timetableGrid[slot.startTime] = {
                    timeLabel: slot.label,
                    isBreak: true,
                    breakLabel: slot.breakLabel
                };
            }
        });

        // Populate grid with periods
        periods.forEach(period => {
            const day = period.day_of_week.toLowerCase();
            const startTime = period.start_time;
            
            // Find matching time slot
            const matchingSlot = timeSlots.find(slot => slot.startTime === startTime);
            
            if (matchingSlot && !matchingSlot.isBreak && dayMap[day]) {
                timetableGrid[startTime].days[day] = period;
            }
        });

        // Build the HTML for the timetable
        const timetableBody = document.querySelector('.timetable tbody');
        timetableBody.innerHTML = '';

        // For each time slot
        Object.keys(timetableGrid).sort().forEach(startTime => {
            const slot = timetableGrid[startTime];
            const row = document.createElement('tr');
            
            // Add time cell
            const timeCell = document.createElement('td');
            timeCell.className = 'time-cell';
            timeCell.textContent = slot.timeLabel;
            row.appendChild(timeCell);
            
            if (slot.isBreak) {
                // Break row spans all days
                const breakCell = document.createElement('td');
                breakCell.colSpan = 7; // For all days (Monday to Saturday)
                breakCell.style.textAlign = 'center';
                breakCell.style.fontWeight = '500';
                breakCell.style.color = '#6b7280';
                breakCell.style.backgroundColor = '#f9fafb';
                breakCell.textContent = slot.breakLabel;
                row.appendChild(breakCell);
            } else {
                // Add cells for each day
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].forEach(day => {
                    const dayCell = document.createElement('td');
                    const period = slot.days[day];
                    
                    if (period) {
                        // We have a class for this day and time slot
                        const subjectClass = subjectClassMap[period.subject_name] || 'default-class';
                        // Get class name and section from the period if available (aggregated view) or use the passed arguments
                        const displayClassName = period.class_name || className;
                        const displaySectionName = period.section_name || sectionName;
                        // Create a class-specific color class
                        const classColorClass = `class-${displayClassName}${displaySectionName}`;
                        
                        dayCell.innerHTML = `
                            <div class="class-block ${subjectClass} ${classColorClass}">
                                <div class="class-name">Class ${displayClassName}${displaySectionName}</div>
                                <div class="class-details">
                                    <div class="class-subject">${period.subject_name}</div>
                                    <div class="class-room">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="class-room-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Period ${period.period_number}
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        // No class for this slot
                        dayCell.innerHTML = '<div class="no-classes">No classes</div>';
                    }
                    
                    row.appendChild(dayCell);
                });
            }
            
            timetableBody.appendChild(row);
        });
        
        // Apply any active filters
        filterTimetable();
    }
    
    // Update filter options based on available timetable data
    function updateFilterOptions(timetables) {
        console.log('updateFilterOptions received timetables:', timetables);
        // Extract unique classes, sections, and subjects with their IDs from timetable data
        const classes = new Map(); // Map: ClassNameSection -> { timetable_id, class_id, section_id }
        const subjects = new Map(); // Map: SubjectName -> subject_id
        
        timetables.forEach(timetable => {
            if (timetable.class_name && timetable.section_name && timetable.class_id && timetable.section_id && timetable.id) {
                 const classNameSection = `${timetable.class_name}${timetable.section_name}`;
                 // Store the timetable ID in the map
                 classes.set(classNameSection, { timetable_id: timetable.id, class_id: timetable.class_id, section_id: timetable.section_id });
            }

            if (timetable.periods && timetable.periods.length > 0) {
                timetable.periods.forEach(period => {
                    if (period.subject_name && period.subject_id) {
                        subjects.set(period.subject_name, period.subject_id);
                    }
                });
            }
        });
        
        // Update class filter options
        const classSelect = document.getElementById('classSelect');
        const currentClassValue = classSelect.value; // Preserve current selection

        classSelect.innerHTML = '';

        // ✅ Add "All Classes" option
        const allOption = document.createElement('option');
        allOption.value = ''; // or use 'all' if you handle it in filtering
        allOption.textContent = 'All Classes';
        if (currentClassValue === '') {
            allOption.selected = true;
        }
        classSelect.appendChild(allOption);

        // Add classes from the data, sorted by name
        const sortedClassNames = Array.from(classes.keys()).sort();
        console.log('Generated classes map for filter:', classes);
        let hasSelection = currentClassValue === '';

        sortedClassNames.forEach((classNameSection, index) => {
            const classInfo = classes.get(classNameSection);
            if (classInfo) {
                const option = document.createElement('option');
                option.value = classInfo.timetable_id;
                option.textContent = `Class ${classNameSection}`;
                
                if (String(classInfo.timetable_id) === currentClassValue) {
                    option.selected = true;
                    hasSelection = true;
                }

                classSelect.appendChild(option);
            }
        });
        
        // Update subject filter options
        const subjectSelect = document.getElementById('subjectSelect');
        const currentSubjectValue = subjectSelect.value; // Preserve current selection
        // Keep the "All Subjects" option
        const allSubjectsOption = subjectSelect.options[0];
        subjectSelect.innerHTML = '';
        subjectSelect.appendChild(allSubjectsOption);
        
        // Add subjects from the data, sorted by name
        const sortedSubjectNames = Array.from(subjects.keys()).sort();
        sortedSubjectNames.forEach(subjectName => {
            const subjectId = subjects.get(subjectName);
            if (subjectId) {
                const option = document.createElement('option');
                // Store subject_id in the value
                option.value = subjectId;
                option.textContent = subjectName;
                 if (String(subjectId) === currentSubjectValue) {
                     option.selected = true; // Restore selection
                 }
                subjectSelect.appendChild(option);
            }
        });

         // Trigger filter to update the displayed timetable based on restored selection
         filterTimetable();
    }
    
    // Filter timetable based on selected options
    function filterTimetable() {
        const classFilter = document.getElementById('classSelect').value;
        const subjectFilter = document.getElementById('subjectSelect').value;
        
        console.log('Filtering by class:', classFilter, 'and subject:', subjectFilter);
        
        // Hide/show rows based on filters
        const rows = document.querySelectorAll('.timetable tbody tr');
        
        rows.forEach(row => {
            if (row.querySelector('.time-cell')?.textContent.includes('Lunch Break')) {
                // Always show lunch break row
                return;
            }
            
            const classBlocks = row.querySelectorAll('.class-block');
            let shouldShow = false;
            
            classBlocks.forEach(block => {
                const className = block.querySelector('.class-name')?.textContent || '';
                const subjectName = block.querySelector('.class-subject')?.textContent || '';
                
                // Fix: Check for empty string instead of 'all' for "All Classes/Subjects"
                const matchesClass = classFilter === '' || className.toLowerCase().includes(classFilter.toLowerCase());
                const matchesSubject = subjectFilter === '' || subjectName.toLowerCase().includes(subjectFilter.toLowerCase());
                
                if (matchesClass && matchesSubject) {
                    shouldShow = true;
                    
                    // If showing the row but filters are active, highlight matching blocks
                    if (classFilter !== '' || subjectFilter !== '') {
                        block.classList.add('highlighted');
                    } else {
                        block.classList.remove('highlighted');
                    }
                } else {
                    block.classList.remove('highlighted');
                }
            });
            
            // Show row if any class blocks match, or if no classes (for consistency)
            if (shouldShow || row.querySelector('.no-classes')) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Update upcoming class notification
    function updateUpcomingClass(timetables) {
        // For now, we'll keep the static notification
        // In a real implementation, we would:
        // 1. Get the current day and time
        // 2. Find the next class in the timetable
        // 3. Calculate time remaining
        // 4. Update the notification
        console.log('Updating upcoming class notification');
        
        // Access notification elements
        const upcomingClassName = document.querySelector('.upcoming-class-name');
        const upcomingClassDetails = document.querySelector('.upcoming-class-details');
        
        // This is where we would dynamically update with real data
        // For now, just show we're processing it by adding "(Checking)" to the text
        if (upcomingClassName && upcomingClassDetails) {
            upcomingClassName.textContent += " (Checking)";
        }
    }
    
    // Show timetable error
    function showTimetableError(message) {
        const timetableBody = document.querySelector('.timetable tbody');
        // Use colspan based on the number of columns (Time + Days)
        const colCount = 1 + 6; // Time + Monday to Saturday
        timetableBody.innerHTML = `<tr><td colspan="${colCount}" class="error-message">${message}</td></tr>`;
    }
    
    // Simulate countdown for upcoming class
    function simulateCountdown() {
        let minutes = 24;
        let seconds = 18;
        
        const countdownValue = document.querySelector('.countdown-value');
        
        const updateCountdown = () => {
            if (seconds === 0) {
                if (minutes === 0) {
                    // Timer has reached zero
                    countdownValue.textContent = "Time's up!";
                    return;
                }
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }
            
            countdownValue.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        };
        
        // Update countdown every second
        // setInterval(updateCountdown, 1000); // Commented out for now
    }

    // Export timetable to calendar format
    function exportToCalendar() {
        // In a real implementation, we would:
        // 1. Generate iCal/vCalendar format data from the timetable
        // 2. Create a downloadable file
        
        alert('This feature will export your timetable to a calendar format (iCal) that can be imported into Google Calendar, Outlook, or other calendar applications.');
        
        // For demonstration, show a loading message
        const btn = document.querySelector('.btn-primary');
        const originalText = btn.textContent;
        btn.textContent = 'Generating...';
        
        // Simulate processing
        setTimeout(() => {
            // Here we would actually generate and download the calendar file
            // For now, just reset the button text and show a message
            btn.textContent = originalText;
            alert('Calendar export would be downloaded now. This is a placeholder for the actual export functionality.');
            
            // In a real implementation:
            // 1. Generate iCal file content
            // const icalContent = generateICalContent(timetableData);
            // 2. Create and trigger download
            // downloadFile('teacher_schedule.ics', icalContent, 'text/calendar');
        }, 1500);
    }
    
    // Helper function to download file (would be used by exportToCalendar)
    function downloadFile(filename, content, contentType) {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }, 0);
    }

    // Render aggregated timetable for all classes
    function renderAggregatedTimetable() {
        console.log('Rendering aggregated timetable');

        const allPeriods = [];

        // Collect periods from all timetables and add class/section info
        allTimetables.forEach(timetable => {
            if (timetable.periods && timetable.periods.length > 0) {
                timetable.periods.forEach(period => {
                    // Augment period object with class and section info
                    allPeriods.push({
                        ...period,
                        class_name: timetable.class_name,
                        section_name: timetable.section_name
                    });
                });
            }
        });

        // Update the card title
        document.querySelector('.card-title').textContent = 'Weekly Timetable for All Classes';

        // Build the timetable with the aggregated periods
        if (allPeriods.length > 0) {
            // Pass null or undefined for className and sectionName as they are now in the period objects
            buildTimetableFromPeriods(allPeriods, null, null);
            // Apply any active filters after building the timetable
            filterTimetable();
        } else {
            showTimetableError('No timetable data available for any class.');
        }
    }

    // Class Notes Modal Functions
    function openClassNotesModal() {
        console.log('Opening class notes modal');
        const modal = document.getElementById('classNotesModal');
        if (!modal) {
            console.error('Modal element not found!');
            return;
        }
        modal.style.display = 'block';
        populateClassNotesForm();
    }

    function closeClassNotesModal() {
        console.log('Closing class notes modal');
        const modal = document.getElementById('classNotesModal');
        if (!modal) {
            console.error('Modal element not found!');
            return;
        }
        modal.style.display = 'none';
    }

    function populateClassNotesForm() {
        console.log('Populating class notes form');
        const classSelect = document.getElementById('noteClass');
        const subjectSelect = document.getElementById('noteSubject');
        
        if (!classSelect || !subjectSelect) {
            console.error('Form elements not found!');
            return;
        }
        // Clear existing options
        classSelect.innerHTML = '<option value="">Select Class</option>';
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        
        // Populate class options from allTimetables
        const uniqueClasses = new Map();
        allTimetables.forEach(timetable => {
            const classKey = `${timetable.class_name}${timetable.section_name}`;
            uniqueClasses.set(classKey, {
                class_id: timetable.class_id,
                section_id: timetable.section_id,
                display: `Class ${timetable.class_name}${timetable.section_name}`
            });
        });
        
        uniqueClasses.forEach((classInfo, key) => {
            const option = document.createElement('option');
            option.value = JSON.stringify({ class_id: classInfo.class_id, section_id: classInfo.section_id });
            option.textContent = classInfo.display;
            classSelect.appendChild(option);
        });
        
        // Set today's date as default
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('noteDate').value = today;
    }

    // Function to populate filter dropdowns in View Notes tab
    function populateViewNotesFilters() {
        console.log('Populating view notes filters');
        const filterClass = document.getElementById('filterClass');
        const filterSubject = document.getElementById('filterSubject');
        const filterDate = document.getElementById('filterDate');
        
        if (!filterClass || !filterSubject || !filterDate) {
            console.error('Filter elements not found');
            return;
        }

        // Clear existing options
        filterClass.innerHTML = '<option value="">All Classes</option>';
        filterSubject.innerHTML = '<option value="">All Subjects</option>';

        // Populate class options from allTimetables
        const uniqueClasses = new Map();
        allTimetables.forEach(timetable => {
            const classKey = `${timetable.class_name}${timetable.section_name}`;
            uniqueClasses.set(classKey, {
                class_id: timetable.class_id,
                section_id: timetable.section_id,
                display: `Class ${timetable.class_name}${timetable.section_name}`
            });
        });

        uniqueClasses.forEach((classInfo, key) => {
            const option = document.createElement('option');
            option.value = JSON.stringify({ class_id: classInfo.class_id, section_id: classInfo.section_id });
            option.textContent = classInfo.display;
            filterClass.appendChild(option);
        });

        // Populate subject options from all timetables
        const uniqueSubjects = new Map();
        allTimetables.forEach(timetable => {
            if (timetable.periods) {
                timetable.periods.forEach(period => {
                    if (period.subject_id && period.subject_name) {
                        uniqueSubjects.set(period.subject_id, period.subject_name);
                    }
                });
            }
        });

        uniqueSubjects.forEach((subjectName, subjectId) => {
            const option = document.createElement('option');
            option.value = subjectId;
            option.textContent = subjectName;
            filterSubject.appendChild(option);
        });

        // Set default date to today
        filterDate.value = new Date().toISOString().split('T')[0];

        // Add event listeners for filter changes
        filterClass.addEventListener('change', applyNotesFilters);
        filterSubject.addEventListener('change', applyNotesFilters);
        filterDate.addEventListener('change', applyNotesFilters);
    }

    // Function to apply filters to notes list
    async function applyNotesFilters() {
        console.log('Applying notes filters');
        const filterClass = document.getElementById('filterClass');
        const filterSubject = document.getElementById('filterSubject');
        const filterDate = document.getElementById('filterDate');

        let params = {};

        // Add class and section filters if selected
        if (filterClass.value) {
            try {
                const classInfo = JSON.parse(filterClass.value);
                params.class_id = classInfo.class_id;
                params.section_id = classInfo.section_id;
            } catch (e) {
                console.error('Error parsing class filter value:', e);
            }
        }

        // Add subject filter if selected
        if (filterSubject.value) {
            params.subject_id = filterSubject.value;
        }

        // Add date filter if selected
        if (filterDate.value) {
            params.note_date = filterDate.value;
        }

        try {
            const response = await axios.get('../../backend/api/class-notes.php', { params });
            const notesList = document.querySelector('.notes-list');

            if (response.data.success && response.data.data) {
                notesList.innerHTML = response.data.data.map(note => `
                    <div class="note-item">
                        <div class="note-header">
                            <span class="note-title">Class ${note.class_name}${note.section_name} - ${note.subject_name}</span>
                            <span class="note-date">${new Date(note.note_date).toLocaleDateString()}</span>
                        </div>
                        <div class="note-content">${note.note_content}</div>
                        <div class="note-actions">
                            <button onclick="editNote(${note.id})" class="btn btn-secondary btn-sm">Edit</button>
                            <button onclick="deleteNote(${note.id})" class="btn btn-danger btn-sm">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else {
                notesList.innerHTML = '<div class="no-notes">No notes found matching the selected filters.</div>';
            }
        } catch (error) {
            console.error('Error loading filtered notes:', error);
            document.querySelector('.notes-list').innerHTML = 
                '<div class="error">Failed to load notes. Please try again.</div>';
        }
    }

    // Load existing notes with filters
    async function loadNotes() {
        console.log('Loading notes with filters');
        const notesList = document.querySelector('.notes-list');
        notesList.innerHTML = '<div class="loading">Loading notes...</div>';

        // Populate filters first
        populateViewNotesFilters();
        
        // Then apply current filter values
        await applyNotesFilters();
    }

    // Add styles for note actions
    const styleSheet = document.createElement("style");
    styleSheet.textContent = `
        .note-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .no-notes {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .error {
            text-align: center;
            padding: 20px;
            color: #dc3545;
            font-weight: 500;
        }
    `;
    document.head.appendChild(styleSheet);

    // Edit note function
    async function editNote(noteId) {
        try {
            const response = await axios.get(`../../backend/api/class-notes.php?id=${noteId}`);
            if (response.data.success && response.data.data) {
                const note = response.data.data;
                
                // Switch to Add Note tab
                document.querySelector('[data-tab="add"]').click();
                
                // Populate form with note data
                document.getElementById('noteClass').value = JSON.stringify({
                    class_id: note.class_id,
                    section_id: note.section_id
                });
                document.getElementById('noteSubject').value = note.subject_id;
                document.getElementById('noteDate').value = note.note_date;
                document.getElementById('noteContent').value = note.note_content;
                
                // Update form submission to handle edit
                const form = document.getElementById('classNoteForm');
                form.dataset.editId = noteId;
            }
        } catch (error) {
            console.error('Error loading note for edit:', error);
            alert('Failed to load note for editing.');
        }
    }

    // Delete note function
    async function deleteNote(noteId) {
        if (confirm('Are you sure you want to delete this note?')) {
            try {
                const response = await axios.delete(`../../backend/api/class-notes.php/${noteId}`);
                if (response.data.success) {
                    await loadNotes(); // Reload notes list
                } else {
                    alert('Failed to delete note.');
                }
            } catch (error) {
                console.error('Error deleting note:', error);
                alert('Failed to delete note: ' + (error.response?.data?.error || error.message));
            }
        }
    }

    // Student List Functions
    function openStudentListModal() {
        console.log('Opening student list modal');
        const modal = document.getElementById('studentListModal');
        if (!modal) {
            console.error('Student list modal element not found!');
            return;
        }
        modal.style.display = 'block';
        
        // Populate class dropdown
        populateStudentListClassDropdown();
        
        // Add event listeners for modal close button
        modal.querySelector('.close-modal').addEventListener('click', closeStudentListModal);
        
        // Add event listener for Load Students button
        document.getElementById('loadStudentsBtn').addEventListener('click', loadStudents);
        
        // Add event listener for search input
        document.getElementById('studentSearch').addEventListener('input', filterStudents);
    }

    function closeStudentListModal() {
        console.log('Closing student list modal');
        const modal = document.getElementById('studentListModal');
        if (!modal) {
            console.error('Student list modal element not found!');
            return;
        }
        modal.style.display = 'none';
    }

    function populateStudentListClassDropdown() {
        console.log('Populating student list class dropdown');
        const classSelect = document.getElementById('studentListClass');
        if (!classSelect) {
            console.error('Student list class select element not found!');
            return;
        }
        
        // Clear existing options except the first one
        classSelect.innerHTML = '<option value="">Select a Class</option>';
        
        // Populate from allTimetables
        const uniqueClasses = new Map();
        allTimetables.forEach(timetable => {
            const classKey = `${timetable.class_name}${timetable.section_name}`;
            uniqueClasses.set(classKey, {
                class_id: timetable.class_id,
                section_id: timetable.section_id,
                timetable_id: timetable.id,
                display: `Class ${timetable.class_name}${timetable.section_name}`
            });
        });
        
        // Add options to dropdown
        uniqueClasses.forEach((classInfo, key) => {
            const option = document.createElement('option');
            option.value = JSON.stringify({
                class_id: classInfo.class_id,
                section_id: classInfo.section_id,
                timetable_id: classInfo.timetable_id
            });
            option.textContent = classInfo.display;
            classSelect.appendChild(option);
        });
    }

    async function loadStudents() {
        console.log('Loading students');
        const classSelect = document.getElementById('studentListClass');
        const loadingMessage = document.getElementById('studentListLoading');
        const studentTable = document.getElementById('studentTable');
        
        if (!classSelect.value) {
            alert('Please select a class first');
            return;
        }
        
        try {
            // Show loading message
            loadingMessage.textContent = 'Loading students...';
            loadingMessage.style.display = 'block';
            studentTable.style.display = 'none';
            
            // Get class info from selected option
            const classInfo = JSON.parse(classSelect.value);
            console.log('Selected class info:', classInfo);
            
            // Fetch students from API
            const response = await axios.get('../../backend/api/students', {
                params: {
                    class_id: classInfo.class_id,
                    section_id: classInfo.section_id
                }
            });
            
            console.log('Student API response:', response.data);
            
            if (response.data.success && response.data.data) {
                displayStudents(response.data.data);
            } else {
                loadingMessage.textContent = 'No students found for this class.';
                studentTable.style.display = 'none';
            }
        } catch (error) {
            console.error('Error loading students:', error);
            loadingMessage.textContent = 'Failed to load students. ' + (error.response?.data?.error || error.message);
            studentTable.style.display = 'none';
        }
    }

    function displayStudents(students) {
        console.log('Displaying students:', students);
        const studentTable = document.getElementById('studentTable');
        const loadingMessage = document.getElementById('studentListLoading');
        const tableBody = studentTable.querySelector('tbody');
        
        if (students.length === 0) {
            loadingMessage.textContent = 'No students found for this class.';
            loadingMessage.style.display = 'block';
            studentTable.style.display = 'none';
            return;
        }
        
        // Sort students by roll number
        students.sort((a, b) => {
            // Convert roll numbers to numbers for proper numeric sorting
            const rollA = parseInt(a.roll_number) || 0;
            const rollB = parseInt(b.roll_number) || 0;
            return rollA - rollB;
        });
        
        // Clear previous students
        tableBody.innerHTML = '';
        
        // Add student rows
        students.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.roll_number || 'N/A'}</td>
                <td>${student.admission_number || 'N/A'}</td>
                <td>${student.full_name || 'N/A'}</td>
                <td>${student.gender_code || 'N/A'}</td>
                <td>${student.mobile || 'N/A'}</td>
                <td>
                    <button class="student-action-btn" onclick="viewStudentDetails(${student.user_id})">View</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        // Show table, hide loading message
        loadingMessage.style.display = 'none';
        studentTable.style.display = 'table';
        
        // Initialize search field
        document.getElementById('studentSearch').value = '';
    }

    function filterStudents() {
        const searchText = document.getElementById('studentSearch').value.toLowerCase();
        const tableBody = document.getElementById('studentTable').querySelector('tbody');
        const rows = tableBody.querySelectorAll('tr');
        
        let matchCount = 0;
        
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchText)) {
                row.style.display = '';
                matchCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show message if no results
        const loadingMessage = document.getElementById('studentListLoading');
        if (matchCount === 0 && searchText) {
            loadingMessage.textContent = 'No students match your search criteria.';
            loadingMessage.style.display = 'block';
        } else {
            loadingMessage.style.display = 'none';
        }
    }

    // Student Details Functions
    function openStudentDetailsModal() {
        console.log('Opening student details modal');
        const modal = document.getElementById('studentDetailsModal');
        if (!modal) {
            console.error('Student details modal element not found!');
            return;
        }
        modal.style.display = 'block';
         // Add event listeners for modal close button
        modal.querySelector('.close-modal').addEventListener('click', closeStudentDetailsModal);
    }

    function closeStudentDetailsModal() {
         console.log('Closing student details modal');
        const modal = document.getElementById('studentDetailsModal');
        if (!modal) {
            console.error('Student details modal element not found!');
            return;
        }
        modal.style.display = 'none';
    }

    async function fetchAndDisplayStudentDetails(studentId) {
        console.log('Fetching and displaying details for student ID:', studentId);
        const detailsContent = document.getElementById('studentDetailsContent');
        if (!detailsContent) {
            console.error('Student details content element not found!');
            return;
        }

        // Show loading message
        detailsContent.innerHTML = '<div class="loading-message">Loading student details...</div>';

        try {
            const response = await axios.get(`../../backend/api/students/${studentId}`);
            console.log('Student details API response:', response.data);

            if (response.data.success && response.data.data) {
                renderStudentDetails(response.data.data);
            } else {
                detailsContent.innerHTML = '<div class="error-message">Failed to load student details.</div>';
                console.error('API returned error or no data for student details:', response.data);
            }
        } catch (error) {
            detailsContent.innerHTML = '<div class="error-message">Error fetching student details.</div>';
            console.error('Error fetching student details:', error);
        }
    }

    function renderStudentDetails(student) {
        console.log('Rendering student details:', student);
        const detailsContent = document.getElementById('studentDetailsContent');
         if (!detailsContent) {
            console.error('Student details content element not found!');
            return;
        }

        let html = `
            <h3>${student.full_name || 'N/A'}</h3>
            <p><strong>Student ID:</strong> ${student.admission_number || 'N/A'}</p>
            <p><strong>Roll Number:</strong> ${student.roll_number || 'N/A'}</p>
            <p><strong>Class:</strong> ${student.class_name || 'N/A'}${student.section_name || ''}</p>
            <p><strong>Gender:</strong> ${student.gender_code || 'N/A'}</p>
            <p><strong>Date of Birth:</strong> ${student.dob || 'N/A'}</p>
            <p><strong>Mobile:</strong> ${student.mobile || 'N/A'}</p>
            <p><strong>Email:</strong> ${student.contact_email || 'N/A'}</p>
            <p><strong>Address:</strong> ${student.address || 'N/A'}</p>
            <p><strong>Admission Date:</strong> ${student.admission_date || 'N/A'}</p>
        `;

        detailsContent.innerHTML = html;
    }

    // Define global function to be called from the table row button
    window.viewStudentDetails = function(studentId) {
        openStudentDetailsModal();
        fetchAndDisplayStudentDetails(studentId);
    };

    // Implement the Message feature (placeholder for now)
    window.sendMessage = function(studentId) {
        console.log('Send message to student ID:', studentId);
        alert('Messaging functionality is not yet implemented.');
        // In a real application, this would open a messaging interface
    };

    // Function to calculate the next class
    function calculateNextClass() {
        if (!allTimetables || allTimetables.length === 0) {
            console.log('No timetable data available');
            return null;
        }

        const now = new Date();
        const currentDay = now.getDay(); // 0 = Sunday, 1 = Monday, etc.
        const currentTime = now.getHours() * 60 + now.getMinutes(); // Current time in minutes

        // Map JavaScript day numbers to your timetable day names
        const dayMap = {
            1: 'monday',
            2: 'tuesday',
            3: 'wednesday',
            4: 'thursday',
            5: 'friday',
            6: 'saturday'
        };

        // If it's Sunday (0) or Saturday (6), look for Monday's first class
        const searchDay = currentDay === 0 ? 1 : (currentDay === 6 ? 1 : currentDay);
        const isNextDay = currentDay === 0 || currentDay === 6 || 
                         (currentDay >= 1 && currentDay <= 5 && currentTime >= 15 * 60 + 20); // After 3:20 PM

        let nextClass = null;
        let earliestTime = 24 * 60; // Initialize to end of day
        let daysToAdd = 0;

        // Function to parse time string to minutes
        function timeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        // Search through all timetables
        allTimetables.forEach(timetable => {
            if (timetable.periods) {
                timetable.periods.forEach(period => {
                    const periodDay = period.day_of_week.toLowerCase();
                    const startTime = timeToMinutes(period.start_time.substring(0, 5)); // Convert HH:MM:SS to minutes

                    if (isNextDay) {
                        // Looking for next day's classes
                        if (periodDay === dayMap[searchDay === 5 ? 1 : searchDay + 1]) {
                            if (startTime < earliestTime) {
                                earliestTime = startTime;
                                nextClass = {
                                    ...period, // Keep period details
                                    class_name: timetable.class_name, // Add class name from timetable
                                    section_name: timetable.section_name // Add section name from timetable
                                };
                                daysToAdd = searchDay === currentDay ? 1 : 
                                           currentDay === 0 ? 1 : 
                                           currentDay === 6 ? 2 : 1;
                            }
                        }
                    } else if (periodDay === dayMap[searchDay]) {
                        // Looking for remaining classes today
                        if (startTime > currentTime && startTime < earliestTime) {
                            earliestTime = startTime;
                            nextClass = {
                                ...period, // Keep period details
                                class_name: timetable.class_name, // Add class name from timetable
                                section_name: timetable.section_name // Add section name from timetable
                            };
                            daysToAdd = 0;
                        }
                    }
                });
            }
        });

        if (nextClass) {
            const nextDate = new Date();
            nextDate.setDate(nextDate.getDate() + daysToAdd);
            return {
                period: nextClass,
                startTime: earliestTime,
                daysToAdd: daysToAdd,
                nextDate: nextDate
            };
        }

        return null;
    }

    function updateUpcomingClassDisplay() {
        const nextClassInfo = calculateNextClass();
        const upcomingClassAlert = document.getElementById('upcomingClassAlert');
        const upcomingClassName = document.getElementById('upcomingClassName');
        const upcomingClassDetails = document.getElementById('upcomingClassDetails');
        const countdownValue = document.getElementById('countdownValue');

        if (!nextClassInfo) {
            upcomingClassAlert.style.display = 'none';
            return;
        }

        const { period, startTime, nextDate } = nextClassInfo;
        
        // Format the display information
        const classInfo = `Class ${period.class_name}${period.section_name} - ${period.subject_name}`;
        const timeStr = period.start_time.substring(0, 5); // HH:MM format
        const endTimeStr = period.end_time.substring(0, 5); // HH:MM format
        // Remove Room information
        const details = `${timeStr} - ${endTimeStr}`;

        // Update the display
        upcomingClassName.textContent = classInfo;
        upcomingClassDetails.textContent = details;
        upcomingClassAlert.style.display = 'flex';

        // Start countdown
        updateCountdown(nextDate, timeStr);
    }

    function updateCountdown(nextDate, startTime) {
        const countdownValue = document.getElementById('countdownValue');
        const [hours, minutes] = startTime.split(':').map(Number);
        nextDate.setHours(hours, minutes, 0, 0);

        // Clear any existing interval
        if (countdownValue.dataset.timerInterval) {
            clearInterval(parseInt(countdownValue.dataset.timerInterval));
        }

        function updateTimer() {
            const now = new Date();
            const diff = nextDate - now;

            if (diff <= 0) {
                // Time's up - refresh next class calculation
                updateUpcomingClassDisplay();
                return;
            }

            // Calculate hours and minutes
            const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
            const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            // Update display
            if (hoursLeft > 0) {
                countdownValue.textContent = `${hoursLeft}h ${minutesLeft}m`;
            } else {
                countdownValue.textContent = `${minutesLeft}m`;
            }
        }

        // Update immediately and then every minute
        updateTimer();
        const timerInterval = setInterval(updateTimer, 60000);
        countdownValue.dataset.timerInterval = timerInterval;
    }

    // Update the display every minute
    setInterval(updateUpcomingClassDisplay, 60000);

</script>
</body>
</html>
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/timetable.css">
    <style>
        /* Additional styles for dynamic functionality */
        .loading-message {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .error-message {
            text-align: center;
            padding: 20px;
            color: #ef4444;
            font-weight: 500;
        }
        
        .highlighted {
            position: relative;
            box-shadow: 0 0 0 2px #4f46e5;
            animation: pulse 2s infinite;
        }
        
        /* Class-specific color schemes */
        .class-block {
            border-radius: 6px;
            padding: 8px;
            margin: 4px;
            transition: all 0.3s ease;
        }

        .class-block:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Color schemes for different classes */
        .class-IIIA {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }

        .class-IIA {
            background-color: #f3e5f5;
            border-left: 4px solid #9c27b0;
        }

        .class-IA {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
        }

        .class-IVA {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }

        .class-VA {
            background-color: #fbe9e7;
            border-left: 4px solid #ff5722;
        }

        /* Existing styles */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4); }
            50% { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.8); }
            100% { box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4); }
        }
        
        /* Print styles */
        @media print {
            .upcoming-class, .quick-actions, .action-bar, .card-header, .card-footer,
            .hamburger-btn, .timetable-filters, .sidebar-overlay, .sidebar {
                display: none !important;
            }
            
            .dashboard-container {
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .card {
                box-shadow: none !important;
                border: none !important;
            }
            
            .card-body {
                padding: 0 !important;
            }
            
            .timetable {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            
            .timetable th, .timetable td {
                border: 1px solid #ddd !important;
                padding: 8px !important;
            }
            
            @page {
                size: landscape;
                margin: 1cm;
            }
        }

        /* Student List Styles */
        .student-list-container {
            max-height: 400px; /* Fixed height for the container */
            overflow-y: auto; /* Enable vertical scrolling */
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .student-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .student-table thead {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
            z-index: 1;
        }
        
        .student-table th {
            background-color: #f9fafb;
            font-weight: 500;
            color: #374151;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .student-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            background-color: #ffffff;
        }
        
        .student-table tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        .student-table tbody tr:hover td {
            background-color: #f3f4f6;
        }

        /* Adjust modal size */
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 30px auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            max-height: 80vh; /* Limit modal height to 80% of viewport height */
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto; /* Enable scrolling for modal body if needed */
            flex: 1;
        }

        /* Loading and error message styles */
        .loading-message, .error-message {
            padding: 20px;
            text-align: center;
            background-color: #ffffff;
        }

        /* Scrollbar styling for better appearance */
        .student-list-container::-webkit-scrollbar {
            width: 8px;
        }

        .student-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .student-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .student-list-container::-webkit-scrollbar-thumb:hover {
            background: #666;
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
        <h1 class="header-title">Timetable</h1>
        <p class="header-subtitle">View and manage your teaching schedule</p>
    </header>

    <main class="dashboard-content">
        <!-- Upcoming Class Alert -->
        <div class="upcoming-class" id="upcomingClassAlert" style="display: none;">
            <div class="upcoming-class-info">
                <div class="upcoming-class-time">Next class in</div>
                <div class="upcoming-class-name" id="upcomingClassName">Loading...</div>
                <div class="upcoming-class-details" id="upcomingClassDetails">Loading...</div>
            </div>
            <div class="countdown">
                <div class="countdown-value" id="countdownValue">--:--</div>
                <div class="countdown-label">minutes remaining</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-card" data-action="class-notes">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <div class="action-title">Class Notes</div>
                <div class="action-description">Add notes for upcoming classes</div>
            </div>
            <div class="action-card" data-action="student-list">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <div class="action-title">Student List</div>
                <div class="action-description">View students for selected class</div>
            </div>
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                <div class="action-title">Substitute Request</div>
                <div class="action-description">Request a substitute teacher</div>
            </div>
            <div class="action-card">
                <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div class="action-title">Schedule Change</div>
                <div class="action-description">Request a schedule change</div>
            </div>
        </div>

        <!-- Timetable Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Weekly Timetable</h2>
                <div class="view-options">
                    <button class="view-option active">Week</button>
                    <button class="view-option">Day</button>
                    <button class="view-option">List</button>
                </div>
            </div>
            <div class="card-body">
                <div class="timetable-filters">
                    <div class="filter-item">
                        <label for="weekSelect" class="form-label">Select Week</label>
                        <select id="weekSelect" class="form-select">
                            <option value="current">Current Week (Mar 10 - Mar 16)</option>
                            <option value="next">Next Week (Mar 17 - Mar 23)</option>
                            <option value="after">Week After (Mar 24 - Mar 30)</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="classSelect" class="form-label">Filter by Class</label>
                        <select id="classSelect" class="form-select">
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="subjectSelect" class="form-label">Filter by Subject</label>
                        <select id="subjectSelect" class="form-select">
                            <option value="all">All Subjects</option>
                            <option value="science">Science</option>
                            <option value="math">Mathematics</option>
                            <option value="english">English</option>
                            <option value="social">Social Studies</option>
                            <option value="art">Art &amp; Craft</option>
                        </select>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th class="time-header">Time</th>
                                <th class="day-header">Monday</th>
                                <th class="day-header">Tuesday</th>
                                <th class="day-header">Wednesday</th>
                                <th class="day-header">Thursday</th>
                                <th class="day-header">Friday</th>
                                <th class="day-header">Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Table body will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="teacher-note">
                    <strong>Note:</strong> Class 9B - Science on Thursday (10:30 - 11:20) will cover chapter 8 (Electromagnetic Spectrum). Please refer to the curriculum guide for the practical demonstration requirements.
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-secondary" style="margin-right: 0.75rem;">Print Timetable</button>
                <button class="btn btn-primary">Export to Calendar</button>
            </div>
        </div>
    </main>
</div>

<!-- Class Notes Modal -->
<div id="classNotesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Class Notes</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="tabs">
                <button class="tab-btn active" data-tab="add">Add Note</button>
                <button class="tab-btn" data-tab="view">View Notes</button>
            </div>

            <!-- Add Note Form -->
            <div class="tab-content active" id="addNoteTab">
                <form id="classNoteForm">
                    <div class="form-group">
                        <label for="noteClass">Class</label>
                        <select id="noteClass" name="class_id" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="noteSubject">Subject</label>
                        <select id="noteSubject" name="subject_id" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="noteDate">Date</label>
                        <input type="date" id="noteDate" name="note_date" required>
                    </div>

                    <div class="form-group">
                        <label for="noteContent">Notes</label>
                        <textarea id="noteContent" name="note_content" rows="6" required 
                                placeholder="Enter your notes here..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeClassNotesModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>

            <!-- View Notes List -->
            <div class="tab-content" id="viewNotesTab">
                <div class="notes-filters">
                    <select id="filterClass">
                        <option value="">All Classes</option>
                    </select>
                    <select id="filterSubject">
                        <option value="">All Subjects</option>
                    </select>
                    <input type="date" id="filterDate">
                </div>
                <div class="notes-list">
                    <!-- Notes will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student List Modal -->
<div id="studentListModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Student List</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="class-selection">
                <label for="studentListClass">Select Class:</label>
                <select id="studentListClass" class="form-select">
                    <option value="">Select a Class</option>
                </select>
                <button id="loadStudentsBtn" class="btn btn-primary">Load Students</button>
            </div>
            
            <div class="student-search">
                <input type="text" id="studentSearch" placeholder="Search by name, ID or roll number" class="form-control">
            </div>
            
            <div class="student-list-container">
                <div id="studentListLoading" class="loading-message">Select a class to view students</div>
                <table id="studentTable" class="student-table">
                    <thead>
                        <tr>
                            <th>Roll No.</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Student data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div id="studentDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Student Details</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="studentDetailsContent">
                <!-- Student details will be loaded here -->
                <div class="loading-message">Loading student details...</div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .modal-content {
        position: relative;
        background-color: #fff;
        margin: 50px auto;
        padding: 0;
        width: 90%;
        max-width: 700px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        margin: 0;
        color: #1f2937;
        font-size: 1.5rem;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
    }

    .modal-body {
        padding: 20px;
    }

    /* Tabs Styles */
    .tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 20px;
    }

    .tab-btn {
        padding: 10px 20px;
        border: none;
        background: none;
        cursor: pointer;
        color: #6b7280;
        font-weight: 500;
    }

    .tab-btn.active {
        color: #4f46e5;
        border-bottom: 2px solid #4f46e5;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #374151;
        font-weight: 500;
    }

    .form-group select,
    .form-group input[type="date"],
    .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-group textarea {
        resize: vertical;
    }

    /* Notes List Styles */
    .notes-filters {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .notes-filters select,
    .notes-filters input {
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }

    .notes-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .note-item {
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .note-item:hover {
        background-color: #f9fafb;
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .note-title {
        font-weight: 500;
        color: #1f2937;
    }

    .note-date {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .note-content {
        color: #4b5563;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #4f46e5;
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background-color: #4338ca;
    }

    .btn-secondary {
        background-color: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background-color: #f9fafb;
    }

    /* Student List Styles */
    .class-selection {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .class-selection select {
        flex: 1;
    }
    
    .student-search {
        margin-bottom: 20px;
    }
    
    .student-search input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }
    
    .student-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .student-table thead {
        position: sticky;
        top: 0;
        background-color: #f9fafb;
        z-index: 1;
    }
    
    .student-table th {
        background-color: #f9fafb;
        font-weight: 500;
        color: #374151;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .student-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        background-color: #ffffff;
    }
    
    .student-table tbody tr:hover {
        background-color: #f3f4f6;
    }
    
    .student-table tbody tr:hover td {
        background-color: #f3f4f6;
    }
    
    .student-action-btn {
        background: none;
        border: none;
        color: #4f46e5;
        cursor: pointer;
        margin-right: 8px;
    }
    
    .student-action-btn:hover {
        text-decoration: underline;
    }
    
    .student-details {
        background-color: #f9fafb;
        padding: 15px;
        border-radius: 6px;
        margin-top: 10px;
        display: none;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 8px;
    }
    
    .detail-label {
        font-weight: 500;
        width: 150px;
    }
    
    .no-results {
        text-align: center;
        padding: 20px;
        color: #6b7280;
        font-style: italic;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load teacher's timetable data
        loadTeacherTimetable();
        
        // Tab switching for view options
        const viewOptions = document.querySelectorAll('.view-option');
        
        viewOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                viewOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to clicked option
                this.classList.add('active');
                
                // Here you would normally switch the content based on the option
                // For this demo, we'll just show an alert
                if (this.textContent !== 'Week') {
                    alert('Switching to ' + this.textContent + ' view - This would show a different layout in a real application.');
                }
            });
        });
        
        // Filter handling
        const filters = document.querySelectorAll('.form-select');
        
        filters.forEach(filter => {
            filter.addEventListener('change', function() {
                // In a real app, this would filter the timetable data
                if (this.id === 'subjectSelect') { // Only subject filter affects the current view directly
                    filterTimetable();
                } else if (this.id === 'classSelect') {
                    // When the class filter changes, find and render the selected timetable
                    const selectedValue = this.value;
                    const selectedTimetableId = parseInt(selectedValue);
                    if (!isNaN(selectedTimetableId)) {
                        selectAndRenderTimetable(selectedTimetableId);
                    }
                }
            });
        });
        
        // Week select handling
        document.getElementById('weekSelect').addEventListener('change', function() {
            // This would load a different week's timetable in a real application
            // For now, just show the same data but acknowledge the change
            console.log('Week changed to:', this.value);
            // Re-load timetable with week filter
            loadTeacherTimetable();
        });
        
        // Countdown timer simulation for upcoming class
        simulateCountdown();
        
        // Function to toggle the sidebar (defined in the sidebar.php)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        };

        // Print timetable functionality
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            window.print();
        });
        
        // Export to calendar functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            exportToCalendar();
        });

        // Tab switching functionality
        document.querySelector('[data-action="class-notes"]').addEventListener('click', function() {
            openClassNotesModal();
        });

        // Student List action card click handler
        document.querySelector('[data-action="student-list"]').addEventListener('click', function() {
            openStudentListModal();
        });

        // Tab switching within the modal
        const tabButtons = document.querySelectorAll('.tab-btn');
        console.log('Found tab buttons:', tabButtons.length);
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                console.log('Tab clicked:', tabId);
        
                // Log all tab contents for debugging
                const allTabContents = document.querySelectorAll('.tab-content');
                console.log('Available tab contents:', Array.from(allTabContents).map(el => el.id));

                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Add active class to clicked tab and its content
                this.classList.add('active');
                // Match the correct tab content ID format
                const tabContentId = tabId === 'add' ? 'addNoteTab' : 'viewNotesTab';
                console.log('Looking for tab content with ID:', tabContentId);
                
                const tabContent = document.getElementById(tabContentId);
                if (tabContent) {
                    console.log('Found tab content, activating:', tabContentId);
                    tabContent.classList.add('active');
                    if (tabId === 'view') {
                        loadNotes();
                    }
                } else {
                    console.error(`Tab content not found for ${tabContentId}`);
                    // Log all elements with IDs for debugging
                    const allElements = document.querySelectorAll('[id]');
                    console.log('All elements with IDs:', Array.from(allElements).map(el => el.id));
                }
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const classNotesModal = document.getElementById('classNotesModal');
            const studentListModal = document.getElementById('studentListModal');
            
            if (event.target === classNotesModal) {
                closeClassNotesModal();
            }
            
            if (event.target === studentListModal) {
                closeStudentListModal();
            }
        });

        // Form submission handler
        document.getElementById('classNoteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const formData = {
                    class_info: JSON.parse(document.getElementById('noteClass').value),
                    subject_id: document.getElementById('noteSubject').value,
                    note_date: document.getElementById('noteDate').value,
                    note_content: document.getElementById('noteContent').value
                };
                
                const response = await axios.post('../../backend/api/class-notes.php', formData);
                
                if (response.data.success) {
                    alert('Note saved successfully!');
                    closeClassNotesModal();
                    // Optionally refresh the view notes tab if it's open
                    if (document.getElementById('viewNotesTab').classList.contains('active')) {
                        loadNotes();
                    }
                } else {
                    // Log the full response data for inspection
                    console.error('Server responded with error:', response.data);
                    alert('Failed to save note. Server responded: ' + (response.data.message || JSON.stringify(response.data)));
                }
            } catch (error) {
                console.error('Error saving note:', error);
                alert('An error occurred while saving the note.');
            }
        });

        // Initial update of upcoming class
        setTimeout(updateUpcomingClassDisplay, 1000);
    });

    // Load teacher's timetable from API
    let allTimetables = []; // Store all timetables fetched
    let currentTimetableId = null; // Store the ID of the currently displayed timetable

    function loadTeacherTimetable() {
        // Show loading state
        const timetableBody = document.querySelector('.timetable tbody');
        timetableBody.innerHTML = '<tr><td colspan="6" class="loading-message">Loading timetable data...</td></tr>';

        // Get filter values (optional, depending on if initial load should respect filters)
        // For now, fetching all timetables for the teacher regardless of initial filter selection

        // Use explicit API endpoint for listing teacher timetables (summaries)
        const listApiUrl = '../../backend/api/timetables';
        const params = {};

        // Note: Filters for class, section, subject are applied on the frontend after loading all data.
        // Academic year filter might need backend implementation if required for the initial list.

        console.log('Requesting timetable list with params:', params);
        
        axios.get(listApiUrl, { params: params })
            .then(response => {
                console.log('API list response:', response.data);
                
                if (response.data && response.data.data && response.data.data.length > 0) {
                    const timetableSummaries = response.data.data;

                    // Now fetch details for each timetable summary
                    const detailPromises = timetableSummaries.map(summary =>
                        axios.get(`../../backend/api/timetables/${summary.id}`)
                            .then(detailResponse => detailResponse.data.data) // Extract the data object
                    );
                    
                    // Wait for all detail promises to resolve
                    Promise.all(detailPromises)
                        .then(detailedTimetables => {
                            console.log('Fetched detailed timetables:', detailedTimetables);
                            allTimetables = detailedTimetables.filter(t => t !== null); // Store valid detailed timetables

                            if (allTimetables.length > 0) {
                                // Update class and subject filter options based on available data
                                updateFilterOptions(allTimetables);

                                // Determine which timetable to render initially
                                const initialClassFilterValue = document.getElementById('classSelect').value;

                                if (initialClassFilterValue === '') {
                                    // "All Classes" is selected (default) - show aggregated timetable
                                    renderAggregatedTimetable();
                                } else {
                                    // Find the timetable that matches the initial filter value
                                    const initialTimetable = allTimetables.find(t => String(t.id) === initialClassFilterValue);
                                    if (initialTimetable) {
                                        selectAndRenderTimetable(initialTimetable.id);
                                    } else {
                                        // Fallback to aggregated view if the filter value doesn't match
                                        renderAggregatedTimetable();
                                    }
                                }
                                
                                // Update upcoming class display after timetable is loaded
                                setTimeout(updateUpcomingClassDisplay, 1000);
                            } else {
                                showTimetableError('No detailed timetable data available after fetching details.');
                            }
                        })
                        .catch(detailError => {
                            console.error('Error fetching timetable details:', detailError);
                            showTimetableError('Failed to load detailed timetable data. Please try again later.');
                        });

                } else {
                    showTimetableError('No timetable summaries found for this teacher.');
                    console.warn('Timetable summary data missing or empty:', response.data);
                     // Clear the timetable body if no data is returned
                    timetableBody.innerHTML = '<tr><td colspan="6" class="no-data-message">No timetable data found for this teacher.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading teacher timetable list:', error);
                showTimetableError('Failed to load timetable list. Please try again later.');
            });
    }
    
    // Render teacher's timetable - This function will now fetch details for a specific timetable ID
    function selectAndRenderTimetable(timetableId) {
        console.log('Selecting and rendering timetable with ID:', timetableId);
        // Find the timetable with periods from the allTimetables array
        const timetableToRender = allTimetables.find(t => t.id === timetableId && t.periods);

        if (!timetableToRender) {
            showTimetableError(`Timetable with ID ${timetableId} or its periods not found in loaded data.`);
            return;
        }

        currentTimetableId = timetableId; // Update current timetable ID

            // Update the card title with actual class/section info
            document.querySelector('.card-title').textContent = 
            `Weekly Timetable for Class ${timetableToRender.class_name}${timetableToRender.section_name}`;
            
        // Build the timetable using the already fetched periods
        if (timetableToRender.periods && timetableToRender.periods.length > 0) {
            const periodsData = timetableToRender.periods;
            console.log(`Rendering ${periodsData.length} periods for timetable ID ${timetableId}`);
            buildTimetableFromPeriods(periodsData, timetableToRender.class_name, timetableToRender.section_name);
                        } else {
             const timetableBody = document.querySelector('.timetable tbody');
                            timetableBody.innerHTML = '<tr><td colspan="6" class="error-message">No classes scheduled for this timetable.</td></tr>';
                        }
    }

    // This function is now responsible for just building the HTML table
    function renderTeacherTimetable(timetables) {
       console.log('renderTeacherTimetable called - this function is deprecated in favor of selectAndRenderTimetable and renderAggregatedTimetable');
       // This function is no longer used directly for rendering the main table
       // It might be used later if we decide to display multiple tables.
       // For now, selectAndRenderTimetable handles the rendering logic.
    }
    
    // Build timetable from period data
    function buildTimetableFromPeriods(periods, className, sectionName) {
        // Define time slots
        const timeSlots = [
            { label: '8:00 - 8:45', startTime: '08:00:00', endTime: '08:45:00' },
            { label: '8:50 - 9:35', startTime: '08:50:00', endTime: '09:35:00' },
            { label: '9:40 - 10:25', startTime: '09:40:00', endTime: '10:25:00' },
            { label: '10:25 - 10:40', startTime: '10:25:00', endTime: '10:40:00', isBreak: true, breakLabel: 'Morning Break' },
            { label: '10:40 - 11:25', startTime: '10:40:00', endTime: '11:25:00' },
            { label: '11:30 - 12:15', startTime: '11:30:00', endTime: '12:15:00' },
            { label: '12:20 - 1:05', startTime: '12:20:00', endTime: '13:05:00' },
            { label: '1:05 - 1:45', startTime: '13:05:00', endTime: '13:45:00', isBreak: true, breakLabel: 'Lunch Break' },
            { label: '1:45 - 2:30', startTime: '13:45:00', endTime: '14:30:00' },
            { label: '2:35 - 3:20', startTime: '14:35:00', endTime: '15:20:00' }
        ];

        // Map day names to columns (1-based index)
        const dayMap = {
            'monday': 1,
            'tuesday': 2,
            'wednesday': 3,
            'thursday': 4,
            'friday': 5,
            'saturday': 6
        };

        // Subject to CSS class mapping for styling
        const subjectClassMap = {
            'ENGLISH': 'english-class',
            'MATHEMATICS': 'math-class',
            'SCIENCE': 'science-class',
            'SOCIAL STUDIES': 'social-class',
            'ART': 'art-class',
            'COMPUTER': 'computer-class'
        };

        // Organize periods by time slot and day
        const timetableGrid = {};
        
        // Initialize grid with empty cells
        timeSlots.forEach(slot => {
            if (!slot.isBreak) {
                timetableGrid[slot.startTime] = {
                    timeLabel: slot.label,
                    days: {
                        'monday': null,
                        'tuesday': null,
                        'wednesday': null,
                        'thursday': null,
                        'friday': null,
                        'saturday': null
                    }
                };
            } else {
                timetableGrid[slot.startTime] = {
                    timeLabel: slot.label,
                    isBreak: true,
                    breakLabel: slot.breakLabel
                };
            }
        });

        // Populate grid with periods
        periods.forEach(period => {
            const day = period.day_of_week.toLowerCase();
            const startTime = period.start_time;
            
            // Find matching time slot
            const matchingSlot = timeSlots.find(slot => slot.startTime === startTime);
            
            if (matchingSlot && !matchingSlot.isBreak && dayMap[day]) {
                timetableGrid[startTime].days[day] = period;
            }
        });

        // Build the HTML for the timetable
        const timetableBody = document.querySelector('.timetable tbody');
        timetableBody.innerHTML = '';

        // For each time slot
        Object.keys(timetableGrid).sort().forEach(startTime => {
            const slot = timetableGrid[startTime];
            const row = document.createElement('tr');
            
            // Add time cell
            const timeCell = document.createElement('td');
            timeCell.className = 'time-cell';
            timeCell.textContent = slot.timeLabel;
            row.appendChild(timeCell);
            
            if (slot.isBreak) {
                // Break row spans all days
                const breakCell = document.createElement('td');
                breakCell.colSpan = 7; // For all days (Monday to Saturday)
                breakCell.style.textAlign = 'center';
                breakCell.style.fontWeight = '500';
                breakCell.style.color = '#6b7280';
                breakCell.style.backgroundColor = '#f9fafb';
                breakCell.textContent = slot.breakLabel;
                row.appendChild(breakCell);
            } else {
                // Add cells for each day
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].forEach(day => {
                    const dayCell = document.createElement('td');
                    const period = slot.days[day];
                    
                    if (period) {
                        // We have a class for this day and time slot
                        const subjectClass = subjectClassMap[period.subject_name] || 'default-class';
                        // Get class name and section from the period if available (aggregated view) or use the passed arguments
                        const displayClassName = period.class_name || className;
                        const displaySectionName = period.section_name || sectionName;
                        // Create a class-specific color class
                        const classColorClass = `class-${displayClassName}${displaySectionName}`;
                        
                        dayCell.innerHTML = `
                            <div class="class-block ${subjectClass} ${classColorClass}">
                                <div class="class-name">Class ${displayClassName}${displaySectionName}</div>
                                <div class="class-details">
                                    <div class="class-subject">${period.subject_name}</div>
                                    <div class="class-room">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="class-room-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Period ${period.period_number}
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        // No class for this slot
                        dayCell.innerHTML = '<div class="no-classes">No classes</div>';
                    }
                    
                    row.appendChild(dayCell);
                });
            }
            
            timetableBody.appendChild(row);
        });
        
        // Apply any active filters
        filterTimetable();
    }
    
    // Update filter options based on available timetable data
    function updateFilterOptions(timetables) {
        console.log('updateFilterOptions received timetables:', timetables);
        // Extract unique classes, sections, and subjects with their IDs from timetable data
        const classes = new Map(); // Map: ClassNameSection -> { timetable_id, class_id, section_id }
        const subjects = new Map(); // Map: SubjectName -> subject_id
        
        timetables.forEach(timetable => {
            if (timetable.class_name && timetable.section_name && timetable.class_id && timetable.section_id && timetable.id) {
                 const classNameSection = `${timetable.class_name}${timetable.section_name}`;
                 // Store the timetable ID in the map
                 classes.set(classNameSection, { timetable_id: timetable.id, class_id: timetable.class_id, section_id: timetable.section_id });
            }

            if (timetable.periods && timetable.periods.length > 0) {
                timetable.periods.forEach(period => {
                    if (period.subject_name && period.subject_id) {
                        subjects.set(period.subject_name, period.subject_id);
                    }
                });
            }
        });
        
        // Update class filter options
        const classSelect = document.getElementById('classSelect');
        const currentClassValue = classSelect.value; // Preserve current selection

        classSelect.innerHTML = '';

        // ✅ Add "All Classes" option
        const allOption = document.createElement('option');
        allOption.value = ''; // or use 'all' if you handle it in filtering
        allOption.textContent = 'All Classes';
        if (currentClassValue === '') {
            allOption.selected = true;
        }
        classSelect.appendChild(allOption);

        // Add classes from the data, sorted by name
        const sortedClassNames = Array.from(classes.keys()).sort();
        console.log('Generated classes map for filter:', classes);
        let hasSelection = currentClassValue === '';

        sortedClassNames.forEach((classNameSection, index) => {
            const classInfo = classes.get(classNameSection);
            if (classInfo) {
                const option = document.createElement('option');
                option.value = classInfo.timetable_id;
                option.textContent = `Class ${classNameSection}`;
                
                if (String(classInfo.timetable_id) === currentClassValue) {
                    option.selected = true;
                    hasSelection = true;
                }

                classSelect.appendChild(option);
            }
        });
        
        // Update subject filter options
        const subjectSelect = document.getElementById('subjectSelect');
        const currentSubjectValue = subjectSelect.value; // Preserve current selection
        // Keep the "All Subjects" option
        const allSubjectsOption = subjectSelect.options[0];
        subjectSelect.innerHTML = '';
        subjectSelect.appendChild(allSubjectsOption);
        
        // Add subjects from the data, sorted by name
        const sortedSubjectNames = Array.from(subjects.keys()).sort();
        sortedSubjectNames.forEach(subjectName => {
            const subjectId = subjects.get(subjectName);
            if (subjectId) {
                const option = document.createElement('option');
                // Store subject_id in the value
                option.value = subjectId;
                option.textContent = subjectName;
                 if (String(subjectId) === currentSubjectValue) {
                     option.selected = true; // Restore selection
                 }
                subjectSelect.appendChild(option);
            }
        });

         // Trigger filter to update the displayed timetable based on restored selection
         filterTimetable();
    }
    
    // Filter timetable based on selected options
    function filterTimetable() {
        const classFilter = document.getElementById('classSelect').value;
        const subjectFilter = document.getElementById('subjectSelect').value;
        
        console.log('Filtering by class:', classFilter, 'and subject:', subjectFilter);
        
        // Hide/show rows based on filters
        const rows = document.querySelectorAll('.timetable tbody tr');
        
        rows.forEach(row => {
            if (row.querySelector('.time-cell')?.textContent.includes('Lunch Break')) {
                // Always show lunch break row
                return;
            }
            
            const classBlocks = row.querySelectorAll('.class-block');
            let shouldShow = false;
            
            classBlocks.forEach(block => {
                const className = block.querySelector('.class-name')?.textContent || '';
                const subjectName = block.querySelector('.class-subject')?.textContent || '';
                
                // Fix: Check for empty string instead of 'all' for "All Classes/Subjects"
                const matchesClass = classFilter === '' || className.toLowerCase().includes(classFilter.toLowerCase());
                const matchesSubject = subjectFilter === '' || subjectName.toLowerCase().includes(subjectFilter.toLowerCase());
                
                if (matchesClass && matchesSubject) {
                    shouldShow = true;
                    
                    // If showing the row but filters are active, highlight matching blocks
                    if (classFilter !== '' || subjectFilter !== '') {
                        block.classList.add('highlighted');
                    } else {
                        block.classList.remove('highlighted');
                    }
                } else {
                    block.classList.remove('highlighted');
                }
            });
            
            // Show row if any class blocks match, or if no classes (for consistency)
            if (shouldShow || row.querySelector('.no-classes')) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Update upcoming class notification
    function updateUpcomingClass(timetables) {
        // For now, we'll keep the static notification
        // In a real implementation, we would:
        // 1. Get the current day and time
        // 2. Find the next class in the timetable
        // 3. Calculate time remaining
        // 4. Update the notification
        console.log('Updating upcoming class notification');
        
        // Access notification elements
        const upcomingClassName = document.querySelector('.upcoming-class-name');
        const upcomingClassDetails = document.querySelector('.upcoming-class-details');
        
        // This is where we would dynamically update with real data
        // For now, just show we're processing it by adding "(Checking)" to the text
        if (upcomingClassName && upcomingClassDetails) {
            upcomingClassName.textContent += " (Checking)";
        }
    }
    
    // Show timetable error
    function showTimetableError(message) {
        const timetableBody = document.querySelector('.timetable tbody');
        // Use colspan based on the number of columns (Time + Days)
        const colCount = 1 + 6; // Time + Monday to Saturday
        timetableBody.innerHTML = `<tr><td colspan="${colCount}" class="error-message">${message}</td></tr>`;
    }
    
    // Simulate countdown for upcoming class
    function simulateCountdown() {
        let minutes = 24;
        let seconds = 18;
        
        const countdownValue = document.querySelector('.countdown-value');
        
        const updateCountdown = () => {
            if (seconds === 0) {
                if (minutes === 0) {
                    // Timer has reached zero
                    countdownValue.textContent = "Time's up!";
                    return;
                }
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }
            
            countdownValue.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        };
        
        // Update countdown every second
        // setInterval(updateCountdown, 1000); // Commented out for now
    }

    // Export timetable to calendar format
    function exportToCalendar() {
        // In a real implementation, we would:
        // 1. Generate iCal/vCalendar format data from the timetable
        // 2. Create a downloadable file
        
        alert('This feature will export your timetable to a calendar format (iCal) that can be imported into Google Calendar, Outlook, or other calendar applications.');
        
        // For demonstration, show a loading message
        const btn = document.querySelector('.btn-primary');
        const originalText = btn.textContent;
        btn.textContent = 'Generating...';
        
        // Simulate processing
        setTimeout(() => {
            // Here we would actually generate and download the calendar file
            // For now, just reset the button text and show a message
            btn.textContent = originalText;
            alert('Calendar export would be downloaded now. This is a placeholder for the actual export functionality.');
            
            // In a real implementation:
            // 1. Generate iCal file content
            // const icalContent = generateICalContent(timetableData);
            // 2. Create and trigger download
            // downloadFile('teacher_schedule.ics', icalContent, 'text/calendar');
        }, 1500);
    }
    
    // Helper function to download file (would be used by exportToCalendar)
    function downloadFile(filename, content, contentType) {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }, 0);
    }

    // Render aggregated timetable for all classes
    function renderAggregatedTimetable() {
        console.log('Rendering aggregated timetable');

        const allPeriods = [];

        // Collect periods from all timetables and add class/section info
        allTimetables.forEach(timetable => {
            if (timetable.periods && timetable.periods.length > 0) {
                timetable.periods.forEach(period => {
                    // Augment period object with class and section info
                    allPeriods.push({
                        ...period,
                        class_name: timetable.class_name,
                        section_name: timetable.section_name
                    });
                });
            }
        });

        // Update the card title
        document.querySelector('.card-title').textContent = 'Weekly Timetable for All Classes';

        // Build the timetable with the aggregated periods
        if (allPeriods.length > 0) {
            // Pass null or undefined for className and sectionName as they are now in the period objects
            buildTimetableFromPeriods(allPeriods, null, null);
            // Apply any active filters after building the timetable
            filterTimetable();
        } else {
            showTimetableError('No timetable data available for any class.');
        }
    }

    // Class Notes Modal Functions
    function openClassNotesModal() {
        console.log('Opening class notes modal');
        const modal = document.getElementById('classNotesModal');
        if (!modal) {
            console.error('Modal element not found!');
            return;
        }
        modal.style.display = 'block';
        populateClassNotesForm();
    }

    function closeClassNotesModal() {
        console.log('Closing class notes modal');
        const modal = document.getElementById('classNotesModal');
        if (!modal) {
            console.error('Modal element not found!');
            return;
        }
        modal.style.display = 'none';
    }

    function populateClassNotesForm() {
        console.log('Populating class notes form');
        const classSelect = document.getElementById('noteClass');
        const subjectSelect = document.getElementById('noteSubject');
        
        if (!classSelect || !subjectSelect) {
            console.error('Form elements not found!');
            return;
        }
        // Clear existing options
        classSelect.innerHTML = '<option value="">Select Class</option>';
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        
        // Populate class options from allTimetables
        const uniqueClasses = new Map();
        allTimetables.forEach(timetable => {
            const classKey = `${timetable.class_name}${timetable.section_name}`;
            uniqueClasses.set(classKey, {
                class_id: timetable.class_id,
                section_id: timetable.section_id,
                display: `Class ${timetable.class_name}${timetable.section_name}`
            });
        });
        
        uniqueClasses.forEach((classInfo, key) => {
            const option = document.createElement('option');
            option.value = JSON.stringify({ class_id: classInfo.class_id, section_id: classInfo.section_id });
            option.textContent = classInfo.display;
            classSelect.appendChild(option);
        });
        
        // Set today's date as default
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('noteDate').value = today;
    }

    // Function to populate filter dropdowns in View Notes tab
    function populateViewNotesFilters() {
        console.log('Populating view notes filters');
        const filterClass = document.getElementById('filterClass');
        const filterSubject = document.getElementById('filterSubject');
        const filterDate = document.getElementById('filterDate');
        
        if (!filterClass || !filterSubject || !filterDate) {
            console.error('Filter elements not found');
            return;
        }

        // Clear existing options
        filterClass.innerHTML = '<option value="">All Classes</option>';
        filterSubject.innerHTML = '<option value="">All Subjects</option>';

        // Populate class options from allTimetables
        const uniqueClasses = new Map();
        allTimetables.forEach(timetable => {
            const classKey = `${timetable.class_name}${timetable.section_name}`;
            uniqueClasses.set(classKey, {
                class_id: timetable.class_id,
                section_id: timetable.section_id,
                display: `Class ${timetable.class_name}${timetable.section_name}`
            });
        });

        uniqueClasses.forEach((classInfo, key) => {
            const option = document.createElement('option');
            option.value = JSON.stringify({ class_id: classInfo.class_id, section_id: classInfo.section_id });
            option.textContent = classInfo.display;
            filterClass.appendChild(option);
        });

        // Populate subject options from all timetables
        const uniqueSubjects = new Map();
        allTimetables.forEach(timetable => {
            if (timetable.periods) {
                timetable.periods.forEach(period => {
                    if (period.subject_id && period.subject_name) {
                        uniqueSubjects.set(period.subject_id, period.subject_name);
                    }
                });
            }
        });

        uniqueSubjects.forEach((subjectName, subjectId) => {
            const option = document.createElement('option');
            option.value = subjectId;
            option.textContent = subjectName;
            filterSubject.appendChild(option);
        });

        // Set default date to today
        filterDate.value = new Date().toISOString().split('T')[0];

        // Add event listeners for filter changes
        filterClass.addEventListener('change', applyNotesFilters);
        filterSubject.addEventListener('change', applyNotesFilters);
        filterDate.addEventListener('change', applyNotesFilters);
    }

    // Function to apply filters to notes list
    async function applyNotesFilters() {
        console.log('Applying notes filters');
        const filterClass = document.getElementById('filterClass');
        const filterSubject = document.getElementById('filterSubject');
        const filterDate = document.getElementById('filterDate');

        let params = {};

        // Add class and section filters if selected
        if (filterClass.value) {
            try {
                const classInfo = JSON.parse(filterClass.value);
                params.class_id = classInfo.class_id;
                params.section_id = classInfo.section_id;
            } catch (e) {
                console.error('Error parsing class filter value:', e);
            }
        }

        // Add subject filter if selected
        if (filterSubject.value) {
            params.subject_id = filterSubject.value;
        }

        // Add date filter if selected
        if (filterDate.value) {
            params.note_date = filterDate.value;
        }

        try {
            const response = await axios.get('../../backend/api/class-notes.php', { params });
            const notesList = document.querySelector('.notes-list');

            if (response.data.success && response.data.data) {
                notesList.innerHTML = response.data.data.map(note => `
                    <div class="note-item">
                        <div class="note-header">
                            <span class="note-title">Class ${note.class_name}${note.section_name} - ${note.subject_name}</span>
                            <span class="note-date">${new Date(note.note_date).toLocaleDateString()}</span>
                        </div>
                        <div class="note-content">${note.note_content}</div>
                        <div class="note-actions">
                            <button onclick="editNote(${note.id})" class="btn btn-secondary btn-sm">Edit</button>
                            <button onclick="deleteNote(${note.id})" class="btn btn-danger btn-sm">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else {
                notesList.innerHTML = '<div class="no-notes">No notes found matching the selected filters.</div>';
            }
        } catch (error) {
            console.error('Error loading filtered notes:', error);
            document.querySelector('.notes-list').innerHTML = 
                '<div class="error">Failed to load notes. Please try again.</div>';
        }
    }

    // Load existing notes with filters
    async function loadNotes() {
        console.log('Loading notes with filters');
        const notesList = document.querySelector('.notes-list');
        notesList.innerHTML = '<div class="loading">Loading notes...</div>';

        // Populate filters first
        populateViewNotesFilters();
        
        // Then apply current filter values
        await applyNotesFilters();
    }

    // Add styles for note actions
    const styleSheet = document.createElement("style");
    styleSheet.textContent = `
        .note-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .no-notes {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .error {
            text-align: center;
            padding: 20px;
            color: #dc3545;
            font-weight: 500;
        }
    `;
    document.head.appendChild(styleSheet);

    // Edit note function
    async function editNote(noteId) {
        try {
            const response = await axios.get(`../../backend/api/class-notes.php?id=${noteId}`);
            if (response.data.success && response.data.data) {
                const note = response.data.data;
                
                // Switch to Add Note tab
                document.querySelector('[data-tab="add"]').click();
                
                // Populate form with note data
                document.getElementById('noteClass').value = JSON.stringify({
                    class_id: note.class_id,
                    section_id: note.section_id
                });
                document.getElementById('noteSubject').value = note.subject_id;
                document.getElementById('noteDate').value = note.note_date;
                document.getElementById('noteContent').value = note.note_content;
                
                // Update form submission to handle edit
                const form = document.getElementById('classNoteForm');
                form.dataset.editId = noteId;
            }
        } catch (error) {
            console.error('Error loading note for edit:', error);
            alert('Failed to load note for editing.');
        }
    }

    // Delete note function
    async function deleteNote(noteId) {
        if (confirm('Are you sure you want to delete this note?')) {
            try {
                const response = await axios.delete(`../../backend/api/class-notes.php/${noteId}`);
                if (response.data.success) {
                    await loadNotes(); // Reload notes list
                } else {
                    alert('Failed to delete note.');
                }
            } catch (error) {
                console.error('Error deleting note:', error);
                alert('Failed to delete note: ' + (error.response?.data?.error || error.message));
            }
        }
    }

    // Student List Functions
    function openStudentListModal() {
        console.log('Opening student list modal');
        const modal = document.getElementById('studentListModal');
        if (!modal) {
            console.error('Student list modal element not found!');
            return;
        }
        modal.style.display = 'block';
        
        // Populate class dropdown
        populateStudentListClassDropdown();
        
        // Add event listeners for modal close button
        modal.querySelector('.close-modal').addEventListener('click', closeStudentListModal);
        
        // Add event listener for Load Students button
        document.getElementById('loadStudentsBtn').addEventListener('click', loadStudents);
        
        // Add event listener for search input
        document.getElementById('studentSearch').addEventListener('input', filterStudents);
    }

    function closeStudentListModal() {
        console.log('Closing student list modal');
        const modal = document.getElementById('studentListModal');
        if (!modal) {
            console.error('Student list modal element not found!');
            return;
        }
        modal.style.display = 'none';
    }

    function populateStudentListClassDropdown() {
        console.log('Populating student list class dropdown');
        const classSelect = document.getElementById('studentListClass');
        if (!classSelect) {
            console.error('Student list class select element not found!');
            return;
        }
        
        // Clear existing options except the first one
        classSelect.innerHTML = '<option value="">Select a Class</option>';
        
        // Populate from allTimetables
        const uniqueClasses = new Map();
        allTimetables.forEach(timetable => {
            const classKey = `${timetable.class_name}${timetable.section_name}`;
            uniqueClasses.set(classKey, {
                class_id: timetable.class_id,
                section_id: timetable.section_id,
                timetable_id: timetable.id,
                display: `Class ${timetable.class_name}${timetable.section_name}`
            });
        });
        
        // Add options to dropdown
        uniqueClasses.forEach((classInfo, key) => {
            const option = document.createElement('option');
            option.value = JSON.stringify({
                class_id: classInfo.class_id,
                section_id: classInfo.section_id,
                timetable_id: classInfo.timetable_id
            });
            option.textContent = classInfo.display;
            classSelect.appendChild(option);
        });
    }

    async function loadStudents() {
        console.log('Loading students');
        const classSelect = document.getElementById('studentListClass');
        const loadingMessage = document.getElementById('studentListLoading');
        const studentTable = document.getElementById('studentTable');
        
        if (!classSelect.value) {
            alert('Please select a class first');
            return;
        }
        
        try {
            // Show loading message
            loadingMessage.textContent = 'Loading students...';
            loadingMessage.style.display = 'block';
            studentTable.style.display = 'none';
            
            // Get class info from selected option
            const classInfo = JSON.parse(classSelect.value);
            console.log('Selected class info:', classInfo);
            
            // Fetch students from API
            const response = await axios.get('../../backend/api/students', {
                params: {
                    class_id: classInfo.class_id,
                    section_id: classInfo.section_id
                }
            });
            
            console.log('Student API response:', response.data);
            
            if (response.data.success && response.data.data) {
                displayStudents(response.data.data);
            } else {
                loadingMessage.textContent = 'No students found for this class.';
                studentTable.style.display = 'none';
            }
        } catch (error) {
            console.error('Error loading students:', error);
            loadingMessage.textContent = 'Failed to load students. ' + (error.response?.data?.error || error.message);
            studentTable.style.display = 'none';
        }
    }

    function displayStudents(students) {
        console.log('Displaying students:', students);
        const studentTable = document.getElementById('studentTable');
        const loadingMessage = document.getElementById('studentListLoading');
        const tableBody = studentTable.querySelector('tbody');
        
        if (students.length === 0) {
            loadingMessage.textContent = 'No students found for this class.';
            loadingMessage.style.display = 'block';
            studentTable.style.display = 'none';
            return;
        }
        
        // Sort students by roll number
        students.sort((a, b) => {
            // Convert roll numbers to numbers for proper numeric sorting
            const rollA = parseInt(a.roll_number) || 0;
            const rollB = parseInt(b.roll_number) || 0;
            return rollA - rollB;
        });
        
        // Clear previous students
        tableBody.innerHTML = '';
        
        // Add student rows
        students.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.roll_number || 'N/A'}</td>
                <td>${student.admission_number || 'N/A'}</td>
                <td>${student.full_name || 'N/A'}</td>
                <td>${student.gender_code || 'N/A'}</td>
                <td>${student.mobile || 'N/A'}</td>
                <td>
                    <button class="student-action-btn" onclick="viewStudentDetails(${student.user_id})">View</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        // Show table, hide loading message
        loadingMessage.style.display = 'none';
        studentTable.style.display = 'table';
        
        // Initialize search field
        document.getElementById('studentSearch').value = '';
    }

    function filterStudents() {
        const searchText = document.getElementById('studentSearch').value.toLowerCase();
        const tableBody = document.getElementById('studentTable').querySelector('tbody');
        const rows = tableBody.querySelectorAll('tr');
        
        let matchCount = 0;
        
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchText)) {
                row.style.display = '';
                matchCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show message if no results
        const loadingMessage = document.getElementById('studentListLoading');
        if (matchCount === 0 && searchText) {
            loadingMessage.textContent = 'No students match your search criteria.';
            loadingMessage.style.display = 'block';
        } else {
            loadingMessage.style.display = 'none';
        }
    }

    // Student Details Functions
    function openStudentDetailsModal() {
        console.log('Opening student details modal');
        const modal = document.getElementById('studentDetailsModal');
        if (!modal) {
            console.error('Student details modal element not found!');
            return;
        }
        modal.style.display = 'block';
         // Add event listeners for modal close button
        modal.querySelector('.close-modal').addEventListener('click', closeStudentDetailsModal);
    }

    function closeStudentDetailsModal() {
         console.log('Closing student details modal');
        const modal = document.getElementById('studentDetailsModal');
        if (!modal) {
            console.error('Student details modal element not found!');
            return;
        }
        modal.style.display = 'none';
    }

    async function fetchAndDisplayStudentDetails(studentId) {
        console.log('Fetching and displaying details for student ID:', studentId);
        const detailsContent = document.getElementById('studentDetailsContent');
        if (!detailsContent) {
            console.error('Student details content element not found!');
            return;
        }

        // Show loading message
        detailsContent.innerHTML = '<div class="loading-message">Loading student details...</div>';

        try {
            const response = await axios.get(`../../backend/api/students/${studentId}`);
            console.log('Student details API response:', response.data);

            if (response.data.success && response.data.data) {
                renderStudentDetails(response.data.data);
            } else {
                detailsContent.innerHTML = '<div class="error-message">Failed to load student details.</div>';
                console.error('API returned error or no data for student details:', response.data);
            }
        } catch (error) {
            detailsContent.innerHTML = '<div class="error-message">Error fetching student details.</div>';
            console.error('Error fetching student details:', error);
        }
    }

    function renderStudentDetails(student) {
        console.log('Rendering student details:', student);
        const detailsContent = document.getElementById('studentDetailsContent');
         if (!detailsContent) {
            console.error('Student details content element not found!');
            return;
        }

        let html = `
            <h3>${student.full_name || 'N/A'}</h3>
            <p><strong>Student ID:</strong> ${student.admission_number || 'N/A'}</p>
            <p><strong>Roll Number:</strong> ${student.roll_number || 'N/A'}</p>
            <p><strong>Class:</strong> ${student.class_name || 'N/A'}${student.section_name || ''}</p>
            <p><strong>Gender:</strong> ${student.gender_code || 'N/A'}</p>
            <p><strong>Date of Birth:</strong> ${student.dob || 'N/A'}</p>
            <p><strong>Mobile:</strong> ${student.mobile || 'N/A'}</p>
            <p><strong>Email:</strong> ${student.contact_email || 'N/A'}</p>
            <p><strong>Address:</strong> ${student.address || 'N/A'}</p>
            <p><strong>Admission Date:</strong> ${student.admission_date || 'N/A'}</p>
        `;

        detailsContent.innerHTML = html;
    }

    // Define global function to be called from the table row button
    window.viewStudentDetails = function(studentId) {
        openStudentDetailsModal();
        fetchAndDisplayStudentDetails(studentId);
    };

    // Implement the Message feature (placeholder for now)
    window.sendMessage = function(studentId) {
        console.log('Send message to student ID:', studentId);
        alert('Messaging functionality is not yet implemented.');
        // In a real application, this would open a messaging interface
    };

    // Function to calculate the next class
    function calculateNextClass() {
        if (!allTimetables || allTimetables.length === 0) {
            console.log('No timetable data available');
            return null;
        }

        const now = new Date();
        const currentDay = now.getDay(); // 0 = Sunday, 1 = Monday, etc.
        const currentTime = now.getHours() * 60 + now.getMinutes(); // Current time in minutes

        // Map JavaScript day numbers to your timetable day names
        const dayMap = {
            1: 'monday',
            2: 'tuesday',
            3: 'wednesday',
            4: 'thursday',
            5: 'friday',
            6: 'saturday'
        };

        // If it's Sunday (0) or Saturday (6), look for Monday's first class
        const searchDay = currentDay === 0 ? 1 : (currentDay === 6 ? 1 : currentDay);
        const isNextDay = currentDay === 0 || currentDay === 6 || 
                         (currentDay >= 1 && currentDay <= 5 && currentTime >= 15 * 60 + 20); // After 3:20 PM

        let nextClass = null;
        let earliestTime = 24 * 60; // Initialize to end of day
        let daysToAdd = 0;

        // Function to parse time string to minutes
        function timeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        // Search through all timetables
        allTimetables.forEach(timetable => {
            if (timetable.periods) {
                timetable.periods.forEach(period => {
                    const periodDay = period.day_of_week.toLowerCase();
                    const startTime = timeToMinutes(period.start_time.substring(0, 5)); // Convert HH:MM:SS to minutes

                    if (isNextDay) {
                        // Looking for next day's classes
                        if (periodDay === dayMap[searchDay === 5 ? 1 : searchDay + 1]) {
                            if (startTime < earliestTime) {
                                earliestTime = startTime;
                                nextClass = {
                                    ...period, // Keep period details
                                    class_name: timetable.class_name, // Add class name from timetable
                                    section_name: timetable.section_name // Add section name from timetable
                                };
                                daysToAdd = searchDay === currentDay ? 1 : 
                                           currentDay === 0 ? 1 : 
                                           currentDay === 6 ? 2 : 1;
                            }
                        }
                    } else if (periodDay === dayMap[searchDay]) {
                        // Looking for remaining classes today
                        if (startTime > currentTime && startTime < earliestTime) {
                            earliestTime = startTime;
                            nextClass = {
                                ...period, // Keep period details
                                class_name: timetable.class_name, // Add class name from timetable
                                section_name: timetable.section_name // Add section name from timetable
                            };
                            daysToAdd = 0;
                        }
                    }
                });
            }
        });

        if (nextClass) {
            const nextDate = new Date();
            nextDate.setDate(nextDate.getDate() + daysToAdd);
            return {
                period: nextClass,
                startTime: earliestTime,
                daysToAdd: daysToAdd,
                nextDate: nextDate
            };
        }

        return null;
    }

    function updateUpcomingClassDisplay() {
        const nextClassInfo = calculateNextClass();
        const upcomingClassAlert = document.getElementById('upcomingClassAlert');
        const upcomingClassName = document.getElementById('upcomingClassName');
        const upcomingClassDetails = document.getElementById('upcomingClassDetails');
        const countdownValue = document.getElementById('countdownValue');

        if (!nextClassInfo) {
            upcomingClassAlert.style.display = 'none';
            return;
        }

        const { period, startTime, nextDate } = nextClassInfo;
        
        // Format the display information
        const classInfo = `Class ${period.class_name}${period.section_name} - ${period.subject_name}`;
        const timeStr = period.start_time.substring(0, 5); // HH:MM format
        const endTimeStr = period.end_time.substring(0, 5); // HH:MM format
        // Remove Room information
        const details = `${timeStr} - ${endTimeStr}`;

        // Update the display
        upcomingClassName.textContent = classInfo;
        upcomingClassDetails.textContent = details;
        upcomingClassAlert.style.display = 'flex';

        // Start countdown
        updateCountdown(nextDate, timeStr);
    }

    function updateCountdown(nextDate, startTime) {
        const countdownValue = document.getElementById('countdownValue');
        const [hours, minutes] = startTime.split(':').map(Number);
        nextDate.setHours(hours, minutes, 0, 0);

        // Clear any existing interval
        if (countdownValue.dataset.timerInterval) {
            clearInterval(parseInt(countdownValue.dataset.timerInterval));
        }

        function updateTimer() {
            const now = new Date();
            const diff = nextDate - now;

            if (diff <= 0) {
                // Time's up - refresh next class calculation
                updateUpcomingClassDisplay();
                return;
            }

            // Calculate hours and minutes
            const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
            const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            // Update display
            if (hoursLeft > 0) {
                countdownValue.textContent = `${hoursLeft}h ${minutesLeft}m`;
            } else {
                countdownValue.textContent = `${minutesLeft}m`;
            }
        }

        // Update immediately and then every minute
        updateTimer();
        const timerInterval = setInterval(updateTimer, 60000);
        countdownValue.dataset.timerInterval = timerInterval;
    }

    // Update the display every minute
    setInterval(updateUpcomingClassDisplay, 60000);

</script>
</body>
</html>