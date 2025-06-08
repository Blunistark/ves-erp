<?php
// Include required files and start session
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../../login.php");
    exit;
}

// Include database connection
require_once 'con.php';

// Get student ID
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$student_id) {
    header("Location: students.php");
    exit;
}

// Fetch student details
$sql = "SELECT s.*, c.name as class_name, sec.name as section_name, ay.name as academic_year, u.email
        FROM students s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        WHERE s.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: students.php");
    exit;
}

$student = $result->fetch_assoc();
$stmt->close();

// Include sidebar
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($student['full_name']); ?> - Student</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/add_student.css">
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
            <h1 class="header-title">Edit Student: <?php echo htmlspecialchars($student['full_name']); ?></h1>
            <span class="header-path">Dashboard > Students > Edit > <?php echo htmlspecialchars($student['admission_number']); ?></span>
        </header>

        <main class="dashboard-content">
            <form class="form-container" id="editStudentForm">
                <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                
                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName" class="form-label required">Full Name</label>
                            <input type="text" id="fullName" name="fullName" class="form-input" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="dateOfBirth" class="form-label required">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" value="<?php echo $student['dob']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label required">Gender</label>
                            <select id="gender" name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($student['gender_code'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($student['gender_code'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($student['gender_code'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="bloodGroup" class="form-label">Blood Group</label>
                            <select id="bloodGroup" name="bloodGroup" class="form-select">
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?php echo ($student['blood_group_code'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($student['blood_group_code'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($student['blood_group_code'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($student['blood_group_code'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo ($student['blood_group_code'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($student['blood_group_code'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo ($student['blood_group_code'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($student['blood_group_code'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" id="nationality" name="nationality" class="form-input" value="<?php echo htmlspecialchars($student['nationality'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="aadharCard" class="form-label">Student Aadhar Number</label>
                            <input type="text" id="aadharCard" name="aadharCard" class="form-input" maxlength="12" pattern="[0-9]{12}" value="<?php echo htmlspecialchars($student['aadhar_card_number'] ?? ''); ?>" placeholder="Enter 12-digit Aadhar number">
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="form-section">
                    <h2 class="section-title">Academic Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admissionNo" class="form-label required">Admission Number</label>
                            <input type="text" id="admissionNo" name="admissionNo" class="form-input" value="<?php echo htmlspecialchars($student['admission_number']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="admissionDate" class="form-label required">Admission Date</label>
                            <input type="date" id="admissionDate" name="admissionDate" class="form-input" value="<?php echo $student['admission_date']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="rollNumber" class="form-label">Roll Number</label>
                            <input type="text" id="rollNumber" name="rollNumber" class="form-input" value="<?php echo htmlspecialchars($student['roll_number'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="class" class="form-label required">Class</label>
                            <select id="class" name="class" class="form-select" required>
                                <option value="">Select Class</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="section" class="form-label required">Section</label>
                            <select id="section" name="section" class="form-select" required>
                                <option value="">Select Section</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="academicYear" class="form-label required">Academic Year</label>
                            <select id="academicYear" name="academicYear" class="form-select" required>
                                <option value="">Select Academic Year</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h2 class="section-title">Contact Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="altPhone" class="form-label">Alternative Phone</label>
                            <input type="tel" id="altPhone" name="altPhone" class="form-input" value="<?php echo htmlspecialchars($student['alt_mobile'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($student['contact_email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address" class="form-label required">Address</label>
                            <input type="text" id="address" name="address" class="form-input" value="<?php echo htmlspecialchars($student['address']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="postalCode" class="form-label">Postal Code</label>
                            <input type="text" id="postalCode" name="postalCode" class="form-input" value="<?php echo htmlspecialchars($student['pincode'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Parent/Guardian Information -->
                <div class="form-section">
                    <h2 class="section-title">Parent/Guardian Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherName" class="form-label">Father's Name</label>
                            <input type="text" id="fatherName" name="fatherName" class="form-input" value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="fatherAadhar" class="form-label">Father's Aadhar Number</label>
                            <input type="text" id="fatherAadhar" name="fatherAadhar" class="form-input" maxlength="12" pattern="[0-9]{12}" value="<?php echo htmlspecialchars($student['father_aadhar_number'] ?? ''); ?>" placeholder="Enter 12-digit Aadhar number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherName" class="form-label">Mother's Name</label>
                            <input type="text" id="motherName" name="motherName" class="form-input" value="<?php echo htmlspecialchars($student['mother_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="motherAadhar" class="form-label">Mother's Aadhar Number</label>
                            <input type="text" id="motherAadhar" name="motherAadhar" class="form-input" maxlength="12" pattern="[0-9]{12}" value="<?php echo htmlspecialchars($student['mother_aadhar_number'] ?? ''); ?>" placeholder="Enter 12-digit Aadhar number">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h2 class="section-title">Additional Information</h2>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="medicalConditions" class="form-label">Medical Conditions (if any)</label>
                        <textarea id="medicalConditions" name="medicalConditions" class="form-input" rows="3"><?php echo htmlspecialchars($student['medical_conditions'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="student_profile.php?id=<?php echo $student['user_id']; ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                </div>
            </form>
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
            const classSelect = document.getElementById('class');
            const sectionSelect = document.getElementById('section');
            const academicYearSelect = document.getElementById('academicYear');
            
            // Current values from PHP
            const currentClassId = <?php echo $student['class_id']; ?>;
            const currentSectionId = <?php echo $student['section_id']; ?>;
            const currentAcademicYearId = <?php echo $student['academic_year_id']; ?>;
            
            // Fetch classes on page load
            fetch('student_actions.php?fetch_classes=1')
                .then(res => res.json())
                .then(classes => {
                    classSelect.innerHTML = '<option value="">Select Class</option>';
                    classes.forEach(cls => {
                        const selected = cls.id == currentClassId ? 'selected' : '';
                        classSelect.innerHTML += `<option value="${cls.id}" ${selected}>${cls.name}</option>`;
                    });
                    
                    // Load sections for current class
                    if (currentClassId) {
                        loadSections(currentClassId, currentSectionId);
                    }
                });
                
            // Fetch academic years on page load
            fetch('student_actions.php?fetch_academic_years=1')
                .then(res => res.json())
                .then(years => {
                    academicYearSelect.innerHTML = '<option value="">Select Academic Year</option>';
                    years.forEach(year => {
                        const isCurrent = year.is_current ? ' (Current)' : '';
                        const selected = year.id == currentAcademicYearId ? 'selected' : '';
                        academicYearSelect.innerHTML += `<option value="${year.id}" ${selected}>${year.name}${isCurrent}</option>`;
                    });
                });
                
            // Function to load sections
            function loadSections(classId, selectedSectionId = null) {
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                if (!classId) return;
                
                fetch('student_actions.php?fetch_sections=' + classId)
                    .then(res => res.json())
                    .then(sections => {
                        sections.forEach(sec => {
                            const selected = sec.id == selectedSectionId ? 'selected' : '';
                            sectionSelect.innerHTML += `<option value="${sec.id}" ${selected}>${sec.name}</option>`;
                        });
                });
            }
                
            // Fetch sections when class changes
            classSelect.addEventListener('change', function() {
                loadSections(this.value);
            });
            
            // AJAX form submission for Edit Student
            const form = document.querySelector('.form-container');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'edit');
                
                fetch('student_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    let feedback = document.getElementById('student-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.id = 'student-feedback';
                        form.prepend(feedback);
                    }
                    
                    feedback.textContent = data.message;
                    feedback.style.marginBottom = '1rem';
                    feedback.style.padding = '0.75rem 1rem';
                    feedback.style.borderRadius = '6px';
                    feedback.style.fontWeight = 'bold';
                    
                    if (data.success) {
                        feedback.style.background = '#d1fae5';
                        feedback.style.color = '#065f46';
                        
                        // Redirect to profile page after successful update
                        setTimeout(() => {
                            window.location.href = 'student_profile.php?id=' + <?php echo $student['user_id']; ?>;
                        }, 1500);
                    } else {
                        feedback.style.background = '#fee2e2';
                        feedback.style.color = '#991b1b';
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Failed to update student. Please try again.');
                });
            });
        });
    </script>
</body>
</html> 
 