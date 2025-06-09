<?php
// main_layout.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Authentication - Redirect if not logged in or not a teacher/headmaster
// This should be robust. Using placeholder logic similar to sidebar.php
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['teacher', 'headmaster'])) {
    header("Location: ../index.php"); // Adjust path if needed
    exit;
}

$user_full_name = htmlspecialchars($_SESSION['full_name'] ?? 'User');
$page_title = $page_title ?? 'Teacher Dashboard'; // Page specific title can be set before including this layout

// Determine the base path for assets if your layout file is in a subdirectory
// For example, if main_layout.php is in teachers/dashboard/
// and your CSS/JS are in teachers/dashboard/css or teachers/dashboard/js
$base_url = './'; // Adjust if assets are located elsewhere relative to pages using this layout.
                  // Or use absolute paths like /erp/teachers/dashboard/css/

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - ERP</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/sidebar.css"> <!-- Existing sidebar CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/main_layout.css"> <!-- New layout CSS -->
    <!-- Add any other global CSS files here -->
    <style>
        /* Basic styles for top-navbar and notification bell - move to main_layout.css */
        body { margin: 0; font-family: sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        .top-navbar {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000; /* Ensure it's above other content */
        }
        .top-navbar .nav-left, .top-navbar .nav-right { display: flex; align-items: center; }
        .top-navbar .nav-left { gap: 15px; }
        .top-navbar .nav-right { gap: 20px; }
        .sidebar-toggle-btn, .notification-bell-button { background: none; border: none; color: white; cursor: pointer; padding: 5px; }
        .sidebar-toggle-btn svg, .notification-bell-button svg { width: 24px; height: 24px; }
        .page-title-display { font-size: 1.2em; font-weight: bold; }
        .user-welcome span { margin-right: 10px; }
        .user-welcome a { color: #a0d2eb; text-decoration: none; }
        .user-welcome a:hover { text-decoration: underline; }

        .notification-bell-area { position: relative; }
        .notification-bell-count {
            position: absolute;
            top: -5px;
            right: -8px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75em;
            border: 1px solid white;
        }
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1001;
            color: #333;
            border-radius: 4px;
        }
        .notification-dropdown-header { padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .notification-dropdown-header span { font-weight: bold; }
        .notification-dropdown-list .notification-item { padding: 10px; border-bottom: 1px solid #eee; font-size: 0.9em; cursor: pointer; }
        .notification-dropdown-list .notification-item:last-child { border-bottom: none; }
        .notification-dropdown-list .notification-item:hover { background-color: #f9f9f9; }
        .notification-item.unread { background-color: #e6f7ff; } /* Example for unread items */
        .notification-item-content p { margin: 0 0 5px 0; }
        .notification-item-meta { font-size: 0.8em; color: #777; }
        .notification-dropdown-footer { padding: 10px; text-align: center; border-top: 1px solid #eee;}
        .notification-dropdown-footer a, .notification-dropdown-header a { color: #007bff; text-decoration: none; }

        .page-container { display: flex; flex-grow: 1; }
        /* main-content will be styled in main_layout.css, ensure it takes remaining space */
        .main-content-area { flex-grow: 1; padding: 20px; overflow-y: auto; background-color: #f4f7f6; }
        
        /* Sidebar collapsed state for main content adjustment */
        .sidebar.collapsed + .main-content-area {
            /* Adjust if necessary, depends on how sidebar collapses */
        }
        .sidebar:not(.collapsed) ~ .main-content-area {
             /* Adjust if necessary */
        }

    </style>
</head>
<body>
    <div class="top-navbar">
        <div class="nav-left">
            <button id="sidebarToggleBtn" class="sidebar-toggle-btn" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"></path></svg>
            </button>
            <div id="pageTitleDisplay" class="page-title-display"><?php echo htmlspecialchars($page_title); ?></div>
        </div>
        <div class="nav-right">
            <div class="notification-bell-area">
                <button type="button" id="notificationBell" class="notification-bell-button" aria-label="View notifications">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span id="notificationBellCount" class="notification-bell-count" style="display:none;">0</span>
                </button>
                <div id="notificationDropdown" class="notification-dropdown" style="display:none;">
                    <div class="notification-dropdown-header">
                        <span>Notifications</span>
                    </div>
                    <div id="notificationDropdownList" class="notification-dropdown-list">
                        <!-- Notifications loaded by JS -->
                        <div class="dropdown-placeholder" style="text-align:center; padding:20px;">No new notifications or loading...</div>
                    </div>
                    <div class="notification-dropdown-footer">
                        <a href="<?php echo $base_url; ?>notifications.php">View All Notifications</a>
                    </div>
                </div>
            </div>
            <div class="user-welcome">
                <span>Welcome, <?php echo $user_full_name; ?></span>
                <a href="<?php echo $base_url; ?>../logout.php">Logout</a> <!-- Adjust path if needed -->
            </div>
        </div>
    </div>

    <div class="page-container">
        <?php include 'sidebar.php'; // The existing sidebar ?>
        
        <main class="main-content-area" id="mainContentArea">
            <?php
            // The $content_php_file variable should be set by the page that includes this layout.
            // Example: $content_php_file = 'dashboard_content.php'; include 'main_layout.php';
            if (isset($content_php_file) && file_exists($content_php_file)) {
                include $content_php_file;
            } else {
                // Fallback or error if content file not specified or not found
                // For direct access to pages like notifications.php, this block won't be used.
                // Instead, notifications.php will set $page_title and then include main_layout.php at its END,
                // with its own content already outputted or captured. This needs careful handling.
                
                // Simpler approach for now: pages will define their content, then include this layout.
                // This means the main content of the page (e.g., notifications.php) must be output *before* this layout is included,
                // or captured into a variable and echoed here.
                // For true templating, the page (e.g. notifications.php) would be *included by* the layout.
                // Let's assume for now that individual pages will output their content, and then this layout provides the shell.
                // This is less ideal than a true templating system but simpler to start.
            }
            ?>
        </main>
    </div>

    <!-- Global JS files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- If you use jQuery -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const mainContentArea = document.getElementById('mainContentArea'); // Ensure this ID exists

            if (sidebarToggleBtn && sidebar) {
                sidebarToggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    // Optional: save state to cookie
                    document.cookie = "sidebar_collapsed=" + sidebar.classList.contains('collapsed') + ";path=/;max-age=" + (60*60*24*30);
                    // Adjust main content margin/padding if sidebar width changes
                    if (mainContentArea) {
                        if (sidebar.classList.contains('collapsed')) {
                            // mainContentArea.style.marginLeft = '80px'; // Example collapsed width
                        } else {
                            // mainContentArea.style.marginLeft = '250px'; // Example expanded width
                        }
                    }
                });
            }
             // Apply initial collapsed state from cookie
            if (sidebar && document.cookie.includes('sidebar_collapsed=true')) {
                sidebar.classList.add('collapsed');
                 if (mainContentArea) { /* mainContentArea.style.marginLeft = '80px'; */ }
            } else {
                 if (mainContentArea) { /* mainContentArea.style.marginLeft = '250px'; */ }
            }


            // Notification Bell Logic
            const notificationBell = document.getElementById('notificationBell');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBellCount = document.getElementById('notificationBellCount');
            const notificationDropdownList = document.getElementById('notificationDropdownList');
            const API_URL = '<?php echo $base_url; ?>../backend/api/notifications.php'; // Adjust path

            function fetchNotificationCount() {
                fetch(`${API_URL}?action=get_unread_count`) // We'll need to add this action to the backend
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.count > 0) {
                            notificationBellCount.textContent = data.count;
                            notificationBellCount.style.display = 'flex'; // Show count
                        } else {
                            notificationBellCount.style.display = 'none'; // Hide count
                        }
                    })
                    .catch(error => console.error('Error fetching notification count:', error));
            }

            function fetchNotificationsForDropdown() {
                notificationDropdownList.innerHTML = '<div style="text-align:center; padding:20px;">Loading notifications...</div>';
                // Fetch only a few recent/unread notifications for the dropdown
                fetch(`${API_URL}?action=list&limit=5&page=1&status=unread`) // Assuming API supports status filter
                    .then(response => response.json())
                    .then(data => {
                        notificationDropdownList.innerHTML = ''; // Clear loading/placeholder
                        if (data.success && data.notifications && data.notifications.length > 0) {
                            data.notifications.forEach(notif => {
                                const item = document.createElement('div');
                                item.classList.add('notification-item');
                                if (!notif.is_read) { // Assuming 'is_read' field (0 or 1)
                                    item.classList.add('unread');
                                }
                                item.innerHTML = `
                                    <div class="notification-item-content">
                                        <p><strong>${notif.title || 'Notification'}</strong></p>
                                        <p>${notif.message ? notif.message.substring(0, 100) + (notif.message.length > 100 ? '...' : '') : 'No message content.'}</p>
                                    </div>
                                    <div class="notification-item-meta">
                                        <span>${new Date(notif.created_at).toLocaleDateString()}</span>
                                    </div>
                                `;
                                item.addEventListener('click', () => {
                                    // Mark as read and redirect or handle click
                                    markNotificationAsRead(notif.id);
                                    // Potentially redirect: window.location.href = 'notifications.php?view=' + notif.id;
                                    notificationDropdown.style.display = 'none'; // Hide dropdown after click
                                });
                                notificationDropdownList.appendChild(item);
                            });
                        } else {
                            notificationDropdownList.innerHTML = '<div style="text-align:center; padding:20px;">No new notifications.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching notifications:', error);
                        notificationDropdownList.innerHTML = '<div style="text-align:center; padding:20px; color:red;">Error loading notifications.</div>';
                    });
            }
            
            function markNotificationAsRead(notificationId) {
                fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=mark_read&notification_ids[]=${notificationId}` // API expects array
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotificationCount(); // Refresh count
                        // Optionally remove 'unread' class from item if still visible
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
            }


            if (notificationBell) {
                notificationBell.addEventListener('click', (event) => {
                    event.stopPropagation(); // Prevent click from closing immediately if body listener exists
                    const isHidden = notificationDropdown.style.display === 'none';
                    notificationDropdown.style.display = isHidden ? 'block' : 'none';
                    if (isHidden) {
                        fetchNotificationsForDropdown(); // Load notifications when dropdown is opened
                    }
                });
            }

            // Close dropdown if clicked outside
            document.addEventListener('click', function(event) {
                if (notificationDropdown && !notificationBell.contains(event.target) && !notificationDropdown.contains(event.target)) {
                    notificationDropdown.style.display = 'none';
                }
            });

            // Initial fetch of notification count
            fetchNotificationCount();
            // Optionally, poll for new notifications periodically
            // setInterval(fetchNotificationCount, 60000); // every 60 seconds
        });
    </script>
    <!-- Page-specific JS files can be included by the content page itself if needed -->
</body>
</html>
