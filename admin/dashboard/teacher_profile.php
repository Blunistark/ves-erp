<?php
// Include required files and start session
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../../index.php");
    exit;
}

// Include database connection
require_once 'con.php';

// Get teacher ID
$teacher_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$teacher_id) {
    header("Location: teachersmanage.php");
    exit;
}

// Fetch teacher details with comprehensive information
$sql = "SELECT 
            t.*,
            u.full_name,
            u.email,
            u.status as user_status,
            u.created_at as account_created,
            COALESCE(class_count.total_classes, 0) as total_classes,
            COALESCE(subject_count.total_subjects, 0) as total_subjects,
            COALESCE(student_count.total_students, 0) as total_students,
            class_teacher.class_name as class_teacher_of
        FROM teachers t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN (
            SELECT 
                ta.teacher_user_id,
                COUNT(DISTINCT CONCAT(ta.class_id, '-', ta.section_id)) as total_classes
            FROM teacher_assignments ta
            GROUP BY ta.teacher_user_id
        ) class_count ON t.user_id = class_count.teacher_user_id
        LEFT JOIN (
            SELECT 
                ts.teacher_user_id,
                COUNT(DISTINCT ts.subject_id) as total_subjects
            FROM teacher_subjects ts
            GROUP BY ts.teacher_user_id
        ) subject_count ON t.user_id = subject_count.teacher_user_id
        LEFT JOIN (
            SELECT 
                ta.teacher_user_id,
                COUNT(DISTINCT s.user_id) as total_students
            FROM teacher_assignments ta
            JOIN students s ON ta.class_id = s.class_id AND ta.section_id = s.section_id
            GROUP BY ta.teacher_user_id
        ) student_count ON t.user_id = student_count.teacher_user_id
        LEFT JOIN (
            SELECT 
                s.class_teacher_user_id,
                CONCAT(c.name, ' - ', s.name) as class_name
            FROM sections s
            JOIN classes c ON s.class_id = c.id
            WHERE s.class_teacher_user_id IS NOT NULL
        ) class_teacher ON t.user_id = class_teacher.class_teacher_user_id
        WHERE t.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: teachersmanage.php");
    exit;
}

$teacher = $result->fetch_assoc();
$stmt->close();

// Fetch subjects taught by this teacher
$subjects_sql = "SELECT s.name, s.code 
                FROM subjects s 
                JOIN teacher_subjects ts ON s.id = ts.subject_id 
                WHERE ts.teacher_user_id = ?
                ORDER BY s.name";
$subjects_stmt = $conn->prepare($subjects_sql);
$subjects_stmt->bind_param('i', $teacher_id);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}
$subjects_stmt->close();

// Fetch classes assigned to this teacher
$classes_sql = "SELECT DISTINCT 
                    CONCAT(c.name, ' - ', sec.name) as class_section,
                    c.name as class_name,
                    sec.name as section_name,
                    COUNT(s.user_id) as student_count
                FROM teacher_assignments ta
                JOIN classes c ON ta.class_id = c.id
                JOIN sections sec ON ta.section_id = sec.id
                LEFT JOIN students s ON ta.class_id = s.class_id AND ta.section_id = s.section_id
                WHERE ta.teacher_user_id = ?
                GROUP BY ta.class_id, ta.section_id, c.name, sec.name
                ORDER BY c.name, sec.name";
$classes_stmt = $conn->prepare($classes_sql);
$classes_stmt->bind_param('i', $teacher_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
$classes = [];
while ($row = $classes_result->fetch_assoc()) {
    $classes[] = $row;
}
$classes_stmt->close();

// Include sidebar
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($teacher['full_name'] ?? 'Teacher'); ?> - Teacher Profile</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .dashboard-container {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8fafc;
        }

        .profile-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .teacher-photo-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e2e8f0;
        }

        .teacher-basic-info h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .teacher-meta {
            display: flex;
            gap: 2rem;
            color: #64748b;
            font-size: 0.875rem;
            flex-wrap: wrap;
        }

        .profile-actions {
            margin-left: auto;
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-outline {
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .btn-outline:hover {
            background: #f8fafc;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .info-value {
            color: #1e293b;
            font-weight: 500;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
            width: fit-content;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .stats-card {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .subject-tag {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            margin: 0.25rem;
        }

        .class-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .class-name {
            font-weight: 500;
            color: #1e293b;
        }

        .student-count {
            background: #3b82f6;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .breadcrumb {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .breadcrumb a {
            color: #3b82f6;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .experience-years {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-radius: 12px;
        }

        .experience-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
                padding: 1rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-content {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .teacher-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
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
        <div class="breadcrumb">
            <a href="index.php">Dashboard</a> > 
            <a href="teachersmanage.php">Teachers</a> > 
            <?php echo htmlspecialchars($teacher['full_name'] ?? 'Teacher'); ?>
        </div>

        <div class="profile-header">
            <img src="<?php echo $teacher['profile_photo'] ? htmlspecialchars($teacher['profile_photo']) : 
                        'https://ui-avatars.com/api/?name=' . urlencode($teacher['full_name'] ?? 'Teacher') . '&background=f8fafc&color=64748b&size=120'; ?>" 
                 alt="<?php echo htmlspecialchars($teacher['full_name'] ?? 'Teacher'); ?>" 
                 class="teacher-photo-large">
            
            <div class="teacher-basic-info">
                <h1><?php echo htmlspecialchars($teacher['full_name'] ?? 'Teacher'); ?></h1>
                <div class="teacher-meta">
                    <span><strong>Employee ID:</strong> <?php echo htmlspecialchars($teacher['employee_number'] ?? 'N/A'); ?></span>
                    <span><strong>Qualification:</strong> <?php echo htmlspecialchars($teacher['qualification'] ?? 'N/A'); ?></span>
                    <span><strong>Joined:</strong> <?php echo date('d M Y', strtotime($teacher['joined_date'])); ?></span>
                    <?php if ($teacher['class_teacher_of']): ?>
                        <span><strong>Class Teacher:</strong> <?php echo htmlspecialchars($teacher['class_teacher_of']); ?></span>
                    <?php endif; ?>
                    <span class="status-badge status-<?php echo $teacher['user_status']; ?>">
                        <?php echo ucfirst($teacher['user_status']); ?>
                    </span>
                </div>
            </div>

            <div class="profile-actions">
                <a href="teacher_view_edit.php?id=<?php echo $teacher['user_id']; ?>&mode=edit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit Teacher
                </a>
                <button class="btn btn-outline" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print Profile
                </button>
            </div>
        </div>

        <div class="profile-content">
            <div class="main-details">
                <!-- Personal Information -->
                <div class="profile-section">
                    <h2 class="section-title">Personal Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value"><?php echo $teacher['date_of_birth'] ? date('d M Y', strtotime($teacher['date_of_birth'])) : 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?php echo htmlspecialchars($teacher['email'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address</span>
                            <span class="info-value"><?php echo $teacher['address'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">City</span>
                            <span class="info-value"><?php echo $teacher['city'] ?: 'Not provided'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="profile-section">
                    <h2 class="section-title">Professional Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Employee Number</span>
                            <span class="info-value"><?php echo htmlspecialchars($teacher['employee_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Qualification</span>
                            <span class="info-value"><?php echo htmlspecialchars($teacher['qualification'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Joining Date</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($teacher['joined_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Created</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($teacher['account_created'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Subjects Taught -->
                <div class="profile-section">
                    <h2 class="section-title">Subjects Taught</h2>
                    <?php if (empty($subjects)): ?>
                        <p style="color: #64748b; font-style: italic;">No subjects assigned yet.</p>
                        <a href="teacher_subject_assign.php" class="btn btn-primary" style="margin-top: 1rem;">
                            Assign Subjects
                        </a>
                    <?php else: ?>
                        <div style="margin-bottom: 1rem;">
                            <?php foreach ($subjects as $subject): ?>
                                <span class="subject-tag">
                                    <?php echo htmlspecialchars($subject['name']); ?>
                                    <small>(<?php echo htmlspecialchars($subject['code']); ?>)</small>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <a href="teacher_subject_assign.php" class="btn btn-outline">
                            Manage Subjects
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Classes Assigned -->
                <div class="profile-section">
                    <h2 class="section-title">Classes Assigned</h2>
                    <?php if (empty($classes)): ?>
                        <p style="color: #64748b; font-style: italic;">No classes assigned yet.</p>
                    <?php else: ?>
                        <?php foreach ($classes as $class): ?>
                            <div class="class-item">
                                <span class="class-name"><?php echo htmlspecialchars($class['class_section']); ?></span>
                                <span class="student-count"><?php echo $class['student_count']; ?> students</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-details">
                <!-- Teaching Statistics -->
                <div class="profile-section">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $teacher['total_subjects']; ?></div>
                        <div class="stats-label">Subjects Teaching</div>
                    </div>
                    <div class="stats-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <div class="stats-number"><?php echo $teacher['total_classes']; ?></div>
                        <div class="stats-label">Classes Assigned</div>
                    </div>
                    <div class="stats-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <div class="stats-number"><?php echo $teacher['total_students']; ?></div>
                        <div class="stats-label">Total Students</div>
                    </div>
                </div>

                <!-- Experience -->
                <?php 
                $years_of_service = 0;
                if ($teacher['joined_date']) {
                    $join_date = new DateTime($teacher['joined_date']);
                    $current_date = new DateTime();
                    $interval = $current_date->diff($join_date);
                    $years_of_service = $interval->y;
                }
                ?>
                <div class="profile-section">
                    <div class="experience-years">
                        <div class="experience-number"><?php echo $years_of_service; ?></div>
                        <div class="stats-label">Years of Service</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="profile-section">
                    <h2 class="section-title">Quick Actions</h2>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="teacher_subject_assign.php" class="btn btn-outline" style="justify-content: center;">
                            Manage Subjects
                        </a>
                        <a href="timetable.php?teacher_id=<?php echo $teacher['user_id']; ?>" class="btn btn-outline" style="justify-content: center;">
                            View Timetable
                        </a>
                        <a href="attendance.php?teacher_id=<?php echo $teacher['user_id']; ?>" class="btn btn-outline" style="justify-content: center;">
                            View Attendance
                        </a>
                        <a href="teacher_view_edit.php?id=<?php echo $teacher['user_id']; ?>&mode=edit" class="btn btn-outline" style="justify-content: center;">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
            }
        }
    </script>
</body>
</html> 