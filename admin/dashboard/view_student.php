<?php
include 'con.php';
include 'sidebar.php';
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) { header('Location: manage_student.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { header('Location: manage_student.php'); exit; }
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect form data
    $first_name = $_POST['firstName'];
    $middle_name = $_POST['middleName'];
    $last_name = $_POST['lastName'];
    $dob = $_POST['dateOfBirth'];
    $gender = $_POST['gender'];
    $blood_group = $_POST['bloodGroup'];
    $religion = $_POST['religion'];
    $standard = $_POST['standard'] ?? null;
    $section = $_POST['section'] ?? null;
    $aadhar = $_POST['aadharCard'] ?? null;
    $medical = $_POST['medicalConditions'] ?? null;
    $sats = $_POST['satsNumber'] ?? null;
    $father_name = $_POST['fatherFullName'] ?? null;
    $father_mobile = $_POST['fatherMobileNumber'] ?? null;
    $father_email = $_POST['fatherEmail'] ?? null;
    $father_aadhar = $_POST['fatherAadharNumber'] ?? null;
    $mother_name = $_POST['motherFullName'] ?? null;
    $mother_mobile = $_POST['motherMobileNumber'] ?? null;
    $mother_email = $_POST['motherEmail'] ?? null;
    $mother_aadhar = $_POST['motherAadharNumber'] ?? null;
    $emergency = $_POST['emergencyContact'] ?? null;
    $address = $_POST['residentialAddress'] ?? null;
    $caste = $_POST['caste'] ?? null;
    // handle photo upload
    $photo_path = $student['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = time() . '_' . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $photo_path = 'uploads/' . $filename;
        }
    }
    // update record
    $sql = 'UPDATE students SET first_name=?,middle_name=?,last_name=?,dob=?,gender=?,blood_group=?,religion=?,standard=?,section=?,aadhar_card_number=?,medical_conditions=?,sats_number=?,father_full_name=?,father_mobile_number=?,father_email=?,father_aadhar_card_number=?,mother_full_name=?,mother_mobile_number=?,mother_email=?,mother_aadhar_card_number=?,emergency_contact_number=?,residential_address=?,caste=?,photo=? WHERE id=?';
    $params = [$first_name,$middle_name,$last_name,$dob,$gender,$blood_group,$religion,$standard,$section,$aadhar,$medical,$sats,$father_name,$father_mobile,$father_email,$father_aadhar,$mother_name,$mother_mobile,$mother_email,$mother_aadhar,$emergency,$address,$caste,$photo_path,$id];
    $upd = $pdo->prepare($sql);
    $upd->execute($params);
    $message = 'Student updated successfully';
    // refetch
    $stmt->execute([$id]);
    $student = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View/Edit Student</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/view_student.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    <button class="hamburger-btn" onclick="toggleSidebar()"><svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg></button>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">View/Edit Student</h1>
            <span class="header-path">Dashboard > Students > View/Edit</span>
        </header>
        <main class="dashboard-content">
            <?php if ($message): ?><div class="alert-message"><?= $message ?></div><?php endif; ?>
            <form class="form-container" method="POST" action="?id=<?= htmlspecialchars($id) ?>" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" id="firstName" name="firstName" class="form-input" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" class="form-input" value="<?= htmlspecialchars($student['middle_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" id="lastName" name="lastName" class="form-input" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateOfBirth" class="form-label">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" value="<?= htmlspecialchars($student['dob']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <?php
                                $genderOptions = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
                                foreach ($genderOptions as $value => $label) {
                                    $selected = strtolower($student['gender']) === $value ? 'selected' : '';
                                    echo "<option value=\"{$value}\" {$selected}>{$label}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bloodGroup" class="form-label">Blood Group</label>
                            <select id="bloodGroup" name="bloodGroup" class="form-select">
                                <?php
                                foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg) {
                                    $sel = $student['blood_group']===$bg?'selected':'';
                                    echo "<option value=\"$bg\" $sel>$bg</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" id="religion" name="religion" class="form-input" value="<?= htmlspecialchars($student['religion']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="caste" class="form-label">Caste</label>
                            <input type="text" id="caste" name="caste" class="form-input" value="<?= htmlspecialchars($student['caste']) ?>">
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h2 class="section-title">Academic Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="standard" class="form-label">Class</label>
                            <select id="standard" name="standard" class="form-select">
                                <?php for($i=1;$i<=12;$i++){ $sel = $student['standard']==$i?'selected':''; echo "<option value=\"$i\" $sel>Class $i</option>"; } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="section" class="form-label">Section</label>
                            <select id="section" name="section" class="form-select">
                                <?php foreach(['A','B','C','D'] as $sec){ $sel=$student['section']===$sec?'selected':''; echo "<option value=\"$sec\" $sel>$sec</option>";}?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h2 class="section-title">Contact Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="aadharCard" class="form-label">Aadhar Card Number</label>
                            <input type="text" id="aadharCard" name="aadharCard" class="form-input" value="<?= htmlspecialchars($student['aadhar_card_number']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="medicalConditions" class="form-label">Medical Conditions</label>
                            <input type="text" id="medicalConditions" name="medicalConditions" class="form-input" value="<?= htmlspecialchars($student['medical_conditions']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="satsNumber" class="form-label">SATS Number</label>
                            <input type="text" id="satsNumber" name="satsNumber" class="form-input" value="<?= htmlspecialchars($student['sats_number']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="emergencyContact" class="form-label">Emergency Contact Number</label>
                            <input type="text" id="emergencyContact" name="emergencyContact" class="form-input" value="<?= htmlspecialchars($student['emergency_contact_number']) ?>">
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h2 class="section-title">Parent/Guardian Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherFullName" class="form-label">Father's Full Name</label>
                            <input type="text" id="fatherFullName" name="fatherFullName" class="form-input" value="<?= htmlspecialchars($student['father_full_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="fatherMobileNumber" class="form-label">Father's Mobile Number</label>
                            <input type="text" id="fatherMobileNumber" name="fatherMobileNumber" class="form-input" value="<?= htmlspecialchars($student['father_mobile_number']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fatherEmail" class="form-label">Father's Email</label>
                            <input type="email" id="fatherEmail" name="fatherEmail" class="form-input" value="<?= htmlspecialchars($student['father_email']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="fatherAadharNumber" class="form-label">Father's Aadhar Number</label>
                            <input type="text" id="fatherAadharNumber" name="fatherAadharNumber" class="form-input" value="<?= htmlspecialchars($student['father_aadhar_card_number']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherFullName" class="form-label">Mother's Full Name</label>
                            <input type="text" id="motherFullName" name="motherFullName" class="form-input" value="<?= htmlspecialchars($student['mother_full_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="motherMobileNumber" class="form-label">Mother's Mobile Number</label>
                            <input type="text" id="motherMobileNumber" name="motherMobileNumber" class="form-input" value="<?= htmlspecialchars($student['mother_mobile_number']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="motherEmail" class="form-label">Mother's Email</label>
                            <input type="email" id="motherEmail" name="motherEmail" class="form-input" value="<?= htmlspecialchars($student['mother_email']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="motherAadharNumber" class="form-label">Mother's Aadhar Number</label>
                            <input type="text" id="motherAadharNumber" name="motherAadharNumber" class="form-input" value="<?= htmlspecialchars($student['mother_aadhar_card_number']) ?>">
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h2 class="section-title">Additional Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="residentialAddress" class="form-label">Residential Address</label>
                            <textarea id="residentialAddress" name="residentialAddress" class="form-input"><?= htmlspecialchars($student['residential_address']) ?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" id="photo" name="photo" class="form-input">
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="window.location='manage_student.php'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </main>
    </div>
    <script>
        function toggleSidebar(){ document.getElementById('sidebar').classList.toggle('show'); document.body.classList.toggle('sidebar-open'); }
    </script>
</body>
</html>
