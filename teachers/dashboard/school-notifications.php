<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Announcements</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/school-notifications.css">
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
            <div class="filter-item active">All</div>
            <div class="filter-item">Academic</div>
            <div class="filter-item">Events</div>
            <div class="filter-item">Important</div>
            <div class="filter-item">General</div>
        </div>

        <!-- Featured Announcement -->
        <div class="featured-announcement">
            <div class="featured-header">
                <svg xmlns="http://www.w3.org/2000/svg" class="featured-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <div class="featured-title">Spring Concert - Date Change</div>
            </div>
            
            <div class="featured-content">
                <div class="featured-message">
                    <p>Please note that the Annual Spring Concert has been rescheduled from April 15th to April 22nd due to venue availability. All ticket purchases will be honored for the new date.</p>
                    <p>The concert will still begin at 6:30 PM and feature performances from our choir, orchestra, and band. If you are unable to attend on the new date, please contact the school office for a refund.</p>
                </div>
                
                <div class="featured-footer">
                    <div class="featured-date">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Posted on March 10, 2025
                    </div>
                    <div class="featured-source">Music Department</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Recent Announcements</h2>
            
            <div class="announcement-list">
                <!-- Academic Announcement -->
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="announcement-title-area">
                            <div class="announcement-title">Final Exam Schedule Released</div>
                            <div class="announcement-meta">
                                <div class="announcement-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Mar 11, 2025
                                </div>
                                <div class="announcement-author">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Academic Office
                                </div>
                            </div>
                        </div>
                        <span class="announcement-badge badge-academic">Academic</span>
                    </div>
                    
                    <div class="announcement-content">
                        <div class="content-preview">
                            The final examination schedule for the Spring semester has been published. Please review your exam dates and times carefully. All exams will take place between May 15-20, 2025. Students with schedule conflicts should contact the Academic Office immediately to arrange alternate times. Study guides for all subjects will be made available starting April 15.
                        </div>
                    </div>
                    
                    <div class="announcement-actions">
                        <div class="action-buttons">
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                        </div>
                        
                        <a href="#" class="read-more">
                            Read More
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Event Announcement -->
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="announcement-title-area">
                            <div class="announcement-title">Career Day - Industry Professionals Visit</div>
                            <div class="announcement-meta">
                                <div class="announcement-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Mar 9, 2025
                                </div>
                                <div class="announcement-author">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Guidance Department
                                </div>
                            </div>
                        </div>
                        <span class="announcement-badge badge-event">Event</span>
                    </div>
                    
                    <div class="announcement-content">
                        <div class="content-preview">
                            We are excited to announce our annual Career Day on April 8, 2025. Over 30 professionals from various industries will be on campus to share insights about their careers and answer your questions. Industries represented include healthcare, technology, finance, arts, education, engineering, and more. This is an excellent opportunity for all students to explore potential career paths and make valuable connections.
                        </div>
                    </div>
                    
                    <div class="announcement-actions">
                        <div class="action-buttons">
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                        </div>
                        
                        <a href="#" class="read-more">
                            Read More
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Important Announcement -->
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="announcement-title-area">
                            <div class="announcement-title">Updated School Safety Procedures</div>
                            <div class="announcement-meta">
                                <div class="announcement-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Mar 5, 2025
                                </div>
                                <div class="announcement-author">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Administration
                                </div>
                            </div>
                        </div>
                        <span class="announcement-badge badge-important">Important</span>
                    </div>
                    
                    <div class="announcement-content">
                        <div class="content-preview">
                            In our ongoing commitment to maintaining a safe learning environment, we have updated our school safety procedures. Beginning March 15, all visitors must check in at the new security desk in the main entrance. Students will need to use their ID cards to access the building during school hours. Additionally, we will conduct monthly emergency drills to ensure everyone is prepared in case of an emergency situation.
                        </div>
                    </div>
                    
                    <div class="announcement-actions">
                        <div class="action-buttons">
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                        </div>
                        
                        <a href="#" class="read-more">
                            Read More
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- General Announcement -->
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="announcement-title-area">
                            <div class="announcement-title">New Library Resources Available</div>
                            <div class="announcement-meta">
                                <div class="announcement-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Mar 3, 2025
                                </div>
                                <div class="announcement-author">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Library
                                </div>
                            </div>
                        </div>
                        <span class="announcement-badge badge-general">General</span>
                    </div>
                    
                    <div class="announcement-content">
                        <div class="content-preview">
                            The school library has added over 200 new books and digital resources to our collection! New titles span multiple genres including fiction, non-fiction, reference materials, and educational resources. Additionally, we have expanded our digital subscription services to include several new research databases. Students can access these resources both at school and remotely through the school portal.
                        </div>
                    </div>
                    
                    <div class="announcement-actions">
                        <div class="action-buttons">
                            <button class="action-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </button>
                            <button class="action-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                        </div>
                        
                        <a href="#" class="read-more">
                            Read More
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- More announcements would follow... -->
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
    // Script to handle announcement filters
    document.querySelectorAll('.filter-item').forEach(filter => {
        filter.addEventListener('click', function() {
            document.querySelectorAll('.filter-item').forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // In a real app, you would filter the announcements here
            // Example filtering logic would be implemented based on selected category
        });
    });
    
    // Script to handle bookmark action
    document.querySelectorAll('.action-buttons button:first-child').forEach(button => {
        button.addEventListener('click', function() {
            // Toggle active state for bookmark button
            this.classList.toggle('active');
            if (this.classList.contains('active')) {
                this.style.background = '#e0e7ff';
                this.querySelector('svg').style.color = '#4338ca';
            } else {
                this.style.background = '#f3f4f6';
                this.querySelector('svg').style.color = '#4b5563';
            }
        });
    });
    
    // Script to handle share action
    document.querySelectorAll('.action-buttons button:last-child').forEach(button => {
        button.addEventListener('click', function() {
            // Show share dialog (in a real app)
            alert('Share functionality would open a sharing dialog');
        });
    });
    
    // Script to handle pagination
    document.querySelectorAll('.page-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.page-item').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            
            // In a real app, you would load the corresponding page here
        });
    });
    
    document.querySelector('.page-arrow:last-child').addEventListener('click', function() {
        const activePage = document.querySelector('.page-item.active');
        const nextPage = activePage.nextElementSibling;
        
        if (nextPage && nextPage.classList.contains('page-item')) {
            activePage.classList.remove('active');
            nextPage.classList.add('active');
            
            // Enable previous arrow if we're moving from page 1
            if (nextPage.textContent === '2') {
                document.querySelector('.page-arrow.disabled').classList.remove('disabled');
            }
        }
    });
    
    // Handle read more links
    document.querySelectorAll('.read-more').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.announcement-card');
            
            // In a real app, you would navigate to the detail page or expand the announcement
            alert('This would open the full announcement in a real application');
        });
    });
</script>
</body>
</html>