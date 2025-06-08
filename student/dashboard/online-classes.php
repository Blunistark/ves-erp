<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Classes</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/online-classes.css">
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
        <h1 class="header-title">Online Classes</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="class-banner">
            <div class="banner-pattern"></div>
            <div class="banner-content">
                <h2 class="banner-title">Welcome to Your Virtual Classroom</h2>
                <p class="banner-description">Access live and recorded classes, course materials, and announcements for all your subjects in one place.</p>
                
                <div class="banner-actions">
                    <button class="banner-button primary-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Join Live Class
                    </button>
                    
                    <button class="banner-button secondary-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Access Course Materials
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card class-schedule">
            <div class="schedule-header">
                <h2 class="schedule-title">Upcoming Classes</h2>
                
                <div class="schedule-filter">
                    <div class="filter-label">Filter by:</div>
                    <select class="filter-select">
                        <option value="all">All Classes</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="live">Live Now</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <div class="schedule-grid">
                <!-- Mathematics Class (Live) -->
                <div class="class-card">
                    <div class="class-badge badge-live">Live Now</div>
                    
                    <div class="class-subject">
                        <div class="subject-icon math-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        
                        <div class="subject-name">Mathematics</div>
                        <div class="subject-teacher">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Mrs. Johnson
                        </div>
                    </div>
                    
                    <div class="class-details">
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Wednesday, March 12, 2025</span>
                        </div>
                        
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>9:00 AM - 10:30 AM</span>
                        </div>
                        
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Topic: Quadratic Equations</span>
                        </div>
                    </div>
                    
                    <div class="class-actions">
                        <div class="class-time">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Started 45 minutes ago
                        </div>
                        
                        <button class="join-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Join Now
                        </button>
                    </div>
                </div>
                
                <!-- Science Class (Upcoming) -->
                <div class="class-card">
                    <div class="class-badge badge-upcoming">Upcoming</div>
                    
                    <div class="class-subject">
                        <div class="subject-icon science-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        
                        <div class="subject-name">Science</div>
                        <div class="subject-teacher">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Mr. Wilson
                        </div>
                    </div>
                    
                    <div class="class-details">
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Wednesday, March 12, 2025</span>
                        </div>
                        
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>11:00 AM - 12:30 PM</span>
                        </div>
                        
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Topic: Light and Optics</span>
                        </div>
                    </div>
                    
                    <div class="class-actions">
                        <div class="class-time">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Starts in 1 hour 15 minutes
                        </div>
                        
                        <button class="join-button" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Not Started Yet
                        </button>
                    </div>
                </div>
                
                <!-- English Class (Upcoming) -->
                <div class="class-card">
                    <div class="class-badge badge-upcoming">Upcoming</div>
                    
                    <div class="class-subject">
                        <div class="subject-icon language-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                        </div>
                        
                        <div class="subject-name">English Language</div>
                        <div class="subject-teacher">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Ms. Adams
                        </div>
                    </div>
                    
                    <div class="class-details">
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Wednesday, March 12, 2025</span>
                        </div>
                        
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>2:00 PM - 3:30 PM</span>
                        </div>
                        
                        <div class="detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Topic: Essay Writing Techniques</span>
                        </div>
                    </div>
                    
                    <div class="class-actions">
                        <div class="class-time">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Starts in 4 hours 15 minutes
                        </div>
                        
                        <button class="join-button" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Not Started Yet
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Class Announcements</h2>
            
            <div class="announcements-list">
                <!-- Mathematics Announcement -->
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="announcement-title">Additional Practice Problems for Quadratic Equations</div>
                        <div class="announcement-subject subject-math">Mathematics</div>
                    </div>
                    
                    <div class="announcement-content">
                        <p>Dear students,</p>
                        <p>To help you prepare for the upcoming test on Quadratic Equations, I have uploaded additional practice problems. These problems cover all the key concepts we've discussed in our recent classes and are similar to what you'll see on the test.</p>
                        <p>Please try to solve these problems before our next class. We will discuss any questions you have during the Q&A session.</p>
                        
                        <div class="announcement-files">
                            <div class="file-item">
                                <div class="file-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                
                                <div class="file-details">
                                    <div class="file-name">Quadratic_Equations_Practice.pdf</div>
                                    <div class="file-size">1.2 MB</div>
                                </div>
                                
                                <button class="file-download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="announcement-footer">
                        <div class="announcement-teacher">
                            <div class="teacher-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            
                            <div class="teacher-info">
                                <div class="teacher-name">Mrs. Johnson</div>
                                <div class="teacher-role">Mathematics Teacher</div>
                            </div>
                        </div>
                        
                        <div class="announcement-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on March 11, 2025
                        </div>
                    </div>
                    
                    <div class="discussion-preview">
                        <h3 class="discussion-title">Class Discussion (3 comments)</h3>
                        
                        <div class="discussion-comments">
                            <div class="comment-item">
                                <div class="comment-avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                
                                <div class="comment-content">
                                    <div class="comment-author">Sarah Parker</div>
                                    <div class="comment-text">Thank you for the practice problems! I'm finding the word problems particularly challenging. Could we spend some time on those in the next class?</div>
                                    <div class="comment-time">Yesterday, 4:32 PM</div>
                                </div>
                            </div>
                            
                            <div class="comment-item">
                                <div class="comment-avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                
                                <div class="comment-content">
                                    <div class="comment-author">Mrs. Johnson</div>
                                    <div class="comment-text">Absolutely, Sarah. We'll dedicate time to work through word problems step-by-step. I recommend trying problems 5-8 which focus on this skill.</div>
                                    <div class="comment-time">Yesterday, 5:15 PM</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="view-all-comments">
                            View all comments
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Science Announcement -->
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="announcement-title">Light and Optics Virtual Lab Preparation</div>
                        <div class="announcement-subject subject-science">Science</div>
                    </div>
                    
                    <div class="announcement-content">
                        <p>Hello Class X-A,</p>
                        <p>In our upcoming virtual class on Light and Optics, we will be conducting a simulation lab using the online platform PhysicsLab. Please ensure you have created an account on the platform before our class. I've attached the instructions for setting up your account and a pre-lab reading assignment.</p>
                        <p>The simulation will allow us to conduct experiments on light refraction and reflection that would normally require specialized equipment. Please come prepared with questions about the pre-lab reading.</p>
                        
                        <div class="announcement-files">
                            <div class="file-item">
                                <div class="file-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                
                                <div class="file-details">
                                    <div class="file-name">PhysicsLab_Setup_Guide.pdf</div>
                                    <div class="file-size">850 KB</div>
                                </div>
                                
                                <button class="file-download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="file-item">
                                <div class="file-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                
                                <div class="file-details">
                                    <div class="file-name">PreLab_Reading_Optics.pdf</div>
                                    <div class="file-size">1.5 MB</div>
                                </div>
                                
                                <button class="file-download">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="announcement-footer">
                        <div class="announcement-teacher">
                            <div class="teacher-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            
                            <div class="teacher-info">
                                <div class="teacher-name">Mr. Wilson</div>
                                <div class="teacher-role">Science Teacher</div>
                            </div>
                        </div>
                        
                        <div class="announcement-date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Posted on March 10, 2025
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Handle class filter
    document.querySelector('.filter-select').addEventListener('change', function() {
        // In a real app, this would filter the displayed classes
        alert(`Filter applied: ${this.value}. In a real application, this would filter the class list.`);
    });
    
    // Handle join class button
    document.querySelectorAll('.join-button:not([disabled])').forEach(button => {
        button.addEventListener('click', function() {
            const subjectName = this.closest('.class-card').querySelector('.subject-name').textContent;
            alert(`Joining ${subjectName} class. In a real application, this would open the virtual classroom.`);
        });
    });
    
    // Handle banner buttons
    document.querySelector('.primary-button').addEventListener('click', function() {
        alert('This would open the virtual classroom for your current class.');
    });
    
    document.querySelector('.secondary-button').addEventListener('click', function() {
        alert('This would open the course materials page.');
    });
    
    // Handle file downloads
    document.querySelectorAll('.file-download').forEach(button => {
        button.addEventListener('click', function() {
            const fileName = this.closest('.file-item').querySelector('.file-name').textContent;
            alert(`Downloading ${fileName}. In a real application, this would start the file download.`);
        });
    });
    
    // Handle view all comments
    document.querySelector('.view-all-comments').addEventListener('click', function() {
        alert('This would show all comments for this announcement.');
    });
</script>
</body>
</html>