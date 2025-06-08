<?php
require_once 'con.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'view';
$student = null;
if ($id) {
    $sql = "SELECT s.*, 
            u.email, u.full_name,
            c.name as class_name, 
            sec.name as section_name,
            g.label as gender_label,
            bg.label as blood_group_label,
            ay.name as academic_year_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN genders g ON s.gender_code = g.code
        LEFT JOIN blood_groups bg ON s.blood_group_code = bg.code
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        WHERE s.user_id = $id LIMIT 1";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Check for name fields in users table if not in students table
        if (empty($student['first_name']) && !empty($student['full_name'])) {
            $nameParts = explode(' ', $student['full_name']);
            $student['first_name'] = $nameParts[0];
            $student['last_name'] = count($nameParts) > 1 ? end($nameParts) : '';
            $student['middle_name'] = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';
        }
    }
}
function val($key, $student) {
    return $student && isset($student[$key]) ? htmlspecialchars($student[$key]) : '';
}
function ro($mode) { return $mode === 'view' ? 'readonly' : ''; }
function dis($mode) { return $mode === 'view' ? 'disabled' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'edit' ? 'Edit' : 'View' ?> Student</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/add_student.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    <?php include 'sidebar.php'; ?>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title"><?= $mode === 'edit' ? 'Edit' : 'View' ?> Student</h1>
            <span class="header-path">Dashboard > Students > <?= $mode === 'edit' ? 'Edit' : 'View' ?></span>
        </header>
        <main class="dashboard-content">
            <form class="form-container" id="studentForm">
                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName" class="form-label required">First Name</label>
                            <input type="text" id="firstName" name="firstName" class="form-input" value="<?= val('first_name',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" class="form-input" value="<?= val('middle_name',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="lastName" class="form-label required">Last Name</label>
                            <input type="text" id="lastName" name="lastName" class="form-input" value="<?= val('last_name',$student) ?>" <?=ro($mode)?> required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateOfBirth" class="form-label required">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" value="<?= val('dob',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label required">Gender</label>
                            <select id="gender" name="gender" class="form-select" <?=dis($mode)?> required>
                                <option value="">Select Gender</option>
                                <option value="M" <?= val('gender_code',$student)==='M'?'selected':'' ?>>Male</option>
                                <option value="F" <?= val('gender_code',$student)==='F'?'selected':'' ?>>Female</option>
                                <option value="O" <?= val('gender_code',$student)==='O'?'selected':'' ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bloodGroup" class="form-label">Blood Group</label>
                            <select id="bloodGroup" name="bloodGroup" class="form-select" <?=dis($mode)?>>
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?= val('blood_group_code',$student)==='A+'?'selected':'' ?>>A+</option>
                                <option value="A-" <?= val('blood_group_code',$student)==='A-'?'selected':'' ?>>A-</option>
                                <option value="B+" <?= val('blood_group_code',$student)==='B+'?'selected':'' ?>>B+</option>
                                <option value="B-" <?= val('blood_group_code',$student)==='B-'?'selected':'' ?>>B-</option>
                                <option value="AB+" <?= val('blood_group_code',$student)==='AB+'?'selected':'' ?>>AB+</option>
                                <option value="AB-" <?= val('blood_group_code',$student)==='AB-'?'selected':'' ?>>AB-</option>
                                <option value="O+" <?= val('blood_group_code',$student)==='O+'?'selected':'' ?>>O+</option>
                                <option value="O-" <?= val('blood_group_code',$student)==='O-'?'selected':'' ?>>O-</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" id="religion" name="religion" class="form-input" value="<?= val('religion',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" id="nationality" name="nationality" class="form-input" value="<?= val('nationality',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="photo" class="form-label">Student Photo</label>
                            <input type="file" id="photo" name="photo" class="file-input" accept="image/jpeg, image/png, image/jpg" <?=dis($mode)?>>
                            <?php if ($student && $student['photo']): ?>
                                <img src="<?= htmlspecialchars($student['photo']) ?>" alt="Student Photo" style="max-width:80px;display:block;margin-top:0.5rem;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Academic Information -->
                <div class="form-section">
                    <h2 class="section-title">Academic Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admissionNo" class="form-label required">Admission Number</label>
                            <input type="text" id="admissionNo" name="admissionNo" class="form-input" value="<?= val('admission_number',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="admissionDate" class="form-label required">Admission Date</label>
                            <input type="date" id="admissionDate" name="admissionDate" class="form-input" value="<?= val('admission_date',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="rollNumber" class="form-label">Roll Number</label>
                            <input type="text" id="rollNumber" name="rollNumber" class="form-input" value="<?= val('roll_number',$student) ?>" <?=ro($mode)?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="class" class="form-label required">Class</label>
                            <input type="text" id="class" name="class" class="form-input" value="<?= val('class_name',$student) ?: val('class_id',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="section" class="form-label required">Section</label>
                            <input type="text" id="section" name="section" class="form-input" value="<?= val('section_name',$student) ?: val('section_id',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="academicYear" class="form-label required">Academic Year</label>
                            <input type="text" id="academicYear" name="academicYear" class="form-input" value="<?= val('academic_year_name',$student) ?: val('academic_year_id',$student) ?>" <?=ro($mode)?> required>
                        </div>
                    </div>
                </div>
                <!-- Contact Information -->
                <div class="form-section">
                    <h2 class="section-title">Contact Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input" value="<?= val('mobile',$student) ?: val('phone',$student) ?>" <?=ro($mode)?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address" class="form-label required">Address</label>
                            <input type="text" id="address" name="address" class="form-input" value="<?= val('address',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="city" class="form-label required">City</label>
                            <input type="text" id="city" name="city" class="form-input" value="<?= val('city',$student) ?>" <?=ro($mode)?> required>
                        </div>
                        <div class="form-group">
                            <label for="postalCode" class="form-label">Postal Code</label>
                            <input type="text" id="postalCode" name="postalCode" class="form-input" value="<?= val('postal_code',$student) ?>" <?=ro($mode)?>>
                        </div>
                    </div>
                </div>
                <!-- Parent/Guardian Information -->
                <div class="form-section">
                    <h2 class="section-title">Parent/Guardian Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherName" class="form-label">Father's Name</label>
                            <input type="text" id="fatherName" name="fatherName" class="form-input" value="<?= val('father_name',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="fatherOccupation" class="form-label">Father's Occupation</label>
                            <input type="text" id="fatherOccupation" name="fatherOccupation" class="form-input" value="<?= val('father_occupation',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="fatherPhone" class="form-label">Father's Phone</label>
                            <input type="tel" id="fatherPhone" name="fatherPhone" class="form-input" value="<?= val('father_phone',$student) ?>" <?=ro($mode)?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherName" class="form-label">Mother's Name</label>
                            <input type="text" id="motherName" name="motherName" class="form-input" value="<?= val('mother_name',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="motherOccupation" class="form-label">Mother's Occupation</label>
                            <input type="text" id="motherOccupation" name="motherOccupation" class="form-input" value="<?= val('mother_occupation',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="motherPhone" class="form-label">Mother's Phone</label>
                            <input type="tel" id="motherPhone" name="motherPhone" class="form-input" value="<?= val('mother_phone',$student) ?>" <?=ro($mode)?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="guardianName" class="form-label">Guardian's Name (If applicable)</label>
                            <input type="text" id="guardianName" name="guardianName" class="form-input" value="<?= val('guardian_name',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="guardianRelation" class="form-label">Relationship with Guardian</label>
                            <input type="text" id="guardianRelation" name="guardianRelation" class="form-input" value="<?= val('guardian_relation',$student) ?>" <?=ro($mode)?>>
                        </div>
                        <div class="form-group">
                            <label for="guardianPhone" class="form-label">Guardian's Phone</label>
                            <input type="tel" id="guardianPhone" name="guardianPhone" class="form-input" value="<?= val('guardian_phone',$student) ?>" <?=ro($mode)?>>
                        </div>
                    </div>
                </div>
                <!-- Additional Information -->
                <div class="form-section">
                    <h2 class="section-title">Additional Information</h2>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="medicalConditions" class="form-label">Medical Conditions (if any)</label>
                        <textarea id="medicalConditions" name="medicalConditions" class="form-input" rows="3" <?=ro($mode)?>><?= val('medical_conditions',$student) ?></textarea>
                    </div>
                </div>
                <!-- Account Information -->
                <div class="form-section">
                    <h2 class="section-title">Account Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-input" value="<?= val('password',$student) ?>" <?=ro($mode)?> autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="manage_student.php" class="btn btn-outline">Cancel</a>
                    <?php if ($mode === 'edit'): ?>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <?php endif; ?>
                </div>
            </form>
        </main>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($mode === 'edit'): ?>
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');
            formData.append('user_id', '<?= $id ?>');
            
            // Debug form data
            console.log('Submitting form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            fetch('student_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    alert(data.message || 'Student updated successfully');
                    document.getElementById('password').value = '';
                    window.location.href = 'manage_student.php';
                } else {
                    alert(data.message || 'Error updating student');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the student');
            });
        });
        <?php endif; ?>
    });
    </script>
</body>
</html> 