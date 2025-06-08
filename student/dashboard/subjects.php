<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Subjects</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/subjects.css">
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
            <h1 class="header-title">Student Subjects</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <h2 class="section-title">Manage Student Subjects</h2>

            <!-- Search input -->
            <div class="search-container">
                <input type="text" id="studentSearch" class="search-input" placeholder="Search for a student...">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <!-- Student selection cards -->
            <div class="students-grid">
                <div class="student-card active" data-student-id="1" onclick="selectStudent(1)">
                    <h3 class="student-name">Emma Johnson</h3>
                    <p class="student-grade">10th Grade</p>
                    <p class="student-gpa">GPA: 3.85</p>
                </div>
                
                <div class="student-card" data-student-id="2" onclick="selectStudent(2)">
                    <h3 class="student-name">Liam Brown</h3>
                    <p class="student-grade">11th Grade</p>
                    <p class="student-gpa">GPA: 3.60</p>
                </div>
                
                <div class="student-card" data-student-id="3" onclick="selectStudent(3)">
                    <h3 class="student-name">Sophia Garcia</h3>
                    <p class="student-grade">9th Grade</p>
                    <p class="student-gpa">GPA: 3.92</p>
                </div>
                
                <div class="student-card" data-student-id="4" onclick="selectStudent(4)">
                    <h3 class="student-name">Noah Williams</h3>
                    <p class="student-grade">12th Grade</p>
                    <p class="student-gpa">GPA: 3.78</p>
                </div>
            </div>

            <!-- Stats for the selected student -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20V10"></path>
                            <path d="M18 20V4"></path>
                            <path d="M6 20v-4"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-title">GPA</p>
                        <p class="stat-value" id="student-gpa">3.85</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-title">Attendance</p>
                        <p class="stat-value" id="student-attendance">95.0%</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-title">Assignments</p>
                        <p class="stat-value" id="student-assignments">138/150</p>
                    </div>
                </div>
            </div>

            <!-- Subjects tab for the selected student -->
            <div class="subjects-card">
                <div class="tab-nav">
                    <button class="tab-button active" onclick="showTab('grades')">Grades</button>
                    <button class="tab-button" onclick="showTab('attendance')">Attendance</button>
                    <button class="tab-button" onclick="showTab('assignments')">Assignments</button>
                </div>
                
                <!-- Grades Tab Content -->
                <div class="tab-content" id="grades-tab">
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="grades-table-body">
                            <tr>
                                <td>Mathematics</td>
                                <td>Dr. Smith</td>
                                <td><span class="grade-badge grade-a">A</span></td>
                                <td><a href="#" class="action-btn">Edit</a></td>
                            </tr>
                            <tr>
                                <td>Science</td>
                                <td>Ms. Peters</td>
                                <td><span class="grade-badge grade-b">B+</span></td>
                                <td><a href="#" class="action-btn">Edit</a></td>
                            </tr>
                            <tr>
                                <td>English</td>
                                <td>Mr. Wilson</td>
                                <td><span class="grade-badge grade-a">A-</span></td>
                                <td><a href="#" class="action-btn">Edit</a></td>
                            </tr>
                            <tr>
                                <td>History</td>
                                <td>Mrs. Adams</td>
                                <td><span class="grade-badge grade-b">B</span></td>
                                <td><a href="#" class="action-btn">Edit</a></td>
                            </tr>
                            <tr>
                                <td>Computer Science</td>
                                <td>Dr. Taylor</td>
                                <td><span class="grade-badge grade-a">A+</span></td>
                                <td><a href="#" class="action-btn">Edit</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Attendance Tab Content -->
                <div class="tab-content" id="attendance-tab" style="display: none;">
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-table-body">
                            <tr>
                                <td>Mathematics</td>
                                <td>Dr. Smith</td>
                                <td>
                                    <div class="attendance-bar">
                                        <div class="attendance-progress high" style="width: 95%;"></div>
                                    </div>
                                    <span style="font-size: 0.875rem;">95%</span>
                                </td>
                                <td><a href="#" class="action-btn">Update</a></td>
                            </tr>
                            <tr>
                                <td>Science</td>
                                <td>Ms. Peters</td>
                                <td>
                                    <div class="attendance-bar">
                                        <div class="attendance-progress high" style="width: 92%;"></div>
                                    </div>
                                    <span style="font-size: 0.875rem;">92%</span>
                                </td>
                                <td><a href="#" class="action-btn">Update</a></td>
                            </tr>
                            <tr>
                                <td>English</td>
                                <td>Mr. Wilson</td>
                                <td>
                                    <div class="attendance-bar">
                                        <div class="attendance-progress high" style="width: 98%;"></div>
                                    </div>
                                    <span style="font-size: 0.875rem;">98%</span>
                                </td>
                                <td><a href="#" class="action-btn">Update</a></td>
                            </tr>
                            <tr>
                                <td>History</td>
                                <td>Mrs. Adams</td>
                                <td>
                                    <div class="attendance-bar">
                                        <div class="attendance-progress high" style="width: 90%;"></div>
                                    </div>
                                    <span style="font-size: 0.875rem;">90%</span>
                                </td>
                                <td><a href="#" class="action-btn">Update</a></td>
                            </tr>
                            <tr>
                                <td>Computer Science</td>
                                <td>Dr. Taylor</td>
                                <td>
                                    <div class="attendance-bar">
                                        <div class="attendance-progress high" style="width: 100%;"></div>
                                    </div>
                                    <span style="font-size: 0.875rem;">100%</span>
                                </td>
                                <td><a href="#" class="action-btn">Update</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Assignments Tab Content -->
                <div class="tab-content" id="assignments-tab" style="display: none;">
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Completed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="assignments-table-body">
                            <tr>
                                <td>Mathematics</td>
                                <td>Dr. Smith</td>
                                <td>
                                    <div class="assignments-status">
                                        <span class="assignments-completed">28/30</span>
                                        <div class="attendance-bar">
                                            <div class="attendance-progress high" style="width: 93.3%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="#" class="action-btn">Details</a></td>
                            </tr>
                            <tr>
                                <td>Science</td>
                                <td>Ms. Peters</td>
                                <td>
                                    <div class="assignments-status">
                                        <span class="assignments-completed">26/30</span>
                                        <div class="attendance-bar">
                                            <div class="attendance-progress medium" style="width: 86.7%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="#" class="action-btn">Details</a></td>
                            </tr>
                            <tr>
                                <td>English</td>
                                <td>Mr. Wilson</td>
                                <td>
                                    <div class="assignments-status">
                                        <span class="assignments-completed">29/30</span>
                                        <div class="attendance-bar">
                                            <div class="attendance-progress high" style="width: 96.7%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="#" class="action-btn">Details</a></td>
                            </tr>
                            <tr>
                                <td>History</td>
                                <td>Mrs. Adams</td>
                                <td>
                                    <div class="assignments-status">
                                        <span class="assignments-completed">25/30</span>
                                        <div class="attendance-bar">
                                            <div class="attendance-progress medium" style="width: 83.3%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="#" class="action-btn">Details</a></td>
                            </tr>
                            <tr>
                                <td>Computer Science</td>
                                <td>Dr. Taylor</td>
                                <td>
                                    <div class="assignments-status">
                                        <span class="assignments-completed">30/30</span>
                                        <div class="attendance-bar">
                                            <div class="attendance-progress high" style="width: 100%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="#" class="action-btn">Details</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
// JavaScript for sidebar toggle
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('show');
    document.querySelector('body').classList.toggle('sidebar-open');
}

// JavaScript for tab switching
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show the selected tab content
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to the clicked button
    document.querySelector(`.tab-button[onclick="showTab('${tabName}')"]`).classList.add('active');
}

// Function to select a student
function selectStudent(studentId) {
    // Remove active class from all student cards
    document.querySelectorAll('.student-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to the selected student card
    document.querySelector(`.student-card[data-student-id="${studentId}"]`).classList.add('active');
    
    // Find the selected student data
    const student = studentData.find(s => s.id === studentId);
    
    // Update the stats display
    document.getElementById('student-gpa').textContent = student.gpa;
    document.getElementById('student-attendance').textContent = student.attendance + '%';
    document.getElementById('student-assignments').textContent = 
        student.assignments.completed + '/' + student.assignments.total;
    
    // Update the grades table
    const gradesTableBody = document.getElementById('grades-table-body');
    gradesTableBody.innerHTML = '';
    
    student.subjects.forEach(subject => {
        // Determine grade class
        let gradeClass = 'grade-a';
        if (subject.grade.startsWith('B')) {
            gradeClass = 'grade-b';
        } else if (subject.grade.startsWith('C')) {
            gradeClass = 'grade-c';
        } else if (subject.grade.startsWith('D') || subject.grade.startsWith('F')) {
            gradeClass = 'grade-d';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${subject.name}</td>
            <td>${subject.teacher}</td>
            <td><span class="grade-badge ${gradeClass}">${subject.grade}</span></td>
            <td><a href="#" class="action-btn">Edit</a></td>
        `;
        gradesTableBody.appendChild(row);
    });
    
    // Update the attendance table
    const attendanceTableBody = document.getElementById('attendance-table-body');
    attendanceTableBody.innerHTML = '';
    
    student.subjects.forEach(subject => {
        // Determine attendance class
        let attendanceClass = 'high';
        if (subject.attendance < 75) {
            attendanceClass = 'critical';
        } else if (subject.attendance < 85) {
            attendanceClass = 'low';
        } else if (subject.attendance < 95) {
            attendanceClass = 'medium';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${subject.name}</td>
            <td>${subject.teacher}</td>
            <td>
                <div class="attendance-bar">
                    <div class="attendance-progress ${attendanceClass}" style="width: ${subject.attendance}%;"></div>
                </div>
                <span style="font-size: 0.875rem;">${subject.attendance}%</span>
            </td>
            <td><a href="#" class="action-btn">Update</a></td>
        `;
        attendanceTableBody.appendChild(row);
    });
    
    // Update the assignments table
    const assignmentsTableBody = document.getElementById('assignments-table-body');
    assignmentsTableBody.innerHTML = '';
    
    student.subjects.forEach(subject => {
        const percentage = (subject.completed / subject.total) * 100;
        
        // Determine progress class
        let progressClass = 'high';
        if (percentage < 60) {
            progressClass = 'critical';
        } else if (percentage < 75) {
            progressClass = 'low';
        } else if (percentage < 90) {
            progressClass = 'medium';
        }
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${subject.name}</td>
            <td>${subject.teacher}</td>
            <td>
                <div class="assignments-status">
                    <span class="assignments-completed">${subject.completed}/${subject.total}</span>
                    <div class="attendance-bar">
                        <div class="attendance-progress ${progressClass}" style="width: ${percentage}%;"></div>
                    </div>
                </div>
            </td>
            <td><a href="#" class="action-btn">Details</a></td>
        `;
        assignmentsTableBody.appendChild(row);
    });
}

// Add search functionality
document.getElementById('studentSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    document.querySelectorAll('.student-card').forEach(card => {
        const studentName = card.querySelector('.student-name').textContent.toLowerCase();
        const studentGrade = card.querySelector('.student-grade').textContent.toLowerCase();
        
        if (studentName.includes(searchTerm) || studentGrade.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Student data
const studentData = [
    {
        id: 1,
        name: "Emma Johnson",
        grade: "10th Grade",
        gpa: 3.85,
        attendance: 95.0,
        assignments: {
            completed: 138,
            total: 150
        },
        subjects: [
            { name: "Mathematics", teacher: "Dr. Smith", grade: "A", attendance: 95, completed: 28, total: 30 },
            { name: "Science", teacher: "Ms. Peters", grade: "B+", attendance: 92, completed: 26, total: 30 },
            { name: "English", teacher: "Mr. Wilson", grade: "A-", attendance: 98, completed: 29, total: 30 },
            { name: "History", teacher: "Mrs. Adams", grade: "B", attendance: 90, completed: 25, total: 30 },
            { name: "Computer Science", teacher: "Dr. Taylor", grade: "A+", attendance: 100, completed: 30, total: 30 }
        ]
    },
    {
        id: 2,
        name: "Liam Brown",
        grade: "11th Grade",
        gpa: 3.60,
        attendance: 90.2,
        assignments: {
            completed: 132,
            total: 150
        },
        subjects: [
            { name: "Mathematics", teacher: "Dr. Smith", grade: "B", attendance: 88, completed: 25, total: 30 },
            { name: "Physics", teacher: "Dr. Clarke", grade: "A", attendance: 94, completed: 28, total: 30 },
            { name: "English Literature", teacher: "Mr. Wilson", grade: "B+", attendance: 91, completed: 27, total: 30 },
            { name: "Economics", teacher: "Ms. Rodriguez", grade: "A-", attendance: 93, completed: 28, total: 30 },
            { name: "Spanish", teacher: "Mrs. Garcia", grade: "B-", attendance: 85, completed: 24, total: 30 }
        ]
    },
    {
        id: 3,
        name: "Sophia Garcia",
        grade: "9th Grade",
        gpa: 3.92,
        attendance: 94.8,
        assignments: {
            completed: 141,
            total: 150
        },
        subjects: [
            { name: "Algebra", teacher: "Mr. Thompson", grade: "A-", attendance: 96, completed: 29, total: 30 },
            { name: "Biology", teacher: "Dr. Martinez", grade: "A", attendance: 97, completed: 29, total: 30 },
            { name: "English", teacher: "Mrs. Johnson", grade: "B+", attendance: 92, completed: 27, total: 30 },
            { name: "Geography", teacher: "Mr. Lee", grade: "B", attendance: 89, completed: 26, total: 30 },
            { name: "Art", teacher: "Ms. Palmer", grade: "A+", attendance: 100, completed: 30, total: 30 }
        ]
    },
    {
        id: 4,
        name: "Noah Williams",
        grade: "12th Grade",
        gpa: 3.78,
        attendance: 93.6,
        assignments: {
            completed: 139,
            total: 150
        },
        subjects: [
            { name: "Calculus", teacher: "Dr. Lewis", grade: "A-", attendance: 94, completed: 28, total: 30 },
            { name: "Chemistry", teacher: "Dr. Harris", grade: "B+", attendance: 91, completed: 27, total: 30 },
            { name: "Literature", teacher: "Ms. Wilson", grade: "A", attendance: 96, completed: 29, total: 30 },
            { name: "Civics", teacher: "Mr. Thomas", grade: "B", attendance: 89, completed: 26, total: 30 },
            { name: "Computer Programming", teacher: "Mr. Davis", grade: "A", attendance: 98, completed: 29, total: 30 }
        ]
    }
];

// Initialize page with the first student selected
document.addEventListener('DOMContentLoaded', function() {
    // Display first student's data by default
    selectStudent(1);
    
    // Add animation delay to student cards
    document.querySelectorAll('.student-card').forEach((card, index) => {
        card.style.animationDelay = `${0.6 + (index * 0.1)}s`;
    });
});
</script>