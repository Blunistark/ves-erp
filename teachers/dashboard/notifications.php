
<?php 
include 'sidebar.php'; 
include 'con.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get teacher's assigned classes for notification targeting
// This includes both class teacher assignments and subject teaching assignments
$teacher_classes_sql = "
    SELECT DISTINCT c.id, c.name as class_name, s.id as section_id, s.name as section_name,
           CASE 
               WHEN s.class_teacher_user_id = ? THEN 'Class Teacher'
               ELSE 'Subject Teacher'
           END as assignment_type
    FROM classes c
    JOIN sections s ON c.id = s.class_id
    WHERE (
        -- Classes where teacher is the class teacher
        s.class_teacher_user_id = ?
        OR 
        -- Classes where teacher teaches subjects (from timetable)
        EXISTS (
            SELECT 1 FROM timetables t 
            JOIN timetable_periods tp ON t.id = tp.timetable_id 
            WHERE t.class_id = c.id 
            AND t.section_id = s.id 
            AND tp.teacher_id = ?
            AND t.status = 'published'
        )
    )
    ORDER BY c.name, s.name";
$teacher_classes_stmt = mysqli_prepare($conn, $teacher_classes_sql);
mysqli_stmt_bind_param($teacher_classes_stmt, "iii", $user_id, $user_id, $user_id);
mysqli_stmt_execute($teacher_classes_stmt);
$teacher_classes_result = mysqli_stmt_get_result($teacher_classes_stmt);

$teacher_classes = [];
while ($row = mysqli_fetch_assoc($teacher_classes_result)) {
    $teacher_classes[] = $row;
}

// Get teacher permissions directly from database instead of API call
$permissions = [
    'can_create_notifications' => 1,
    'can_target_all_school' => 0,
    'can_target_other_classes' => 0,
    'can_schedule_notifications' => 1,
    'can_require_acknowledgment' => 1,
    'max_priority_level' => 'important'
];

// Check if user has custom permissions in database
$permissions_sql = "SELECT * FROM notification_permissions WHERE user_id = ? AND user_type = 'teacher'";
$permissions_stmt = mysqli_prepare($conn, $permissions_sql);
if ($permissions_stmt) {
    mysqli_stmt_bind_param($permissions_stmt, "i", $user_id);
    mysqli_stmt_execute($permissions_stmt);
    $permissions_result = mysqli_stmt_get_result($permissions_stmt);
    if ($custom_permissions = mysqli_fetch_assoc($permissions_result)) {
        $permissions = array_merge($permissions, $custom_permissions);
    }
}

// Get user information
$user_name = $_SESSION['full_name'] ?? 'Teacher';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Teacher Notifications - Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/index.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* Enhanced styles while maintaining the same theme */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .school-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header-title {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .header-date {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .user-welcome {
            background: linear-gradient(135deg, #f9f9f9 0%, #f0f0f0 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .user-welcome::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .welcome-text h2 {
            margin: 0;
            color: #333;
            font-size: 1.6rem;
            font-weight: 600;
        }
        
        .welcome-text p {
            margin: 8px 0 0;
            color: #666;
            font-size: 1rem;
        }
        
        .date-time {
            background: rgba(255,255,255,0.9);
            padding: 15px 20px;
            border-radius: 10px;
            text-align: center;
            min-width: 200px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .date-time .time {
            font-size: 1.4rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-family: 'Courier New', monospace;
        }
        
        .date-time .date {
            font-size: 0.9rem;
            color: #666;
        }
        
        /* Quick Stats Section */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid #667eea;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card.classes { border-left-color: #667eea; }
        .stat-card.sent { border-left-color: #4CAF50; }
        .stat-card.received { border-left-color: #FF9800; }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }
        
        /* Enhanced tab system */
        .action-tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .tab-button {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .tab-button:hover:not(.active) {
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Enhanced tab content */
        .tab-content {
            display: none;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Form styling consistent with dashboard */
        .form-section {
            margin-bottom: 25px;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fff;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        /* Class targeting section */
        .class-targeting {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 2px solid #bbf7d0;
        }
        
        .class-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .class-option {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .class-option:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .class-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
        }
        
        .class-option input[type="checkbox"] {
            margin-right: 15px;
            transform: scale(1.2);
            accent-color: #667eea;
        }
        
        .class-info {
            flex: 1;
        }
        
        .class-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .section-name {
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
        }
        
        /* Priority selector */
        .priority-selector {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .priority-option {
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .priority-option.normal {
            border-color: #10b981;
            color: #059669;
        }
        
        .priority-option.important {
            border-color: #f59e0b;
            color: #d97706;
        }
        
        .priority-option.urgent {
            border-color: #ef4444;
            color: #dc2626;
        }
        
        .priority-option.selected {
            background-color: currentColor;
            color: white;
            transform: scale(1.05);
        }
        
        /* Button styling consistent with dashboard */
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
        }
        
        /* Notification list styling */
        .notification-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .notification-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: #666;
            flex-wrap: wrap;
        }
        
        .notification-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        /* Alert styling */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left-color: #10b981;
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left-color: #ef4444;
        }
        
        /* Loading states */
        .loading {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic;
        }
        
        .no-classes {
            color: #666;
            font-style: italic;
            padding: 20px 0;
            text-align: center;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .header-left {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .school-logo {
                width: 40px;
                height: 40px;
            }
            
            .header-title {
                font-size: 1.4rem;
            }
            
            .user-welcome {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .class-selector {
                grid-template-columns: 1fr;
            }
            
            .priority-selector {
                flex-direction: column;
            }
            
            .action-tabs {
                flex-direction: column;
            }
            
            .tab-button {
                border-radius: 0;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-left {
                flex-direction: row;
                gap: 10px;
            }
            
            .school-logo {
                width: 35px;
                height: 35px;
            }
            
            .header-title {
                font-size: 1.2rem;
            }
            
            .tab-content {
                padding: 15px;
            }
        }
        
        /* Summernote editor styling */
        .note-editor {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
        }
        
        .note-editor.note-frame .note-editing-area .note-editable {
            padding: 15px !important;
        }
        
        .note-editor.note-frame {
            border: 2px solid #e5e7eb !important;
        }
        
        .note-editor.note-frame:focus-within {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }
        
        /* Select2 styling */
        .select2-container--default .select2-selection--single {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            height: 48px !important;
            padding: 8px 12px !important;
        }
        
        .select2-container--default .select2-selection--single:focus {
            border-color: #667eea !important;
        }
        
        /* Flatpickr styling */
        .flatpickr-input {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
        }
        
        .flatpickr-input:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
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

<div class="dashboard-container">
    <header class="dashboard-header">
        <div class="header-left">
            <img src="../../assets/images/school-logo.png" alt="VES School Logo" class="school-logo">
            <h1 class="header-title">Teacher Notifications</h1>
        </div>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <!-- User Welcome Section -->
        <div class="user-welcome">
            <div class="welcome-text">
                <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p>Manage your notifications and communicate with students effectively</p>
            </div>
            <div class="date-time">
                <div class="time" id="current-time">00:00:00</div>
                <div class="date"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card classes">
                <div class="stat-number" id="totalClassesCount"><?php echo count($teacher_classes); ?></div>
                <div class="stat-label">Assigned Classes</div>
            </div>
            <div class="stat-card sent">
                <div class="stat-number" id="sentNotificationsCount">0</div>
                <div class="stat-label">Sent This Week</div>
            </div>
            <div class="stat-card received">
                <div class="stat-number" id="unreadNotificationsCount">0</div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>

        <!-- Action Tabs -->
        <div class="action-tabs">
            <button class="tab-button active" onclick="switchTab('create')">Create Notification</button>
            <button class="tab-button" onclick="switchTab('sent')">Sent Notifications</button>
            <button class="tab-button" onclick="switchTab('received')">Received Messages</button>
        </div>

        <!-- Create Notification Tab -->
        <div id="create-tab" class="tab-content active">
            <div id="notification-alert"></div>
            
            <form id="create-notification-form">
                <div class="form-section">
                    <h3>Notification Details</h3>
                    
                    <div class="form-group">
                        <label for="notification-title">Title <span style="color: red;">*</span></label>
                        <input type="text" id="notification-title" name="title" class="form-control" 
                               placeholder="Enter notification title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notification-message">Message <span style="color: red;">*</span></label>
                        <textarea id="notification-message" name="message" class="form-control" 
                                  rows="4" placeholder="Enter your message here" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="notification-type">Type</label>
                            <select id="notification-type" name="type" class="form-control">
                                <option value="announcement">General Announcement</option>
                                <option value="assignment">Assignment Notice</option>
                                <option value="reminder">Reminder</option>
                                <option value="info">Information</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Priority</label>
                            <div class="priority-selector">
                                <div class="priority-option normal selected" data-priority="normal">
                                    <span>Normal</span>
                                </div>
                                <div class="priority-option important" data-priority="important">
                                    <span>Important</span>
                                </div>
                                <div class="priority-option urgent" data-priority="urgent">
                                    <span>Urgent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Target Classes</h3>
                    
                    <div class="class-targeting">
                        <p>Select the classes you want to send this notification to:</p>
                        <div class="class-selector">
                            <?php foreach ($teacher_classes as $class): ?>
                            <div class="class-option" onclick="toggleClassSelection(this)">
                                <input type="checkbox" name="target_classes[]" 
                                       value="class_<?php echo $class['id']; ?>_section_<?php echo $class['section_id']; ?>">
                                <div class="class-info">
                                    <div class="class-name"><?php echo htmlspecialchars($class['class_name']); ?></div>
                                    <div class="section-name">
                                        Section: <?php echo htmlspecialchars($class['section_name']); ?>
                                        <span style="color: #667eea; font-size: 0.75rem; margin-left: 0.5rem;">
                                            (<?php echo htmlspecialchars($class['assignment_type']); ?>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($teacher_classes)): ?>
                            <div class="no-classes">
                                <p>No classes assigned yet.</p>
                                <p style="font-size: 0.875rem;">Contact the admin to get assigned to classes or timetable periods.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Additional Options</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expires-at">Expiry Date (optional)</label>
                            <input type="datetime-local" id="expires-at" name="expires_at" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="scheduled-for">Schedule For (optional)</label>
                            <input type="datetime-local" id="scheduled-for" name="scheduled_for" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="requires_acknowledgment" value="1" style="margin-right: 8px;">
                            Require acknowledgment from recipients
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15.854.146a.5.5 0 0 1 0 .708L11.707 6l-1.414-1.414L14.146.854a.5.5 0 0 1 .708 0z"/>
                            <path d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                        </svg>
                        Send Notification
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()" style="margin-left: 10px;">Reset</button>
                </div>
            </form>
        </div>

        <!-- Sent Notifications Tab -->
        <div id="sent-tab" class="tab-content">
            <div class="notification-list">
                <div id="sent-notifications-loading" class="loading">
                    Loading sent notifications...
                </div>
                <div id="sent-notifications-list"></div>
            </div>
        </div>

        <!-- Received Messages Tab -->
        <div id="received-tab" class="tab-content">
            <div class="notification-list">
                <div id="received-notifications-loading" class="loading">
                    Loading received messages...
                </div>
                <div id="received-notifications-list"></div>
           </div>
       </div>
   </main>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
   // Global variables
   let currentTab = 'create';
   let selectedPriority = 'normal';

   // Initialize the page
   $(document).ready(function() {
       initializePage();
       loadNotificationCounts();
       updateTime();
       setInterval(updateTime, 1000);
   });

   // Update current time with smooth animation (consistent with dashboard)
   function updateTime() {
       const currentTimeElement = document.getElementById('current-time');
       const now = new Date();
       const hours = String(now.getHours()).padStart(2, '0');
       const minutes = String(now.getMinutes()).padStart(2, '0');
       const seconds = String(now.getSeconds()).padStart(2, '0');
       
       const newTime = `${hours}:${minutes}:${seconds}`;
       
       if (currentTimeElement && currentTimeElement.textContent !== newTime) {
           currentTimeElement.style.opacity = '0.7';
           setTimeout(() => {
               currentTimeElement.textContent = newTime;
               currentTimeElement.style.opacity = '1';
           }, 100);
       }
   }

   function initializePage() {
       // Initialize rich text editor
       $('#notification-message').summernote({
           height: 150,
           toolbar: [
               ['style', ['style']],
               ['font', ['bold', 'underline', 'clear']],
               ['color', ['color']],
               ['para', ['ul', 'ol', 'paragraph']],
               ['table', ['table']],
               ['insert', ['link']],
               ['view', ['fullscreen', 'help']]
           ],
           callbacks: {
               onInit: function() {
                   // Apply consistent styling to summernote
                   $('.note-editor').addClass('form-control-editor');
               }
           }
       });

       // Initialize date pickers with consistent styling
       flatpickr("#expires-at", {
           enableTime: true,
           dateFormat: "Y-m-d H:i",
           minDate: "today",
           theme: "light"
       });

       flatpickr("#scheduled-for", {
           enableTime: true,
           dateFormat: "Y-m-d H:i",
           minDate: "today",
           theme: "light"
       });

       // Form submission handler
       $('#create-notification-form').on('submit', handleFormSubmission);

       // Priority selector handlers
       $('.priority-option').on('click', function() {
           $('.priority-option').removeClass('selected');
           $(this).addClass('selected');
           selectedPriority = $(this).data('priority');
       });

       // Add smooth animations to form elements
       $('.form-control').on('focus', function() {
           $(this).parent().addClass('focused');
       }).on('blur', function() {
           $(this).parent().removeClass('focused');
       });
   }

   function switchTab(tab) {
       // Update tab buttons with smooth animation
       $('.tab-button').removeClass('active');
       $('.tab-button').each(function() {
           if ($(this).text().toLowerCase().includes(tab)) {
               $(this).addClass('active');
           }
       });

       // Update tab content with fade effect
       $('.tab-content').removeClass('active').hide();
       $(`#${tab}-tab`).addClass('active').fadeIn(300);

       currentTab = tab;

       // Load data for the selected tab
       if (tab === 'sent') {
           loadSentNotifications();
       } else if (tab === 'received') {
           loadReceivedNotifications();
       }
   }

   function toggleClassSelection(element) {
       const checkbox = $(element).find('input[type="checkbox"]');
       const isChecked = !checkbox.prop('checked');
       
       checkbox.prop('checked', isChecked);
       
       // Add smooth animation
       if (isChecked) {
           $(element).addClass('selected');
           $(element).css('transform', 'scale(1.02)');
           setTimeout(() => {
               $(element).css('transform', 'scale(1)');
           }, 150);
       } else {
           $(element).removeClass('selected');
       }
   }

   function handleFormSubmission(e) {
       e.preventDefault();
       
       // Show loading state
       const submitBtn = $(e.target).find('button[type="submit"]');
       const originalText = submitBtn.html();
       submitBtn.html('<svg class="animate-spin" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16c-4.418 0-8-3.582-8-8s3.582-8 8-8c1.074 0 2.09.215 3.018.602l-.708 1.857C9.688 2.162 8.875 2 8 2c-3.309 0-6 2.691-6 6s2.691 6 6 6 6-2.691 6-6h2c0 4.418-3.582 8-8 8z"/></svg> Sending...').prop('disabled', true);
       
       // Validate form
       const title = $('#notification-title').val().trim();
       const message = $('#notification-message').summernote('code').trim();
       const selectedClasses = $('input[name="target_classes[]"]:checked');

       if (!title) {
           showAlert('Please enter a notification title', 'error');
           resetSubmitButton(submitBtn, originalText);
           return;
       }

       if (!message || message === '<p><br></p>') {
           showAlert('Please enter a notification message', 'error');
           resetSubmitButton(submitBtn, originalText);
           return;
       }

       if (selectedClasses.length === 0) {
           showAlert('Please select at least one class to notify', 'error');
           resetSubmitButton(submitBtn, originalText);
           return;
       }

       // Prepare form data
       const formData = {
           title: title,
           message: message,
           type: $('#notification-type').val(),
           priority: selectedPriority,
           target_type: 'multiple_classes',
           target_value: Array.from(selectedClasses).map(el => el.value).join(','),
           expires_at: $('#expires-at').val() || null,
           scheduled_for: $('#scheduled-for').val() || null,
           requires_acknowledgment: $('input[name="requires_acknowledgment"]').is(':checked') ? 1 : 0
       };

       // Send to API
       $.ajax({
           url: '/erp/backend/api/notifications?action=create',
           method: 'POST',
           headers: {
               'Content-Type': 'application/json'
           },
           data: JSON.stringify(formData),
           success: function(response) {
               resetSubmitButton(submitBtn, originalText);
               if (response.success) {
                   showAlert('Notification sent successfully!', 'success');
                   resetForm();
                   loadNotificationCounts();
               } else {
                   showAlert(response.message || 'Failed to send notification', 'error');
               }
           },
           error: function() {
               resetSubmitButton(submitBtn, originalText);
               showAlert('Network error. Please try again.', 'error');
           }
       });
   }

   function resetSubmitButton(btn, originalText) {
       btn.html(originalText).prop('disabled', false);
   }

   function loadNotificationCounts() {
       $.ajax({
           url: '/erp/backend/api/notifications?action=count',
           method: 'GET',
           success: function(response) {
               if (response.success) {
                   animateNumber('#unreadNotificationsCount', response.data.unread || 0);
               }
           }
       });

       // Load sent notifications count for this week
       $.ajax({
           url: '/erp/backend/api/notifications?action=list&limit=100',
           method: 'GET',
           success: function(response) {
               if (response.success) {
                   const thisWeek = response.data.filter(notif => {
                       const notifDate = new Date(notif.created_at);
                       const weekAgo = new Date();
                       weekAgo.setDate(weekAgo.getDate() - 7);
                       return notifDate >= weekAgo && notif.created_by == <?php echo $user_id; ?>;
                   });
                   animateNumber('#sentNotificationsCount', thisWeek.length);
               }
           }
       });
   }

   function animateNumber(selector, targetValue) {
       const element = $(selector);
       const currentValue = parseInt(element.text()) || 0;
       
       if (currentValue !== targetValue) {
           $({ value: currentValue }).animate({ value: targetValue }, {
               duration: 1000,
               step: function() {
                   element.text(Math.floor(this.value));
               },
               complete: function() {
                   element.text(targetValue);
               }
           });
       }
   }

   function loadSentNotifications() {
       $('#sent-notifications-loading').show();
       $('#sent-notifications-list').empty();

       $.ajax({
           url: '/erp/backend/api/notifications?action=list&limit=50',
           method: 'GET',
           success: function(response) {
               $('#sent-notifications-loading').hide();
               
               if (response.success) {
                   const sentNotifications = response.data.filter(notif => 
                       notif.created_by == <?php echo $user_id; ?>
                   );
                   
                   if (sentNotifications.length === 0) {
                       $('#sent-notifications-list').html('<div class="loading">No sent notifications found.</div>');
                       return;
                   }

                   let html = '';
                   sentNotifications.forEach(function(notification) {
                       html += buildNotificationItem(notification, true);
                   });
                   
                   $('#sent-notifications-list').html(html);
               } else {
                   $('#sent-notifications-list').html('<div class="loading">Error loading notifications.</div>');
               }
           },
           error: function() {
               $('#sent-notifications-loading').hide();
               $('#sent-notifications-list').html('<div class="loading">Network error loading notifications.</div>');
           }
       });
   }

   function loadReceivedNotifications() {
       $('#received-notifications-loading').show();
       $('#received-notifications-list').empty();

       $.ajax({
           url: '/erp/backend/api/notifications?action=list&limit=50',
           method: 'GET',
           success: function(response) {
               $('#received-notifications-loading').hide();
               
               if (response.success) {
                   const receivedNotifications = response.data.filter(notif => 
                       notif.created_by != <?php echo $user_id; ?>
                   );
                   
                   if (receivedNotifications.length === 0) {
                       $('#received-notifications-list').html('<div class="loading">No received notifications found.</div>');
                       return;
                   }

                   let html = '';
                   receivedNotifications.forEach(function(notification) {
                       html += buildNotificationItem(notification, false);
                   });
                   
                   $('#received-notifications-list').html(html);
               } else {
                   $('#received-notifications-list').html('<div class="loading">Error loading notifications.</div>');
               }
           },
           error: function() {
               $('#received-notifications-loading').hide();
               $('#received-notifications-list').html('<div class="loading">Network error loading notifications.</div>');
           }
       });
   }

   function buildNotificationItem(notification, isSent) {
       const priorityClass = notification.priority || 'normal';
       const priorityColor = {
           'normal': '#667eea',
           'important': '#f59e0b',
           'urgent': '#ef4444'
       }[priorityClass];

       const date = new Date(notification.created_at).toLocaleDateString();
       const time = new Date(notification.created_at).toLocaleTimeString();

       return `
           <div class="notification-item" style="opacity: 0; transform: translateY(20px);" onload="$(this).animate({opacity: 1, transform: 'translateY(0)'}, 300)">
               <div class="notification-header">
                   <div>
                       <div class="notification-title" style="border-left: 4px solid ${priorityColor}; padding-left: 15px;">
                           ${notification.title}
                       </div>
                       <div class="notification-meta">
                           <span><strong>Type:</strong> ${notification.type}</span>
                           <span><strong>Priority:</strong> ${notification.priority}</span>
                           <span><strong>Date:</strong> ${date} at ${time}</span>
                           ${isSent ? `<span><strong>To:</strong> Multiple Classes</span>` : `<span><strong>From:</strong> ${notification.created_by_name || 'System'}</span>`}
                       </div>
                   </div>
               </div>
               <div class="notification-content">
                   ${notification.message}
               </div>
               ${!isSent && !notification.is_read ? `
                   <div style="margin-top: 15px;">
                       <button class="btn btn-primary" onclick="markAsRead(${notification.id})" style="font-size: 0.8rem; padding: 8px 16px;">
                           <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                               <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                           </svg>
                           Mark as Read
                       </button>
                   </div>
               ` : ''}
           </div>
       `;
   }

   function markAsRead(notificationId) {
       $.ajax({
           url: '/erp/backend/api/notifications?action=mark_read',
           method: 'POST',
           headers: {
               'Content-Type': 'application/json'
           },
           data: JSON.stringify({ notification_id: notificationId }),
           success: function(response) {
               if (response.success) {
                   loadReceivedNotifications();
                   loadNotificationCounts();
                   showAlert('Marked as read successfully', 'success');
               }
           },
           error: function() {
               showAlert('Failed to mark as read', 'error');
           }
       });
   }

   function resetForm() {
       $('#create-notification-form')[0].reset();
       $('#notification-message').summernote('code', '');
       $('.class-option').removeClass('selected');
       $('.class-option input[type="checkbox"]').prop('checked', false);
       $('.priority-option').removeClass('selected');
       $('.priority-option[data-priority="normal"]').addClass('selected');
       selectedPriority = 'normal';
       
       // Add animation to reset
       $('.form-control').each(function(index) {
           setTimeout(() => {
               $(this).css('transform', 'scale(1.02)');
               setTimeout(() => {
                   $(this).css('transform', 'scale(1)');
               }, 100);
           }, index * 50);
       });
   }

   function showAlert(message, type) {
       const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
       const alertHtml = `
           <div class="alert ${alertClass}" style="opacity: 0; transform: translateY(-10px);">
               ${message}
           </div>
       `;
       
       $('#notification-alert').html(alertHtml);
       
       // Animate in
       $('#notification-alert .alert').animate({
           opacity: 1,
           transform: 'translateY(0)'
       }, 300);
       
       // Auto hide after 5 seconds
       setTimeout(() => {
           $('#notification-alert .alert').animate({
               opacity: 0,
               transform: 'translateY(-10px)'
           }, 300, function() {
               $('#notification-alert').empty();
           });
       }, 5000);
   }

   // Sidebar toggle function (consistent with dashboard)
   function toggleSidebar() {
       const sidebar = document.querySelector('.sidebar');
       const overlay = document.querySelector('.sidebar-overlay');
       
       if (sidebar && overlay) {
           sidebar.classList.toggle('active');
           overlay.classList.toggle('active');
       }
   }

   // Add intersection observer for smooth animations
   const observerOptions = {
       threshold: 0.1,
       rootMargin: '0px 0px -50px 0px'
   };
   
   const observer = new IntersectionObserver((entries) => {
       entries.forEach(entry => {
           if (entry.isIntersecting) {
               entry.target.style.opacity = '1';
               entry.target.style.transform = 'translateY(0)';
           }
       });
   }, observerOptions);
   
   // Observe cards for animation when they come into view
   document.addEventListener('DOMContentLoaded', function() {
       document.querySelectorAll('.stat-card, .tab-content, .class-option').forEach(card => {
           card.style.opacity = '0';
           card.style.transform = 'translateY(20px)';
           card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
           observer.observe(card);
       });
   });

   // Add loading states for better UX
   $(document).ajaxStart(function() {
       $('body').addClass('loading');
   }).ajaxStop(function() {
       $('body').removeClass('loading');
   });

   // Auto-refresh notification counts every 2 minutes
   setInterval(() => {
       if (!document.hidden) {
           loadNotificationCounts();
       }
   }, 120000);
</script>
</body>
</html>
