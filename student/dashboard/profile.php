<?php include 'con.php'; ?>
<?php include 'sidebar.php'; ?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get comprehensive student details
$student_id = $_SESSION['user_id'];
$query = "SELECT s.*, u.email, u.full_name as user_full_name, u.created_at as join_date, 
            u.last_login, u.status as user_status,
            c.name as class_name, sec.name as section_name, 
            g.label as gender_name, b.label as blood_group_name,
            ay.name as academic_year,
            COALESCE(t.full_name, 'Not Assigned') as class_teacher_name
          FROM students s
          JOIN users u ON s.user_id = u.id
          LEFT JOIN classes c ON s.class_id = c.id
          LEFT JOIN sections sec ON s.section_id = sec.id
          LEFT JOIN genders g ON s.gender_code = g.code
          LEFT JOIN blood_groups b ON s.blood_group_code = b.code
          LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
          LEFT JOIN teachers ct ON sec.class_teacher_user_id = ct.user_id
          LEFT JOIN users t ON ct.user_id = t.id
          WHERE s.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    // Handle case where student data is not found
    $student = [];
    $error_message = "Student profile not found. Please contact administration.";
}

// Helper function to format dates
function formatDate($date, $default = 'Not available') {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return $default;
    }
    return date('F j, Y', strtotime($date));
}

// Helper function to format datetime
function formatDateTime($datetime, $default = 'Not available') {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return $default;
    }
    return date('F j, Y g:i A', strtotime($datetime));
}

// Helper function to safely display data
function safeDisplay($value, $default = 'Not available') {
    return !empty($value) ? htmlspecialchars($value) : $default;
}

// Format important dates and values
$formatted_dob = formatDate($student['dob'] ?? null);
$formatted_admission_date = formatDate($student['admission_date'] ?? null);
$formatted_join_date = formatDateTime($student['join_date'] ?? null);
$formatted_last_login = formatDateTime($student['last_login'] ?? null, 'Never logged in');

// Default photo if none exists
$photo_url = !empty($student['photo']) ? '../uploads/student_photos/' . $student['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($student['full_name'] ?? 'Student') . '&background=4f46e5&color=ffffff&size=200';

// Calculate age if DOB is available
$age = 'Not available';
if (!empty($student['dob']) && $student['dob'] != '0000-00-00') {
    $birthDate = new DateTime($student['dob']);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y . ' years old';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Profile - <?php echo isset($student['full_name']) ? safeDisplay($student['full_name']) : 'Student Profile'; ?></title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/profile.css">
    <style>
        /* Main container and sidebar integration */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            background-color: #f8fafc;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }
        
        /* Mobile responsive for sidebar */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .sidebar.active ~ .main-content {
                margin-left: 0;
            }
        }
        
        /* Hamburger button for mobile */
        .hamburger-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .hamburger-btn {
                display: block;
            }
        }
        
        .hamburger-icon {
            width: 24px;
            height: 24px;
        }
        
        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Header styling */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
        }
        
        .header-title {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .header-subtitle {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }
        
        /* Profile header card */
        .profile-header-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .profile-image {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .profile-name {
            margin-bottom: 0.5rem;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .profile-info {
            color: #64748b;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .status-active {
            color: #059669;
            background: #d1fae5;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }
        
        /* Profile stats grid */
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Quick actions */
        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .action-btn {
            background: #4f46e5;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        
        .action-btn:hover {
            background: #3730a3;
            transform: translateY(-1px);
        }
        
        .action-btn.secondary {
            background: #64748b;
        }
        
        .action-btn.secondary:hover {
            background: #475569;
        }
        
        /* Information grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .info-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .info-card h4 {
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #64748b;
            flex: 1;
            min-width: 120px;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 500;
            flex: 2;
            text-align: right;
            word-break: break-word;
        }
        
        /* Responsive design */
        @media (max-width: 1200px) {
            .info-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .header-title {
                font-size: 1.4rem;
            }
            
            .profile-header-card {
                padding: 1.5rem;
            }
            
            .profile-image {
                width: 100px;
                height: 100px;
            }
            
            .profile-name {
                font-size: 1.25rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .info-card {
                padding: 1.25rem;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            
            .info-value {
                text-align: left;
                font-size: 0.9rem;
            }
            
            .info-label {
                font-size: 0.85rem;
                min-width: auto;
            }
            
            .profile-stats {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .quick-actions {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .action-btn {
                justify-content: center;
                padding: 1rem 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 0.75rem;
            }
            
            .profile-header-card {
                padding: 1rem;
            }
            
            .profile-image {
                width: 80px;
                height: 80px;
            }
            
            .profile-name {
                font-size: 1.125rem;
            }
            
            .profile-info {
                font-size: 0.875rem;
            }
            
            .info-card {
                padding: 1rem;
            }
            
            .info-card h4 {
                font-size: 1rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-number {
                font-size: 1.25rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
        }
        
        /* Print styles */
        @media print {
            .hamburger-btn,
            .sidebar-overlay,
            .quick-actions {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            
            .profile-header-card,
            .info-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
        }
        
        /* Loading and error states */
        .error-message {
            text-align: center;
            padding: 2rem;
            color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            margin: 2rem 0;
        }
        
        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Focus styles for keyboard navigation */
        .action-btn:focus,
        .hamburger-btn:focus {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .info-card {
                border-width: 2px;
            }
            
            .stat-card {
                border: 2px solid #333;
            }
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

<?php if (isset($error_message)): ?>
    <!-- Error message display -->
    <div class="main-content">
        <div class="error-message">
            <h2><?php echo $error_message; ?></h2>
        </div>
    </div>
<?php else: ?>
    <!-- Main profile content -->
    <div class="main-content">
        <header class="dashboard-header">
            <div>
                <h1 class="header-title">Student Profile</h1>
                <span class="header-subtitle">Complete profile information</span>
            </div>
        </header>

        <main class="dashboard-content">
            <!-- Profile Header Card -->
            <div class="profile-header-card">
                <img src="<?php echo $photo_url; ?>" alt="Student Profile" class="profile-image">
                <h2 class="profile-name"><?php echo safeDisplay($student['full_name']); ?></h2>
                <p class="profile-info">Student • <?php echo safeDisplay($student['class_name'] . ' - ' . $student['section_name']); ?></p>
                <span class="status-active">Account Active</span>
                
                <!-- Quick Stats -->
                <div class="profile-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo safeDisplay($student['roll_number']); ?></div>
                        <div class="stat-label">Roll Number</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $age; ?></div>
                        <div class="stat-label">Age</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo date('Y', strtotime($student['admission_date'] ?? 'now')); ?></div>
                        <div class="stat-label">Admission Year</div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="id-card_student.php" class="action-btn">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 4h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm1 2v12h16V6H4zm8 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm0 2a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                        </svg>
                        View ID Card
                    </a>
                    <button class="action-btn secondary" onclick="window.print()">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 2h12v4H6V2zM4 6h16a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-3v4H7v-4H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zm0 2v6h2v-2h12v2h2V8H4zm5 8v2h6v-2H9z"/>
                        </svg>
                        Print Profile
                    </button>
                </div>
            </div>

            <!-- Information Grid -->
            <div class="info-grid">
                <!-- Personal Information -->
                <div class="info-card">
                    <h4>👤 Personal Information</h4>
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo safeDisplay($student['full_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Birth</span>
                        <span class="info-value"><?php echo $formatted_dob; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender</span>
                        <span class="info-value"><?php echo safeDisplay($student['gender_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Blood Group</span>
                        <span class="info-value"><?php echo safeDisplay($student['blood_group_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mother Tongue</span>
                        <span class="info-value"><?php echo safeDisplay($student['mother_tongue']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nationality</span>
                        <span class="info-value"><?php echo safeDisplay($student['nationality'], 'Indian'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Aadhar Number</span>
                        <span class="info-value"><?php echo safeDisplay($student['aadhar_card_number']); ?></span>
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="info-card">
                    <h4>🎓 Academic Information</h4>
                    <div class="info-row">
                        <span class="info-label">Admission Number</span>
                        <span class="info-value"><?php echo safeDisplay($student['admission_number']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Admission Date</span>
                        <span class="info-value"><?php echo $formatted_admission_date; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Class</span>
                        <span class="info-value"><?php echo safeDisplay($student['class_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Section</span>
                        <span class="info-value"><?php echo safeDisplay($student['section_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Roll Number</span>
                        <span class="info-value"><?php echo safeDisplay($student['roll_number']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Academic Year</span>
                        <span class="info-value"><?php echo safeDisplay($student['academic_year']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Class Teacher</span>
                        <span class="info-value"><?php echo safeDisplay($student['class_teacher_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Student State Code</span>
                        <span class="info-value"><?php echo safeDisplay($student['student_state_code']); ?></span>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="info-card">
                    <h4>📞 Contact Information</h4>
                    <div class="info-row">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?php echo safeDisplay($student['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mobile Number</span>
                        <span class="info-value"><?php echo safeDisplay($student['mobile']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alternative Mobile</span>
                        <span class="info-value"><?php echo safeDisplay($student['alt_mobile']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Email</span>
                        <span class="info-value"><?php echo safeDisplay($student['contact_email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address</span>
                        <span class="info-value"><?php echo safeDisplay($student['address']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pincode</span>
                        <span class="info-value"><?php echo safeDisplay($student['pincode']); ?></span>
                    </div>
                </div>

                <!-- Family Information -->
                <div class="info-card">
                    <h4>👨‍👩‍👧‍👦 Family Information</h4>
                    <div class="info-row">
                        <span class="info-label">Father's Name</span>
                        <span class="info-value"><?php echo safeDisplay($student['father_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Father's Aadhar</span>
                        <span class="info-value"><?php echo safeDisplay($student['father_aadhar_number']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mother's Name</span>
                        <span class="info-value"><?php echo safeDisplay($student['mother_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mother's Aadhar</span>
                        <span class="info-value"><?php echo safeDisplay($student['mother_aadhar_number']); ?></span>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="info-card">
                    <h4>⚙️ Account Information</h4>
                    <div class="info-row">
                        <span class="info-label">Account Created</span>
                        <span class="info-value"><?php echo $formatted_join_date; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Login</span>
                        <span class="info-value"><?php echo $formatted_last_login; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Account Status</span>
                        <span class="info-value">
                            <span class="status-active"><?php echo ucfirst(safeDisplay($student['user_status'], 'Active')); ?></span>
                        </span>
                    </div>
                </div>

                <!-- Medical Information -->
                <div class="info-card">
                    <h4>🏥 Medical Information</h4>
                    <div class="info-row">
                        <span class="info-label">Medical Conditions</span>
                        <span class="info-value"><?php echo safeDisplay($student['medical_conditions'], 'None reported'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Emergency Contact</span>
                        <span class="info-value"><?php echo safeDisplay($student['mobile']); ?></span>
                    </div>
                </div>
            </div>
        </main>
    </div>
<?php endif; ?>

<script>
    // Disable all form inputs to make the page truly read-only
    document.addEventListener('DOMContentLoaded', function() {
        // Disable any remaining form elements
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.disabled = true;
            input.style.cursor = 'not-allowed';
        });
        
        // Print functionality
        window.print = function() {
            window.print();
        };
    });

    // Sidebar toggle function
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }

    // Handle window resize for sidebar responsiveness
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.querySelector('.sidebar-overlay').classList.remove('active');
            document.querySelector('.sidebar').classList.remove('active');
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!e.target.closest('.sidebar, .hamburger-btn')) {
                document.querySelector('.sidebar').classList.remove('active');
                document.querySelector('.sidebar-overlay').classList.remove('active');
            }
        }
    });
</script>
</body>
</html>
