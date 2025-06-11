<?php 
include 'sidebar.php'; 
include 'con.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'headmaster'])) {
  header('Location: ../index.php');
  exit();
} 


$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>School Notifications</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/school-notifications.css">
    
    <style>
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .notification-item {
            margin-bottom: 1rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        
        .notification-item.unread {
            border-left: 4px solid #3b82f6;
        }
        
        .notification-header {
            display: flex;
            justify-content: between;
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
        
        .notification-content {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .notification-actions {
            display: flex;
            gap: 0.5rem;
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
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .priority-normal {
            background-color: #10b981;
            color: white;
        }
        
        .priority-important {
            background-color: #f59e0b;
            color: white;
        }
        
        .priority-urgent {
            background-color: #ef4444;
            color: white;
        }
        
        .mark-read-btn {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .mark-read-btn:hover {
            background-color: #059669;
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
        <h1 class="header-title">School Notifications</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="announcement-filters">
            <div class="filter-item active" data-filter="all">All</div>
            <div class="filter-item" data-filter="unread">Unread</div>
            <div class="filter-item" data-filter="academic">Academic</div>
            <div class="filter-item" data-filter="urgent">Urgent</div>
            <div class="filter-item" data-filter="important">Important</div>
        </div>


        <div class="card">
            <h2 class="card-title">School Notifications</h2>
            
            <div class="announcement-list" id="notifications-container">
                <div class="loading" id="loading-indicator">
                    <div>Loading notifications...</div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <div class="page-arrow disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </div>
                <div class="page-item active">1</div>
                <div class="page-item">2</div>
                <div class="page-item">3</div>
                <div class="page-item">4</div>
                <div class="page-item">5</div>
                <div class="page-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    let currentFilter = 'all';
    let currentPage = 1;
    let notifications = [];

    // Load notifications from API
    async function loadNotifications(filter = 'all', unreadOnly = false) {
        const loadingIndicator = document.getElementById('loading-indicator');
        const container = document.getElementById('notifications-container');
        
        // Check if elements exist before using them
        if (!container) {
            console.error('notifications-container element not found');
            return;
        }
        
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        try {
            let url = '../../backend/api/notifications.php?action=list&limit=20&offset=0';
            if (filter !== 'all') {
                url += `&type=${filter}`;
            }
            if (unreadOnly) {
                url += '&unread_only=true';
            }
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin', // Include cookies for session
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                notifications = data.data;
                renderNotifications(notifications);
            } else {
                showError('Failed to load notifications: ' + data.message);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            showError('Failed to load notifications. Please try again.');
        } finally {
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        }
    }

    // Render notifications in the container
    function renderNotifications(notificationList) {
        const container = document.getElementById('notifications-container');
        const loadingIndicator = document.getElementById('loading-indicator');
        
        if (!container) {
            console.error('notifications-container element not found');
            return;
        }
        
        // Clear existing content except loading indicator
        Array.from(container.children).forEach(child => {
            if (child.id !== 'loading-indicator') {
                child.remove();
            }
        });
        
        if (notificationList.length === 0) {
            container.innerHTML = '<div class="loading">No notifications found.</div>';
            return;
        }
        
        notificationList.forEach(notification => {
            const notificationElement = createNotificationElement(notification);
            container.appendChild(notificationElement);
        });
    }

    // Create notification HTML element
    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `announcement-card ${!notification.is_read ? 'unread' : ''}`;
        div.dataset.notificationId = notification.id;
        
        const priorityClass = getPriorityClass(notification.priority);
        const dateFormatted = formatDate(notification.created_at);
        
        div.innerHTML = `
            <div class="announcement-header">
                <div class="announcement-title-area">
                    <div class="announcement-title">${escapeHtml(notification.title)}</div>
                    <div class="announcement-meta">
                        <div class="announcement-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            ${dateFormatted}
                        </div>
                        <div class="announcement-author">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            ${escapeHtml(notification.created_by_name || 'System')}
                        </div>
                    </div>
                </div>
                <span class="announcement-badge ${priorityClass}">${getTypeLabel(notification.type)}</span>
            </div>
            
            <div class="announcement-content">
                <div class="content-preview">
                    ${escapeHtml(notification.message)}
                </div>
            </div>
            
            <div class="announcement-actions">
                <div class="action-buttons">
                    ${!notification.is_read ? `<button class="btn btn-primary mark-read-btn" onclick="markAsRead(${notification.id})">Mark as Read</button>` : ''}
                    ${notification.requires_acknowledgment && !notification.is_acknowledged ? `<button class="btn btn-secondary" onclick="acknowledgeNotification(${notification.id})">Acknowledge</button>` : ''}
                </div>
            </div>
        `;
        
        return div;
    }

    // Mark notification as read
    async function markAsRead(notificationId) {
        try {
            const response = await fetch('../../backend/api/notifications.php?action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update the notification in the UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    const markReadBtn = notificationElement.querySelector('.mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                }
            } else {
                showError('Failed to mark notification as read: ' + data.message);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
            showError('Failed to mark notification as read.');
        }
    }

    // Acknowledge notification
    async function acknowledgeNotification(notificationId) {
        try {
            const response = await fetch('../../backend/api/notifications.php?action=acknowledge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update the notification in the UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    const ackBtn = notificationElement.querySelector('.btn-secondary');
                    if (ackBtn) {
                        ackBtn.textContent = 'Acknowledged';
                        ackBtn.disabled = true;
                        ackBtn.style.opacity = '0.6';
                    }
                }
            } else {
                showError('Failed to acknowledge notification: ' + data.message);
            }
        } catch (error) {
            console.error('Error acknowledging notification:', error);
            showError('Failed to acknowledge notification.');
        }
    }

    // Utility functions
    function getPriorityClass(priority) {
        switch(priority.toLowerCase()) {
            case 'urgent': return 'badge-urgent';
            case 'important': return 'badge-important';
            case 'academic': return 'badge-academic';
            case 'event': return 'badge-event';
            default: return 'badge-general';
        }
    }

    function getTypeLabel(type) {
        switch(type.toLowerCase()) {
            case 'academic': return 'Academic';
            case 'event': return 'Event';
            case 'urgent': return 'Urgent';
            case 'important': return 'Important';
            case 'general': return 'General';
            default: return 'Announcement';
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showError(message) {
        const container = document.getElementById('notifications-container');
        if (container) {
            container.innerHTML = `<div class="loading" style="color: #ef4444;">${message}</div>`;
        } else {
            console.error('Cannot show error message - notifications-container not found:', message);
        }
    }

    // Handle filters
    document.querySelectorAll('.filter-item').forEach(filter => {
        filter.addEventListener('click', function() {
            try {
                document.querySelectorAll('.filter-item').forEach(f => f.classList.remove('active'));
                this.classList.add('active');
                
                const filterType = this.dataset.filter;
                currentFilter = filterType;
                
                if (filterType === 'unread') {
                    loadNotifications('all', true);
                } else if (filterType === 'all') {
                    loadNotifications('all', false);
                } else {
                    loadNotifications(filterType, false);
                }
            } catch (error) {
                console.error('Error in filter click handler:', error);
            }
        });
    });

    // Load notifications when page loads
    document.addEventListener('DOMContentLoaded', function() {
        try {
            loadNotifications();
        } catch (error) {
            console.error('Error during initial load:', error);
        }
    });
</script>
</body>
</html>
