<?php
// This file assumes the following variables are in scope from the parent PHP file (e.g., notifications.php):
// - $conn: Database connection
// - $user_id: Logged-in user's ID
// - $is_headmaster: Boolean, true if the user is a headmaster
// - $teacher_classes: Array of classes for a regular teacher
// - $permissions: Array of user permissions
// - $all_classes_for_headmaster_view: Array of all classes, if $is_headmaster is true. 
//   (This should be populated in notifications.php before including this file)

// Placeholder function for headmaster class fetching. 
// Data should ideally be prepared in the main notifications.php.
if (!function_exists('prepareAllClassesForHeadmasterView')) {
    function prepareAllClassesForHeadmasterView($db_conn, $hm_user_id) {
        $all_classes_data = [];
        // This SQL should be robust and secure, same as in the original notifications.php
        $sql = "SELECT DISTINCT c.id, c.name as class_name, s.id as section_id, s.name as section_name,
                       CASE 
                           WHEN s.class_teacher_user_id = ? THEN 'Class Teacher (You)'
                           WHEN EXISTS (
                               SELECT 1 FROM timetables t 
                               JOIN timetable_periods tp ON t.id = tp.timetable_id 
                               WHERE t.class_id = c.id 
                               AND t.section_id = s.id 
                               AND tp.teacher_id = ?
                               AND t.status = 'published'
                           ) THEN 'Subject Teacher (You)'
                           ELSE 'Other Class'
                       END as assignment_type
                FROM classes c
                JOIN sections s ON c.id = s.class_id
                ORDER BY c.name, s.name";
        $stmt = mysqli_prepare($db_conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $hm_user_id, $hm_user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $all_classes_data[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
        return $all_classes_data;
    }
}
// Example in parent notifications.php:
// if ($is_headmaster) { 
//     $all_classes_for_headmaster_view = prepareAllClassesForHeadmasterView($conn, $user_id); 
// }
?>

<!-- Specific CSS for Notifications Page (Original lines 87-89) -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* Extracted from notifications.php (Original lines 91-429) */
    .notification-dashboard {
        padding: 2rem;
        background-color: #f8fafc;
    }
    .header-section {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .header-section h1 { margin: 0; font-size: 2rem; font-weight: 700; }
    .header-section p { margin: 0.5rem 0 0 0; opacity: 0.9; }
    .quick-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
    .stat-number { font-size: 2rem; font-weight: 700; color: #10b981; margin-bottom: 0.5rem; }
    .stat-label { color: #6b7280; font-weight: 600; }
    .action-tabs { display: flex; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem; }
    .tab-button { flex: 1; padding: 1rem 2rem; background: none; border: none; cursor: pointer; font-weight: 600; color: #6b7280; transition: all 0.3s ease; border-radius: 12px; }
    .tab-button.active { background-color: #10b981; color: white; }
    .tab-content { display: none; background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .tab-content.active { display: block; }
    .form-section { margin-bottom: 2rem; }
    .form-section h3 { margin-bottom: 1rem; color: #374151; font-weight: 600; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; }
    .form-control { width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .class-targeting { background-color: #f0fdf4; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 2px solid #bbf7d0; }
    .class-selector { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem; }
    .class-option { display: flex; align-items: center; padding: 1rem; background: white; border-radius: 8px; border: 2px solid transparent; cursor: pointer; transition: all 0.3s ease; }
    .class-option:hover { border-color: #10b981; }
    .class-option.selected { border-color: #10b981; background-color: #ecfdf5; }
    .class-option input[type="checkbox"] { margin-right: 1rem; transform: scale(1.2); }
    .class-info { flex: 1; }
    .class-name { font-weight: 600; color: #374151; }
    .section-name { font-size: 0.875rem; color: #6b7280; }
    .priority-selector { display: flex; gap: 1rem; flex-wrap: wrap; }
    .priority-option { padding: 0.75rem 1.5rem; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; background: white; font-weight: 600; }
    .priority-option.normal { border-color: #10b981; color: #059669; }
    .priority-option.important { border-color: #f59e0b; color: #d97706; }
    .priority-option.urgent { border-color: #ef4444; color: #dc2626; }
    .priority-option.selected { background-color: currentColor; color: white; }
    .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; text-decoration: none; }
    .btn-primary { background-color: #10b981; color: white; }
    .btn-primary:hover { background-color: #059669; }
    .btn-secondary { background-color: #6b7280; color: white; }
    .btn-secondary:hover { background-color: #4b5563; }
    .notification-list { background: white; border-radius: 12px; overflow: hidden; }
    .notification-item { padding: 1.5rem; border-bottom: 1px solid #e5e7eb; transition: background-color 0.3s ease; }
    .notification-item:hover { background-color: #f9fafb; }
    .notification-item:last-child { border-bottom: none; }
    .notification-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .notification-title { font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
    .notification-meta { display: flex; gap: 1rem; font-size: 0.875rem; color: #6b7280; }
    .notification-content { color: #374151; line-height: 1.6; }
    .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .alert-success { background-color: #ecfdf5; color: #065f46; border: 1px solid #bbf7d0; }
    .alert-error { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .loading { text-align: center; padding: 2rem; color: #6b7280; }
    @media (max-width: 768px) {
        .notification-dashboard { padding: 1rem; }
        .form-row { grid-template-columns: 1fr; }
        .class-selector { grid-template-columns: 1fr; }
        .priority-selector { flex-direction: column; }
    }
</style>

<!-- HTML Body Content (Original lines 433-723) -->
<div class="notification-dashboard">
    <div class="header-section">
        <h1>Teacher Notifications</h1>
        <p>Communicate with your students and manage class announcements</p>
    </div>

    <div class="quick-stats">
        <div class="stat-card">
            <div id="unreadNotificationsCount" class="stat-number">0</div>
            <div class="stat-label">Unread Notifications</div>
        </div>
        <div class="stat-card">
            <div id="sentNotificationsCount" class="stat-number">0</div>
            <div class="stat-label">Sent This Week</div>
        </div>
    </div>

    <div class="action-tabs">
        <button class="tab-button active" onclick="switchTab('create')">Create Notification</button>
        <button class="tab-button" onclick="switchTab('sent')">Sent</button>
        <button class="tab-button" onclick="switchTab('received')">Received</button>
    </div>

    <!-- Create Notification Tab -->
    <div id="create-tab" class="tab-content active">
        <form id="create-notification-form">
            <div class="form-section">
                <h3>Notification Details</h3>
                <div class="form-group">
                    <label for="notification-title">Title</label>
                    <input type="text" id="notification-title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="notification-message">Message</label>
                    <textarea id="notification-message" name="message" class="form-control"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="notification-type">Type</label>
                        <select id="notification-type" name="type" class="form-control">
                            <option value="general">General Announcement</option>
                            <option value="assignment">Assignment Reminder</option>
                            <option value="event">Event Notification</option>
                            <option value="alert">Urgent Alert</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <div class="priority-selector">
                            <div class="priority-option normal selected" data-priority="normal">Normal</div>
                            <div class="priority-option important" data-priority="important">Important</div>
                            <div class="priority-option urgent" data-priority="urgent">Urgent</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Target Audience</h3>
                <?php if ($is_headmaster): ?>
                <div class="form-group">
                    <label>Target Mode:</label>
                    <div>
                        <label><input type="radio" name="headmaster_target_mode" value="all_students" checked onchange="updateTargetMode(this.value)"> All Students in School</label>
                        <label><input type="radio" name="headmaster_target_mode" value="all_teachers" onchange="updateTargetMode(this.value)"> All Teachers in School</label>
                        <label><input type="radio" name="headmaster_target_mode" value="specific_classes_sections" onchange="updateTargetMode(this.value)"> Specific Classes/Sections</label>
                        <!-- Add more modes like all_parents if needed -->
                    </div>
                </div>
                <div id="headmasterSpecificClassesSelector" class="class-targeting" style="display: none;">
                    <p>Select the classes/sections you want to send this notification to:</p>
                    <div class="class-selector">
                        <?php if (!empty($all_classes_for_headmaster_view)): ?>
                            <?php foreach ($all_classes_for_headmaster_view as $class): ?>
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
                        <?php else: ?>
                            <p>No classes found in the system.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: // Regular Teacher ?>
                <div class="class-targeting">
                    <p>Select the classes you want to send this notification to:</p>
                    <div class="class-selector">
                        <?php if (!empty($teacher_classes)): ?>
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
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                            <p>No classes assigned yet.</p>
                            <p style="font-size: 0.875rem;">Contact the admin to get assigned to classes or timetable periods.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
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
            <div id="sent-notifications-loading" class="loading">Loading sent notifications...</div>
            <div id="sent-notifications-list"></div>
        </div>
    </div>

    <!-- Received Messages Tab -->
    <div id="received-tab" class="tab-content">
        <div class="notification-list">
            <div id="received-notifications-loading" class="loading">Loading received messages...</div>
            <div id="received-notifications-list"></div>
        </div>
    </div>
</div> <!-- End of .notification-dashboard -->

<?php if ($is_headmaster): ?>
<script>
    // This script was originally at lines 570-580 in notifications.php
    let headmasterTargetMode = 'all_students'; // Default for headmaster

    function updateTargetMode(mode) {
        headmasterTargetMode = mode;
        const specificClassesSelector = document.getElementById('headmasterSpecificClassesSelector');
        if (mode === 'specific_classes_sections') {
            specificClassesSelector.style.display = 'block';
        } else {
            specificClassesSelector.style.display = 'none';
        }
    }
    // Initialize based on default checked radio when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const initialModeRadio = document.querySelector('input[name="headmaster_target_mode"]:checked');
        if (initialModeRadio) {
            updateTargetMode(initialModeRadio.value);
        }
    });
</script>
<?php endif; ?>

<!-- External JS Libraries (Original lines 726-729) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Main Page Specific JS (Original lines 731-1052, with modifications) -->
<script>
    // Global variables
    let currentTab = 'create';
    let selectedPriority = 'normal';
    const phpUserId = <?php echo json_encode($user_id); ?>;
    const phpIsHeadmaster = <?php echo json_encode($is_headmaster); ?>;

    // Initialize the page
    $(document).ready(function() {
        initializePage();
        loadNotificationCounts();
        // Initial tab setup
        switchTab(currentTab); 
    });

    function initializePage() {
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

        flatpickr("#expires-at", { enableTime: true, dateFormat: "Y-m-d H:i", minDate: "today" });
        flatpickr("#scheduled-for", { enableTime: true, dateFormat: "Y-m-d H:i", minDate: "today" });

        $('#create-notification-form').on('submit', handleFormSubmission);

        $('.priority-option').on('click', function() {
            $('.priority-option').removeClass('selected');
            $(this).addClass('selected');
            selectedPriority = $(this).data('priority');
        });
    }

    function switchTab(tab) {
        $('.tab-button').removeClass('active');
        $('.tab-button').filter(function() { return $(this).text().toLowerCase().startsWith(tab); }).addClass('active');
        
        $('.tab-content').removeClass('active');
        $(`#${tab}-tab`).addClass('active');
        currentTab = tab;

        if (tab === 'sent') loadSentNotifications();
        else if (tab === 'received') loadReceivedNotifications();
    }

    function toggleClassSelection(element) {
        const checkbox = $(element).find('input[type="checkbox"]');
        const isChecked = !checkbox.prop('checked');
        checkbox.prop('checked', isChecked);
        $(element).toggleClass('selected', isChecked);
    }

    function handleFormSubmission(e) {
        e.preventDefault();
        
        const title = $('#notification-title').val().trim();
        const message = $('#notification-message').summernote('code').trim();
        const selectedClassesCheckboxes = $('input[name="target_classes[]"]:checked');

        if (!title) { showAlert('Please enter a notification title', 'error'); return; }
        if (!message || message === '<p><br></p>') { showAlert('Please enter a notification message', 'error'); return; }

        let target_type_val = 'multiple_classes';
        let target_value_val = Array.from(selectedClassesCheckboxes).map(el => el.value).join(',');

        if (phpIsHeadmaster) {
            const hmTargetMode = document.querySelector('input[name="headmaster_target_mode"]:checked').value;
            if (hmTargetMode === 'all_students') {
                target_type_val = 'all_students_in_school'; // Backend should map this
                target_value_val = null; 
            } else if (hmTargetMode === 'all_teachers') {
                target_type_val = 'all_teachers_in_school'; // Backend should map this
                target_value_val = null;
            } else if (hmTargetMode === 'specific_classes_sections') {
                if (selectedClassesCheckboxes.length === 0) {
                    showAlert('Please select at least one class/section for headmaster specific targeting.', 'error');
                    return;
                }
                target_type_val = 'multiple_classes'; // Uses the same logic as regular teacher for selected classes
            } else {
                 // Potentially other headmaster modes, for now, default or error
            }
        } else { // Regular teacher
            if (selectedClassesCheckboxes.length === 0) {
                showAlert('Please select at least one class to notify', 'error');
                return;
            }
        }
        
        const formData = {
            title: title,
            message: message,
            type: $('#notification-type').val(),
            priority: selectedPriority,
            target_type: target_type_val,
            target_value: target_value_val,
            expires_at: $('#expires-at').val() || null,
            scheduled_for: $('#scheduled-for').val() || null,
            requires_acknowledgment: $('input[name="requires_acknowledgment"]').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: '/erp/backend/api/notifications.php?action=create',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showAlert('Notification sent successfully!', 'success');
                    resetForm();
                    loadNotificationCounts();
                    if (currentTab === 'sent') loadSentNotifications(); // Refresh sent tab if active
                } else {
                    showAlert(response.message || 'Failed to send notification', 'error');
                }
            },
            error: function() { showAlert('Network error. Please try again.', 'error'); }
        });
    }

    function loadNotificationCounts() {
        $.ajax({
            url: '/erp/backend/api/notifications.php?action=count_summary', // Assuming a new or modified endpoint for both counts
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#unreadNotificationsCount').text(response.data.unread_received_count || 0);
                    $('#sentNotificationsCount').text(response.data.sent_this_week_count || 0);
                }
            },
            error: function() { 
                console.error('Error fetching notification counts.'); 
                $('#unreadNotificationsCount').text('Err');
                $('#sentNotificationsCount').text('Err');
            }
        });
    }

    function loadSentNotifications() {
        $('#sent-notifications-loading').show();
        $('#sent-notifications-list').empty();
        $.ajax({
            url: '/erp/backend/api/notifications.php?action=list&filter=sent&limit=50',
            method: 'GET',
            success: function(response) {
                $('#sent-notifications-loading').hide();
                if (response.success) {
                    if (response.data.length === 0) {
                        $('#sent-notifications-list').html('<p class="loading">No sent notifications found.</p>');
                        return;
                    }
                    let html = '';
                    response.data.forEach(function(notification) { html += buildNotificationItem(notification, true); });
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
            url: '/erp/backend/api/notifications.php?action=list&filter=received&limit=50',
            method: 'GET',
            success: function(response) {
                $('#received-notifications-loading').hide();
                if (response.success) {
                    if (response.data.length === 0) {
                        $('#received-notifications-list').html('<p class="loading">No received notifications found.</p>');
                        return;
                    }
                    let html = '';
                    response.data.forEach(function(notification) { html += buildNotificationItem(notification, false); });
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
        const priorityColor = { 'normal': '#10b981', 'important': '#f59e0b', 'urgent': '#ef4444' }[priorityClass];
        const date = new Date(notification.created_at).toLocaleDateString();
        const time = new Date(notification.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        return `
            <div class="notification-item">
                <div class="notification-header">
                    <div>
                        <div class="notification-title" style="border-left: 4px solid ${priorityColor}; padding-left: 1rem;">
                            ${notification.title || 'Untitled'}
                        </div>
                        <div class="notification-meta">
                            <span>Type: ${notification.type || 'N/A'}</span>
                            <span>Priority: ${notification.priority || 'N/A'}</span>
                            <span>${date} at ${time}</span>
                            ${isSent ? `<span>To: ${notification.target_summary || 'Multiple Classes'}</span>` : `<span>From: ${notification.created_by_name || 'System'}</span>`}
                        </div>
                    </div>
                </div>
                <div class="notification-content">
                    ${notification.message || ''}
                </div>
                ${!isSent && notification.is_read === '0' ? `
                    <div style="margin-top: 1rem;">
                        <button class="btn btn-sm btn-primary" onclick="markAsRead(${notification.id}, this)">
                            Mark as Read
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }

    function markAsRead(notificationId, buttonElement) {
        $.ajax({
            url: '/erp/backend/api/notifications.php?action=mark_read',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            data: JSON.stringify({ notification_id: notificationId }),
            success: function(response) {
                if (response.success) {
                    // Visually update: remove button, change style, etc.
                    $(buttonElement).closest('.notification-item').css('opacity', '0.7');
                    $(buttonElement).remove();
                    // Reload counts (especially unread count for bell)
                    loadNotificationCounts(); 
                    // Optionally, could update the global unread count directly if API returns it
                    // The main notification bell JS should also update from its own polling/trigger
                } else {
                    showAlert(response.message || 'Failed to mark as read.', 'error');
                }
            },
            error: function() { showAlert('Network error. Please try again.', 'error'); }
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
        if (phpIsHeadmaster) {
            // Reset headmaster target mode to default and hide specific selector
            $('input[name="headmaster_target_mode"][value="all_students"]').prop('checked', true);
            updateTargetMode('all_students');
        }
    }

    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;
        // Prepend to the form, or a dedicated alert area
        $('#create-tab').prepend(alertHtml);
        setTimeout(() => { $(`.${alertClass}`).fadeOut(500, function() { $(this).remove(); }); }, 5000);
    }
</script>
