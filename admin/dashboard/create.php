<?php include "sidebar.php"?>

<?php
require_once 'con.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $academicYear = trim($_POST['academic_year'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate input
    $errors = [];
    if (empty($name)) {
        $errors['name'] = 'Class name is required';
    }
    if (empty($academicYear)) {
        $errors['academic_year'] = 'Academic year is required';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO classes (name, academic_year, department, description) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $academicYear, $department, $description);
        
        if ($stmt->execute()) {
            // Redirect to classes management page on success
            header('Location: classesmanage.php?success=1');
            exit;
        } else {
            $errors['db'] = 'Database error: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get list of academic years from the database
$academicYears = [];
$result = $conn->query('SELECT id, name FROM academic_years ORDER BY start_date DESC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $academicYears[] = $row;
    }
    $result->free();
}

// Get list of departments (placeholder data - could be from database)
$departments = [
    'primary' => 'Primary',
    'secondary' => 'Secondary',
    'higher_secondary' => 'Higher Secondary'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Create Class</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .dashboard-content {
            padding: 20px;
        }
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-title {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .form-group {
            flex: 1 1 calc(50% - 20px);
            margin: 0 10px 20px;
            min-width: 200px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-label.required::after {
            content: "*";
            color: #e53e3e;
            margin-left: 4px;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
        }
        .form-error {
            color: #e53e3e;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn {
            padding: 10px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #ddd;
            color: #555;
        }
        .btn-outline:hover {
            background: #f9f9f9;
        }
        .btn-primary {
            background: #4299e1;
            border: 1px solid #3182ce;
            color: white;
        }
        .btn-primary:hover {
            background: #3182ce;
        }
    </style>
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
            <h1 class="header-title">Create New Class</h1>
            <span class="header-path">Dashboard > Classes > Create</span>
        </header>

        <main class="dashboard-content">
            <div class="form-container">
                <h2 class="form-title">Class Information</h2>
                
                <?php if (!empty($errors['db'])): ?>
                <div style="background-color: #FEE2E2; color: #B91C1C; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($errors['db']); ?>
                </div>
                <?php endif; ?>
                
                <form action="create.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label required">Class Name</label>
                            <input type="text" id="name" name="name" class="form-input" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            <?php if (!empty($errors['name'])): ?>
                                <div class="form-error"><?php echo htmlspecialchars($errors['name']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="academic_year" class="form-label required">Academic Year</label>
                            <?php if (!empty($academicYears)): ?>
                                <select id="academic_year" name="academic_year" class="form-select" required>
                                    <option value="">Select Academic Year</option>
                                    <?php foreach ($academicYears as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year['name']); ?>" <?php echo (isset($_POST['academic_year']) && $_POST['academic_year'] == $year['name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" id="academic_year" name="academic_year" class="form-input" placeholder="e.g. 2023-2024" value="<?php echo isset($_POST['academic_year']) ? htmlspecialchars($_POST['academic_year']) : ''; ?>" required>
                            <?php endif; ?>
                            <?php if (!empty($errors['academic_year'])): ?>
                                <div class="form-error"><?php echo htmlspecialchars($errors['academic_year']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="department" class="form-label">Department</label>
                            <select id="department" name="department" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $value => $label): ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($_POST['department']) && $_POST['department'] == $value) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <!-- This space can be used for additional fields in the future -->
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 1 1 100%;">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-textarea" placeholder="Enter class description (optional)"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="classesmanage.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Class</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        }
    </script>
</body>
</html> 