<?php 
include 'sidebar.php'; 
include 'con.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create_notification') {
        try {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $message = mysqli_real_escape_string($conn, $_POST['message']);
            $type = mysqli_real_escape_string($conn, $_POST['type']);
            $priority = mysqli_real_escape_string($conn, $_POST['priority']);
            $requires_acknowledgment = isset($_POST['requires_acknowledgment']) ? 1 : 0;
            $target_type = mysqli_real_escape_string($conn, $_POST['target_type']);
            $target_value = mysqli_real_escape_string($conn, $_POST['target_value'] ?? '');
            $expires_at = !empty($_POST['expires_at']) ? mysqli_real_escape_string($conn, $_POST['expires_at']) : NULL;
            $scheduled_for = !empty($_POST['scheduled_for']) ? mysqli_real_escape_string($conn, $_POST['scheduled_for']) : NULL;
            
            // Insert notification
            $sql = "INSERT INTO notifications (title, message, type, priority, requires_acknowledgment, created_by, expires_at, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssiis", $title, $message, $type, $priority, $requires_acknowledgment, $user_id, $expires_at);
            
            if (mysqli_stmt_execute($stmt)) {
                $notification_id = mysqli_insert_id($conn);
                
                // Insert targeting information
                $target_sql = "INSERT INTO notification_targets (notification_id, target_type, target_value) VALUES (?, ?, ?)";
                $target_stmt = mysqli_prepare($conn, $target_sql);
                mysqli_stmt_bind_param($target_stmt, "iss", $notification_id, $target_type, $target_value);
                mysqli_stmt_execute($target_stmt);
                
                // If scheduled, add to queue
                if ($scheduled_for) {
                    $queue_sql = "INSERT INTO notification_queue (notification_id, scheduled_for) VALUES (?, ?)";
                    $queue_stmt = mysqli_prepare($conn, $queue_sql);
                    mysqli_stmt_bind_param($queue_stmt, "is", $notification_id, $scheduled_for);
                    mysqli_stmt_execute($queue_stmt);
                }
                
                $success_message = "Notification created successfully!";
            } else {
                $error_message = "Error creating notification: " . mysqli_error($conn);
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch notifications with analytics
$notifications_sql = "
    SELECT n.*, 
           u.full_name as created_by_name,
           COALESCE(na.total_recipients, 0) as total_recipients,
           COALESCE(na.total_read, 0) as total_read,
           COALESCE(na.total_acknowledged, 0) as total_acknowledged,
           COALESCE(na.read_rate, 0) as read_rate,
           COALESCE(na.acknowledgment_rate, 0) as acknowledgment_rate
    FROM notifications n 
    LEFT JOIN users u ON n.created_by = u.id 
    LEFT JOIN notification_analytics na ON n.id = na.notification_id 
    WHERE n.type IN ('admin', 'announcement') 
    ORDER BY n.created_at DESC 
    LIMIT 50";
$notifications_result = mysqli_query($conn, $notifications_sql);

// Fetch classes for targeting
$classes_sql = "SELECT id, name as class_name FROM classes ORDER BY name";
$classes_result = mysqli_query($conn, $classes_sql);

// Fetch sections for targeting
$sections_sql = "SELECT s.id, s.name as section_name, c.name as class_name 
                 FROM sections s 
                 JOIN classes c ON s.class_id = c.id
                 ORDER BY c.name, s.name";
$sections_result = mysqli_query($conn, $sections_sql);

// Fetch notification statistics
$stats_sql = "
    SELECT 
        COUNT(*) as total_notifications,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as this_week,
        COUNT(CASE WHEN priority = 'urgent' THEN 1 END) as urgent_count,
        COUNT(CASE WHEN requires_acknowledgment = 1 THEN 1 END) as require_ack
    FROM notifications 
    WHERE type IN ('admin', 'announcement') AND is_active = 1";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Management - Admin Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .notification-management {
            padding: 2rem;
            background-color: #f8fafc;
            min-height: 100vh;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .stats-grid {
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
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }
        
        .content-tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
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
            border-radius: 12px;
        }
        
        .tab-button.active {
            background-color: #4f46e5;
            color: white;
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
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .targeting-section {
            background-color: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 2px solid #e5e7eb;
        }
        
        .targeting-option {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .targeting-option:hover {
            border-color: #4f46e5;
        }
        
        .targeting-option.selected {
            border-color: #4f46e5;
            background-color: #eef2ff;
        }
        
        .targeting-option input[type="radio"] {
            margin-right: 1rem;
        }
        
        .option-details {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .option-details.active {
            display: block;
        }
        
        .priority-selector {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .priority-option {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .priority-option.normal {
            border-color: #10b981;
            color: #047857;
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
            background-color: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
        }
        
        .btn-success {
            background-color: #059669;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #047857;
        }
        
        .notification-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 0.5rem;
        }
        
        .notification-title {
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        
        .notification-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .notification-content {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .notification-analytics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            background: #f9fafb;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        .analytics-item {
            text-align: center;
        }
        
        .analytics-number {
            font-weight: 700;
            color: #4f46e5;
        }
        
        .analytics-label {
            color: #6b7280;
        }
        
        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-normal {
            background-color: #d1fae5;
            color: #047857;
        }
        
        .priority-important {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .priority-urgent {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .notification-management {
                padding: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .tab-button {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>

<body>
    <main class="main-content">
        <div class="notification-management">
            <div class="header-section">
                <h1>📢 Notification Management</h1>
                <p>Create and manage notifications for the entire school community with granular targeting options</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Statistics Dashboard -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_notifications']; ?></div>
                    <div class="stat-label">Total Notifications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['this_week']; ?></div>
                    <div class="stat-label">This Week</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['urgent_count']; ?></div>
                    <div class="stat-label">Urgent Alerts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['require_ack']; ?></div>
                    <div class="stat-label">Require Acknowledgment</div>
                </div>
            </div>

            <!-- Content Tabs -->
            <div class="content-tabs">
                <button class="tab-button active" onclick="showTab('create')">📝 Create Notification</button>
                <button class="tab-button" onclick="showTab('manage')">📋 Manage Notifications</button>
                <button class="tab-button" onclick="showTab('analytics')">📊 Analytics</button>
            </div>

            <!-- Create Notification Tab -->
            <div id="create-tab" class="tab-content active">
                <h2>Create New Notification</h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_notification">
                    
                    <div class="form-group">
                        <label for="title">Notification Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required 
                               placeholder="Enter a clear, descriptive title">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message Content *</label>
                        <textarea id="message" name="message" class="form-control" rows="6" required 
                                  placeholder="Enter the notification message"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Notification Type *</label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="announcement">📢 Announcement</option>
                                <option value="admin">🔔 Administrative Notice</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Priority Level *</label>
                            <div class="priority-selector">
                                <div class="priority-option normal selected" data-priority="normal">
                                    📗 Normal
                                </div>
                                <div class="priority-option important" data-priority="important">
                                    📙 Important
                                </div>
                                <div class="priority-option urgent" data-priority="urgent">
                                    📕 Urgent
                                </div>
                            </div>
                            <input type="hidden" id="priority" name="priority" value="normal">
                        </div>
                    </div>
                    
                    <!-- Targeting Section -->
                    <div class="targeting-section">
                        <h3>📍 Target Audience</h3>
                        
                        <div class="targeting-option selected" data-target="all_school">
                            <input type="radio" name="target_type" value="all_school" checked>
                            <div>
                                <strong>🏫 Entire School</strong>
                                <p>Send to all teachers, students, and staff</p>
                            </div>
                        </div>
                        
                        <div class="targeting-option" data-target="role">
                            <input type="radio" name="target_type" value="role">
                            <div>
                                <strong>👥 By Role</strong>
                                <p>Target specific user roles</p>
                            </div>
                        </div>
                        
                        <div id="role-details" class="option-details">
                            <select name="target_value" class="form-control">
                                <option value="teachers">👨‍🏫 All Teachers</option>
                                <option value="students">👨‍🎓 All Students</option>
                                <option value="admin">👤 Admin Staff</option>
                            </select>
                        </div>
                        
                        <div class="targeting-option" data-target="class">
                            <input type="radio" name="target_type" value="class">
                            <div>
                                <strong>🏛️ By Class</strong>
                                <p>Target specific classes</p>
                            </div>
                        </div>
                        
                        <div id="class-details" class="option-details">
                            <select name="target_value" class="form-control">
                                <option value="">Select Class</option>
                                <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                                    <option value="class_<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="targeting-option" data-target="section">
                            <input type="radio" name="target_type" value="section">
                            <div>
                                <strong>📝 By Section</strong>
                                <p>Target specific sections</p>
                            </div>
                        </div>
                        
                        <div id="section-details" class="option-details">
                            <select name="target_value" class="form-control">
                                <option value="">Select Section</option>
                                <?php mysqli_data_seek($sections_result, 0); while ($section = mysqli_fetch_assoc($sections_result)): ?>
                                    <option value="section_<?php echo $section['id']; ?>">
                                        <?php echo htmlspecialchars($section['class_name'] . ' - ' . $section['section_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Additional Options -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expires_at">Expiry Date (Optional)</label>
                            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="scheduled_for">Schedule For Later (Optional)</label>
                            <input type="datetime-local" id="scheduled_for" name="scheduled_for" class="form-control">
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="requires_acknowledgment" name="requires_acknowledgment">
                        <label for="requires_acknowledgment">✓ Require acknowledgment from recipients</label>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            📤 Create Notification
                        </button>
                        <button type="reset" class="btn" style="background-color: #6b7280; color: white; margin-left: 1rem;">
                            🔄 Reset Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- Manage Notifications Tab -->
            <div id="manage-tab" class="tab-content">
                <h2>📋 Recent Notifications</h2>
                
                <div class="notification-list">
                    <?php if (mysqli_num_rows($notifications_result) > 0): ?>
                        <?php while ($notification = mysqli_fetch_assoc($notifications_result)): ?>
                            <div class="notification-item">
                                <div class="notification-header">
                                    <h3 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                    <span class="priority-badge priority-<?php echo $notification['priority']; ?>">
                                        <?php echo ucfirst($notification['priority']); ?>
                                    </span>
                                </div>
                                
                                <div class="notification-meta">
                                    <span>📅 <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></span>
                                    <span>👤 <?php echo htmlspecialchars($notification['created_by_name'] ?? 'System'); ?></span>
                                    <span>📑 <?php echo ucfirst($notification['type']); ?></span>
                                    <?php if ($notification['requires_acknowledgment']): ?>
                                        <span>✓ Requires Ack</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="notification-content">
                                    <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                                </div>
                                
                                <?php if ($notification['total_recipients'] > 0): ?>
                                    <div class="notification-analytics">
                                        <div class="analytics-item">
                                            <div class="analytics-number"><?php echo $notification['total_recipients']; ?></div>
                                            <div class="analytics-label">Recipients</div>
                                        </div>
                                        <div class="analytics-item">
                                            <div class="analytics-number"><?php echo $notification['total_read']; ?></div>
                                            <div class="analytics-label">Read</div>
                                        </div>
                                        <div class="analytics-item">
                                            <div class="analytics-number"><?php echo round($notification['read_rate'], 1); ?>%</div>
                                            <div class="analytics-label">Read Rate</div>
                                        </div>
                                        <?php if ($notification['requires_acknowledgment']): ?>
                                            <div class="analytics-item">
                                                <div class="analytics-number"><?php echo $notification['total_acknowledged']; ?></div>
                                                <div class="analytics-label">Acknowledged</div>
                                            </div>
                                            <div class="analytics-item">
                                                <div class="analytics-number"><?php echo round($notification['acknowledgment_rate'], 1); ?>%</div>
                                                <div class="analytics-label">Ack Rate</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="notification-item" style="text-align: center; color: #6b7280;">
                            <h3>📭 No notifications yet</h3>
                            <p>Create your first notification to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics-tab" class="tab-content">
                <h2>📊 Notification Analytics</h2>
                <p>Detailed analytics and insights coming soon...</p>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        // Priority selector functionality
        document.querySelectorAll('.priority-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.priority-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                document.getElementById('priority').value = this.dataset.priority;
            });
        });
        
        // Targeting option functionality
        document.querySelectorAll('.targeting-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.targeting-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                document.querySelectorAll('.option-details').forEach(detail => {
                    detail.classList.remove('active');
                });
                
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
                
                const target = this.dataset.target;
                const detailElement = document.getElementById(target + '-details');
                if (detailElement) {
                    detailElement.classList.add('active');
                }
            });
        });
        
        // Initialize Summernote for rich text editing
        $(document).ready(function() {
            $('#message').summernote({
                height: 200,
                placeholder: 'Enter your notification message here...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // Initialize Select2 for better dropdowns
            $('select').select2({
                theme: 'default',
                width: '100%'
            });
            
            // Initialize Flatpickr for date/time pickers
            flatpickr("#expires_at", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today"
            });
            
            flatpickr("#scheduled_for", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today"
            });
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!title || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Title must be at least 5 characters long.');
                return;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Message must be at least 10 characters long.');
                return;
            }
        });
    </script>
</body>
</html>