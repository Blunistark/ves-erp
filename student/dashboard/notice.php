<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Class Notice Board</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/notice.css">
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
        <h1 class="header-title">Class Notice Board</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="class-selector">
            <label class="selector-label">Select Your Class</label>
            <div class="class-dropdown">
                <button class="dropdown-button" onclick="toggleDropdown()">
                    <span id="selected-class">Class X-A</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="dropdown-menu" id="class-dropdown">
                    <div class="dropdown-item active" onclick="selectClass('Class X-A')">Class X-A</div>
                    <div class="dropdown-item" onclick="selectClass('Class X-B')">Class X-B</div>
                    <div class="dropdown-item" onclick="selectClass('Class IX-A')">Class IX-A</div>
                    <div class="dropdown-item" onclick="selectClass('Class IX-B')">Class IX-B</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Class X-A Notice Board</h2>
            
            <div class="notice-board">
                <!-- High Priority Notice -->
                <div class="notice-card">
                    <div class="notice-header">
                        <div class="notice-title">Math Test Postponed</div>
                        <span class="notice-priority priority-high">High Priority</span>
                    </div>
                    
                    <div class="notice-content">
                        <p>Dear students,</p>
                        <p>Please note that the Mathematics test scheduled for tomorrow (March 13, 2025) has been postponed to March 17, 2025 due to unavoidable circumstances. The syllabus for the test remains the same as previously announced.</p>
                        <p>Use this additional time to prepare well. If you have any questions about the test material, feel free to ask during our next class.</p>
                        
                        <div class="notice-attachment">
                            <div class="attachment-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">Math_Test_Syllabus.pdf</div>
                                <div class="attachment-meta">
                                    <div class="attachment-size">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                        </svg>
                                        356 KB
                                    </div>
                                    <div class="attachment-type">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        PDF Document
                                    </div>
                                </div>
                            </div>
                            <button class="attachment-download">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="notice-footer">
                        <div class="notice-teacher">
                            <div class="teacher-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="teacher-info">
                                <div class="teacher-name">Mrs. Johnson</div>
                                <div class="teacher-subject">Mathematics</div>
                            </div>
                        </div>
                        
                        <div class="notice-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on Mar 12, 2025
                        </div>
                    </div>
                </div>
                
                <!-- Medium Priority Notice -->
                <div class="notice-card">
                    <div class="notice-header">
                        <div class="notice-title">Science Project Groups</div>
                        <span class="notice-priority priority-medium">Medium Priority</span>
                    </div>
                    
                    <div class="notice-content">
                        <p>Hello Class X-A,</p>
                        <p>I've assigned the groups for our upcoming Science project on "Renewable Energy Sources". Each group will focus on a different energy source and prepare a presentation and working model.</p>
                        <p>Please check the attached document for group assignments and detailed project requirements. The project deadline is April 10, 2025.</p>
                        
                        <div class="notice-attachment">
                            <div class="attachment-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">Science_Project_Groups.docx</div>
                                <div class="attachment-meta">
                                <div class="attachment-size">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                        </svg>
                                        245 KB
                                    </div>
                                    <div class="attachment-type">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Word Document
                                    </div>
                                </div>
                            </div>
                            <button class="attachment-download">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="notice-footer">
                        <div class="notice-teacher">
                            <div class="teacher-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="teacher-info">
                                <div class="teacher-name">Mr. Wilson</div>
                                <div class="teacher-subject">Science</div>
                            </div>
                        </div>
                        
                        <div class="notice-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on Mar 9, 2025
                        </div>
                    </div>
                </div>
                
                <!-- Low Priority Notice -->
                <div class="notice-card">
                    <div class="notice-header">
                        <div class="notice-title">English Literature Book List</div>
                        <span class="notice-priority priority-low">Low Priority</span>
                    </div>
                    
                    <div class="notice-content">
                        <p>Dear students,</p>
                        <p>For our upcoming unit on Modern Literature, we will be analyzing several short stories and poems. Please ensure you have access to the following books by next week:</p>
                        <ol>
                            <li>The Collected Works of Edgar Allan Poe</li>
                            <li>Selected Short Stories by O. Henry</li>
                            <li>Modern Poetry Anthology (School Edition)</li>
                        </ol>
                        <p>These books are available in the school library or can be purchased from the bookstore. Digital versions are also acceptable for those who prefer e-books.</p>
                    </div>
                    
                    <div class="notice-footer">
                        <div class="notice-teacher">
                            <div class="teacher-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="teacher-info">
                                <div class="teacher-name">Ms. Adams</div>
                                <div class="teacher-subject">English</div>
                            </div>
                        </div>
                        
                        <div class="notice-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on Mar 7, 2025
                        </div>
                    </div>
                </div>
                
                <!-- Reminder Notice -->
                <div class="notice-card">
                    <div class="notice-header">
                        <div class="notice-title">Parent-Teacher Meeting Reminder</div>
                        <span class="notice-priority priority-medium">Medium Priority</span>
                    </div>
                    
                    <div class="notice-content">
                        <p>Dear Class X-A students,</p>
                        <p>This is a reminder that the Parent-Teacher Meeting is scheduled for this Saturday, March 15, 2025 from 9:00 AM to 1:00 PM. Please inform your parents/guardians about this important meeting.</p>
                        <p>The meeting will take place in your regular classroom. Parents are requested to meet with each subject teacher to discuss your academic progress.</p>
                        <p>Attendance is mandatory for all students along with at least one parent/guardian.</p>
                    </div>
                    
                    <div class="notice-footer">
                        <div class="notice-teacher">
                            <div class="teacher-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="teacher-info">
                                <div class="teacher-name">Mr. Peterson</div>
                                <div class="teacher-subject">Class Teacher</div>
                            </div>
                        </div>
                        
                        <div class="notice-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on Mar 5, 2025
                        </div>
                    </div>
                </div>
                
                <!-- More notices would follow... -->
            </div>
        </div>
    </main>
</div>

<script>
    // Toggle dropdown menu
    function toggleDropdown() {
        const dropdown = document.getElementById('class-dropdown');
        dropdown.classList.toggle('show');
        
        // Close the dropdown when clicking outside
        if (dropdown.classList.contains('show')) {
            document.addEventListener('click', closeDropdownOnOutsideClick);
        } else {
            document.removeEventListener('click', closeDropdownOnOutsideClick);
        }
    }
    
    function closeDropdownOnOutsideClick(event) {
        const dropdown = document.getElementById('class-dropdown');
        const button = document.querySelector('.dropdown-button');
        
        if (!dropdown.contains(event.target) && !button.contains(event.target)) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdownOnOutsideClick);
        }
    }
    
    // Select class option
    function selectClass(className) {
        document.getElementById('selected-class').textContent = className;
        document.querySelector('.card-title').textContent = className + ' Notice Board';
        
        // Update active class
        document.querySelectorAll('.dropdown-item').forEach(item => {
            if (item.textContent === className) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
        
        // Close the dropdown
        document.getElementById('class-dropdown').classList.remove('show');
        
        // In a real app, you would load the corresponding notices here
        // For demonstration, we're keeping the same notices
    }
    
    // Handle attachment downloads
    document.querySelectorAll('.attachment-download').forEach(button => {
        button.addEventListener('click', function() {
            const fileName = this.closest('.notice-attachment').querySelector('.attachment-name').textContent;
            alert(`Downloading ${fileName}... (This would initiate a real download in a production environment)`);
        });
    });
</script>
</body>
</html>