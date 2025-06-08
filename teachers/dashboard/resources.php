<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Resource Library</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/resources.css">
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
        <span class="header-subtitle">Upload and share teaching materials</span>
    </header>

    <main class="dashboard-content">
        <!-- Upload Section -->
        <section class="upload-section" onclick="showUploadModal()">
            <svg class="upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <h2 class="upload-title">Upload Teaching Resources</h2>
            <p class="upload-description">Drag and drop files here, or click to browse your computer for documents, presentations, videos, and other teaching materials.</p>
            <button class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Upload Files
            </button>
            <p class="upload-formats">Supported formats: PDF, DOCX, PPT, PPTX, MP4, MP3, JPG, PNG, etc.</p>
        </section>

        <!-- Filter and Search -->
        <section class="filter-bar">
            <div class="filter-group">
                <div class="search-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" placeholder="Search resources..." class="search-input" id="resourceSearch">
                </div>
                <div class="select-container">
                    <select class="filter-select" id="resourceTypeFilter">
                        <option value="all">All Types</option>
                        <option value="pdf">PDF</option>
                        <option value="doc">Word</option>
                        <option value="ppt">PowerPoint</option>
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                        <option value="image">Image</option>
                        <option value="other">Other</option>
                    </select>
                    <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="select-container">
                    <select class="filter-select" id="subjectFilter">
                        <option value="all">All Subjects</option>
                        <option value="math">Mathematics</option>
                        <option value="science">Science</option>
                        <option value="english">English</option>
                        <option value="history">History</option>
                        <option value="geography">Geography</option>
                        <option value="art">Art</option>
                    </select>
                    <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            <div class="select-container">
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                    <option value="size-asc">Size (Smallest)</option>
                    <option value="size-desc">Size (Largest)</option>
                </select>
                <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </section>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tab active" onclick="changeTab(this, 'all-resources')">All Resources</div>
            <div class="tab" onclick="changeTab(this, 'my-uploads')">My Uploads</div>
            <div class="tab" onclick="changeTab(this, 'shared-with-me')">Shared with Me</div>
            <div class="tab" onclick="changeTab(this, 'favorites')">Favorites</div>
            <div class="tab" onclick="changeTab(this, 'recent')">Recent</div>
        </div>

        <!-- Resources Grid -->
        <section class="resources-grid" id="resources-container">
            <!-- PDF Resource Card Example -->
            <div class="resource-card" data-type="pdf" data-subject="math">
                <div class="resource-thumbnail">
                    <span class="resource-type-badge type-pdf">PDF</span>
                    <svg class="resource-type-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="resource-content">
                    <h3 class="resource-title">Quadratic Equations Practice Worksheet</h3>
                    <div class="tags-container">
                        <span class="tag subject-math">Mathematics</span>
                        <span class="tag">Class 9</span>
                        <span class="tag">Worksheet</span>
                    </div>
                    <p class="resource-description">A comprehensive worksheet with 20 practice problems on solving quadratic equations using various methods.</p>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Mar 10, 2025
                        </span>
                        <span class="resource-size">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            425 KB
                        </span>
                    </div>
                </div>
                <div class="resource-actions">
                    <div class="action-buttons">
                        <button class="action-btn" title="Download" onclick="downloadResource(1)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Share" onclick="showShareModal(1)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Add to Favorites" onclick="toggleFavorite(1)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="resource-shared">
                        <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shared with 5 users
                    </div>
                </div>
            </div>

            <!-- PowerPoint Resource Card Example -->
            <div class="resource-card" data-type="ppt" data-subject="science">
                <div class="resource-thumbnail">
                    <span class="resource-type-badge type-ppt">PPT</span>
                    <svg class="resource-type-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="resource-content">
                    <h3 class="resource-title">Cell Structure and Functions</h3>
                    <div class="tags-container">
                        <span class="tag subject-science">Science</span>
                        <span class="tag">Class 10</span>
                        <span class="tag">Presentation</span>
                    </div>
                    <p class="resource-description">A detailed presentation on cell structure, organelles, and their functions with visual aids and animations.</p>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Mar 8, 2025
                        </span>
                        <span class="resource-size">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            2.8 MB
                        </span>
                    </div>
                </div>
                <div class="resource-actions">
                    <div class="action-buttons">
                        <button class="action-btn" title="Download" onclick="downloadResource(2)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Share" onclick="showShareModal(2)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Add to Favorites" onclick="toggleFavorite(2)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="resource-shared">
                        <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shared with 12 users
                    </div>
                </div>
            </div>

            <!-- Video Resource Card Example -->
            <div class="resource-card" data-type="video" data-subject="science">
                <div class="resource-thumbnail">
                    <span class="resource-type-badge type-video">VIDEO</span>
                    <img src="https://img.youtube.com/vi/AQGKLA7AfXs/mqdefault.jpg" alt="Video Thumbnail">
                </div>
                <div class="resource-content">
                    <h3 class="resource-title">Understanding Photosynthesis Process</h3>
                    <div class="tags-container">
                        <span class="tag subject-science">Science</span>
                        <span class="tag">Class 10</span>
                        <span class="tag">Video</span>
                    </div>
                    <p class="resource-description">A detailed video explanation of the photosynthesis process with animations and real-world examples.</p>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Mar 5, 2025
                        </span>
                        <span class="resource-size">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            8:45
                        </span>
                    </div>
                </div>
                <div class="resource-actions">
                    <div class="action-buttons">
                        <button class="action-btn" title="Play" onclick="playResource(3)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Share" onclick="showShareModal(3)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Add to Favorites" onclick="toggleFavorite(3)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="resource-shared">
                        <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shared with 8 users
                    </div>
                </div>
            </div>

            <!-- Word Document Resource Card Example -->
            <div class="resource-card" data-type="doc" data-subject="english">
                <div class="resource-thumbnail">
                    <span class="resource-type-badge type-doc">DOC</span>
                    <svg class="resource-type-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="resource-content">
                    <h3 class="resource-title">Essay Writing Guide: Structure and Examples</h3>
                    <div class="tags-container">
                        <span class="tag subject-english">English</span>
                        <span class="tag">Class 9</span>
                        <span class="tag">Guide</span>
                    </div>
                    <p class="resource-description">A comprehensive guide to essay writing with proper structure, formatting tips, and example essays for students.</p>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Mar 2, 2025
                        </span>
                        <span class="resource-size">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            675 KB
                        </span>
                    </div>
                </div>
                <div class="resource-actions">
                    <div class="action-buttons">
                        <button class="action-btn" title="Download" onclick="downloadResource(4)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Share" onclick="showShareModal(4)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Add to Favorites" onclick="toggleFavorite(4)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="resource-shared">
                        <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shared with 3 users
                    </div>
                </div>
            </div>

            <!-- Image Resource Card Example -->
            <div class="resource-card" data-type="image" data-subject="geography">
                <div class="resource-thumbnail">
                    <span class="resource-type-badge type-image">IMAGE</span>
                    <img src="https://images.unsplash.com/photo-1589519160732-57fc498494f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Earth Layers Diagram">
                </div>
                <div class="resource-content">
                    <h3 class="resource-title">Earth's Internal Structure Diagram</h3>
                    <div class="tags-container">
                        <span class="tag subject-geography">Geography</span>
                        <span class="tag">Class 10</span>
                        <span class="tag">Diagram</span>
                    </div>
                    <p class="resource-description">High-resolution diagram showing Earth's internal layers including the inner core, outer core, mantle, and crust.</p>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Feb 28, 2025
                        </span>
                        <span class="resource-size">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                            1.2 MB
                        </span>
                    </div>
                </div>
                <div class="resource-actions">
                    <div class="action-buttons">
                        <button class="action-btn" title="Preview" onclick="previewResource(5)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Share" onclick="showShareModal(5)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Add to Favorites" onclick="toggleFavorite(5)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="resource-shared">
                        <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shared with 15 users
                    </div>
                </div>
            </div>

            <!-- Audio Resource Card Example -->
            <div class="resource-card" data-type="audio" data-subject="english">
                <div class="resource-thumbnail">
                    <span class="resource-type-badge type-audio">AUDIO</span>
                    <svg class="resource-type-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <div class="resource-content">
                    <h3 class="resource-title">Shakespeare's Hamlet - Act 1 Audio Reading</h3>
                    <div class="tags-container">
                        <span class="tag subject-english">English</span>
                        <span class="tag">Class 10</span>
                        <span class="tag">Literature</span>
                    </div>
                    <p class="resource-description">Professional audio recording of Hamlet's Act 1 with proper intonation and character voices to help students understand the play.</p>
                    <div class="resource-meta">
                        <span class="resource-date">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Feb 24, 2025
                        </span>
                        <span class="resource-size">
                            <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            32:15
                        </span>
                    </div>
                </div>
                <div class="resource-actions">
                    <div class="action-buttons">
                        <button class="action-btn" title="Play" onclick="playResource(6)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Share" onclick="showShareModal(6)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </button>
                        <button class="action-btn" title="Add to Favorites" onclick="toggleFavorite(6)">
                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="resource-shared">
                        <svg class="meta-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Shared with 7 users
                    </div>
                </div>
            </div>
        </section>

        <!-- Empty State (Hidden by default) -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="empty-title">No Resources Found</h3>
            <p class="empty-description">There are no resources matching your current filters or search criteria.</p>
            <button class="btn btn-primary" onclick="showUploadModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Upload New Resource
            </button>
        </div>
    </main>
</div>

<!-- Upload Resource Modal -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Upload New Resource</h3>
            <button class="modal-close" onclick="hideUploadModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="uploadForm">
                <div class="form-group">
                    <label class="form-label">File <span class="form-required">*</span></label>
                    <div class="file-upload" id="fileUploadContainer">
                        <input type="file" id="fileUpload" class="file-input">
                        <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <div class="file-upload-text">Drag and drop file here, or click to browse</div>
                        <div class="file-upload-hint">Supported formats: PDF, DOCX, PPT, PPTX, MP4, MP3, JPG, PNG, etc. (Max size: 50MB)</div>
                    </div>
                </div>
                
                <div class="resource-preview" id="filePreview" style="display: none;">
                    <div class="preview-header">
                        <svg class="preview-icon" id="previewIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="preview-info">
                            <div class="preview-title" id="previewFileName">Document.pdf</div>
                            <div class="preview-details" id="previewFileDetails">PDF • 2.4 MB</div>
                        </div>
                    </div>
                    <div class="preview-thumbnail" id="previewThumbnail">
                        <!-- Thumbnail will be inserted here for images/videos -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Title <span class="form-required">*</span></label>
                    <input type="text" class="form-input" id="resourceTitle" placeholder="Enter a descriptive title for the resource" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" id="resourceDescription" placeholder="Add a description to help others understand what this resource contains"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subject <span class="form-required">*</span></label>
                    <select class="form-select" id="resourceSubject" required>
                        <option value="">Select Subject</option>
                        <option value="math">Mathematics</option>
                        <option value="science">Science</option>
                        <option value="english">English</option>
                        <option value="history">History</option>
                        <option value="geography">Geography</option>
                        <option value="art">Art</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Class Level <span class="form-required">*</span></label>
                    <select class="form-select" id="resourceClass" required>
                        <option value="">Select Class</option>
                        <option value="class-9">Class 9</option>
                        <option value="class-10">Class 10</option>
                        <option value="all">All Classes</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tags-input-container">
                        <div class="tag-item">
                            Worksheet
                            <button type="button" onclick="removeTag(this)">×</button>
                        </div>
                        <input type="text" class="tags-input" placeholder="Type and press Enter to add tags" onkeydown="addTag(event)">
                    </div>
                    <span class="form-hint">Add tags to make your resource easier to find</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Share with</label>
                    <div class="shared-with-section">
                        <div class="shared-with-list" id="sharedWithList">
                            <span class="shared-item">
                                <span class="shared-item-avatar">
                                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Jane Smith">
                                </span>
                                Jane Smith
                                <button type="button" class="shared-item-remove" onclick="removeRecipient(this)">×</button>
                            </span>
                            <span class="shared-item">
                                <span class="shared-item-avatar">
                                    <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="Robert Brown">
                                </span>
                                Robert Brown
                                <button type="button" class="shared-item-remove" onclick="removeRecipient(this)">×</button>
                            </span>
                            <button type="button" class="add-recipient-btn" onclick="showAddRecipientModal()">
                                <svg class="add-recipient-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="resourcePublic" class="form-checkbox">
                        <label for="resourcePublic" class="form-check-label">Make public to all teachers</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="resourceAllowDownload" class="form-checkbox" checked>
                        <label for="resourceAllowDownload" class="form-check-label">Allow downloading</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="resourceNotifyOnDownload" class="form-checkbox">
                        <label for="resourceNotifyOnDownload" class="form-check-label">Notify me when someone downloads</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideUploadModal()">Cancel</button>
            <button class="btn btn-primary" onclick="uploadResource()">Upload Resource</button>
        </div>
    </div>
</div>

<!-- Share Resource Modal -->
<div class="modal-overlay" id="shareModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Share Resource</h3>
            <button class="modal-close" onclick="hideShareModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Resource</label>
                <div class="resource-preview">
                    <div class="preview-header">
                        <svg class="preview-icon" id="sharePreviewIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="preview-info">
                            <div class="preview-title" id="shareResourceTitle">Quadratic Equations Practice Worksheet</div>
                            <div class="preview-details" id="shareResourceDetails">PDF • 425 KB • Uploaded on Mar 10, 2025</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Share with</label>
                <div class="shared-with-section">
                    <div class="shared-with-list" id="shareWithList">
                        <button type="button" class="add-recipient-btn" onclick="showAddRecipientModal()">
                            <svg class="add-recipient-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Recipients
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Message (Optional)</label>
                <textarea class="form-textarea" id="shareMessage" placeholder="Add a message to recipients"></textarea>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="shareAllowDownload" class="form-checkbox" checked>
                    <label for="shareAllowDownload" class="form-check-label">Allow downloading</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="shareAllowEdit" class="form-checkbox">
                    <label for="shareAllowEdit" class="form-check-label">Allow editing</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="shareNotifyOnDownload" class="form-checkbox">
                    <label for="shareNotifyOnDownload" class="form-check-label">Notify me when someone downloads</label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Share Link</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" class="form-input" id="shareLink" value="https://school.edu/resources/share/ad7f9e23" readonly>
                    <button class="btn btn-secondary btn-sm" onclick="copyShareLink()">Copy</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideShareModal()">Cancel</button>
            <button class="btn btn-primary" onclick="shareResource()">Share</button>
        </div>
    </div>
</div>

<script>
    // Function to toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');
        const body = document.querySelector('body');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        dashboardContainer.classList.toggle('sidebar-open');
    }
    
    // Function to handle tab changes
    function changeTab(tab, tabId) {
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to selected tab
        tab.classList.add('active');
        
        // Filter resources based on selected tab
        if (tabId === 'all-resources') {
            filterResources('all');
        } else if (tabId === 'my-uploads') {
            filterResources('uploads');
        } else if (tabId === 'shared-with-me') {
            filterResources('shared');
        } else if (tabId === 'favorites') {
            filterResources('favorites');
        } else if (tabId === 'recent') {
            filterResources('recent');
        }
    }
    
    // Function to filter resources
    function filterResources(filterType = 'all') {
        const resourceTypeFilter = document.getElementById('resourceTypeFilter').value;
        const subjectFilter = document.getElementById('subjectFilter').value;
        const searchText = document.getElementById('resourceSearch').value.toLowerCase();
        
        const resourceCards = document.querySelectorAll('.resource-card');
        let matchFound = false;
        
        resourceCards.forEach(card => {
            const cardType = card.getAttribute('data-type');
            const cardSubject = card.getAttribute('data-subject');
            const cardTitle = card.querySelector('.resource-title').textContent.toLowerCase();
            const cardDescription = card.querySelector('.resource-description').textContent.toLowerCase();
            const cardTags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());
            
            // Filter based on resource type
            const typeMatch = resourceTypeFilter === 'all' || cardType === resourceTypeFilter;
            
            // Filter based on subject
            const subjectMatch = subjectFilter === 'all' || cardSubject === subjectFilter;
            
            // Filter based on search text
            const searchMatch = searchText === '' || 
                cardTitle.includes(searchText) || 
                cardDescription.includes(searchText) || 
                cardTags.some(tag => tag.includes(searchText));
            
            // Filter based on tab selection (for demo, using simple logic)
            let tabMatch = true;
            if (filterType === 'uploads') {
                // In a real app, would check if current user is the uploader
                tabMatch = cardTitle.includes('Equation') || cardTitle.includes('Essay');
            } else if (filterType === 'shared') {
                // In a real app, would check if shared with current user
                tabMatch = cardTitle.includes('Cell') || cardTitle.includes('Photosynthesis');
            } else if (filterType === 'favorites') {
                // In a real app, would check if user has favorited this
                tabMatch = cardTitle.includes('Hamlet') || cardTitle.includes('Earth');
            } else if (filterType === 'recent') {
                // In a real app, would check if recently accessed
                tabMatch = true; // Showing all for demo purposes
            }
            
            if (typeMatch && subjectMatch && searchMatch && tabMatch) {
                card.style.display = '';
                matchFound = true;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show empty state if no matches found
        const emptyState = document.getElementById('emptyState');
        emptyState.style.display = matchFound ? 'none' : 'flex';
    }
    
    // Upload Modal Functions
    function showUploadModal() {
        const modal = document.getElementById('uploadModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Reset form
        document.getElementById('uploadForm').reset();
        document.getElementById('filePreview').style.display = 'none';
    }
    
    function hideUploadModal() {
        const modal = document.getElementById('uploadModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Share Modal Functions
    function showShareModal(resourceId) {
        const modal = document.getElementById('shareModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Set resource details based on the resource ID
        if (resourceId === 1) {
            document.getElementById('shareResourceTitle').textContent = 'Quadratic Equations Practice Worksheet';
            document.getElementById('shareResourceDetails').textContent = 'PDF • 425 KB • Uploaded on Mar 10, 2025';
            document.getElementById('sharePreviewIcon').innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            `;
        } else if (resourceId === 2) {
            document.getElementById('shareResourceTitle').textContent = 'Cell Structure and Functions';
            document.getElementById('shareResourceDetails').textContent = 'PPT • 2.8 MB • Uploaded on Mar 8, 2025';
            document.getElementById('sharePreviewIcon').innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            `;
        }
        
        // Generate unique share link
        document.getElementById('shareLink').value = `https://school.edu/resources/share/${Math.random().toString(36).substring(2, 10)}`;
    }
    
    function hideShareModal() {
        const modal = document.getElementById('shareModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Add Tags to Resource
    function addTag(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            
            const tagsInput = event.target;
            const tagValue = tagsInput.value.trim();
            
            if (tagValue) {
                const tagsContainer = tagsInput.closest('.tags-input-container');
                const tagItem = document.createElement('div');
                tagItem.className = 'tag-item';
                tagItem.innerHTML = `${tagValue}<button type="button" onclick="removeTag(this)">×</button>`;
                
                tagsContainer.insertBefore(tagItem, tagsInput);
                tagsInput.value = '';
            }
        }
    }
    
    function removeTag(button) {
        const tagItem = button.parentNode;
        tagItem.parentNode.removeChild(tagItem);
    }
    
    // Remove Recipient
    function removeRecipient(button) {
        const sharedItem = button.parentNode;
        sharedItem.parentNode.removeChild(sharedItem);
    }
    
    // Show Add Recipient Modal (simplified in this example)
    function showAddRecipientModal() {
        // In a real app, this would open a modal to select recipients
        alert('In a real application, this would open a modal to select recipients from a list of teachers or students.');
        
        // For demo purposes, add a sample recipient
        const sharedList = document.getElementById('sharedWithList');
        const addButton = sharedList.querySelector('.add-recipient-btn');
        
        const newRecipient = document.createElement('span');
        newRecipient.className = 'shared-item';
        newRecipient.innerHTML = `
            <span class="shared-item-avatar">
                <img src="https://randomuser.me/api/portraits/men/22.jpg" alt="Michael Chen">
            </span>
            Michael Chen
            <button type="button" class="shared-item-remove" onclick="removeRecipient(this)">×</button>
        `;
        
        sharedList.insertBefore(newRecipient, addButton);
    }
    
    // Handle File Upload
    document.addEventListener('DOMContentLoaded', function() {
        const fileUpload = document.getElementById('fileUpload');
        const fileUploadContainer = document.getElementById('fileUploadContainer');
        const filePreview = document.getElementById('filePreview');
        const previewFileName = document.getElementById('previewFileName');
        const previewFileDetails = document.getElementById('previewFileDetails');
        const previewIcon = document.getElementById('previewIcon');
        const previewThumbnail = document.getElementById('previewThumbnail');
        
        // File drag and drop events
        fileUploadContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileUploadContainer.style.borderColor = '#667eea';
            fileUploadContainer.style.backgroundColor = '#f9fafb';
        });
        
        fileUploadContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileUploadContainer.style.borderColor = '#e5e7eb';
            fileUploadContainer.style.backgroundColor = '';
        });
        
        fileUploadContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            fileUploadContainer.style.borderColor = '#e5e7eb';
            fileUploadContainer.style.backgroundColor = '';
            
            if (e.dataTransfer.files.length) {
                fileUpload.files = e.dataTransfer.files;
                handleFileSelection(e.dataTransfer.files[0]);
            }
        });
        
        fileUploadContainer.addEventListener('click', function() {
            fileUpload.click();
        });
        
        fileUpload.addEventListener('change', function() {
            if (fileUpload.files.length) {
                handleFileSelection(fileUpload.files[0]);
            }
        });
        
        function handleFileSelection(file) {
            // Update file preview
            previewFileName.textContent = file.name;
            
            // Format file size
            let fileSize = file.size;
            let sizeText = '';
            
            if (fileSize < 1024) {
                sizeText = `${fileSize} bytes`;
            } else if (fileSize < 1024 * 1024) {
                sizeText = `${(fileSize / 1024).toFixed(1)} KB`;
            } else {
                sizeText = `${(fileSize / (1024 * 1024)).toFixed(1)} MB`;
            }
            
            // Determine file type and set appropriate icon
            let fileType = '';
            if (file.name.endsWith('.pdf')) {
                fileType = 'PDF';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                `;
                previewThumbnail.innerHTML = '';
            } else if (file.name.match(/\.(docx?|rtf)$/i)) {
                fileType = 'DOC';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                `;
                previewThumbnail.innerHTML = '';
            } else if (file.name.match(/\.(pptx?|pps|potx?)$/i)) {
                fileType = 'PPT';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                `;
                previewThumbnail.innerHTML = '';
            } else if (file.name.match(/\.(jpe?g|png|gif|bmp|webp)$/i)) {
                fileType = 'Image';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                `;
                
                // For images, create a thumbnail preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewThumbnail.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            } else if (file.name.match(/\.(mp4|mov|avi|wmv|flv|webm)$/i)) {
                fileType = 'Video';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                `;
                previewThumbnail.innerHTML = '';
            } else if (file.name.match(/\.(mp3|wav|ogg|aac|flac)$/i)) {
                fileType = 'Audio';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                `;
                previewThumbnail.innerHTML = '';
            } else {
                fileType = 'File';
                previewIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                `;
                previewThumbnail.innerHTML = '';
            }
            
            previewFileDetails.textContent = `${fileType} • ${sizeText}`;
            filePreview.style.display = 'block';
            
            // Set resource title based on filename (removing extension)
            const fileName = file.name.replace(/\.[^/.]+$/, "");
            document.getElementById('resourceTitle').value = fileName;
        }
    });
    
    // Function to upload resource
    function uploadResource() {
        // Get form values
        const resourceTitle = document.getElementById('resourceTitle').value;
        const resourceSubject = document.getElementById('resourceSubject').value;
        
        // Validate form
        if (!resourceTitle || !resourceSubject) {
            alert('Please fill all required fields.');
            return;
        }
        
        // In a real application, this would submit the form data via AJAX
        alert('Resource uploaded successfully!');
        hideUploadModal();
        
        // Optionally refresh the resources list
        // location.reload();
    }
    
    // Function to share resource
    function shareResource() {
        // In a real application, this would share the resource with selected users
        alert('Resource shared successfully!');
        hideShareModal();
    }
    
    // Function to play audio/video resource
    function playResource(resourceId) {
        // In a real application, this would open a player or redirect to the resource
        alert('Playing resource ID: ' + resourceId);
    }
    
    // Function to preview resource
    function previewResource(resourceId) {
        // In a real application, this would open a preview window
        alert('Previewing resource ID: ' + resourceId);
    }
    
    // Function to download resource
    function downloadResource(resourceId) {
        // In a real application, this would download the file
        alert('Downloading resource ID: ' + resourceId);
    }
    
    // Function to toggle favorite status
    function toggleFavorite(resourceId) {
        // In a real application, this would toggle favorite status in database
        alert('Resource ID ' + resourceId + ' added to favorites!');
    }
    
    // Function to copy share link
    function copyShareLink() {
        const shareLink = document.getElementById('shareLink');
        shareLink.select();
        document.execCommand('copy');
        
        // Show feedback
        alert('Link copied to clipboard!');
    }
    
    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event listeners to all navigation links
        const navLinks = document.querySelectorAll('a[href]:not([href^="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href.includes(window.location.hostname)) {
                    e.preventDefault();
                    
                    // Add exit animation class
                    document.body.classList.add('fade-out');
                    
                    // Navigate to new page after animation completes
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 500); // Match animation duration
                }
            });
        });
        
        // Initialize search functionality
        const searchInput = document.getElementById('resourceSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterResources();
            });
        }
        
        // Initialize resource type filter
        const resourceTypeFilter = document.getElementById('resourceTypeFilter');
        if (resourceTypeFilter) {
            resourceTypeFilter.addEventListener('change', function() {
                filterResources();
            });
        }
        
        // Initialize subject filter
        const subjectFilter = document.getElementById('subjectFilter');
        if (subjectFilter) {
            subjectFilter.addEventListener('change', function() {
                filterResources();
            });
        }
        
        // Apply animation delay to resource cards
        const cards = document.querySelectorAll('.resource-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${0.1 * index}s`;
        });
    });
</script>
</body>
</html>