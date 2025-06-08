<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Resource Library</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/resourcemanage.css">
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
            <h1 class="header-title">Resource Library</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs">
                    <div class="tab active" data-tab="upload-resources">Upload Resources</div>
                    <div class="tab" data-tab="manage-resources">Manage Resources</div>
                </div>
            </div>

            <!-- Upload Resources Tab -->
            <div class="tab-content active" id="upload-resources">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Upload Learning Resources</h2>
                            <p class="card-subtitle">Share educational materials with students and teachers</p>
                        </div>
                    </div>
                    <form id="upload-resource-form" action="process_upload.php" method="post" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="resource-title" class="form-label">Resource Title *</label>
                                        <input type="text" id="resource-title" name="resource_title" class="form-control" placeholder="Enter a title for this resource" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="resource-type" class="form-label">Resource Type *</label>
                                        <select id="resource-type" name="resource_type" class="form-select" required>
                                            <option value="">Select type</option>
                                            <option value="lesson_plan">Lesson Plan</option>
                                            <option value="worksheet">Worksheet</option>
                                            <option value="presentation">Presentation</option>
                                            <option value="ebook">E-Book</option>
                                            <option value="assessment">Assessment</option>
                                            <option value="video">Video</option>
                                            <option value="audio">Audio</option>
                                            <option value="image">Image/Infographic</option>
                                            <option value="interactive">Interactive Resource</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <select id="subject" name="subject" class="form-select" required>
                                            <option value="">Select subject</option>
                                            <option value="mathematics">Mathematics</option>
                                            <option value="science">Science</option>
                                            <option value="english">English</option>
                                            <option value="history">History</option>
                                            <option value="geography">Geography</option>
                                            <option value="physics">Physics</option>
                                            <option value="chemistry">Chemistry</option>
                                            <option value="biology">Biology</option>
                                            <option value="computer_science">Computer Science</option>
                                            <option value="physical_education">Physical Education</option>
                                            <option value="art">Art</option>
                                            <option value="music">Music</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="grade-level" class="form-label">Grade Level *</label>
                                        <select id="grade-level" name="grade_level[]" class="form-select" multiple required>
                                            <option value="1">Class 1</option>
                                            <option value="2">Class 2</option>
                                            <option value="3">Class 3</option>
                                            <option value="4">Class 4</option>
                                            <option value="5">Class 5</option>
                                            <option value="6">Class 6</option>
                                            <option value="7">Class 7</option>
                                            <option value="8">Class 8</option>
                                            <option value="9">Class 9</option>
                                            <option value="10">Class 10</option>
                                            <option value="11">Class 11</option>
                                            <option value="12">Class 12</option>
                                            <option value="all">All Grades</option>
                                        </select>
                                        <small class="form-text">Hold Ctrl/Cmd to select multiple grades</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="resource-description" class="form-label">Description</label>
                                <textarea id="resource-description" name="resource_description" class="form-control" placeholder="Provide a brief description of this resource"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="resource-tags" class="form-label">Tags</label>
                                <input type="text" id="resource-tags" name="resource_tags" class="form-control" placeholder="Enter tags separated by commas">
                                <small class="form-text">Example: algebra, equations, factoring</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Upload Files *</label>
                                <div class="upload-area" id="dropzone">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <div class="upload-text">Drag and drop files here</div>
                                    <div class="upload-hint">or click to browse your computer</div>
                                    <button type="button" class="button button-outline button-sm" id="browse-btn">Browse Files</button>
                                    <input type="file" id="file-upload" name="files[]" class="upload-input" multiple>
                                </div>
                                <small class="form-text">Allowed file types: PDF, DOCX, PPTX, XLSX, MP4, MP3, JPG, PNG, ZIP (Max size: 50MB)</small>
                            </div>

                            <div class="progress-container" id="upload-progress-container">
                                <!-- Progress items will be inserted here dynamically -->
                            </div>

                            <div class="form-group">
                                <label class="form-label">Access Permissions</label>
                                <div class="form-check">
                                    <input type="checkbox" id="access-students" name="access_students" class="form-check-input" value="1" checked>
                                    <label for="access-students" class="form-check-label">Students</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="access-teachers" name="access_teachers" class="form-check-input" value="1" checked>
                                    <label for="access-teachers" class="form-check-label">Teachers</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="access-parents" name="access_parents" class="form-check-input" value="1">
                                    <label for="access-parents" class="form-check-label">Parents</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Additional Options</label>
                                <div class="form-check">
                                    <input type="checkbox" id="notify-users" name="notify_users" class="form-check-input" value="1">
                                    <label for="notify-users" class="form-check-label">Notify users about this resource</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="featured-resource" name="featured_resource" class="form-check-input" value="1">
                                    <label for="featured-resource" class="form-check-label">Mark as featured resource</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="track-downloads" name="track_downloads" class="form-check-input" value="1" checked>
                                    <label for="track-downloads" class="form-check-label">Track downloads and usage</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="button button-outline">Cancel</button>
                            <button type="submit" class="button" id="upload-btn">Upload Resource</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Manage Resources Tab -->
            <div class="tab-content" id="manage-resources">
                <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search resources...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Subjects</option>
                            <option value="mathematics">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="english">English</option>
                            <option value="history">History</option>
                            <option value="physics">Physics</option>
                            <option value="chemistry">Chemistry</option>
                            <option value="biology">Biology</option>
                            <option value="computer_science">Computer Science</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Grades</option>
                            <option value="1">Class 1</option>
                            <option value="2">Class 2</option>
                            <option value="3">Class 3</option>
                            <option value="4-5">Class 4-5</option>
                            <option value="6-8">Class 6-8</option>
                            <option value="9-10">Class 9-10</option>
                            <option value="11-12">Class 11-12</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Types</option>
                            <option value="lesson_plan">Lesson Plan</option>
                            <option value="worksheet">Worksheet</option>
                            <option value="presentation">Presentation</option>
                            <option value="ebook">E-Book</option>
                            <option value="assessment">Assessment</option>
                            <option value="video">Video</option>
                            <option value="audio">Audio</option>
                            <option value="image">Image/Infographic</option>
                        </select>
                        <select class="form-select">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="a-z">A-Z</option>
                            <option value="z-a">Z-A</option>
                            <option value="most_downloaded">Most Downloaded</option>
                        </select>
                    </div>
                </div>

                <div class="category-pills">
                    <div class="category-pill active">All Resources</div>
                    <div class="category-pill">My Uploads</div>
                    <div class="category-pill">Featured</div>
                    <div class="category-pill">Recently Added</div>
                    <div class="category-pill">Most Popular</div>
                </div>

                <div class="resource-grid">
                    <!-- Resource Card 1 -->
                    <div class="resource-card">
                        <div class="resource-thumbnail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="resource-thumbnail-icon" fill="none" viewBox="0 0 24 24" stroke="#f87171">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2v5a2 2 0 002 2h5" />
                            </svg>
                            <div class="resource-type-badge resource-type-pdf">PDF</div>
                        </div>
                        <div class="resource-details">
                            <h3 class="resource-title">Algebra Fundamentals: Quadratic Equations and Factoring</h3>
                            <div class="resource-subject">Mathematics</div>
                            <div class="resource-meta">
                                <div class="resource-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Mar 15, 2025
                                </div>
                                <div class="resource-size">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    2.4 MB
                                </div>
                            </div>
                        </div>
                        <div class="resource-footer">
                            <div class="resource-uploader">
                                <div class="uploader-avatar">JS</div>
                                <span>John Smith</span>
                            </div>
                            <div class="resource-actions">
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="12" cy="5" r="1"></circle>
                                        <circle cx="12" cy="19" r="1"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Card 2 -->
                    <div class="resource-card">
                        <div class="resource-thumbnail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="resource-thumbnail-icon" fill="none" viewBox="0 0 24 24" stroke="#3b82f6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2v5a2 2 0 002 2h5" />
                            </svg>
                            <div class="resource-type-badge resource-type-doc">DOCX</div>
                        </div>
                        <div class="resource-details">
                            <h3 class="resource-title">English Literature: Shakespeare's Macbeth Character Analysis</h3>
                            <div class="resource-subject">English</div>
                            <div class="resource-meta">
                                <div class="resource-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Mar 12, 2025
                                </div>
                                <div class="resource-size">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    1.8 MB
                                </div>
                            </div>
                        </div>
                        <div class="resource-footer">
                            <div class="resource-uploader">
                                <div class="uploader-avatar">MB</div>
                                <span>Michael Brown</span>
                            </div>
                            <div class="resource-actions">
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="12" cy="5" r="1"></circle>
                                        <circle cx="12" cy="19" r="1"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Card 3 -->
                    <div class="resource-card">
                        <div class="resource-thumbnail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="resource-thumbnail-icon" fill="none" viewBox="0 0 24 24" stroke="#f59e0b">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2v5a2 2 0 002 2h5" />
                            </svg>
                            <div class="resource-type-badge resource-type-ppt">PPTX</div>
                        </div>
                        <div class="resource-details">
                            <h3 class="resource-title">Biology: Cell Structure and Function</h3>
                            <div class="resource-subject">Biology</div>
                            <div class="resource-meta">
                                <div class="resource-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Mar 10, 2025
                                </div>
                                <div class="resource-size">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    5.2 MB
                                </div>
                            </div>
                        </div>
                        <div class="resource-footer">
                            <div class="resource-uploader">
                                <div class="uploader-avatar">DM</div>
                                <span>David Miller</span>
                            </div>
                            <div class="resource-actions">
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="12" cy="5" r="1"></circle>
                                        <circle cx="12" cy="19" r="1"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Card 4 -->
                    <div class="resource-card">
                        <div class="resource-thumbnail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="resource-thumbnail-icon" fill="none" viewBox="0 0 24 24" stroke="#8b5cf6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <div class="resource-type-badge resource-type-vid">MP4</div>
                        </div>
                        <div class="resource-details">
                            <h3 class="resource-title">Physics: Understanding Newton's Laws of Motion</h3>
                            <div class="resource-subject">Physics</div>
                            <div class="resource-meta">
                                <div class="resource-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Mar 8, 2025
                                </div>
                                <div class="resource-size">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    45.7 MB
                                </div>
                            </div>
                        </div>
                        <div class="resource-footer">
                            <div class="resource-uploader">
                             <div class="uploader-avatar">RD</div>
                                <span>Robert Davis</span>
                            </div>
                            <div class="resource-actions">
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="12" cy="5" r="1"></circle>
                                        <circle cx="12" cy="19" r="1"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Card 5 -->
                    <div class="resource-card">
                        <div class="resource-thumbnail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="resource-thumbnail-icon" fill="none" viewBox="0 0 24 24" stroke="#10b981">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="resource-type-badge resource-type-img">PNG</div>
                        </div>
                        <div class="resource-details">
                            <h3 class="resource-title">World Map: Continents and Major Geographical Features</h3>
                            <div class="resource-subject">Geography</div>
                            <div class="resource-meta">
                                <div class="resource-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Mar 5, 2025
                                </div>
                                <div class="resource-size">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    3.8 MB
                                </div>
                            </div>
                        </div>
                        <div class="resource-footer">
                            <div class="resource-uploader">
                                <div class="uploader-avatar">JA</div>
                                <span>James Anderson</span>
                            </div>
                            <div class="resource-actions">
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="12" cy="5" r="1"></circle>
                                        <circle cx="12" cy="19" r="1"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Card 6 -->
                    <div class="resource-card">
                        <div class="resource-thumbnail">
                            <svg xmlns="http://www.w3.org/2000/svg" class="resource-thumbnail-icon" fill="none" viewBox="0 0 24 24" stroke="#4338ca">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <div class="resource-type-badge resource-type-zip">ZIP</div>
                        </div>
                        <div class="resource-details">
                            <h3 class="resource-title">Computer Science: HTML/CSS Project Templates</h3>
                            <div class="resource-subject">Computer Science</div>
                            <div class="resource-meta">
                                <div class="resource-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Mar 3, 2025
                                </div>
                                <div class="resource-size">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    12.6 MB
                                </div>
                            </div>
                        </div>
                        <div class="resource-footer">
                            <div class="resource-uploader">
                                <div class="uploader-avatar">AW</div>
                                <span>Amanda White</span>
                            </div>
                            <div class="resource-actions">
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </button>
                                <button class="resource-action-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="12" cy="5" r="1"></circle>
                                        <circle cx="12" cy="19" r="1"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pagination">
                    <div class="pagination-info">
                        Showing 1 to 6 of 24 resources
                    </div>
                    <div class="pagination-buttons">
                        <button class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <button class="pagination-button active">1</button>
                        <button class="pagination-button">2</button>
                        <button class="pagination-button">3</button>
                        <button class="pagination-button">4</button>
                        <button class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Resource Details Modal -->
    <div id="resource-details-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Resource Details</h3>
                <button class="modal-close" onclick="closeModal('resource-details-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="resource-detail-grid">
                    <div class="resource-preview">
                        <svg xmlns="http://www.w3.org/2000/svg" class="preview-icon" fill="none" viewBox="0 0 24 24" stroke="#f87171">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2v5a2 2 0 002 2h5" />
                        </svg>
                        <div class="preview-actions">
                            <button class="button button-sm">
                                <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Preview
                            </button>
                            <button class="button button-sm">
                                <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Download
                            </button>
                        </div>
                    </div>
                    <div class="resource-info-rows">
                        <div class="resource-info-row">
                            <div class="resource-info-label">Title</div>
                            <div class="resource-info-value" id="modal-resource-title">Algebra Fundamentals: Quadratic Equations and Factoring</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Type</div>
                            <div class="resource-info-value" id="modal-resource-type">Lesson Plan</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Subject</div>
                            <div class="resource-info-value" id="modal-resource-subject">Mathematics</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Grade Level</div>
                            <div class="resource-info-value" id="modal-resource-grade">Class 9-10</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">File Size</div>
                            <div class="resource-info-value" id="modal-resource-size">2.4 MB</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Uploaded By</div>
                            <div class="resource-info-value" id="modal-resource-uploader">John Smith</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Upload Date</div>
                            <div class="resource-info-value" id="modal-resource-date">March 15, 2025</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Downloads</div>
                            <div class="resource-info-value" id="modal-resource-downloads">127</div>
                        </div>
                        <div class="resource-info-row">
                            <div class="resource-info-label">Tags</div>
                            <div class="resource-info-value">
                                <div class="resource-tags" id="modal-resource-tags">
                                    <span class="resource-tag">algebra</span>
                                    <span class="resource-tag">quadratic equations</span>
                                    <span class="resource-tag">factoring</span>
                                    <span class="resource-tag">polynomials</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="resource-description">
                    <h4 class="resource-description-title">Description</h4>
                    <p class="resource-description-text" id="modal-resource-description">
                        This comprehensive resource covers all aspects of quadratic equations and factoring for high school algebra students. It includes detailed explanations, examples, practice problems, and solution keys. Suitable for both classroom instruction and self-study. The resource aligns with standard mathematics curriculum for grades 9-10 and includes interactive elements.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('resource-details-modal')">Close</button>
                <button type="button" class="button">Download Resource</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <div id="delete-confirmation-dialog" class="confirmation-dialog">
        <div class="confirmation-dialog-header">
            <h3 class="confirmation-dialog-title">Delete Resource</h3>
        </div>
        <div class="confirmation-dialog-body">
            <p class="confirmation-dialog-message">Are you sure you want to delete this resource? This action cannot be undone.</p>
        </div>
        <div class="confirmation-dialog-footer">
            <button type="button" class="button button-outline" onclick="closeConfirmationDialog()">Cancel</button>
            <button type="button" class="button" style="background-color: #ef4444;" onclick="deleteResource()">Delete</button>
        </div>
    </div>
    <div id="confirmation-dialog-overlay" class="confirmation-dialog-overlay"></div>

    <script>
        // DOM ready function
        document.addEventListener('DOMContentLoaded', function() {
            initializeTabs();
            initializeFileUpload();
            initializeCategoryPills();
            initializeResourceActions();
        });

        // Toggle sidebar visibility
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            if (sidebar.classList.contains('show')) {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
            } else {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                `;
            }
        }

        // Tabs functionality
        function initializeTabs() {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab contents
                    const tabContents = document.querySelectorAll('.tab-content');
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        }

        // File upload functionality
        function initializeFileUpload() {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('file-upload');
            const browseBtn = document.getElementById('browse-btn');
            const progressContainer = document.getElementById('upload-progress-container');
            
            if (dropzone && fileInput && browseBtn) {
                // Open file dialog when browse button is clicked
                browseBtn.addEventListener('click', function() {
                    fileInput.click();
                });
                
                // Handle file selection
                fileInput.addEventListener('change', function() {
                    handleFiles(this.files);
                });
                
                // Drag and drop functionality
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, function() {
                        dropzone.classList.add('dragover');
                    }, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, function() {
                        dropzone.classList.remove('dragover');
                    }, false);
                });
                
                dropzone.addEventListener('drop', function(e) {
                    const files = e.dataTransfer.files;
                    handleFiles(files);
                }, false);
                
                // Handle files
                function handleFiles(files) {
                    for (let i = 0; i < files.length; i++) {
                        uploadFile(files[i]);
                    }
                }
                
                // Simulate file upload
                function uploadFile(file) {
                    // Create progress item
                    const progressItem = document.createElement('div');
                    progressItem.className = 'progress-item';
                    
                    let fileIcon = '';
                    const fileType = file.type.split('/')[1];
                    
                    // Set appropriate icon based on file type
                    if (file.type.includes('pdf')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#f87171">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>`;
                    } else if (file.type.includes('word') || file.type.includes('document')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#3b82f6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>`;
                    } else if (file.type.includes('presentation')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#f59e0b">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>`;
                    } else if (file.type.includes('image')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#10b981">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>`;
                    } else if (file.type.includes('video')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#8b5cf6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>`;
                    } else if (file.type.includes('audio')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#f97316">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 010-7.072m12.728 2.828a9 9 0 010-12.728" />
                        </svg>`;
                    } else if (file.type.includes('zip') || file.type.includes('compressed')) {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#4338ca">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>`;
                    } else {
                        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" class="progress-icon" fill="none" viewBox="0 0 24 24" stroke="#6b7280">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>`;
                    }
                    
                  
                    
                
                    
                    // Format file size
                    const fileSize = formatFileSize(file.size);
                    
                    progressItem.innerHTML = `
                        ${fileIcon}
                        <div class="progress-info">
                            <div class="progress-name">${file.name}</div>
                            <div class="progress-meta">
                                <span>${fileType.toUpperCase()}</span>
                                <span>${fileSize}</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="progress-actions">
                            <button class="resource-action-btn cancel-upload-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="resource-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    `;
                    
                    progressContainer.appendChild(progressItem);
                    
                    // Simulate upload progress
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 5;
                        const progressBar = progressItem.querySelector('.progress-bar');
                        progressBar.style.width = progress + '%';
                        
                        if (progress >= 100) {
                            clearInterval(interval);
                            
                            // Add success indicator
                            const progressActions = progressItem.querySelector('.progress-actions');
                            progressActions.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            `;
                        }
                    }, 200);
                    
                    // Cancel upload
                    const cancelBtn = progressItem.querySelector('.cancel-upload-btn');
                    cancelBtn.addEventListener('click', function() {
                        clearInterval(interval);
                        progressItem.remove();
                    });
                }
                
                // Format file size
                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        }

        // Category pills functionality
        function initializeCategoryPills() {
            const categoryPills = document.querySelectorAll('.category-pill');
            
            categoryPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    // Remove active class from all pills
                    categoryPills.forEach(p => p.classList.remove('active'));
                    // Add active class to clicked pill
                    this.classList.add('active');
                    
                    // In a real application, you would filter resources based on the selected category
                    // For this demo, we'll just show an alert
                    const category = this.textContent;
                    console.log(`Filtering by category: ${category}`);
                });
            });
        }

        // Resource actions functionality
        function initializeResourceActions() {
            // View resource details
            const viewButtons = document.querySelectorAll('.resource-action-btn:first-child');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Open resource details modal
                    document.getElementById('resource-details-modal').classList.add('show');
                });
            });
            
            // Download resource
            const downloadButtons = document.querySelectorAll('.resource-action-btn:nth-child(2)');
            downloadButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // In a real application, you would trigger a download here
                    // For this demo, we'll just show an alert
                    alert('Downloading resource...');
                });
            });
            
            // More actions menu
            const moreButtons = document.querySelectorAll('.resource-action-btn:nth-child(3)');
            moreButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Create and show dropdown menu
                    const menu = document.createElement('div');
                    menu.className = 'actions-menu show';
                    menu.style.position = 'absolute';
                    menu.style.right = '0';
                    menu.style.top = '100%';
                    menu.style.zIndex = '10';
                    menu.style.minWidth = '150px';
                    
                    menu.innerHTML = `
                        <div class="actions-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                            Edit
                        </div>
                        <div class="actions-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                <polyline points="16 6 12 2 8 6"></polyline>
                                <line x1="12" y1="2" x2="12" y2="15"></line>
                            </svg>
                            Share
                        </div>
                        <div class="actions-item danger" onclick="showDeleteConfirmation()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Delete
                        </div>
                    `;
                    
                    // Remove any existing menus
                    document.querySelectorAll('.actions-menu').forEach(m => m.remove());
                    
                    // Add new menu
                    this.appendChild(menu);
                    
                    // Close menu when clicking outside
                    document.addEventListener('click', function closeMenu(e) {
                        if (!e.target.closest('.actions-menu')) {
                            menu.remove();
                            document.removeEventListener('click', closeMenu);
                        }
                    });
                });
            });
        }

        // Show delete confirmation dialog
        function showDeleteConfirmation() {
            const dialog = document.getElementById('delete-confirmation-dialog');
            const overlay = document.getElementById('confirmation-dialog-overlay');
            
            if (dialog && overlay) {
                dialog.style.display = 'block';
                overlay.style.display = 'block';
            }
        }

        // Close confirmation dialog
        function closeConfirmationDialog() {
            const dialog = document.getElementById('delete-confirmation-dialog');
            const overlay = document.getElementById('confirmation-dialog-overlay');
            
            if (dialog && overlay) {
                dialog.style.display = 'none';
                overlay.style.display = 'none';
            }
        }

        // Delete resource
        function deleteResource() {
            // In a real application, you would send a delete request to the server
            // For this demo, we'll just show an alert
            alert('Resource deleted successfully');
            
            // Close the dialog
            closeConfirmationDialog();
            
            // Remove any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => menu.remove());
        }

        // View resource details
        function viewResourceDetails(resourceId) {
            // In a real application, you would fetch resource details from the server
            // For this demo, we'll just open the modal with static data
            document.getElementById('resource-details-modal').classList.add('show');
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close all modals
        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('show');
            });
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const resourceCards = document.querySelectorAll('.resource-card');
                    
                    resourceCards.forEach(card => {
                        const title = card.querySelector('.resource-title').textContent.toLowerCase();
                        const subject = card.querySelector('.resource-subject').textContent.toLowerCase();
                        
                        if (title.includes(searchTerm) || subject.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelects = document.querySelectorAll('.filter-container .form-select');
            
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // In a real application, you would apply all filters together
                    // For this demo, we'll just log the selected filter
                    console.log(`Filter changed: ${this.value}`);
                });
            });
        });

        // Page transitions
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a:not([href^="#"])');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.getAttribute('href').startsWith('javascript:')) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        
                        setTimeout(() => {
                            window.location.href = this.getAttribute('href');
                        }, 500);
                    }
                });
            });
        });
    </script>
</body>
</html>