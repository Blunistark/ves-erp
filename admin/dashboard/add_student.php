<?php
if (isset($_GET['id']) || isset($_GET['mode'])) {
    // Redirect to manage_student.php if id or mode is present
    header('Location: manage_student.php');
    exit;
}
?>
<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    
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
            <h1 class="header-title">Add New Student</h1>
            <span class="header-path">Dashboard > Students > Add New</span>
        </header>

        <main class="dashboard-content">
            <form class="form-container" id="addStudentForm">
                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName" class="form-label required">First Name</label>
                            <input type="text" id="firstName" name="firstName" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="lastName" class="form-label required">Last Name</label>
                            <input type="text" id="lastName" name="lastName" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateOfBirth" class="form-label required">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label required">Gender</label>
                            <select id="gender" name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bloodGroup" class="form-label">Blood Group</label>
                            <select id="bloodGroup" name="bloodGroup" class="form-select">
                                <option value="">Select Blood Group</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" id="religion" name="religion" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" id="nationality" name="nationality" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="photo" class="form-label">Student Photo</label>
                            <div class="file-input-wrapper">
                                <label for="photo" class="file-input-label">
                                    <div class="file-input-text">
                                        <svg class="file-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Click to upload</span>
                                        <span class="help-text">JPEG, PNG or JPG (MAX. 2MB)</span>
                                    </div>
                                </label>
                                <input type="file" id="photo" name="photo" class="file-input" accept="image/jpeg, image/png, image/jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="form-section">
                    <h2 class="section-title">Academic Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admissionNo" class="form-label required">Admission Number</label>
                            <input type="text" id="admissionNo" name="admissionNo" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="admissionDate" class="form-label required">Admission Date</label>
                            <input type="date" id="admissionDate" name="admissionDate" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="rollNumber" class="form-label">Roll Number</label>
                            <input type="text" id="rollNumber" name="rollNumber" class="form-input">
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
                            <input type="tel" id="phone" name="phone" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address" class="form-label required">Address</label>
                            <input type="text" id="address" name="address" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="city" class="form-label required">City</label>
                            <input type="text" id="city" name="city" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="postalCode" class="form-label">Postal Code</label>
                            <input type="text" id="postalCode" name="postalCode" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Parent/Guardian Information -->
                <div class="form-section">
                    <h2 class="section-title">Parent/Guardian Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherName" class="form-label">Father's Name</label>
                            <input type="text" id="fatherName" name="fatherName" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="fatherOccupation" class="form-label">Father's Occupation</label>
                            <input type="text" id="fatherOccupation" name="fatherOccupation" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="fatherPhone" class="form-label">Father's Phone</label>
                            <input type="tel" id="fatherPhone" name="fatherPhone" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherEmail" class="form-label">Father's Email</label>
                            <input type="email" id="fatherEmail" name="fatherEmail" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="fatherAadhar" class="form-label">Father's Aadhar Number</label>
                            <input type="text" id="fatherAadhar" name="fatherAadhar" class="form-input" maxlength="12" pattern="[0-9]{12}" placeholder="Enter 12-digit Aadhar number">
                        </div>
                        <div class="form-group">
                            <!-- Removed parent account creation option -->
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherName" class="form-label">Mother's Name</label>
                            <input type="text" id="motherName" name="motherName" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="motherOccupation" class="form-label">Mother's Occupation</label>
                            <input type="text" id="motherOccupation" name="motherOccupation" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="motherPhone" class="form-label">Mother's Phone</label>
                            <input type="tel" id="motherPhone" name="motherPhone" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherEmail" class="form-label">Mother's Email</label>
                            <input type="email" id="motherEmail" name="motherEmail" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="motherAadhar" class="form-label">Mother's Aadhar Number</label>
                            <input type="text" id="motherAadhar" name="motherAadhar" class="form-input" maxlength="12" pattern="[0-9]{12}" placeholder="Enter 12-digit Aadhar number">
                        </div>
                        <div class="form-group">
                            <!-- Removed parent account creation option -->
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="guardianName" class="form-label">Guardian's Name (If applicable)</label>
                            <input type="text" id="guardianName" name="guardianName" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="guardianRelation" class="form-label">Relationship with Guardian</label>
                            <input type="text" id="guardianRelation" name="guardianRelation" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="guardianPhone" class="form-label">Guardian's Phone</label>
                            <input type="tel" id="guardianPhone" name="guardianPhone" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="guardianEmail" class="form-label">Guardian's Email</label>
                            <input type="email" id="guardianEmail" name="guardianEmail" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="guardianAadhar" class="form-label">Guardian's Aadhar Number</label>
                            <input type="text" id="guardianAadhar" name="guardianAadhar" class="form-input" maxlength="12" pattern="[0-9]{12}" placeholder="Enter 12-digit Aadhar number">
                        </div>
                        <div class="form-group">
                            <!-- Removed guardian account creation option -->
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h2 class="section-title">Additional Information</h2>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="medicalConditions" class="form-label">Medical Conditions (if any)</label>
                        <textarea id="medicalConditions" name="medicalConditions" class="form-input" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline">Cancel</button>
                    <button type="button" class="btn btn-outline">Save as Draft</button>
                    <button type="submit" class="btn btn-primary">Add Student</button>
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

        // Display file name when selected
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('photo');
            const fileLabel = document.querySelector('.file-input-text');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const fileName = this.files[0].name;
                    fileLabel.innerHTML = `
                        <svg class="file-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>${fileName}</span>
                        <button type="button" class="btn-remove-file">Remove</button>
                    `;
                    
                    const removeBtn = document.querySelector('.btn-remove-file');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            fileInput.value = '';
                            fileLabel.innerHTML = `
                                <svg class="file-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Click to upload</span>
                                <span class="help-text">JPEG, PNG or JPG (MAX. 2MB)</span>
                            `;
                        });
                    }
                }
            });

            // Removed parent account creation toggle functionality
            
            // AJAX form submission for Add Student
            const form = document.querySelector('.form-container');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'add');
                
                // Removed parent account password validation
                
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
                        form.reset();
                    } else {
                        feedback.style.background = '#fee2e2';
                        feedback.style.color = '#991b1b';
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Failed to add student. Please try again.');
                });
            });

            // Dynamic class and section dropdowns
            const classSelect = document.getElementById('class');
            const sectionSelect = document.getElementById('section');
            const academicYearSelect = document.getElementById('academicYear');
            
            // Fetch classes on page load
            fetch('student_actions.php?fetch_classes=1')
                .then(res => res.json())
                .then(classes => {
                    classSelect.innerHTML = '<option value="">Select Class</option>';
                    classes.forEach(cls => {
                        classSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                    });
                });
                
            // Fetch academic years on page load
            fetch('student_actions.php?fetch_academic_years=1')
                .then(res => res.json())
                .then(years => {
                    academicYearSelect.innerHTML = '<option value="">Select Academic Year</option>';
                    years.forEach(year => {
                        const isCurrent = year.is_current ? ' (Current)' : '';
                        academicYearSelect.innerHTML += `<option value="${year.id}">${year.name}${isCurrent}</option>`;
                    });
                });
                
            // Fetch sections when class changes
            classSelect.addEventListener('change', function() {
                const classId = this.value;
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                if (!classId) return;
                fetch('student_actions.php?fetch_sections=' + classId)
                    .then(res => res.json())
                    .then(sections => {
                        sections.forEach(sec => {
                            sectionSelect.innerHTML += `<option value="${sec.id}">${sec.name}</option>`;
                        });
                });
            });
        });
    </script>
</body>
</html>