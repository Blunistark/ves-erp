<?php 
include 'sidebar.php'; 
include 'con.php';

// Check if user is logged in and is a headmaster
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'headmaster') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get headmaster permissions - headmasters have full admin privileges
$permissions = [
    'can_create_notifications' => 1,
    'can_target_all_school' => 1,
    'can_target_other_classes' => 1,
    'can_schedule_notifications' => 1,
    'can_require_acknowledgment' => 1,
    'max_priority_level' => 'urgent'
];

// Get notification statistics
$stats = [
    'total_notifications' => 0,
    'weekly_notifications' => 0,
    'urgent_notifications' => 0,
    'acknowledgment_required' => 0
];

// Get total notifications count
$total_query = "SELECT COUNT(*) as count FROM notifications WHERE created_by = ?";
$total_stmt = mysqli_prepare($conn, $total_query);
mysqli_stmt_bind_param($total_stmt, "i", $user_id);
mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
if ($row = mysqli_fetch_assoc($total_result)) {
    $stats['total_notifications'] = $row['count'];
}

// Get weekly notifications count
$weekly_query = "SELECT COUNT(*) as count FROM notifications WHERE created_by = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$weekly_stmt = mysqli_prepare($conn, $weekly_query);
mysqli_stmt_bind_param($weekly_stmt, "i", $user_id);
mysqli_stmt_execute($weekly_stmt);
$weekly_result = mysqli_stmt_get_result($weekly_stmt);
if ($row = mysqli_fetch_assoc($weekly_result)) {
    $stats['weekly_notifications'] = $row['count'];
}

// Get urgent notifications count
$urgent_query = "SELECT COUNT(*) as count FROM notifications WHERE created_by = ? AND priority = 'urgent' AND is_active = 1";
$urgent_stmt = mysqli_prepare($conn, $urgent_query);
mysqli_stmt_bind_param($urgent_stmt, "i", $user_id);
mysqli_stmt_execute($urgent_stmt);
$urgent_result = mysqli_stmt_get_result($urgent_stmt);
if ($row = mysqli_fetch_assoc($urgent_result)) {
    $stats['urgent_notifications'] = $row['count'];
}

// Get acknowledgment required count
$ack_query = "SELECT COUNT(*) as count FROM notifications WHERE created_by = ? AND requires_acknowledgment = 1 AND is_active = 1";
$ack_stmt = mysqli_prepare($conn, $ack_query);
mysqli_stmt_bind_param($ack_stmt, "i", $user_id);
mysqli_stmt_execute($ack_stmt);
$ack_result = mysqli_stmt_get_result($ack_stmt);
if ($row = mysqli_fetch_assoc($ack_result)) {
    $stats['acknowledgment_required'] = $row['count'];
}

// Get all classes for targeting
$classes_query = "SELECT c.id as class_id, c.name as class_name, s.id as section_id, s.name as section_name FROM classes c JOIN sections s ON c.id = s.class_id ORDER BY c.name, s.name";
$classes_result = mysqli_query($conn, $classes_query);
$all_classes = [];
while ($row = mysqli_fetch_assoc($classes_result)) {
    $all_classes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications - Headmaster Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* Sidebar optimization and main content spacing */
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
            background: #6366f1;
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
        
        .header-section {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-section h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .header-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #6366f1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }
        
        .action-tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .tab-button {
            flex: 1;
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background-color: #6366f1;
            color: white;
        }
        
        .tab-button:hover:not(.active) {
            background-color: #f9fafb;
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            margin-bottom: 1rem;
            color: #374151;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .targeting-section {
            background-color: #f0f9ff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 2px solid #bfdbfe;
        }
        
        .targeting-mode {
            margin-bottom: 1.5rem;
        }
        
        .targeting-options {
            display: none;
        }
        
        .targeting-options.active {
            display: block;
        }
        
        .class-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .class-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .class-option:hover {
            border-color: #6366f1;
        }
        
        .class-option.selected {
            border-color: #6366f1;
            background-color: #f0f9ff;
        }
        
        .class-option input[type="checkbox"] {
            margin-right: 1rem;
        }
        
        .class-info {
            flex: 1;
        }
        
        .class-name {
            font-weight: 600;
            color: #374151;
        }
        
        .section-name {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .priority-selector {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .priority-option {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            font-weight: 600;
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
        }
        
        .role-checkboxes {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .role-checkboxes label {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-checkboxes label:hover {
            border-color: #6366f1;
        }
        
        .role-checkboxes input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        
        .search-results {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 0.5rem;
            display: none;
        }
        
        .search-result-item {
            padding: 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .search-result-item:hover {
            background-color: #f9fafb;
        }
        
        .selected-recipients {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .recipient-tag {
            background-color: #6366f1;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .recipient-tag .remove {
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #6366f1;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4f46e5;
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        .notification-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .notification-item {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.3s ease;
        }
        
        .notification-item:hover {
            background-color: #f9fafb;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .notification-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .notification-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
            flex-wrap: wrap;
        }
        
        .notification-content {
            color: #374151;
            line-height: 1.6;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .analytics-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .analytics-card h4 {
            margin-bottom: 1rem;
            color: #374151;
        }
        
        /* Responsive optimizations */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .header-section {
                padding: 1.5rem;
            }
            
            .header-section h1 {
                font-size: 1.5rem;
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
            
            .role-checkboxes {
                flex-direction: column;
            }
            
            .action-tabs {
                flex-direction: column;
            }
            
            .tab-button {
                padding: 0.75rem 1rem;
            }
            
            .tab-content {
                padding: 1.5rem;
            }
            
            .notification-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem;
            }
            
            .header-section {
                padding: 1rem;
            }
            
            .tab-content {
                padding: 1rem;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
        
        /* Summernote editor optimizations */
        .note-editor {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
        }
        
        .note-editor.note-frame .note-editing-area .note-editable {
            padding: 15px !important;
        }
        
        .note-editor.note-frame:focus-within {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
        
        /* Select2 optimizations */
        .select2-container--default .select2-selection--single {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            height: 48px !important;
        }
        
        .select2-container--default .select2-selection--single:focus {
            border-color: #6366f1 !important;
        }
        
        /* Flatpickr optimizations */
        .flatpickr-input {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
        }
        
        .flatpickr-input:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
    </style>
</head>

<body>
    <!-- Mobile hamburger button -->
    <button class="hamburger-btn" onclick="toggleSidebar()">
        <svg class="hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    
    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="main-content">
        <!-- Header Section -->
        <div class="header-section">
            <h1>Administrative Notifications</h1>
            <p>Manage school-wide communications and announcements with full administrative privileges</p>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_notifications']; ?></div>
                <div class="stat-label">Total Notifications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['weekly_notifications']; ?></div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['urgent_notifications']; ?></div>
                <div class="stat-label">Urgent Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['acknowledgment_required']; ?></div>
                <div class="stat-label">Require Acknowledgment</div>
            </div>
        </div>

        <!-- Action Tabs -->
        <div class="action-tabs">
            <button class="tab-button active" onclick="switchTab('create')">Create Notification</button>
            <button class="tab-button" onclick="switchTab('manage')">Manage Notifications</button>
            <button class="tab-button" onclick="switchTab('analytics')">Analytics</button>
        </div>

        <!-- Create Notification Tab -->
        <div id="create-tab" class="tab-content active">
            <div id="notification-alert"></div>
            
            <form id="create-notification-form">
                <div class="form-section">
                    <h3>📝 Notification Details</h3>
                    
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
                                <option value="urgent">Urgent Notice</option>
                                <option value="academic">Academic Notice</option>
                                <option value="administrative">Administrative Notice</option>
                                <option value="event">Event Announcement</option>
                                <option value="reminder">Reminder</option>
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
                    <h3>🎯 Target Recipients</h3>
                    
                    <div class="targeting-section">
                        <div class="form-group">
                            <label for="targeting-mode">Select targeting mode:</label>
                            <select id="targeting-mode" name="targeting_mode" class="form-control" onchange="handleTargetingModeChange(this.value)">
                                <option value="classes">Select Classes</option>
                                <option value="all_school">All School</option>
                                <option value="role_based">By Role</option>
                                <option value="individual">Individual Recipients</option>
                            </select>
                        </div>
                        
                        <!-- All School Option -->
                        <div id="all-school-section" class="targeting-options">
                            <div class="alert alert-info">
                                <strong>All School:</strong> This notification will be sent to all teachers, students, and staff members.
                            </div>
                        </div>
                        
                        <!-- Role Based Option -->
                        <div id="role-based-section" class="targeting-options">
                            <div class="form-group">
                                <label>Select roles to target:</label>
                                <div class="role-checkboxes">
                                    <label><input type="checkbox" name="target_roles[]" value="teachers"> All Teachers</label>
                                    <label><input type="checkbox" name="target_roles[]" value="students"> All Students</label>
                                    <label><input type="checkbox" name="target_roles[]" value="staff"> All Staff</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Individual Recipients Option -->
                        <div id="individual-section" class="targeting-options">
                            <div class="form-group">
                                <label for="individual-search">Search and select individuals:</label>
                                <input type="text" id="individual-search" class="form-control" placeholder="Type name or email to search...">
                                <div id="individual-results" class="search-results"></div>
                                <div id="selected-individuals" class="selected-recipients"></div>
                            </div>
                        </div>
                        
                        <!-- Classes Option (default) -->
                        <div id="classes-section" class="targeting-options active">
                            <p>Select the classes you want to send this notification to:</p>
                            <div class="class-selector">
                                <?php foreach ($all_classes as $class): ?>
                                <div class="class-option" onclick="toggleClassSelection(this)">
                                    <input type="checkbox" name="target_classes[]" 
                                           value="class_<?php echo $class['class_id']; ?>_section_<?php echo $class['section_id']; ?>">
                                    <div class="class-info">
                                        <div class="class-name"><?php echo htmlspecialchars($class['class_name']); ?></div>
                                        <div class="section-name">Section: <?php echo htmlspecialchars($class['section_name']); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>⚙️ Additional Options</h3>
                    
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
                            <input type="checkbox" name="requires_acknowledgment" value="1">
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
                   <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
               </div>
           </form>
       </div>

       <!-- Manage Notifications Tab -->
       <div id="manage-tab" class="tab-content">
           <div class="notification-list">
               <div id="manage-notifications-loading" class="loading">
                   Loading notifications...
               </div>
               <div id="manage-notifications-list"></div>
           </div>
       </div>

       <!-- Analytics Tab -->
       <div id="analytics-tab" class="tab-content">
           <div class="analytics-grid">
               <div class="analytics-card">
                   <h4>Notification Performance</h4>
                   <div id="analytics-performance">Loading...</div>
               </div>
               <div class="analytics-card">
                   <h4>Acknowledgment Rates</h4>
                   <div id="analytics-acknowledgment">Loading...</div>
               </div>
               <div class="analytics-card">
                   <h4>Recent Activity</h4>
                   <div id="analytics-activity">Loading...</div>
               </div>
           </div>
       </div>
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
       let currentTargetingMode = 'classes';
       let selectedIndividuals = [];

       // Initialize the page
       $(document).ready(function() {
           initializePage();
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

       function initializePage() {
           // Initialize rich text editor
           $('#notification-message').summernote({
               height: 200,
               toolbar: [
                   ['style', ['style']],
                   ['font', ['bold', 'underline', 'clear']],
                   ['color', ['color']],
                   ['para', ['ul', 'ol', 'paragraph']],
                   ['table', ['table']],
                   ['insert', ['link']],
                   ['view', ['fullscreen', 'help']]
               ]
           });

           // Initialize date pickers
           flatpickr("#expires-at", {
               enableTime: true,
               dateFormat: "Y-m-d H:i",
               minDate: "today"
           });

           flatpickr("#scheduled-for", {
               enableTime: true,
               dateFormat: "Y-m-d H:i",
               minDate: "today"
           });

           // Form submission handler
           $('#create-notification-form').on('submit', handleFormSubmission);

           // Priority selector handlers
           $('.priority-option').on('click', function() {
               $('.priority-option').removeClass('selected');
               $(this).addClass('selected');
               selectedPriority = $(this).data('priority');
           });

           // Individual search handler
           $('#individual-search').on('input', debounce(function() {
               searchIndividuals($('#individual-search').val());
           }, 300));
       }

       function switchTab(tab) {
           // Update tab buttons
           $('.tab-button').removeClass('active');
           $(`.tab-button:contains(${tab.charAt(0).toUpperCase() + tab.slice(1)})`).addClass('active');

           // Update tab content
           $('.tab-content').removeClass('active');
           $(`#${tab}-tab`).addClass('active');

           currentTab = tab;

           // Load data for the selected tab
           if (tab === 'manage') {
               loadManageNotifications();
           } else if (tab === 'analytics') {
               loadAnalytics();
           }
       }

       function handleTargetingModeChange(mode) {
           currentTargetingMode = mode;
           
           // Hide all targeting options first
           $('.targeting-options').removeClass('active').hide();
           
           // Show selected targeting option
           $(`#${mode}-section`).addClass('active').show();
       }

       function toggleClassSelection(element) {
           const checkbox = $(element).find('input[type="checkbox"]');
           const isChecked = !checkbox.prop('checked');
           
           checkbox.prop('checked', isChecked);
           
           if (isChecked) {
               $(element).addClass('selected');
           } else {
               $(element).removeClass('selected');
           }
       }

       function searchIndividuals(query) {
           if (query.length < 2) {
               $('#individual-results').hide();
               return;
           }

           console.log('Searching for:', query);

           // API call to search for individuals
           $.ajax({
               url: '/erp/backend/api/notifications?action=search_users',
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json'
               },
               data: JSON.stringify({ query: query }),
               success: function(response) {
                   console.log('Search response:', response);
                   if (response.success) {
                       displaySearchResults(response.data);
                   } else {
                       console.error('Search failed:', response.message);
                       $('#individual-results').html('<div style="color: red; padding: 10px;">Error: ' + response.message + '</div>').show();
                   }
               },
               error: function(xhr, status, error) {
                   console.error('Search error:', xhr.responseText);
                   $('#individual-results').html('<div style="color: red; padding: 10px;">Network error: ' + error + '</div>').show();
               }
           });
       }

       function displaySearchResults(users) {
           let html = '';
           users.forEach(user => {
               if (!selectedIndividuals.find(selected => selected.id === user.id)) {
                   html += `
                       <div class="search-result-item" onclick="selectIndividual(${user.id}, '${user.name}', '${user.role}')">
                           <strong>${user.name}</strong> - ${user.role}
                           <br><small>${user.email}</small>
                       </div>
                   `;
               }
           });
           
           $('#individual-results').html(html).show();
       }

       function selectIndividual(id, name, role) {
           selectedIndividuals.push({ id, name, role });
           updateSelectedIndividuals();
           $('#individual-search').val('');
           $('#individual-results').hide();
       }

       function removeIndividual(id) {
           selectedIndividuals = selectedIndividuals.filter(individual => individual.id !== id);
           updateSelectedIndividuals();
       }

       function updateSelectedIndividuals() {
           let html = '';
           selectedIndividuals.forEach(individual => {
               html += `
                   <div class="recipient-tag">
                       ${individual.name} (${individual.role})
                       <span class="remove" onclick="removeIndividual(${individual.id})">×</span>
                   </div>
               `;
           });
           $('#selected-individuals').html(html);
       }

       function handleFormSubmission(e) {
           e.preventDefault();
           
           // Validate form
           const title = $('#notification-title').val().trim();
           const message = $('#notification-message').summernote('code').trim();

           if (!title) {
               showAlert('Please enter a notification title', 'error');
               return;
           }

           if (!message || message === '<p><br></p>') {
               showAlert('Please enter a notification message', 'error');
               return;
           }

           // Validate targeting based on mode
           if (!validateTargeting()) {
               return;
           }

           // Prepare form data based on targeting mode
           const formData = {
               title: title,
               message: message,
               type: $('#notification-type').val(),
               priority: selectedPriority,
               expires_at: $('#expires-at').val() || null,
               scheduled_for: $('#scheduled-for').val() || null,
               requires_acknowledgment: $('input[name="requires_acknowledgment"]').is(':checked') ? 1 : 0
           };

           // Set targeting data based on mode
           switch (currentTargetingMode) {
               case 'all_school':
                   formData.target_type = 'all_school';
                   formData.target_value = 'all';
                   break;
               case 'role_based':
                   const selectedRoles = $('input[name="target_roles[]"]:checked').map(function() {
                       return this.value;
                   }).get();
                   formData.target_type = 'role_based';
                   formData.target_value = selectedRoles.join(',');
                   break;
               case 'individual':
                   formData.target_type = 'individual';
                   formData.target_value = selectedIndividuals.map(ind => ind.id).join(',');
                   break;
               case 'classes':
               default:
                   const selectedClasses = $('input[name="target_classes[]"]:checked').map(function() {
                       return this.value;
                   }).get();
                   formData.target_type = 'multiple_classes';
                   formData.target_value = selectedClasses.join(',');
                   break;
           }

           // Send to API
           $.ajax({
               url: '/erp/backend/api/notifications?action=create',
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json'
               },
               data: JSON.stringify(formData),
               success: function(response) {
                   if (response.success) {
                       showAlert('Notification sent successfully!', 'success');
                       resetForm();
                       // Reload stats
                       location.reload();
                   } else {
                       showAlert(response.message || 'Failed to send notification', 'error');
                   }
               },
               error: function() {
                   showAlert('Network error. Please try again.', 'error');
               }
           });
       }

       function validateTargeting() {
           switch (currentTargetingMode) {
               case 'role_based':
                   const selectedRoles = $('input[name="target_roles[]"]:checked');
                   if (selectedRoles.length === 0) {
                       showAlert('Please select at least one role to target', 'error');
                       return false;
                   }
                   break;
               case 'individual':
                   if (selectedIndividuals.length === 0) {
                       showAlert('Please select at least one individual to notify', 'error');
                       return false;
                   }
                   break;
               case 'classes':
                   const selectedClasses = $('input[name="target_classes[]"]:checked');
                   if (selectedClasses.length === 0) {
                       showAlert('Please select at least one class to notify', 'error');
                       return false;
                   }
                   break;
           }
           return true;
       }

       function loadManageNotifications() {
           $('#manage-notifications-loading').show();
           $('#manage-notifications-list').empty();

           $.ajax({
               url: '/erp/backend/api/notifications?action=list&limit=50',
               method: 'GET',
               success: function(response) {
                   $('#manage-notifications-loading').hide();
                   
                   if (response.success) {
                       const notifications = response.data.filter(notif => 
                           notif.created_by == <?php echo $user_id; ?>
                       );
                       
                       if (notifications.length === 0) {
                           $('#manage-notifications-list').html('<p class="loading">No notifications found.</p>');
                           return;
                       }

                       let html = '';
                       notifications.forEach(function(notification) {
                           html += buildNotificationItem(notification, true);
                       });
                       
                       $('#manage-notifications-list').html(html);
                   } else {
                       $('#manage-notifications-list').html('<p class="loading">Error loading notifications.</p>');
                   }
               },
               error: function() {
                   $('#manage-notifications-loading').hide();
                   $('#manage-notifications-list').html('<p class="loading">Network error loading notifications.</p>');
               }
           });
       }

       function loadAnalytics() {
           // Load analytics data
           $.ajax({
               url: '/erp/backend/api/notifications?action=analytics',
               method: 'GET',
               success: function(response) {
                   if (response.success) {
                       updateAnalytics(response.data);
                   }
               }
           });
       }

       function updateAnalytics(data) {
           $('#analytics-performance').html(`
               <p>Total Sent: ${data.total_sent || 0}</p>
               <p>Read Rate: ${data.read_rate || 0}%</p>
               <p>Response Rate: ${data.response_rate || 0}%</p>
           `);
           
           $('#analytics-acknowledgment').html(`
               <p>Acknowledgments Required: ${data.ack_required || 0}</p>
               <p>Acknowledgments Received: ${data.ack_received || 0}</p>
               <p>Acknowledgment Rate: ${data.ack_rate || 0}%</p>
           `);
           
           $('#analytics-activity').html(`
               <p>Notifications This Week: ${data.weekly_count || 0}</p>
               <p>Urgent Notifications: ${data.urgent_count || 0}</p>
               <p>Last Sent: ${data.last_sent || 'Never'}</p>
           `);
       }

       function buildNotificationItem(notification, showActions) {
           const priorityClass = notification.priority || 'normal';
           const priorityColor = {
               'normal': '#10b981',
               'important': '#f59e0b',
               'urgent': '#ef4444'
           }[priorityClass];

           const date = new Date(notification.created_at).toLocaleDateString();
           const time = new Date(notification.created_at).toLocaleTimeString();

           return `
               <div class="notification-item">
                   <div class="notification-header">
                       <div>
                           <div class="notification-title" style="border-left: 4px solid ${priorityColor}; padding-left: 1rem;">
                               ${notification.title}
                           </div>
                           <div class="notification-meta">
                               <span>Type: ${notification.type}</span>
                               <span>Priority: ${notification.priority}</span>
                               <span>${date} at ${time}</span>
                               <span>Status: ${notification.is_active ? 'Active' : 'Inactive'}</span>
                           </div>
                       </div>
                       ${showActions ? `
                       <div>
                           <button class="btn btn-sm btn-secondary" onclick="toggleNotificationStatus(${notification.id}, ${notification.is_active})">
                               ${notification.is_active ? 'Deactivate' : 'Activate'}
                           </button>
                       </div>
                       ` : ''}
                   </div>
                   <div class="notification-content">
                       ${notification.message}
                   </div>
               </div>
           `;
       }

       function toggleNotificationStatus(id, currentStatus) {
           const action = currentStatus ? 'deactivate' : 'activate';
           
           $.ajax({
               url: `/erp/backend/api/notifications?action=${action}`,
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json'
               },
               data: JSON.stringify({ notification_id: id }),
               success: function(response) {
                   if (response.success) {
                       showAlert(`Notification ${action}d successfully!`, 'success');
                       loadManageNotifications();
                   } else {
                       showAlert(response.message || `Failed to ${action} notification`, 'error');
                   }
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
           $('input[name="target_roles[]"]').prop('checked', false);
           selectedPriority = 'normal';
           selectedIndividuals = [];
           updateSelectedIndividuals();
           $('#targeting-mode').val('classes');
           handleTargetingModeChange('classes');
       }

       function showAlert(message, type) {
           const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
           const alertHtml = `
               <div class="alert ${alertClass}">
                   ${message}
               </div>
           `;
           
           $('#notification-alert').html(alertHtml);
           
           setTimeout(() => {
               $('#notification-alert').empty();
           }, 5000);
       }

       function debounce(func, wait) {
           let timeout;
           return function executedFunction(...args) {
               const later = () => {
                   clearTimeout(timeout);
                   func(...args);
               };
               clearTimeout(timeout);
               timeout = setTimeout(later, wait);
           };
       }

       // Handle window resize for sidebar responsiveness
       $(window).resize(function() {
           if ($(window).width() > 768) {
               $('.sidebar-overlay').removeClass('active');
               $('.sidebar').removeClass('active');
           }
       });

       // Close sidebar when clicking outside on mobile
       $(document).click(function(e) {
           if ($(window).width() <= 768) {
               if (!$(e.target).closest('.sidebar, .hamburger-btn').length) {
                   $('.sidebar').removeClass('active');
                   $('.sidebar-overlay').removeClass('active');
               }
           }
       });
   </script>
</body>
</html>
