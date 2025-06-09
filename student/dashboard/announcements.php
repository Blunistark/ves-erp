<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'sidebar.php';
include 'con.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit();
}

// Fetch announcements for students (either target_audience = 'all' or 'students')
$query = "SELECT a.*, u.full_name as created_by_name 
          FROM announcements a 
          JOIN users u ON a.created_by = u.id 
          WHERE (a.target_audience = 'all' OR a.target_audience = 'students') 
          AND a.is_active = 1
          AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())
          ORDER BY a.priority DESC, a.created_at DESC";
$result = $conn->query($query);

// Create array to store announcements
$announcements = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}

// Get featured announcement (most recent urgent or important announcement)
$featured_announcement = null;
foreach ($announcements as $key => $announcement) {
    if ($announcement['priority'] === 'urgent' || $announcement['priority'] === 'important') {
        $featured_announcement = $announcement;
        // Remove from regular list to avoid duplication
        unset($announcements[$key]);
        break;
    }
}

// If no urgent/important announcement, use the most recent one
if ($featured_announcement === null && !empty($announcements)) {
    $featured_announcement = reset($announcements);
    // Remove from regular list to avoid duplication
    unset($announcements[key($announcements)]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Announcements</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/announcements.css">
    <style>
        /* Additional styles for announcement priorities */
        .badge-normal {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-important {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .badge-urgent {
            background: #fecaca;
            color: #7f1d1d;
        }
        
        .no-announcements {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
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
        <h1 class="header-title">Announcements</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="announcement-filters">
            <div class="filter-item active" data-filter="all">All</div>
            <div class="filter-item" data-filter="normal">General</div>
            <div class="filter-item" data-filter="important">Important</div>
            <div class="filter-item" data-filter="urgent">Urgent</div>
        </div>

        <?php if ($featured_announcement): ?>
            <?php
                // Format date for featured announcement
                $featuredDate = new DateTime($featured_announcement['created_at']);
                $formattedFeaturedDate = $featuredDate->format('F j, Y');
            ?>
            <!-- Featured Announcement -->
            <div class="featured-announcement">
                <div class="featured-header">
                    <svg xmlns="http://www.w3.org/2000/svg" class="featured-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <div class="featured-title"><?php echo htmlspecialchars($featured_announcement['title']); ?></div>
                </div>
                
                <div class="featured-content">
                    <div class="featured-message">
                        <?php echo $featured_announcement['content']; ?>
                    </div>
                    
                    <div class="featured-footer">
                        <div class="featured-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on <?php echo $formattedFeaturedDate; ?>
                        </div>
                        <div class="featured-source"><?php echo htmlspecialchars($featured_announcement['created_by_name']); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title">Announcements</h2>
            
            <div class="announcement-list">
                <?php if (empty($announcements)): ?>
                    <div class="no-announcements">
                        <p>No additional announcements available at this time.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <?php
                            // Format date
                            $createdDate = new DateTime($announcement['created_at']);
                            $formattedDate = $createdDate->format('M j, Y');
                            
                            // Determine priority badge class
                            $priorityClass = 'badge-normal';
                            $priorityLabel = 'General';
                            if ($announcement['priority'] === 'important') {
                                $priorityClass = 'badge-important';
                                $priorityLabel = 'Important';
                            } else if ($announcement['priority'] === 'urgent') {
                                $priorityClass = 'badge-urgent';
                                $priorityLabel = 'Urgent';
                            }
                        ?>
                        <div class="announcement-card" data-priority="<?php echo $announcement['priority']; ?>">
                            <div class="announcement-header">
                                <div class="announcement-title-area">
                                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                    <div class="announcement-meta">
                                        <div class="announcement-date">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <?php echo $formattedDate; ?>
                                        </div>
                                        <div class="announcement-author">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <?php echo htmlspecialchars($announcement['created_by_name']); ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="announcement-badge <?php echo $priorityClass; ?>"><?php echo $priorityLabel; ?></span>
                            </div>
                            
                            <div class="announcement-content">
                                <div class="content-preview">
                                    <?php echo $announcement['content']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    // Announcement filtering
    document.addEventListener('DOMContentLoaded', function() {
        // Get all filter items and announcement cards
        const filterItems = document.querySelectorAll('.filter-item');
        const announcementCards = document.querySelectorAll('.announcement-card');
        
        // Add click event listeners to filter items
        filterItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all filter items
                filterItems.forEach(filter => filter.classList.remove('active'));
                
                // Add active class to clicked filter item
                this.classList.add('active');
                
                // Get the filter value
                const filterValue = this.getAttribute('data-filter');
                
                // Show/hide announcement cards based on filter
                announcementCards.forEach(card => {
                    if (filterValue === 'all' || card.getAttribute('data-priority') === filterValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
        
        // Create an array of announcement IDs that were viewed
        const announcementIds = [
            <?php if ($featured_announcement): ?>
                <?php echo $featured_announcement['id']; ?>,
            <?php endif; ?>
            <?php foreach ($announcements as $announcement): ?>
                <?php echo $announcement['id']; ?>,
            <?php endforeach; ?>
        ];
        
        // If there are announcements, mark them as read
        if (announcementIds.length > 0) {
            fetch('mark_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'announcement',
                    ids: announcementIds
                }),
            });
        }
    });
</script>
</body>
</html>