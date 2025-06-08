<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class Sections</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/sections.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Manage Class Sections</h1>
            <span class="header-path">Dashboard > Classes > Sections</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="sectionSearch" class="search-input" placeholder="Search by section name, grade, or teacher...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" id="newSectionBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Section
                    </button>
                </div>
            </div>

            <!-- Section Form -->
            <div class="section-form-container" id="sectionForm">
                <h2 class="form-title">Create New Class Section</h2>
                <form>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="sectionName">Section Name/ID</label>
                            <input type="text" class="form-input" id="sectionName" name="sectionName" placeholder="e.g. 9A, 10B, etc.">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="grade">Grade/Class</label>
                            <select class="form-select" id="grade" name="grade">
                                <option value="">Select Grade</option>
                                <option value="7">Grade 7</option>
                                <option value="8">Grade 8</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="classTeacher">Class Teacher</label>
                            <select class="form-select" id="classTeacher" name="classTeacher">
                                <option value="">Select Class Teacher</option>
                                <option value="1">John Davis (Mathematics)</option>
                                <option value="2">Sarah Parker (Science)</option>
                                <option value="3">Robert Johnson (English)</option>
                                <option value="4">Emily Smith (Computer Science)</option>
                                <option value="5">David Wilson (Physical Education)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="roomNumber">Room Number</label>
                            <select class="form-select" id="roomNumber" name="roomNumber">
                                <option value="">Select Room</option>
                                <option value="101">Room 101</option>
                                <option value="102">Room 102</option>
                                <option value="103">Room 103</option>
                                <option value="104">Room 104</option>
                                <option value="105">Room 105</option>
                                <option value="106">Room 106</option>
                                <option value="107">Room 107</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="capacity">Maximum Capacity</label>
                            <input type="number" class="form-input" id="capacity" name="capacity" min="1" max="50" placeholder="e.g. 30">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Section</button>
                    </div>
                </form>
            </div>

            <h2 class="section-title">Class Sections Overview</h2>
            <div class="section-cards">
                <!-- Card 1 -->
                <div class="section-card">
                    <div class="section-card-header">
                        <h3 class="section-title">Grade 9A</h3>
                        <span class="section-badge section-badge-active">Active</span>
                    </div>
                    <div class="section-info">
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Room 101</span>
                        </div>
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="student-count">32 Students</span>
                        </div>
                    </div>
                    <div class="section-footer">
                        <div class="class-teacher">
                            <div class="teacher-avatar">JD</div>
                            <span class="teacher-name">John Davis</span>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn" title="View Students">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Edit">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Delete">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="section-card">
                    <div class="section-card-header">
                        <h3 class="section-title">Grade 10B</h3>
                        <span class="section-badge section-badge-active">Active</span>
                    </div>
                    <div class="section-info">
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Room 102</span>
                        </div>
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="student-count">28 Students</span>
                        </div>
                    </div>
                    <div class="section-footer">
                        <div class="class-teacher">
                            <div class="teacher-avatar">SP</div>
                            <span class="teacher-name">Sarah Parker</span>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn" title="View Students">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Edit">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Delete">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="section-card">
                    <div class="section-card-header">
                        <h3 class="section-title">Grade 8C</h3>
                        <span class="section-badge section-badge-active">Active</span>
                    </div>
                    <div class="section-info">
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Room 103</span>
                        </div>
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="student-count">30 Students</span>
                        </div>
                    </div>
                    <div class="section-footer">
                        <div class="class-teacher">
                            <div class="teacher-avatar">RJ</div>
                            <span class="teacher-name">Robert Johnson</span>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn" title="View Students">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Edit">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Delete">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="section-card">
                    <div class="section-card-header">
                        <h3 class="section-title">Grade 11A</h3>
                        <span class="section-badge section-badge-inactive">Inactive</span>
                    </div>
                    <div class="section-info">
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Room 104</span>
                        </div>
                        <div class="info-item">
                            <svg class="info-item-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="student-count">0 Students</span>
                        </div>
                    </div>
                    <div class="section-footer">
                        <div class="class-teacher">
                            <div class="teacher-avatar">ES</div>
                            <span class="teacher-name">Emily Smith</span>
                        </div>
                        <div class="card-actions">
                            <button class="card-btn" title="View Students">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Edit">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="card-btn" title="Delete">
                                <svg class="card-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="section-title">All Class Sections</h2>
            <div class="sections-table-container">
                <table class="sections-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Room</th>
                            <th>Class Teacher</th>
                            <th>Students</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-section-id="<?php echo $section['id']; ?>">
                            <td>
                                <div class="section-info-cell">
                                    <span class="section-name">Grade <?php echo htmlspecialchars($section['name']); ?></span>
                                    <span class="section-details">Academic Year 2024-25</span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($section['classroom']); ?></td>
                            <td><?php echo htmlspecialchars($section['teacher']); ?></td>
                            <td><?php echo $section['student_count']; ?></td>
                            <td><?php echo $section['capacity']; ?></td>
                            <td><span class="status-badge status-<?php echo $section['status'] === 'active' ? 'active' : 'inactive'; ?>"><?php echo ucfirst($section['status']); ?></span></td>
                            <td class="table-actions">
                                <button class="action-btn" title="View Students" data-section-id="<?php echo $section['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </button>
                                <button class="action-btn" title="Edit" data-section-id="<?php echo $section['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button class="action-btn" title="Delete" data-section-id="<?php echo $section['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    Showing 1-5 of 10 sections
                </div>
                <div class="pagination-buttons">
                    <button class="page-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Form toggle
            const newSectionBtn = document.getElementById('newSectionBtn');
            const sectionForm = document.getElementById('sectionForm');
            const cancelBtn = document.getElementById('cancelBtn');
            
            // Initially hide the form
            sectionForm.style.display = 'none';
            
            newSectionBtn.addEventListener('click', () => {
                sectionForm.style.display = 'block';
                // Scroll to the form
                sectionForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            cancelBtn.addEventListener('click', () => {
                sectionForm.style.display = 'none';
            });
            
            // Filter functionality
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            filterToggleBtn.addEventListener('click', () => {
                alert('Filter functionality will be implemented soon!');
            });
            
            // Search functionality
            const searchInput = document.getElementById('sectionSearch');
            const tableRows = document.querySelectorAll('.sections-table tbody tr');
            const cards = document.querySelectorAll('.section-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                // Search in table
                tableRows.forEach(row => {
                    const sectionName = row.querySelector('.section-name').textContent.toLowerCase();
                    const teacherName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const roomNumber = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    
                    const matchFound = 
                        sectionName.includes(searchTerm) || 
                        teacherName.includes(searchTerm) || 
                        roomNumber.includes(searchTerm);
                    
                    row.style.display = matchFound ? '' : 'none';
                });
                
                // Search in cards
                cards.forEach(card => {
                    const title = card.querySelector('.section-title').textContent.toLowerCase();
                    const teacher = card.querySelector('.teacher-name').textContent.toLowerCase();
                    const info = card.querySelector('.section-info').textContent.toLowerCase();
                    
                    const matchFound = 
                        title.includes(searchTerm) || 
                        teacher.includes(searchTerm) || 
                        info.includes(searchTerm);
                    
                    card.style.display = matchFound ? '' : 'none';
                });
            });
            
            // Form validation
            const sectionFormElement = document.querySelector('.section-form-container form');
            
            sectionFormElement.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const sectionName = document.getElementById('sectionName').value;
                const grade = document.getElementById('grade').value;
                const classTeacher = document.getElementById('classTeacher').value;
                const roomNumber = document.getElementById('roomNumber').value;
                const capacity = document.getElementById('capacity').value;
                
                if (!sectionName || !grade || !classTeacher || !roomNumber || !capacity) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Here you would typically submit the form via AJAX or redirect
                alert('Section created successfully!');
                
                // Reset form and hide it
                sectionFormElement.reset();
                sectionForm.style.display = 'none';
            });
            
            // Action buttons functionality
            const viewButtons = document.querySelectorAll('.card-btn[title="View Students"], .action-btn[title="View Students"]');
            const editButtons = document.querySelectorAll('.card-btn[title="Edit"], .action-btn[title="Edit"]');
            const deleteButtons = document.querySelectorAll('.card-btn[title="Delete"], .action-btn[title="Delete"]');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    alert('Redirecting to students list for this section...');
                });
            });
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section-id');
                    // Find the row for this section
                    const row = document.querySelector('tr[data-section-id="' + sectionId + '"]');
                    if (!row) return;
                    // Populate form fields from row
                    document.getElementById('sectionName').value = row.querySelector('.section-name').textContent.replace('Grade ', '');
                    document.getElementById('classTeacher').value = row.children[2].textContent.trim();
                    document.getElementById('roomNumber').value = row.children[1].textContent.trim();
                    document.getElementById('capacity').value = row.children[4].textContent.trim();
                    document.getElementById('status').value = row.querySelector('.status-badge').textContent.trim().toLowerCase();
                    // Store sectionId in form for update
                    sectionForm.setAttribute('data-edit-id', sectionId);
                    // Show modal
                    sectionForm.style.display = 'block';
                    sectionForm.scrollIntoView({ behavior: 'smooth' });
                    document.querySelector('.form-title').textContent = 'Edit Class Section';
                    document.querySelector('.form-actions .btn-primary').textContent = 'Update Section';
                });
            });
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section-id');
                    if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
                        fetch('section_actions.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'action=delete&id=' + encodeURIComponent(sectionId)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Remove row from table
                                const row = document.querySelector('tr[data-section-id="' + sectionId + '"]');
                                if (row) row.remove();
                        alert('Section deleted successfully!');
                            } else {
                                alert('Delete failed: ' + data.message);
                            }
                        })
                        .catch(() => alert('Delete failed: network or server error.'));
                    }
                });
            });
            
            // Form validation and AJAX submit for update
            sectionFormElement.addEventListener('submit', function(e) {
                e.preventDefault();
                const sectionName = document.getElementById('sectionName').value;
                const grade = document.getElementById('grade').value;
                const classTeacher = document.getElementById('classTeacher').value;
                const roomNumber = document.getElementById('roomNumber').value;
                const capacity = document.getElementById('capacity').value;
                const sectionId = sectionForm.getAttribute('data-edit-id');
                if (!sectionName || !grade || !classTeacher || !roomNumber || !capacity) {
                    alert('Please fill in all required fields');
                    return;
                }
                if (!sectionId) {
                    alert('Section ID missing for update.');
                    return;
                }
                // AJAX update
                const params = new URLSearchParams();
                params.append('action', 'update');
                params.append('id', sectionId);
                params.append('class_id', grade);
                params.append('name', sectionName);
                params.append('capacity', capacity);
                params.append('teacher', classTeacher);
                params.append('classroom', roomNumber);
                params.append('description', ''); // Add description if needed
                params.append('status', document.getElementById('status').value);
                fetch('section_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Section updated successfully!');
                        // Optionally update the row in the table or reload
                        location.reload();
                    } else {
                        alert('Update failed: ' + data.message);
                    }
                })
                .catch(() => alert('Update failed: network or server error.'));
                // Reset form and hide it
                sectionFormElement.reset();
                sectionForm.style.display = 'none';
                sectionForm.removeAttribute('data-edit-id');
            });
            
            // Pagination functionality
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert('In a real application, this would load page ' + this.textContent);
                    }
                });
            });
        });
    </script>
</body>
</html>