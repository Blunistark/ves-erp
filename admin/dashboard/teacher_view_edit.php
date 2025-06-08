<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'con.php';

$teacher_id = $_GET['id'] ?? '';
$mode = $_GET['mode'] ?? 'view'; // 'view' or 'edit'

if (empty($teacher_id)) {
    header('Location: teachersmanage.php');
    exit;
}

// Fetch teacher data
$sql = "
    SELECT 
        t.user_id,
        u.full_name,
        u.email,
        u.status,
        t.employee_number,
        t.qualification,
        t.date_of_birth,
        t.joined_date,
        t.address,
        t.city,
        t.profile_photo
    FROM teachers t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: teachersmanage.php');
    exit;
}

                $teacher = $result->fetch_assoc();
        $stmt->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mode === 'edit' ? 'Edit' : 'View'; ?> Teacher</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/teachersadd.css">
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
            <h1 class="header-title"><?php echo $mode === 'edit' ? 'Edit' : 'View'; ?> Teacher</h1>
            <span class="header-path">Dashboard > Teachers > <?php echo $mode === 'edit' ? 'Edit' : 'View'; ?></span>
        </header>

        <main class="dashboard-content">
            <div class="form-container">
                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($teacher['full_name']); ?>" <?php echo $mode === 'view' ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employee Number</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($teacher['employee_number']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-input" value="<?php echo $teacher['date_of_birth']; ?>" <?php echo $mode === 'view' ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Joining Date</label>
                            <input type="date" class="form-input" value="<?php echo $teacher['joined_date']; ?>" <?php echo $mode === 'view' ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="form-section">
                    <h2 class="section-title">Professional Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Qualification</label>
                            <select class="form-select" <?php echo $mode === 'view' ? 'disabled' : ''; ?>>
                                <option value="phd" <?php echo $teacher['qualification'] === 'phd' ? 'selected' : ''; ?>>Ph.D</option>
                                <option value="masters" <?php echo $teacher['qualification'] === 'masters' ? 'selected' : ''; ?>>Masters</option>
                                <option value="bachelors" <?php echo $teacher['qualification'] === 'bachelors' ? 'selected' : ''; ?>>Bachelors</option>
                                <option value="diploma" <?php echo $teacher['qualification'] === 'diploma' ? 'selected' : ''; ?>>Diploma</option>
                                <option value="certificate" <?php echo $teacher['qualification'] === 'certificate' ? 'selected' : ''; ?>>Certificate</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" <?php echo $mode === 'view' ? 'disabled' : ''; ?>>
                                <option value="active" <?php echo $teacher['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $teacher['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>


                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h2 class="section-title">Contact Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" value="<?php echo htmlspecialchars($teacher['email']); ?>" <?php echo $mode === 'view' ? 'readonly' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($teacher['address'] ?? ''); ?>" <?php echo $mode === 'view' ? 'readonly' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($teacher['city'] ?? ''); ?>" <?php echo $mode === 'view' ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="teachersmanage.php" class="btn btn-outline">Back to Teachers</a>
                    <?php if ($mode === 'view'): ?>
                        <a href="teacher_view_edit.php?id=<?php echo $teacher_id; ?>&mode=edit" class="btn btn-primary">Edit Teacher</a>
                    <?php else: ?>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <?php endif; ?>
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
    </script>
</body>
</html> 