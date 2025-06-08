<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>School Notifications</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/notifications.css">
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
        <div class="notification-filters">
            <div class="filter-item active">All Notifications</div>
            <div class="filter-item">Unread</div>
            <div class="filter-item">Academic</div>
            <div class="filter-item">Events</div>
            <div class="filter-item">Alerts</div>
        </div>

        <div class="card">
            <h2 class="card-title">Recent Notifications</h2>
            
            <div class="notification-list">
                <!-- Urgent Alert Notification -->
                <div class="notification-item unread">
                    <div class="notification-icon icon-alert">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    
                    <div class="notification-content">
                        <div class="notification-header">
                            <div class="notification-title">
                                School Closure Due to Weather
                                <span class="notification-badge badge-alert">Urgent</span>
                            </div>
                            <div class="notification-time">Today, 7:15 AM</div>
                        </div>
                        
                        <div class="notification-message">
                            Due to the severe weather forecast, all classes are cancelled for tomorrow, March 13. Please check for updates on the school website. Stay safe and avoid unnecessary travel.
                        </div>
                        
                        <div class="notification-footer">
                            <div class="notification-sender">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Principal Thompson
                            </div>
                            
                            <div class="notification-actions">
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Notification -->
                <div class="notification-item unread">
                    <div class="notification-icon icon-academic">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                    
                    <div class="notification-content">
                        <div class="notification-header">
                            <div class="notification-title">
                                Math Test Results Published
                                <span class="notification-badge badge-academic">Academic</span>
                            </div>
                            <div class="notification-time">Yesterday, 3:45 PM</div>
                        </div>
                        
                        <div class="notification-message">
                            The results for your recent Mathematics examination (Chapter 7-9) have been published. You can view your score and feedback in the Student Portal under "Assessment Results".
                        </div>
                        
                        <div class="notification-footer">
                            <div class="notification-sender">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Mrs. Johnson, Mathematics
                            </div>
                            
                            <div class="notification-actions">
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Event Notification -->
                <div class="notification-item">
                    <div class="notification-icon icon-event">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    
                    <div class="notification-content">
                        <div class="notification-header">
                            <div class="notification-title">
                                Annual Science Fair Registration Open
                                <span class="notification-badge badge-event">Event</span>
                            </div>
                            <div class="notification-time">Mar 10, 10:20 AM</div>
                        </div>
                        
                        <div class="notification-message">
                            Registration for the Annual Science Fair is now open! This year's theme is "Sustainable Innovation". Join us on April 15th to showcase your scientific projects. Register by March 25th to secure your spot.
                        </div>
                        
                        <div class="notification-footer">
                            <div class="notification-sender">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Science Department
                            </div>
                            
                            <div class="notification-actions">
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- General Notification -->
                <div class="notification-item read">
                    <div class="notification-icon icon-general">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    
                    <div class="notification-content">
                        <div class="notification-header">
                            <div class="notification-title">
                                Library Hours Extended During Exam Week
                                <span class="notification-badge badge-general">General</span>
                            </div>
                            <div class="notification-time">Mar 8, 9:00 AM</div>
                        </div>
                        
                        <div class="notification-message">
                            To support your exam preparation, the school library will extend its hours during exam week (March 20-24). The library will remain open until 7:00 PM on weekdays. Additional study rooms will be available for group study sessions.
                        </div>
                        
                        <div class="notification-footer">
                            <div class="notification-sender">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Ms. Parker, Librarian
                            </div>
                            
                            <div class="notification-actions">
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                
                                <button class="action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- More notifications would follow... -->
            </div>
        </div>
    </main>
</div>

<script>
    // Script to handle notification filters
    document.querySelectorAll('.filter-item').forEach(filter => {
        filter.addEventListener('click', function() {
            document.querySelectorAll('.filter-item').forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // In a real app, you would filter the notifications here
        });
    });
    
    // Script to handle mark as read
    document.querySelectorAll('.notification-actions button:first-child').forEach(button => {
        button.addEventListener('click', function() {
            const notification = this.closest('.notification-item');
            notification.classList.remove('unread');
            notification.classList.add('read');
        });
    });
    
    // Script to handle delete notification
    document.querySelectorAll('.notification-actions button:last-child').forEach(button => {
        button.addEventListener('click', function() {
            const notification = this.closest('.notification-item');
            notification.style.height = notification.offsetHeight + 'px';
            
            // Trigger a reflow
            notification.offsetHeight;
            
            // Add transition and fade out
            notification.style.transition = 'all 0.3s ease';
            notification.style.opacity = '0';
            notification.style.height = '0';
            notification.style.marginBottom = '0';
            notification.style.paddingTop = '0';
            notification.style.paddingBottom = '0';
            
            // Remove from DOM after animation
            setTimeout(() => {
                notification.remove();
                
                // Check if all notifications are gone
                const notifications = document.querySelectorAll('.notification-item');
                if (notifications.length === 0) {
                    addEmptyState();
                }
            }, 300);
        });
    });
    
    // Function to add empty state
    function addEmptyState() {
        const notificationList = document.querySelector('.notification-list');
        
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        
        emptyState.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <div class="empty-title">No notifications</div>
            <div class="empty-description">You're all caught up! Check back later for new notifications.</div>
        `;
        
        notificationList.appendChild(emptyState);
    }
</script>
</body>
</html>