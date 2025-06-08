// Notifications JavaScript functionality
$(document).ready(function() {
    // Load initial notifications
    loadNotifications();
    
    // Set up filter buttons
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        loadNotifications();
    });
    
    // Mark all as read button
    $('#markAllReadBtn').click(function() {
        if ($(this).is(':disabled')) return;
        
        const notificationIds = [];
        $('.notification-card.unread').each(function() {
            notificationIds.push($(this).data('id'));
        });
        
        if (notificationIds.length === 0) return;
        
        markAsRead('announcement', notificationIds);
    });
});

// Function to load notifications
function loadNotifications() {
    const filter = $('.filter-btn.active').data('filter') || 'all';
    $('#notificationList').html(
        `<div class="notification-loading">
            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>`
    );
    
    $.ajax({
        url: 'notification_actions.php',
        type: 'GET',
        data: {
            action: 'get_notifications',
            filter: filter
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                showError(response.error);
                return;
            }
            
            renderNotifications(response.notifications);
            updateUnreadCount(response.unread_count);
        },
        error: function() {
            showError('Failed to load notifications. Please try again.');
        }
    });
}

// Function to render notifications
function renderNotifications(notifications) {
    const container = $('#notificationList');
    container.empty();
    
    if (!notifications || notifications.length === 0) {
        container.html(
            `<div class="notification-empty">
                <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <h3 class="empty-title">No notifications</h3>
                <p class="empty-message">You don't have any notifications at the moment. Check back later!</p>
            </div>`
        );
        return;
    }
    
    let hasUnread = false;
    
    notifications.forEach(notification => {
        // Format date
        const createdDate = new Date(notification.created_at);
        const formattedDate = formatDate(createdDate);
        
        // Determine notification icon and classes
        let iconClass = 'icon-announcement';
        let cardClass = '';
        
        // Add unread class if notification is unread
        if (!notification.is_read) {
            cardClass += ' unread';
            hasUnread = true;
        }
        
        // Add priority class if notification has priority
        if (notification.priority === 'urgent') {
            cardClass += ' urgent';
        } else if (notification.priority === 'important') {
            cardClass += ' important';
        }
        
        // Create the notification card
        const card = `
            <div class="notification-card${cardClass}" data-id="${notification.id}" data-type="${notification.type}">
                ${!notification.is_read ? '<span class="unread-indicator"></span>' : ''}
                <div class="notification-icon ${iconClass}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <div class="notification-content">
                    <div class="notification-title">
                        ${notification.title}
                        ${notification.priority !== 'normal' ? 
                            `<span class="notification-priority priority-${notification.priority}">${notification.priority}</span>` : ''}
                    </div>
                    <div class="notification-message">${notification.message || notification.content}</div>
                    <div class="notification-meta">
                        <div class="notification-date">
                            <svg xmlns="http://www.w3.org/2000/svg" class="notification-date-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            ${formattedDate}
                        </div>
                        <div class="notification-actions">
                            ${!notification.is_read ? 
                                `<button class="notification-action-btn read-btn" onclick="markAsRead('${notification.type}', [${notification.id}])">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>` : ''}
                            <button class="notification-action-btn" onclick="viewNotificationDetails('${notification.type}', ${notification.id})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(card);
    });
    
    // Enable/disable mark all as read button
    $('#markAllReadBtn').prop('disabled', !hasUnread);
}

// Function to mark notifications as read
function markAsRead(type, ids) {
    if (!ids || ids.length === 0) return;
    
    $.ajax({
        url: 'mark_read.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            type: type,
            ids: ids
        }),
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.success) {
                    // Update UI to show notifications as read
                    ids.forEach(id => {
                        const card = $(`.notification-card[data-id="${id}"][data-type="${type}"]`);
                        card.removeClass('unread');
                        card.find('.unread-indicator').remove();
                        card.find('.read-btn').remove();
                    });
                    
                    // Update unread count
                    loadNotifications();
                } else if (data.error) {
                    showError(data.error);
                }
            } catch (e) {
                showError('Invalid response from server');
            }
        },
        error: function() {
            showError('Failed to mark notifications as read');
        }
    });
}

// Function to view notification details
function viewNotificationDetails(type, id) {
    if (type === 'announcement') {
        window.location.href = `announcement_details.php?id=${id}`;
    } else if (type === 'notice') {
        window.location.href = `notice_details.php?id=${id}`;
    } else if (type === 'message') {
        window.location.href = `message_details.php?id=${id}`;
    }
}

// Helper function to format date
function formatDate(date) {
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        // Today, show time
        const hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes < 10 ? `0${minutes}` : minutes;
        return `Today at ${formattedHours}:${formattedMinutes} ${ampm}`;
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        // Within a week, show day
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return days[date.getDay()];
    } else {
        // Older, show full date
        const options = { month: 'short', day: 'numeric', year: diffDays > 365 ? 'numeric' : undefined };
        return date.toLocaleDateString('en-US', options);
    }
}

// Function to update unread notification count
function updateUnreadCount(count) {
    // Update the badge in the sidebar
    const badge = $('.sidebar .notification-badge');
    if (count > 0) {
        if (badge.length > 0) {
            badge.text(count);
        } else {
            // Add badge if it doesn't exist
            $('.sidebar a[href="notifications.php"]').append(`<span class="notification-badge">${count}</span>`);
        }
    } else {
        badge.remove();
    }
}

// Function to show error message
function showError(message) {
    $('#notificationList').html(
        `<div class="notification-empty">
            <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="empty-title">Error</h3>
            <p class="empty-message">${message}</p>
        </div>`
    );
} 