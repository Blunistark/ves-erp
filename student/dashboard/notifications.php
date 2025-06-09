<?php 
include 'sidebar.php'; 
include 'con.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { // Changed 'teacher' to 'student'
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// It would be beneficial to also have student's class_id and section_id from session for targeted notifications
// $class_id = $_SESSION['class_id'] ?? null;
// $section_id = $_SESSION['section_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Notifications</title> {/* Changed title */}
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/notifications.css"> {/* Changed CSS file */}
    
    <style>
        /* Styles from the original teacher's file, potentially to be merged or overridden by notifications.css */
        /* It's better to move these to notifications.css if they are shared or adapt them */
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .notification-item { /* Adjusted class name */
            margin-bottom: 1rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .notification-item:hover { /* Adjusted class name */
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        
        .notification-item.unread { /* Adjusted class name */
            border-left: 4px solid #3b82f6; /* Blue for unread */
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between; /* Corrected typo: between -> space-between */
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .notification-title {
            font-weight: 600;
            color: #111827;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }
        
        .notification-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .notification-content-text { /* Renamed for clarity */
            color: #374151;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .notification-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end; /* Align buttons to the right */
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-secondary { /* For delete or other actions */
            background-color: #ef4444; /* Red for delete */
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #dc2626;
        }

        .btn-mark-read { /* Specific style for mark as read */
            background-color: #10b981; /* Green */
            color: white;
        }
        .btn-mark-read:hover {
            background-color: #059669;
        }
        
        .priority-badge { /* General badge styling */
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        /* Specific priority/type badges - these should align with getPriorityClass/getBadgeClass */
        .badge-general { background-color: #6b7280; color: white; }
        .badge-academic { background-color: #3b82f6; color: white; }
        .badge-event { background-color: #10b981; color: white; }
        .badge-important { background-color: #f59e0b; color: white; }
        .badge-urgent { background-color: #ef4444; color: white; }
        .badge-teacher { background-color: #8b5cf6; color: white; } /* Example for teacher messages */
        .badge-school { background-color: #34d399; color: white; } /* Example for school messages */


        /* Animation for removing notification */
        .notification-item.removing {
            animation: fadeOutAndShrink 0.5s forwards;
        }

        @keyframes fadeOutAndShrink {
            from {
                opacity: 1;
                transform: scale(1);
                max-height: 200px; /* Approximate height */
            }
            to {
                opacity: 0;
                transform: scale(0.95);
                max-height: 0;
                padding-top: 0;
                padding-bottom: 0;
                margin-bottom: 0;
                border: none;
            }
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            background-color: #f9fafb;
        }
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #9ca3af;
        }
        .error-message {
            text-align: center;
            padding: 2rem;
            color: #ef4444; /* Red for errors */
            background-color: #fee2e2; /* Light red background */
            border: 1px solid #fca5a5; /* Red border */
            border-radius: 8px;
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
        <h1 class="header-title">My Notifications</h1> {/* Changed title */}
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="notification-filters"> {/* Changed class name */}
            <div class="filter-item active" data-filter="all">All Notifications</div>
            <div class="filter-item" data-filter="unread">Unread</div>
            <div class="filter-item" data-filter="school">School Announcements</div> {/* Added/Updated filter */}
            <div class="filter-item" data-filter="teacher">Teacher Messages</div> {/* Added/Updated filter */}
            <div class="filter-item" data-filter="urgent">Urgent</div>
            {/* <div class="filter-item" data-filter="important">Important</div> */}
        </div>

        {/* Featured Announcement section removed */}

        <div class="card">
            <h2 class="card-title">All Notifications</h2> {/* Changed title */}
            
            <div class="notification-list" id="notifications-container"> {/* Changed class name */}
                <div class="loading" id="loading-indicator">
                    <div>Loading notifications...</div>
                </div>
            </div>
            
            {/* Pagination - kept for now, will need JS logic if API supports pagination */}
            <div class="pagination" id="pagination-controls" style="display: none;">
                <button class="page-arrow" id="prev-page" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span id="page-info">Page 1 of 1</span>
                <button class="page-arrow" id="next-page" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </main>
</div>

<script>
    let currentFilter = 'all';
    let currentPage = 1;
    const notificationsPerPage = 10; // Or get from API if dynamic
    let totalNotifications = 0;
    let allFetchedNotifications = []; // To store all notifications if paginating client-side

    const API_URL = '../../backend/api/notifications.php';

    // Load notifications from API
    async function loadNotifications(filter = 'all', unreadOnly = false, page = 1) {
        const loadingIndicator = document.getElementById('loading-indicator');
        const container = document.getElementById('notifications-container');
        
        if (!container) {
            console.error('notifications-container element not found');
            showError('Critical error: UI element missing. Please contact support.');
            return;
        }
        
        if (loadingIndicator) loadingIndicator.style.display = 'block';
        // Clear previous notifications before loading new ones, except for the loading indicator
        Array.from(container.children).forEach(child => {
            if (child.id !== 'loading-indicator') child.remove();
        });

        try {
            // Construct URL with parameters for student view
            // The API will need to be updated to handle 'source_type' and 'role=student' implicitly or explicitly
            let url = `${API_URL}?action=list_student&filter_type=${filter}&page=${page}&limit=${notificationsPerPage}`;
            if (unreadOnly) {
                url += '&unread_only=true';
            }
            // Add student_id if needed by API, though session on backend should handle this
            // url += `&user_id=<?php echo $user_id; ?>`; 

            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin', 
                headers: { 'Content-Type': 'application/json' }
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                throw new Error(errorData?.message || `HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                allFetchedNotifications = data.data; // Assuming API returns current page data
                totalNotifications = data.total_count || allFetchedNotifications.length; // API should provide total count for pagination
                renderNotifications(allFetchedNotifications);
                updatePagination(page, Math.ceil(totalNotifications / notificationsPerPage));
            } else {
                showError('Failed to load notifications: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            showError(`Failed to load notifications: ${error.message}. Please try again.`);
        } finally {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        }
    }

    // Render notifications in the container
    function renderNotifications(notificationList) {
        const container = document.getElementById('notifications-container');
        // Clear existing content (excluding loading indicator)
        Array.from(container.children).forEach(child => {
            if (child.id !== 'loading-indicator') child.remove();
        });
        
        if (!notificationList || notificationList.length === 0) {
            addEmptyState(container);
            return;
        }
        
        notificationList.forEach(notification => {
            const notificationElement = createNotificationElement(notification);
            container.appendChild(notificationElement);
        });
    }

    function addEmptyState(container) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty-state';
        emptyDiv.innerHTML = `
            <div class="empty-state-icon">📄</div>
            <div>No notifications here.</div>
            <p>Looks like your notification tray is empty. Check back later!</p>
        `;
        container.appendChild(emptyDiv);
    }

    // Create notification HTML element (Student Version)
    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
        div.dataset.notificationId = notification.id;
        
        const badgeClass = getBadgeClass(notification.type, notification.priority, notification.source); // source could be 'school' or 'teacher'
        const dateFormatted = formatDate(notification.created_at);
        const sender = notification.sender_name || (notification.source === 'teacher' ? 'Teacher' : 'School Admin');

        // Icon based on type or source
        const iconSvg = getNotificationIcon(notification.type, notification.source);

        div.innerHTML = `
            <div class="notification-header">
                <div class="notification-title-area">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        ${iconSvg}
                        <span class="notification-title">${escapeHtml(notification.title)}</span>
                    </div>
                    <div class="notification-meta">
                        <span>${dateFormatted}</span>
                        <span>From: ${escapeHtml(sender)}</span>
                    </div>
                </div>
                <span class="priority-badge ${badgeClass}">${escapeHtml(notification.type || notification.priority || 'General')}</span>
            </div>
            
            <div class="notification-content-text">
                ${escapeHtml(notification.message)}
            </div>
            
            <div class="notification-actions">
                ${!notification.is_read ? `<button class="btn btn-mark-read" onclick="markAsRead(${notification.id}, this)">Mark as Read</button>` : ''}
                <button class="btn btn-secondary" onclick="deleteNotification(${notification.id}, this)">Delete</button>
            </div>
        `;
        return div;
    }

    // Mark notification as read
    async function markAsRead(notificationId, buttonElement) {
        try {
            const response = await fetch(`${API_URL}?action=mark_read_student`, { // API needs this action
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: notificationId, user_id: <?php echo $user_id; ?> })
            });
            
            const data = await response.json();
            
            if (data.success) {
                const notificationElement = document.querySelector(`.notification-item[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    notificationElement.classList.add('read'); // Optional: for styling read items
                    if (buttonElement) buttonElement.remove();
                }
            } else {
                showError('Failed to mark as read: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error marking as read:', error);
            showError('Failed to mark as read. Please try again.');
        }
    }

    // Delete notification (visually, and call API for soft delete)
    async function deleteNotification(notificationId, buttonElement) {
        // Optional: Add a confirmation dialog
        // if (!confirm("Are you sure you want to delete this notification?")) return;

        const notificationElement = document.querySelector(`.notification-item[data-notification-id="${notificationId}"]`);
        if (!notificationElement) return;

        try {
            const response = await fetch(`${API_URL}?action=delete_student_notification`, { // API needs this action
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: notificationId, user_id: <?php echo $user_id; ?> })
            });

            const data = await response.json();

            if (data.success) {
                notificationElement.classList.add('removing');
                notificationElement.addEventListener('animationend', () => {
                    notificationElement.remove();
                    // Check if container is empty after removal
                    const container = document.getElementById('notifications-container');
                    if (container && container.children.length === 1 && container.children[0].id === 'loading-indicator' || container.children.length === 0) {
                         // If only loading indicator is left or it's empty (after loading indicator is hidden)
                        if(document.getElementById('loading-indicator').style.display === 'none'){
                           addEmptyState(container);
                        }
                    }
                });
            } else {
                showError('Failed to delete notification: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
            showError('Failed to delete notification. Please try again.');
        }
    }


    // Utility functions
    function getBadgeClass(type, priority, source) {
        type = type?.toLowerCase();
        priority = priority?.toLowerCase();
        source = source?.toLowerCase();

        if (priority === 'urgent') return 'badge-urgent';
        if (priority === 'important') return 'badge-important';
        if (type === 'academic') return 'badge-academic';
        if (type === 'event') return 'badge-event';
        if (source === 'teacher') return 'badge-teacher';
        if (source === 'school') return 'badge-school';
        return 'badge-general';
    }

    function getNotificationIcon(type, source) {
        type = type?.toLowerCase();
        source = source?.toLowerCase();
        let icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:24px; height:24px; flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>'; // Default bell icon

        if (type === 'academic' || source === 'teacher') {
            icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:24px; height:24px; flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" /></svg>'; // Book/academic icon
        } else if (type === 'event') {
            icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:24px; height:24px; flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>'; // Calendar/event icon
        } else if (type === 'urgent' || type === 'important') {
             icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:24px; height:24px; flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>'; // Exclamation/alert icon
        }
        return icon;
    }


    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            // hour: '2-digit',
            // minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    function showError(message) {
        const container = document.getElementById('notifications-container');
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) loadingIndicator.style.display = 'none';

        if (container) {
            // Clear other notifications before showing error
            Array.from(container.children).forEach(child => {
                if (child.id !== 'loading-indicator') child.remove();
            });
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<strong>Error:</strong> ${escapeHtml(message)}`;
            container.appendChild(errorDiv);
        } else {
            console.error('Cannot show error message - notifications-container not found:', message);
            alert(`Error: ${message}`); // Fallback
        }
    }

    // Handle filters
    document.querySelectorAll('.notification-filters .filter-item').forEach(filterElement => {
        filterElement.addEventListener('click', function() {
            try {
                document.querySelectorAll('.notification-filters .filter-item').forEach(f => f.classList.remove('active'));
                this.classList.add('active');
                
                const filterType = this.dataset.filter;
                currentFilter = filterType;
                currentPage = 1; // Reset to first page on filter change
                
                if (filterType === 'unread') {
                    loadNotifications('all', true, currentPage); // 'all' types, but unread only
                } else {
                    // For 'school', 'teacher', 'urgent', 'all'
                    loadNotifications(filterType, false, currentPage);
                }
            } catch (error) {
                console.error('Error in filter click handler:', error);
                showError('Could not apply filter.');
            }
        });
    });

    // Pagination controls
    function updatePagination(page, totalPages) {
        const paginationControls = document.getElementById('pagination-controls');
        const pageInfo = document.getElementById('page-info');
        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');

        if (!paginationControls || !pageInfo || !prevButton || !nextButton) return;

        currentPage = page;
        if (totalPages > 0) {
            paginationControls.style.display = 'flex';
            pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;
        } else {
            paginationControls.style.display = 'none';
        }
    }

    document.getElementById('prev-page')?.addEventListener('click', () => {
        if (currentPage > 1) {
            loadNotifications(currentFilter, currentFilter === 'unread', currentPage - 1);
        }
    });

    document.getElementById('next-page')?.addEventListener('click', () => {
        // Assuming totalPages is available globally or recalculated
        const totalPages = Math.ceil(totalNotifications / notificationsPerPage);
        if (currentPage < totalPages) {
            loadNotifications(currentFilter, currentFilter === 'unread', currentPage + 1);
        }
    });


    // Load notifications when page loads
    document.addEventListener('DOMContentLoaded', function() {
        try {
            loadNotifications(currentFilter, false, currentPage); // Initial load: all, not unread_only, page 1
        } catch (error) {
            console.error('Error during initial load:', error);
            showError('Error loading initial notifications.');
        }
    });
</script>
</body>
</html>