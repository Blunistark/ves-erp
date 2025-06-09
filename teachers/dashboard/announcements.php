<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit();
}

include 'sidebar.php'; 
?>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1 class="header-title">School Announcements</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <?php
        // Fetch announcements for teachers (either target_audience = 'all' or 'teachers')
        $query = "SELECT a.*, u.full_name as created_by_name 
                FROM announcements a 
                JOIN users u ON a.created_by = u.id 
                WHERE (a.target_audience = 'all' OR a.target_audience = 'teachers') 
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
        ?>

        <div class="card">
            <h2 class="card-title">Official School Announcements</h2>
            
            <div class="announcement-list">
                <?php if (empty($announcements)): ?>
                    <div class="no-announcements">
                        <p>No announcements available at this time.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <?php
                            // Format date
                            $createdDate = new DateTime($announcement['created_at']);
                            $formattedDate = $createdDate->format('M j, Y');
                            
                            // Determine priority badge class
                            $priorityClass = 'badge-normal';
                            if ($announcement['priority'] === 'important') {
                                $priorityClass = 'badge-important';
                            } else if ($announcement['priority'] === 'urgent') {
                                $priorityClass = 'badge-urgent';
                            }
                        ?>
                        <div class="announcement-card">
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
                                <span class="announcement-badge <?php echo $priorityClass; ?>">
                                    <?php echo ucfirst($announcement['priority']); ?>
                                </span>
                            </div>
                            
                            <div class="announcement-content">
                                <?php echo $announcement['content']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Mark notifications as read when viewed -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Create an array of announcement IDs that were viewed
        const announcementIds = [
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
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: 'announcement',
                    ids: announcementIds
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('Error marking announcements as read:', data.error);
                }
            })
            .catch(error => {
                console.error('Error marking announcements as read:', error);
            });
        }
    });
    
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
    }
</script>

<link rel="stylesheet" href="css/announcements.css">