<?php 
include 'sidebar.php'; 
include 'con.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Student';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Notifications - Student Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/notifications.css"> 
    
<style>
/* COMPLETE FIXED CSS FOR NOTIFICATIONS.PHP - NO GAPS, WORKING MOBILE */
body {
    margin: 0 !important;
    padding: 0 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #ffffff !important;
    overflow-x: hidden !important;
}

html {
    margin: 0 !important;
    padding: 0 !important;
}

/* SIDEBAR - ABSOLUTELY FIXED NO GAPS */
.sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    width: 280px !important;
    background: #fff !important;
    border-right: 1px solid #e5e7eb !important;
    z-index: 1000 !important;
    display: flex !important;
    flex-direction: column !important;
    transition: transform 0.3s ease !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

.sidebar.collapsed {
    width: 60px !important;
}

.sidebar-header {
    padding: 1rem !important;
    border-bottom: 1px solid #e5e7eb !important;
    flex-shrink: 0 !important;
    background: #fff !important;
}

.sidebar-content {
    flex: 1 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

.sidebar-footer {
    padding: 1rem !important;
    border-top: 1px solid #e5e7eb !important;
    flex-shrink: 0 !important;
    background: #fff !important;
}

/* DASHBOARD CONTAINER - PERFECT ALIGNMENT */
.dashboard-container {
    margin-left: 280px !important;
    padding: 2rem !important;
    background-color: #ffffff !important;
    min-height: 100vh !important;
    transition: margin-left 0.3s ease !important;
    width: calc(100% - 280px) !important;
    box-sizing: border-box !important;
    position: relative !important;
}

.sidebar.collapsed ~ .dashboard-container {
    margin-left: 60px !important;
    width: calc(100% - 60px) !important;
}

/* FIXED MOBILE OVERLAY - NO MORE UNRESPONSIVE SCREEN */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none; /* KEY FIX - prevents overlay from blocking touches when hidden */
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto; /* Allow touches only when active */
}

/* HAMBURGER BUTTON */
.hamburger-btn {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: #ffffff;
    color: #4a5568;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.hamburger-btn:hover {
    background: #f7fafc;
    transform: scale(1.05);
}

.hamburger-icon {
    width: 20px;
    height: 20px;
}

/* HEADER STYLING */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0 30px 0;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 30px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.school-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header-title {
    margin: 0;
    color: #1a202c;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: -0.025em;
}

.header-date {
    color: #718096;
    font-size: 0.95rem;
    font-weight: 500;
    background: #f7fafc;
    padding: 8px 16px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
}

/* USER WELCOME SECTION */
.user-welcome {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a202c;
    letter-spacing: -0.025em;
}

.welcome-text p {
    margin: 8px 0 0;
    font-size: 1rem;
    color: #4a5568;
    font-weight: 500;
}

.date-time {
    background: rgba(255,255,255,0.9);
    padding: 20px 24px;
    border-radius: 12px;
    text-align: center;
    min-width: 200px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.date-time .time {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    font-family: 'SF Mono', 'Monaco', 'Consolas', 'Liberation Mono', monospace;
    color: #2d3748;
}

.date-time .date {
    font-size: 0.875rem;
    color: #718096;
    font-weight: 500;
}

/* NOTIFICATION FILTERS */
.notification-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    background: #ffffff;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #f1f5f9;
}

.filter-item {
    padding: 12px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 600;
    font-size: 0.875rem;
    background: #ffffff;
    color: #4a5568;
    white-space: nowrap;
}

.filter-item:hover {
    border-color: #667eea;
    background-color: #f0f4ff;
    color: #5a67d8;
    transform: translateY(-1px);
}

.filter-item.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
}

/* CARD STYLING */
.card {
    background: #ffffff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    border: 1px solid #f1f5f9;
}

.card-title {
    margin: 0 0 25px 0;
    color: #1a202c;
    font-size: 1.5rem;
    font-weight: 700;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 15px;
    letter-spacing: -0.025em;
}

/* NOTIFICATION ITEMS */
.notification-item {
    margin-bottom: 20px;
    padding: 24px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
}

.notification-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #cbd5e0;
}

.notification-item.unread {
    border-left: 4px solid #667eea;
    background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    top: 15px;
    right: 15px;
    width: 10px;
    height: 10px;
    background: #667eea;
    border-radius: 50%;
    box-shadow: 0 0 0 2px #ffffff;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.notification-title-area {
    flex: 1;
    margin-right: 16px;
}

.notification-title {
    font-weight: 700;
    color: #1a202c;
    font-size: 1.125rem;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    line-height: 1.4;
}

.notification-meta {
    display: flex;
    gap: 16px;
    font-size: 0.875rem;
    color: #718096;
    flex-wrap: wrap;
    font-weight: 500;
}

.notification-content-text {
    color: #2d3748;
    line-height: 1.6;
    margin-bottom: 16px;
    background: #f7fafc;
    padding: 16px 20px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    font-size: 0.95rem;
    font-weight: 500;
}

.notification-content-text p {
    margin: 0;
    padding: 0;
}

.notification-content-text * {
    all: unset;
    display: inline;
    color: inherit;
    font-size: inherit;
    font-weight: inherit;
    line-height: inherit;
}

.notification-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

/* PRIORITY BADGES */
.priority-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
    letter-spacing: 0.025em;
}

.badge-general { background-color: #718096; color: white; }
.badge-academic { background-color: #4299e1; color: white; }
.badge-event { background-color: #48bb78; color: white; }
.badge-important { background-color: #ed8936; color: white; }
.badge-urgent { background-color: #f56565; color: white; }
.badge-teacher { background-color: #9f7aea; color: white; }
.badge-school { background-color: #38b2ac; color: white; }
.badge-announcement { background-color: #ed8936; color: white; }

/* BUTTONS */
.btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-primary {
    background-color: #4299e1;
    color: white;
}

.btn-primary:hover {
    background-color: #3182ce;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
}

.btn-mark-read {
    background-color: #48bb78;
    color: white;
}

.btn-mark-read:hover {
    background-color: #38a169;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
}

.btn-secondary {
    background-color: #f56565;
    color: white;
}

.btn-secondary:hover {
    background-color: #e53e3e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3);
}

/* PAGINATION */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 30px;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #f1f5f9;
}

.page-arrow {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-arrow:hover:not(:disabled) {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-1px);
}

.page-arrow:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

#page-info {
    font-weight: 600;
    color: #2d3748;
    min-width: 120px;
    text-align: center;
    font-size: 0.95rem;
}

/* LOADING AND EMPTY STATES */
.loading {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
    font-style: italic;
    font-size: 1.1rem;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #718096;
    border: 2px dashed #cbd5e0;
    border-radius: 16px;
    background-color: #f7fafc;
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    color: #a0aec0;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: #4a5568;
    font-size: 1.25rem;
    font-weight: 600;
}

.empty-state p {
    margin: 0;
    color: #718096;
    font-size: 1rem;
}

.error-message {
    text-align: center;
    padding: 24px;
    color: #e53e3e;
    background-color: #fed7d7;
    border: 1px solid #feb2b2;
    border-radius: 12px;
    margin: 20px 0;
    font-weight: 600;
}

/* ANIMATIONS */
.notification-item.removing {
    animation: fadeOutAndShrink 0.5s forwards;
}

@keyframes fadeOutAndShrink {
    from {
        opacity: 1;
        transform: scale(1);
        max-height: 300px;
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

/* MOBILE RESPONSIVE */
@media (max-width: 768px) {
    .hamburger-btn {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    
    .sidebar {
        transform: translateX(-100%) !important;
        width: 280px !important;
    }
    
    .sidebar.show {
        transform: translateX(0) !important;
    }
    
    /* Prevent body scroll when sidebar is open */
    body.sidebar-open {
        overflow: hidden !important;
    }
    
    .dashboard-container {
        margin-left: 0 !important;
        padding: 1rem !important;
        width: 100% !important;
    }
    
    .dashboard-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
        padding-bottom: 20px;
    }
    
    .header-left {
        flex-direction: column;
        gap: 12px;
    }
    
    .header-title {
        font-size: 1.5rem;
    }
    
    .user-welcome {
        flex-direction: column;
        text-align: center;
        gap: 20px;
        padding: 25px;
    }
    
    .welcome-text h2 {
        font-size: 1.5rem;
    }
    
    .notification-filters {
        padding: 20px;
        gap: 8px;
    }
    
    .filter-item {
        padding: 10px 16px;
        font-size: 0.8rem;
    }
    
    .card {
        padding: 20px;
    }
    
    .notification-item {
        padding: 20px;
    }
    
    .notification-header {
        flex-direction: column;
        gap: 12px;
    }
    
    .notification-title-area {
        margin-right: 0;
    }
    
    .notification-meta {
        flex-direction: column;
        gap: 6px;
    }
    
    .notification-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .pagination {
        padding: 20px;
        gap: 15px;
    }
    
    #page-info {
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .dashboard-container {
        padding: 0.75rem !important;
    }
    
    .user-welcome {
        padding: 20px;
    }
    
    .welcome-text h2 {
        font-size: 1.25rem;
    }
    
    .card {
        padding: 16px;
    }
    
    .notification-item {
        padding: 16px;
    }
    
    .notification-title {
        font-size: 1rem;
    }
    
    .notification-content-text {
        padding: 14px 16px;
    }
    
    .filter-item {
        padding: 8px 14px;
        font-size: 0.75rem;
    }
}

/* ACCESSIBILITY */
@media (prefers-contrast: high) {
    .notification-item {
        border-width: 2px;
    }
    
    .filter-item {
        border-width: 2px;
    }
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

.btn:focus,
.filter-item:focus,
.page-arrow:focus,
.hamburger-btn:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}

* {
    scrollbar-width: thin;
    scrollbar-color: #e1e5e9 transparent;
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
            <img src="../../assets/images/school-logo.png" alt="School Logo" class="school-logo">
            <h1 class="header-title">My Notifications</h1>
        </div>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <!-- User Welcome Section -->
        <div class="user-welcome">
            <div class="welcome-text">
                <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p>Stay updated with your latest notifications and announcements</p>
            </div>
            <div class="date-time">
                <div class="time" id="current-time">00:00:00</div>
                <div class="date"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>

        <!-- Notification Filters -->
        <div class="notification-filters"> 
            <div class="filter-item active" data-filter="all">All Notifications</div>
            <div class="filter-item" data-filter="unread">Unread</div>
            <div class="filter-item" data-filter="school">School Announcements</div>
            <div class="filter-item" data-filter="teacher">Teacher Messages</div>
            <div class="filter-item" data-filter="urgent">Urgent</div>
            <div class="filter-item" data-filter="important">Important</div> 
        </div>

        <!-- Notifications Card -->
        <div class="card">
            <h2 class="card-title">All Notifications</h2>
            
            <div class="notification-list" id="notifications-container">
                <div class="loading" id="loading-indicator">
                    <div>Loading notifications...</div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="pagination" id="pagination-controls">
                <button class="page-arrow" id="prev-page" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7 7-7-7" />
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
    const notificationsPerPage = 10;
    let totalNotifications = 0;
    let allFetchedNotifications = [];

    const API_URL = '../../backend/api/notifications.php';

    // FIXED SIDEBAR TOGGLE FUNCTION
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const body = document.body;
        
        if (sidebar && overlay) {
            // Toggle sidebar visibility
            sidebar.classList.toggle('show');
            overlay.classList.toggle('active');
            body.classList.toggle('sidebar-open');
            
            // Ensure overlay is clickable when active
            if (overlay.classList.contains('active')) {
                overlay.style.pointerEvents = 'auto';
            } else {
                overlay.style.pointerEvents = 'none';
            }
        }
    }

    // Update current time
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
        Array.from(container.children).forEach(child => {
            if (child.id !== 'loading-indicator') child.remove();
        });

        try {
            let url = `${API_URL}?action=list&filter_type=${filter}&page=${page}&limit=${notificationsPerPage}`;
            if (unreadOnly) {
                url += '&unread_only=true';
            }

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
                allFetchedNotifications = data.data;
                totalNotifications = data.total_count || allFetchedNotifications.length;
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

   // Create notification HTML element
   function createNotificationElement(notification) {
       const div = document.createElement('div');
       div.className = `notification-item ${!notification.is_read ? 'unread' : ''}`;
       div.dataset.notificationId = notification.id;
       
       const badgeClass = getBadgeClass(notification.type, notification.priority, notification.source);
       const dateFormatted = formatDate(notification.created_at);
       const sender = notification.sender_name || (notification.source === 'teacher' ? 'Teacher' : 'School Admin');

       const iconSvg = getNotificationIcon(notification.type, notification.source);

       div.innerHTML = `
           <div class="notification-header">
               <div class="notification-title-area">
                   <div class="notification-title">
                       ${iconSvg}
                       ${escapeHtml(notification.title)}
                   </div>
                   <div class="notification-meta">
                       <span>📅 ${dateFormatted}</span>
                       <span>👤 From: ${escapeHtml(sender)}</span>
                   </div>
               </div>
               <span class="priority-badge ${badgeClass}">${escapeHtml(notification.type || notification.priority || 'General')}</span>
           </div>
           
           <div class="notification-content-text">
               ${escapeHtml(notification.message)}
           </div>
           
           <div class="notification-actions">
               ${!notification.is_read ? `<button class="btn btn-mark-read" onclick="markAsRead(${notification.id}, this)">✓ Mark as Read</button>` : ''}
           </div>
       `;
       return div;
   }

   // Mark notification as read
   async function markAsRead(notificationId, buttonElement) {
       try {
           const response = await fetch(`${API_URL}?action=mark_read`, {
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
                   notificationElement.classList.add('read');
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
       let icon = '🔔'; // Default bell icon

       if (type === 'academic' || source === 'teacher') {
           icon = '📚';
       } else if (type === 'event') {
           icon = '📅';
       } else if (type === 'urgent' || type === 'important') {
           icon = '⚠️';
       }
       return icon;
   }

   function formatDate(dateString) {
       if (!dateString) return 'N/A';
       const date = new Date(dateString);
       return date.toLocaleDateString('en-US', { 
           year: 'numeric', 
           month: 'short', 
           day: 'numeric'
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
           Array.from(container.children).forEach(child => {
               if (child.id !== 'loading-indicator') child.remove();
           });
           const errorDiv = document.createElement('div');
           errorDiv.className = 'error-message';
           errorDiv.innerHTML = `<strong>Error:</strong> ${escapeHtml(message)}`;
           container.appendChild(errorDiv);
       } else {
           console.error('Cannot show error message - notifications-container not found:', message);
           alert(`Error: ${message}`);
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
               currentPage = 1;
               
               if (filterType === 'unread') {
                   loadNotifications('all', true, currentPage);
               } else {
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
       const totalPages = Math.ceil(totalNotifications / notificationsPerPage);
       if (currentPage < totalPages) {
           loadNotifications(currentFilter, currentFilter === 'unread', currentPage + 1);
       }
   });

   // CLOSE SIDEBAR WHEN OVERLAY IS CLICKED
   document.addEventListener('DOMContentLoaded', function() {
       const overlay = document.querySelector('.sidebar-overlay');
       
       if (overlay) {
           overlay.addEventListener('click', function(e) {
               // Only close if clicking the overlay itself, not sidebar content
               if (e.target === overlay) {
                   toggleSidebar();
               }
           });
       }
   });

   // CLOSE SIDEBAR WHEN CLICKING OUTSIDE
   document.addEventListener('click', function(e) {
       if (window.innerWidth <= 768) {
           const sidebar = document.querySelector('.sidebar');
           const hamburger = document.querySelector('.hamburger-btn');
           
           if (sidebar && hamburger) {
               // Close if clicking outside sidebar and hamburger
               if (!sidebar.contains(e.target) && !hamburger.contains(e.target) && sidebar.classList.contains('show')) {
                   toggleSidebar();
               }
           }
       }
   });

   // HANDLE WINDOW RESIZE
   window.addEventListener('resize', function() {
       if (window.innerWidth > 768) {
           const sidebar = document.querySelector('.sidebar');
           const overlay = document.querySelector('.sidebar-overlay');
           const body = document.body;
           
           // Reset mobile states when switching to desktop
           if (sidebar) sidebar.classList.remove('show');
           if (overlay) {
               overlay.classList.remove('active');
               overlay.style.pointerEvents = 'none';
           }
           if (body) body.classList.remove('sidebar-open');
       }
   });

   // Load notifications when page loads
   document.addEventListener('DOMContentLoaded', function() {
       try {
           loadNotifications(currentFilter, false, currentPage);
           updateTime();
           setInterval(updateTime, 1000);
       } catch (error) {
           console.error('Error during initial load:', error);
           showError('Error loading initial notifications.');
       }
   });
</script>
</body>
</html>
