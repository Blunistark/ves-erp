
https://vinodhenglishschool.com/erp/teachers/dashboard/notifications.php

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Notifications - Dashboard</title>
    
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
            background: #10b981;
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
            background-color: #10b981;
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
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .class-targeting {
            background-color: #f0fdf4;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 2px solid #bbf7d0;
        }
        
        .class-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
            border-color: #10b981;
        }
        
        .class-option.selected {
            border-color: #10b981;
            background-color: #ecfdf5;
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
            background-color: #10b981;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #059669;
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
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
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
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
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }
        
        /* Select2 optimizations */
        .select2-container--default .select2-selection--single {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            height: 48px !important;
        }
        
        .select2-container--default .select2-selection--single:focus {
            border-color: #10b981 !important;
        }
        
        /* Flatpickr optimizations */
        .flatpickr-input {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
        }
        
        .flatpickr-input:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
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
            <h1>Teacher Notifications</h1>
            <p>Communicate with your students and manage class announcements</p>
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
                                        <span style="color: #10b981; font-size: 0.75rem; margin-left: 0.5rem;">
                                            (<?php echo htmlspecialchars($class['assignment_type']); ?>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($teacher_classes)): ?>
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
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
                height: 150,
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
            
            if (isChecked) {
                $(element).addClass('selected');
            } else {
                $(element).removeClass('selected');
            }
        }

        function handleFormSubmission(e) {
            e.preventDefault();
            
            // Validate form
            const title = $('#notification-title').val().trim();
            const message = $('#notification-message').summernote('code').trim();
            const selectedClasses = $('input[name="target_classes[]"]:checked');

            if (!title) {
                showAlert('Please enter a notification title', 'error');
                return;
            }

            if (!message || message === '<p><br></p>') {
                showAlert('Please enter a notification message', 'error');
                return;
            }

            if (selectedClasses.length === 0) {
                showAlert('Please select at least one class to notify', 'error');
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
                    if (response.success) {
                        showAlert('Notification sent successfully!', 'success');
                        resetForm();
                        loadNotificationCounts();
                    } else {
                        showAlert(response.message || 'Failed to send notification', 'error');
                    }
                },
                error: function() {
                    showAlert('Network error. Please try again.', 'error');
                }
            });
        }

        function loadNotificationCounts() {
            $.ajax({
                url: '/erp/backend/api/notifications?action=count',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#unreadNotificationsCount').text(response.data.unread || 0);
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
                        $('#sentNotificationsCount').text(thisWeek.length);
                    }
                }
            });

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
                           $('#sent-notifications-list').html('<p class="loading">No sent notifications found.</p>');
                           return;
                       }

                       let html = '';
                       sentNotifications.forEach(function(notification) {
                           html += buildNotificationItem(notification, true);
                       });
                       
                       $('#sent-notifications-list').html(html);
                   } else {
                       $('#sent-notifications-list').html('<p class="loading">Error loading notifications.</p>');
                   }
               },
               error: function() {
                   $('#sent-notifications-loading').hide();
                   $('#sent-notifications-list').html('<p class="loading">Network error loading notifications.</p>');
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
                           $('#received-notifications-list').html('<p class="loading">No received notifications found.</p>');
                           return;
                       }

                       let html = '';
                       receivedNotifications.forEach(function(notification) {
                           html += buildNotificationItem(notification, false);
                       });
                       
                       $('#received-notifications-list').html(html);
                   } else {
                       $('#received-notifications-list').html('<p class="loading">Error loading notifications.</p>');
                   }
               },
               error: function() {
                   $('#received-notifications-loading').hide();
                   $('#received-notifications-list').html('<p class="loading">Network error loading notifications.</p>');
               }
           });
       }

       function buildNotificationItem(notification, isSent) {
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
                               ${isSent ? `<span>To: Multiple Classes</span>` : `<span>From: ${notification.created_by_name || 'System'}</span>`}
                           </div>
                       </div>
                   </div>
                   <div class="notification-content">
                       ${notification.message}
                   </div>
                   ${!isSent && !notification.is_read ? `
                       <div style="margin-top: 1rem;">
                           <button class="btn btn-primary" onclick="markAsRead(${notification.id})">
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
           selectedPriority = 'normal';
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
