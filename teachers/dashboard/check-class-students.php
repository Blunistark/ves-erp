<?php
// Include sidebar for authentication and navigation
include 'sidebar.php'; 

// Include database connection
include 'con.php';

// Get teacher ID from session
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Fetch classes assigned to this teacher (where they are class teacher or teach subjects)
$classQuery = "SELECT DISTINCT c.id, c.name, s.id AS section_id, s.name AS section_name 
               FROM classes c 
               JOIN sections s ON c.id = s.class_id 
               LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
               LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
               WHERE s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL
               ORDER BY c.name, s.name";

$stmt = $conn->prepare($classQuery);
$stmt->bind_param("ii", $teacher_user_id, $teacher_user_id);
$stmt->execute();
$classResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Class Students</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .student-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .student-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            transition: transform 0.2s;
        }
        
        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .student-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .student-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.3rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .info-value {
            font-weight: 500;
            color: #333;
        }
        
        .no-students {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .export-btn {
            margin-top: 1rem;
            background-color: #28a745;
            color: white;
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
        <h1 class="header-title">Class Students</h1>
        <span class="header-subtitle">View students in your assigned classes</span>
    </header>

    <main class="dashboard-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Student Information</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="classSelect" class="form-label">Select Class</label>
                    <select id="classSelect" class="form-select">
                        <option value="">Select a class</option>
                        <?php
                        // Output class options from database
                        if ($classResult && $classResult->num_rows > 0) {
                            while ($row = $classResult->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "-" . $row['section_id'] . "'>" .
                                    htmlspecialchars($row['name'] . " " . $row['section_name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div id="studentListContainer" style="display: none;">
                    <div id="studentDetails" class="student-details">
                        <!-- Student cards will be populated here -->
                    </div>
                    
                    <button id="exportBtn" class="btn export-btn" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16">
                            <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                        </svg>
                        Export to Excel
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('classSelect');
    const studentListContainer = document.getElementById('studentListContainer');
    const studentDetails = document.getElementById('studentDetails');
    const exportBtn = document.getElementById('exportBtn');
    
    // Event listener for class selection
    classSelect.addEventListener('change', function() {
        if (this.value) {
            loadStudents(this.value);
        } else {
            studentListContainer.style.display = 'none';
            exportBtn.style.display = 'none';
        }
    });
    
    // Function to load students for the selected class
    function loadStudents(classData) {
        const [classId, sectionId] = classData.split('-');
        
        // Show loading indicator
        studentListContainer.style.display = 'block';
        studentDetails.innerHTML = '<div class="no-students">Loading students...</div>';
        
        // Fetch students for the selected class and section
        fetch(`class_students_action.php?action=get_students&class_id=${classId}&section_id=${sectionId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Display the student list
                    studentListContainer.style.display = 'block';
                    
                    // Clear previous data
                    studentDetails.innerHTML = '';
                    
                    // Check if students array exists and has data
                    if (!data.students || data.students.length === 0) {
                        studentDetails.innerHTML = '<div class="no-students">No students found for this class</div>';
                        exportBtn.style.display = 'none';
                        return;
                    }
                    
                    // Populate with student data
                    data.students.forEach(student => {
                        const card = document.createElement('div');
                        card.className = 'student-card';
                        
                        // Format data for display
                        const dob = student.dob ? new Date(student.dob).toLocaleDateString() : 'Not specified';
                        const address = student.address || 'Not specified';
                        const mobile = student.mobile || 'Not specified';
                        const email = student.contact_email || 'Not specified';
                        
                        card.innerHTML = `
                            <div class="student-name">${student.full_name}</div>
                            <div class="student-info">
                                <div class="student-info-item">
                                    <span class="info-label">Roll Number:</span>
                                    <span class="info-value">${student.roll_number || 'N/A'}</span>
                                </div>
                                <div class="student-info-item">
                                    <span class="info-label">Admission Number:</span>
                                    <span class="info-value">${student.admission_number || 'N/A'}</span>
                                </div>
                                <div class="student-info-item">
                                    <span class="info-label">Gender:</span>
                                    <span class="info-value">${student.gender_code || 'Not specified'}</span>
                                </div>
                                <div class="student-info-item">
                                    <span class="info-label">Date of Birth:</span>
                                    <span class="info-value">${dob}</span>
                                </div>
                                <div class="student-info-item">
                                    <span class="info-label">Parent:</span>
                                    <span class="info-value">${student.father_name || student.mother_name || 'Not specified'}</span>
                                </div>
                                <div class="student-info-item">
                                    <span class="info-label">Mobile:</span>
                                    <span class="info-value">${mobile}</span>
                                </div>
                            </div>
                        `;
                        
                        studentDetails.appendChild(card);
                    });
                    
                    // Show export button
                    exportBtn.style.display = 'inline-block';
                } else {
                    console.error('API Error:', data.message);
                    studentDetails.innerHTML = `<div class="no-students">Error: ${data.message || 'Failed to load students'}</div>`;
                    exportBtn.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                studentDetails.innerHTML = '<div class="no-students">Error loading students. Please try again.</div>';
                exportBtn.style.display = 'none';
            });
    }
    
    // Export button functionality
    exportBtn.addEventListener('click', function() {
        const [classId, sectionId] = classSelect.value.split('-');
        if (classId && sectionId) {
            window.location.href = `class_students_action.php?action=export_students&class_id=${classId}&section_id=${sectionId}`;
        }
    });
});
</script>

</body>
</html> 