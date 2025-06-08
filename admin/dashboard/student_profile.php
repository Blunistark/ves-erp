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

// Get student ID
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$student_id) {
    header("Location: students.php");
    exit;
}

// Fetch student details
$sql = "SELECT 
            s.*,
            c.name as class_name,
            sec.name as section_name,
            ay.name as academic_year,
            u.email,
            u.status as user_status,
            COALESCE(att.present_count, 0) as present_days,
            COALESCE(att.total_days, 0) as total_days,
            COALESCE(ROUND((att.present_count / NULLIF(att.total_days, 0)) * 100, 1), 0) as attendance_percentage,
            COALESCE(f.total_fees, 0) as total_fees,
            COALESCE(f.paid_amount, 0) as paid_fees,
            COALESCE(f.total_fees - f.paid_amount, 0) as pending_fees,
            CASE 
                WHEN COALESCE(f.paid_amount, 0) >= COALESCE(f.total_fees, 0) THEN 'paid'
                WHEN COALESCE(f.paid_amount, 0) > 0 THEN 'partial'
                ELSE 'pending'
            END as fee_status
        FROM students s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        LEFT JOIN (
            SELECT 
                student_user_id,
                COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                COUNT(*) as total_days
            FROM attendance 
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY student_user_id
        ) att ON s.user_id = att.student_user_id
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
    <title><?php echo htmlspecialchars($student['full_name']); ?> - Student Profile</title>
    
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

        .student-photo-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e2e8f0;
        }

        .student-basic-info h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .student-meta {
            display: flex;
            gap: 2rem;
            color: #64748b;
            font-size: 0.875rem;
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

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .attendance-card {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 12px;
        }

        .attendance-percentage {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .attendance-label {
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .attendance-details {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.2);
            font-size: 0.875rem;
        }

        .fee-summary {
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .fee-amount {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .fee-breakdown {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #64748b;
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

            .student-meta {
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
            <a href="students.php?class=<?php echo $student['class_id']; ?>&section=<?php echo urlencode($student['section_name']); ?>">Students</a> > 
            <?php echo htmlspecialchars($student['full_name']); ?>
        </div>

        <div class="profile-header">
            <img src="<?php echo $student['photo'] ? htmlspecialchars($student['photo']) : 
                        'https://ui-avatars.com/api/?name=' . urlencode($student['full_name']) . '&background=f8fafc&color=64748b&size=120'; ?>" 
                 alt="<?php echo htmlspecialchars($student['full_name']); ?>" 
                 class="student-photo-large">
            
            <div class="student-basic-info">
                <h1><?php echo htmlspecialchars($student['full_name']); ?></h1>
                <div class="student-meta">
                    <span><strong>Admission No:</strong> <?php echo htmlspecialchars($student['admission_number']); ?></span>
                    <span><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name']); ?> - <?php echo htmlspecialchars(strtoupper($student['section_name'])); ?></span>
                    <span><strong>Roll No:</strong> <?php echo $student['roll_number']; ?></span>
                    <span class="status-badge status-<?php echo $student['user_status']; ?>">
                        <?php echo ucfirst($student['user_status']); ?>
                    </span>
                </div>
            </div>

            <div class="profile-actions">
                <a href="edit_student.php?id=<?php echo $student['user_id']; ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit Student
                </a>
                <button class="btn btn-outline" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
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
                            <span class="info-value"><?php echo $student['dob'] ? date('d M Y', strtotime($student['dob'])) : 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender</span>
                            <span class="info-value"><?php echo $student['gender_code'] ? ucfirst($student['gender_code']) : 'Not specified'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Blood Group</span>
                            <span class="info-value"><?php echo $student['blood_group_code'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nationality</span>
                            <span class="info-value"><?php echo $student['nationality'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Student Aadhar Number</span>
                            <span class="info-value"><?php echo $student['aadhar_card_number'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Medical Conditions</span>
                            <span class="info-value"><?php echo $student['medical_conditions'] ?: 'None'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="profile-section">
                    <h2 class="section-title">Academic Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Admission Date</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($student['admission_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Academic Year</span>
                            <span class="info-value"><?php echo $student['academic_year'] ?: 'Not assigned'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mother Tongue</span>
                            <span class="info-value"><?php echo $student['mother_tongue'] ?: 'Not provided'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="profile-section">
                    <h2 class="section-title">Contact Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Address</span>
                            <span class="info-value"><?php echo $student['address'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Pincode</span>
                            <span class="info-value"><?php echo $student['pincode'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mobile</span>
                            <span class="info-value"><?php echo $student['mobile'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Alternative Mobile</span>
                            <span class="info-value"><?php echo $student['alt_mobile'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo $student['email'] ?: 'Not provided'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Parent Information -->
                <div class="profile-section">
                    <h2 class="section-title">Parent Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Father's Name</span>
                            <span class="info-value"><?php echo $student['father_name'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Father's Aadhar Number</span>
                            <span class="info-value"><?php echo $student['father_aadhar_number'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mother's Name</span>
                            <span class="info-value"><?php echo $student['mother_name'] ?: 'Not provided'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Mother's Aadhar Number</span>
                            <span class="info-value"><?php echo $student['mother_aadhar_number'] ?: 'Not provided'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar-details">
                <!-- Attendance Summary -->
                <div class="profile-section">
                    <div class="attendance-card">
                        <div class="attendance-percentage"><?php echo $student['attendance_percentage']; ?>%</div>
                        <div class="attendance-label">Attendance Rate</div>
                        <div class="attendance-details">
                            <?php echo $student['present_days']; ?> present out of <?php echo $student['total_days']; ?> days
                            <br><small>(Last 30 days)</small>
                        </div>
                    </div>
                </div>

                <!-- Fee Summary -->
                <div class="profile-section">
                    <h2 class="section-title">Fee Summary</h2>
                    <div class="fee-summary">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span>Status:</span>
                            <span class="status-badge status-<?php echo $student['fee_status']; ?>">
                                <?php echo ucfirst($student['fee_status']); ?>
                            </span>
                        </div>
                        <div class="fee-breakdown">
                            <span>Total Fees:</span>
                            <span class="fee-amount">₹<?php echo number_format($student['total_fees']); ?></span>
                        </div>
                        <div class="fee-breakdown">
                            <span>Paid:</span>
                            <span style="color: #059669;">₹<?php echo number_format($student['paid_fees']); ?></span>
                        </div>
                        <div class="fee-breakdown">
                            <span>Pending:</span>
                            <span style="color: #dc2626;">₹<?php echo number_format($student['pending_fees']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="profile-section">
                    <h2 class="section-title">Quick Actions</h2>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="attendance.php?student_id=<?php echo $student['user_id']; ?>" class="btn btn-outline" style="justify-content: center;">
                            View Attendance
                        </a>
                        <a href="fees.php?student_id=<?php echo $student['user_id']; ?>" class="btn btn-outline" style="justify-content: center;">
                            Manage Fees
                        </a>
                        <a href="homework.php?student_id=<?php echo $student['user_id']; ?>" class="btn btn-outline" style="justify-content: center;">
                            View Homework
                        </a>
                        <a href="results.php?student_id=<?php echo $student['user_id']; ?>" class="btn btn-outline" style="justify-content: center;">
                            View Results
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
 