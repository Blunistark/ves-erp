<?php
// Start session and include required files at the very top
require_once __DIR__ . '/../../includes/functions.php';
startSecureSession();

// Check if user is logged in and is an admin
// if (!isLoggedIn() || !hasRole('admin','headmaster')) {
//     header("Location: ../../index.php");
//     exit;
// }

// Include database connection
require_once 'con.php';

// Get parameters
$class_id = isset($_GET['class']) ? intval($_GET['class']) : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';

// Get class name
$class_name = '';
if ($class_id) {
    $class_res = $conn->query("SELECT name FROM classes WHERE id = $class_id LIMIT 1");
    if ($class_res && $class_res->num_rows > 0) {
        $class_name = 'Class ' . $class_res->fetch_assoc()['name'];
    }
}

// Fetch sections for the class
$sections = [];
if ($class_id) {
    $res = $conn->query("SELECT id, name FROM sections WHERE class_id = $class_id ORDER BY name");
    while ($row = $res->fetch_assoc()) {
        $sections[$row['id']] = $row['name'];
    }
}
// Fetch students with attendance and fees data
$students = [];
if ($class_id) {
    $section_filter = '';
    if ($section) {
        $section_safe = $conn->real_escape_string($section);
        $section_id_res = $conn->query("SELECT id FROM sections WHERE class_id = $class_id AND name = '$section_safe' LIMIT 1");
        $section_id = ($section_id_res && $section_id_res->num_rows > 0) ? $section_id_res->fetch_assoc()['id'] : 0;
        if ($section_id) {
            $section_filter = "AND s.section_id = $section_id";
        }
    }
    
    // Complex query to get student details with attendance and fees
    $sql = "SELECT 
                s.*, 
                COALESCE(a.present_count, 0) as present_days,
                COALESCE(a.total_days, 0) as total_days,
                COALESCE(ROUND((a.present_count / NULLIF(a.total_days, 0)) * 100, 1), 0) as attendance_percentage,
                COALESCE(f.total_fees, 0) as total_fees,
                COALESCE(f.paid_amount, 0) as paid_fees,
                COALESCE(f.total_fees - f.paid_amount, 0) as pending_fees,
                CASE 
                    WHEN COALESCE(f.paid_amount, 0) >= COALESCE(f.total_fees, 0) THEN 'paid'
                    WHEN COALESCE(f.paid_amount, 0) > 0 THEN 'partial'
                    ELSE 'pending'
                END as fee_status
            FROM students s
            LEFT JOIN (
                SELECT 
                    student_user_id,
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                    COUNT(*) as total_days
                FROM attendance 
                WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY student_user_id
            ) a ON s.user_id = a.student_user_id
            LEFT JOIN (
                SELECT 
                    fp.student_user_id,
                    SUM(fs.amount) as total_fees,
                    SUM(fp.amount_paid) as paid_amount
                FROM fee_payments fp
                JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                WHERE fs.academic_year_id = (SELECT id FROM academic_years WHERE is_current = 1)
                GROUP BY fp.student_user_id
            ) f ON s.user_id = f.student_user_id
            WHERE s.class_id = $class_id $section_filter
            ORDER BY s.full_name";
            
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}

// Section/class summary for specific section
$section_summary = null;
if ($class_id && $section) {
    $section_safe = $conn->real_escape_string($section);
    $summary_sql = "SELECT s.id, s.name AS section_name, s.class_id, s.class_teacher_user_id, 
                     COUNT(st.user_id) AS total_students, 
                     COUNT(CASE WHEN st.gender_code IN ('M', 'MALE') THEN 1 END) AS boys, 
                     COUNT(CASE WHEN st.gender_code IN ('F', 'FEMALE') THEN 1 END) AS girls 
                    FROM sections s 
                    LEFT JOIN students st ON s.id = st.section_id 
                    LEFT JOIN teachers t ON s.class_teacher_user_id = t.user_id 
                    LEFT JOIN users u ON t.user_id = u.id
                    WHERE s.class_id = $class_id AND s.name = '$section_safe' 
                    GROUP BY s.id, s.name, s.class_id, s.class_teacher_user_id";
    $summary_res = $conn->query($summary_sql);
    if ($summary_res && $summary_res->num_rows > 0) {
        $section_summary = $summary_res->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $class_name; ?> Students</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/classessections.css">
    <style>
    .dashboard-content {
        padding: 2rem;
    }
    
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .student-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }
    
    .student-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .student-header {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .student-photo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        margin-right: 1rem;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    
    .student-info {
        flex: 1;
    }
    
    .student-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .student-id {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .student-details {
        padding: 1.5rem;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .detail-label {
        color: #64748b;
        font-size: 0.875rem;
    }
    
    .detail-value {
        font-weight: 500;
        color: #1e293b;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .status-paid {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-partial {
        background: #fef9c3;
        color: #854d0e;
    }
    
    .status-pending {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .attendance-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        margin-top: 0.5rem;
    }
    
    .attendance-progress {
        height: 100%;
        background: #3b82f6;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    .section-summary {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .summary-item {
        text-align: center;
    }
    
    .summary-value {
        font-size: 2rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .summary-label {
        color: #64748b;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .filters-bar {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding: 1rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filter-select {
        min-width: 200px;
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
    }
    
    .action-button {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        background: #3b82f6;
        color: white;
        transition: background-color 0.2s;
    }
    
    .action-button:hover {
        background: #2563eb;
    }
    
    @media (max-width: 768px) {
        .students-grid {
            grid-template-columns: 1fr;
        }
        
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filters-bar {
            flex-direction: column;
        }
        
        .filter-select {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <div class="sidebar-overlay"></div>
    <?php include 'sidebar.php'; ?>
    
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1><?php echo $class_name; ?> Students</h1>
            <p class="breadcrumb">Dashboard > Classes > <?php echo $class_name; ?></p>
        </header>
        
        <main class="dashboard-content">
            <?php if ($section_summary): ?>
            <div class="section-summary">
                <h2><?php echo $class_name; ?> - Section <?php echo strtoupper(htmlspecialchars($section_summary['section_name'])); ?></h2>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value"><?php echo $section_summary['total_students']; ?></div>
                        <div class="summary-label">Total Students</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo $section_summary['boys']; ?></div>
                        <div class="summary-label">Boys</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo $section_summary['girls']; ?></div>
                        <div class="summary-label">Girls</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="filters-bar">
                <form method="get" action="students.php" style="display: flex; gap: 1rem; align-items: center; flex: 1;">
                    <input type="hidden" name="class" value="<?php echo $class_id; ?>">
                    <select name="section" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sid => $sname): ?>
                            <option value="<?php echo htmlspecialchars($sname); ?>" 
                                    <?php if ($section == $sname) echo 'selected'; ?>>
                                Section <?php echo htmlspecialchars(strtoupper($sname)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <button class="action-button" onclick="location.href='add_student.php'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14m-7-7h14"/>
                    </svg>
                    Add Student
                </button>
                <button class="action-button" onclick="exportStudents()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m4-5l5 5 5-5m-5 5V3"/>
                    </svg>
                    Export CSV
                </button>
            </div>
            
            <div class="students-grid">
            <?php foreach ($students as $student): ?>
                <div class="student-card" onclick="location.href='student_profile.php?id=<?php echo $student['user_id']; ?>'">
                    <div class="student-header">
                        <img src="<?php echo $student['photo'] ? htmlspecialchars($student['photo']) : 
                                'https://ui-avatars.com/api/?name=' . urlencode($student['full_name']) . '&background=f8fafc&color=64748b'; ?>" 
                             alt="<?php echo htmlspecialchars($student['full_name']); ?>" 
                             class="student-photo">
                        <div class="student-info">
                            <div class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                            <div class="student-id"><?php echo htmlspecialchars($student['admission_number']); ?></div>
                        </div>
                    </div>
                    <div class="student-details">
                        <div class="detail-row">
                            <span class="detail-label">Attendance</span>
                            <span class="detail-value"><?php echo $student['attendance_percentage']; ?>%</span>
                        </div>
                        <div class="attendance-bar">
                            <div class="attendance-progress" style="width: <?php echo $student['attendance_percentage']; ?>%"></div>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Days Present</span>
                            <span class="detail-value"><?php echo $student['present_days']; ?>/<?php echo $student['total_days']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Fees Status</span>
                            <span class="status-badge status-<?php echo $student['fee_status']; ?>">
                                <?php echo ucfirst($student['fee_status']); ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Fees Paid</span>
                            <span class="detail-value">₹<?php echo number_format($student['paid_fees']); ?>/₹<?php echo number_format($student['total_fees']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($students)): ?>
            <div style="text-align: center; padding: 3rem; color: #64748b;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"></path>
                </svg>
                <p style="margin-top: 1rem; font-size: 1.1rem;">No students found for this class<?php echo $section ? ' and section ' . htmlspecialchars(strtoupper($section)) : ''; ?>.</p>
                <button class="action-button" style="margin-top: 1rem;" onclick="location.href='add_student.php'">Add Student</button>
            </div>
            <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
    function exportStudents() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = `export_students.php?${params.toString()}`;
    }
    </script>
</body>
</html> 