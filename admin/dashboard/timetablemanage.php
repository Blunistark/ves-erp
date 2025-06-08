<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Timetables</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/timetablemanage.css">
    
    <!-- Add axios for making API requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
            <h1 class="header-title">Manage Timetables</h1>
            <span class="header-path">Dashboard > Timetable > Manage</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="timetableSearch" class="search-input" placeholder="Search by class, section or academic year...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <a href="createtimetable.php" class="btn btn-primary">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create New Timetable
                    </a>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel">
                <h3 class="filter-title">Filter Timetables</h3>
                <form class="filter-form" id="filterForm">
                    <div class="filter-group">
                        <label class="filter-label">Academic Year</label>
                        <select class="filter-select" id="academicYearFilter" name="academic_year">
                            <option value="">All Academic Years</option>
                            <!-- Academic years will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Class</label>
                        <select class="filter-select" id="classFilter" name="class_id">
                            <option value="">All Classes</option>
                            <!-- Classes will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Section</label>
                        <select class="filter-select" id="sectionFilter" name="section_id">
                            <option value="">All Sections</option>
                            <!-- Sections will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="statusFilter" name="status">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset" id="resetFiltersBtn">Reset</button>
                    <button class="filter-btn filter-btn-apply" id="applyFiltersBtn">Apply Filters</button>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="loading-container" style="display: none;">
                <div class="loading-spinner"></div>
                <p>Loading timetables...</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="error-message" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="error-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p id="errorText">An error occurred while loading timetables. Please try again.</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3>No Timetables Found</h3>
                <p>There are no timetables matching your search criteria.</p>
                <a href="createtimetable.php" class="btn btn-primary">Create New Timetable</a>
            </div>

            <!-- Timetable Cards Grid -->
            <div class="timetable-grid" id="timetableGrid">
                <!-- Timetable cards will be loaded dynamically here -->
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination" style="display: none;">
                <button class="pagination-btn" id="prevPage" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" class="pagination-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>
                <div class="pagination-info">
                    Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                        </div>
                <button class="pagination-btn" id="nextPage">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" class="pagination-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                    </div>
        </main>
                            </div>

    <!-- Timetable Preview Modal -->
    <!--
    <div id="timetablePreviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Timetable Preview</h2>
            <div id="timetablePreviewContent">
                <!-- Timetable preview will be loaded here -->
                Loading...
                            </div>
                        </div>
                    </div>
    -->

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <h2 id="confirmationTitle">Confirm Action</h2>
                <button class="modal-close" onclick="closeConfirmationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">Are you sure you want to perform this action?</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-outline" onclick="closeConfirmationModal()">Cancel</button>
                    <button class="btn btn-danger" id="confirmActionBtn">Confirm</button>
                        </div>
                        </div>
                    </div>
                </div>

    <script>
        // Global variables
        let currentPage = 1;
        let totalPages = 1;
        let currentFilters = {};
        
        // DOM elements
        const timetableGrid = document.getElementById('timetableGrid');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const errorMessage = document.getElementById('errorMessage');
        const emptyState = document.getElementById('emptyState');
        const pagination = document.getElementById('pagination');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const currentPageSpan = document.getElementById('currentPage');
        const totalPagesSpan = document.getElementById('totalPages');
        const filterPanel = document.getElementById('filterPanel');
        const filterToggleBtn = document.getElementById('filterToggleBtn');
        const filterForm = document.getElementById('filterForm');
        const resetFiltersBtn = document.getElementById('resetFiltersBtn');
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        const searchInput = document.getElementById('timetableSearch');
        const previewModal = document.getElementById('previewModal');
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial data
            loadTimetables();
            
            // Load filter options
            loadFilterOptions();
            
            // Event listeners
            filterToggleBtn.addEventListener('click', toggleFilterPanel);
            resetFiltersBtn.addEventListener('click', resetFilters);
            applyFiltersBtn.addEventListener('click', applyFilters);
            prevPageBtn.addEventListener('click', goToPrevPage);
            nextPageBtn.addEventListener('click', goToNextPage);
            searchInput.addEventListener('input', debounce(handleSearch, 500));
        });
        
        // Load timetables from API
        function loadTimetables() {
            showLoading();
            
            // Build query parameters
            const params = new URLSearchParams();
            params.append('page', currentPage);
            params.append('limit', 10);
            
            // Add filters if any
            for (const [key, value] of Object.entries(currentFilters)) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Make API request
            axios.get(`../../backend/api/timetables?${params.toString()}`)
                .then(response => {
                    const { data, pagination } = response.data;
                    
                    // Update pagination
                    if (pagination) {
                        currentPage = pagination.page;
                        totalPages = pagination.pages;
                        updatePaginationUI();
                    }
                    
                    // Render timetables
                    renderTimetables(data);
                })
                .catch(error => {
                    console.error('Error loading timetables:', error);
                    showError('Failed to load timetables. Please try again.');
                });
        }
        
        // Render timetables in grid
        function renderTimetables(timetables) {
            hideLoading();
            
            if (!timetables || timetables.length === 0) {
                showEmptyState();
                return;
            }
            
            // Clear grid and show it
            timetableGrid.innerHTML = '';
            timetableGrid.style.display = 'grid';
            emptyState.style.display = 'none';
            errorMessage.style.display = 'none';
            
            // Create timetable cards
            timetables.forEach(timetable => {
                const card = createTimetableCard(timetable);
                timetableGrid.appendChild(card);
            });
            
            // Show pagination if needed
            pagination.style.display = totalPages > 1 ? 'flex' : 'none';
        }
        
        // Create timetable card element
        function createTimetableCard(timetable) {
            const card = document.createElement('div');
            card.className = 'timetable-card';
            
            // Format dates for display
            const createdDate = new Date(timetable.created_at);
            const updatedDate = timetable.updated_at ? new Date(timetable.updated_at) : null;
            const effectiveDate = new Date(timetable.effective_date);
            
            // Get status class
            const statusClass = getStatusClass(timetable.status);
            
            // Create card HTML
            const cardHtml = `
                    <div class="timetable-header">
                        <div>
                        <h3 class="timetable-title">Class ${timetable.class_name}-${timetable.section_name} Timetable</h3>
                        <div class="timetable-subtitle">Academic Year ${timetable.academic_year_name}</div>
                        </div>
                    <span class="timetable-status ${statusClass}">${capitalizeFirstLetter(timetable.status)}</span>
                    </div>
                    <div class="timetable-body">
                        <div class="timetable-info">
                            <div class="info-item">
                                <span class="info-label">Class</span>
                            <span class="info-value">${timetable.class_name}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Section</span>
                            <span class="info-value">${timetable.section_name}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Effective From</span>
                            <span class="info-value">${formatDate(effectiveDate)}</span>
                            </div>
                        ${timetable.description ? `
                        <div class="info-item">
                            <span class="info-label">Description</span>
                            <span class="info-value">${timetable.description}</span>
                        </div>
                        ` : ''}
                        </div>
                    </div>
                    <div class="timetable-footer">
                        <div class="timetable-meta">
                        ${updatedDate ? `Last updated: ${formatDate(updatedDate)}` : `Created: ${formatDate(createdDate)}`}
                        </div>
                        <div class="timetable-actions">
                            <a href="edittimetable.php?id=${timetable.id}" class="btn btn-primary btn-sm edit-timetable-btn">Edit</a>
                             <button class="btn btn-sm btn-outline download-timetable-btn" data-id="${timetable.id}">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                 Download
                            </button>
                            <button class="btn btn-danger btn-sm delete-timetable-btn" data-id="${timetable.id}">Delete</button>
                       </div>
                   </div>
            `;
            
            card.innerHTML = cardHtml;

            // Add event listeners to buttons on this specific card
            card.querySelector('.delete-timetable-btn').addEventListener('click', function() {
                const timetableId = this.dataset.id;
                showConfirmationModal(
                    'Confirm Deletion',
                    'Are you sure you want to delete this timetable? This action cannot be undone.',
                    () => {
                        deleteTimetable(timetableId);
                    }
                );
            });

            // Add download button event listener
            card.querySelector('.download-timetable-btn').addEventListener('click', function() {
                 const timetableId = this.dataset.id;
                 downloadTimetable(timetableId);
            });

            return card;
        }
        
        // Load filter options from API
        function loadFilterOptions() {
            // Load academic years
            axios.get('../../backend/api/academic-years')
                .then(response => {
                    const academicYears = response.data.data;
                    const academicYearFilter = document.getElementById('academicYearFilter');
                    
                    academicYears.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year.id;
                        option.textContent = year.name;
                        academicYearFilter.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading academic years:', error);
                });
            
            // Load classes
            axios.get('../../backend/api/classes')
                .then(response => {
                    const classes = response.data.data;
                    const classFilter = document.getElementById('classFilter');
                    
                    classes.forEach(cls => {
                        const option = document.createElement('option');
                        option.value = cls.id;
                        option.textContent = cls.name;
                        classFilter.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                });
            
            // Load sections
            axios.get('../../backend/api/sections')
                .then(response => {
                    const sections = response.data.data;
                    const sectionFilter = document.getElementById('sectionFilter');
                    
                    sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        sectionFilter.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading sections:', error);
                });
        }
        
        // Reset filters
        function resetFilters() {
            filterForm.reset();
            currentFilters = {};
            currentPage = 1;
            loadTimetables();
        }
        
        // Apply filters
        function applyFilters() {
            const formData = new FormData(filterForm);
            currentFilters = {};
            
            for (const [key, value] of formData.entries()) {
                if (value) {
                    currentFilters[key] = value;
                }
            }
            
            currentPage = 1;
            loadTimetables();
            toggleFilterPanel();
        }
        
        // Handle search
        function handleSearch() {
            const searchValue = searchInput.value.trim();
            currentFilters.search = searchValue;
            currentPage = 1;
            loadTimetables();
        }
        
        // Preview timetable
        function previewTimetable(timetableId) {
            const previewTitle = document.getElementById('previewTitle');
            const previewContent = document.getElementById('previewContent');
            
            // Set loading state
            previewTitle.textContent = 'Loading Timetable...';
            previewContent.innerHTML = '<div class="loading-spinner"></div>';
            previewModal.style.display = 'block';
            
            // Fetch timetable details
            axios.get(`../../backend/api/timetables/${timetableId}`)
                .then(response => {
                    // Check if data and timetable data exist in the response
                    if (response.data && response.data.data) {
                        const timetable = response.data.data;
                        previewTitle.textContent = `${timetable.class_name}-${timetable.section_name} Timetable`;
                        
                        // Render timetable preview
                        previewContent.innerHTML = renderTimetablePreview(timetable);
                    } else {
                        // Handle cases where data is not in the expected format
                        console.error('Unexpected response format:', response);
                        previewContent.innerHTML = '<div class="error-message">Failed to load timetable preview: Unexpected data format.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading timetable preview:', error);
                    previewContent.innerHTML = '<div class="error-message">Failed to load timetable preview.</div>';
                });
        }
        
        // Render timetable preview HTML
        function renderTimetablePreview(timetable) {
            // Group periods by day of week
            const periodsByDay = {};
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            days.forEach((day, index) => {
                periodsByDay[index + 1] = [];
            });
            
            timetable.periods.forEach(period => {
                if (period.day_of_week >= 1 && period.day_of_week <= 6) {
                    periodsByDay[period.day_of_week].push(period);
                }
            });
            
            // Sort periods by start time
            for (const day in periodsByDay) {
                periodsByDay[day].sort((a, b) => {
                    return a.start_time.localeCompare(b.start_time);
                });
            }
            
            // Create HTML
            let html = `
                <div class="timetable-info-preview">
                    <p><strong>Class:</strong> ${timetable.class_name}</p>
                    <p><strong>Section:</strong> ${timetable.section_name}</p>
                    <p><strong>Academic Year:</strong> ${timetable.academic_year_name}</p>
                    <p><strong>Effective From:</strong> ${formatDate(new Date(timetable.effective_date))}</p>
                    ${timetable.description ? `<p><strong>Description:</strong> ${timetable.description}</p>` : ''}
           </div>
                <div class="timetable-preview-table-container">
                    <table class="timetable-preview-table">
                       <thead>
                           <tr>
                                <th>Day</th>
                                <th>Period</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                           </tr>
                       </thead>
                       <tbody>
            `;
            
            days.forEach((day, index) => {
                const dayPeriods = periodsByDay[index + 1];
                
                if (dayPeriods.length === 0) {
                    html += `
                        <tr>
                            <td>${day}</td>
                            <td colspan="4" class="no-periods">No periods scheduled</td>
                           </tr>
                    `;
                } else {
                    dayPeriods.forEach((period, i) => {
                        html += `
                            <tr>
                                ${i === 0 ? `<td rowspan="${dayPeriods.length}">${day}</td>` : ''}
                                <td>${period.period_label || (i + 1)}</td>
                                <td>${formatTime(period.start_time)} - ${formatTime(period.end_time)}</td>
                                <td>${period.subject_name}</td>
                                <td>${period.teacher_name}</td>
                           </tr>
                        `;
                    });
                }
            });
            
            html += `
                       </tbody>
                   </table>
               </div>
            `;
            
            return html;
        }
        
        // Change timetable status
        function changeTimetableStatus(timetableId, newStatus) {
            const actionMap = {
                'published': 'publish',
                'archived': 'archive',
                'draft': 'save as draft'
            };
            
            const action = actionMap[newStatus] || 'update status of';
            
            showConfirmationModal(
                `Confirm Status Change`,
                `Are you sure you want to ${action} this timetable?`,
                () => {
                    axios.post(`../../backend/api/timetables/${timetableId}/status`, { status: newStatus })
                        .then(response => {
                            loadTimetables();
                        })
                        .catch(error => {
                            console.error('Error changing timetable status:', error);
                            showError('Failed to change timetable status. Please try again.');
                        });
                }
            );
        }
        
        // Delete timetable
        function deleteTimetable(timetableId) {
            showConfirmationModal(
                'Confirm Deletion',
                'Are you sure you want to delete this timetable? This action cannot be undone.',
                () => {
                    axios.delete(`../../backend/api/timetables/${timetableId}`)
                        .then(response => {
                            loadTimetables();
                        })
                        .catch(error => {
                            console.error('Error deleting timetable:', error);
                            showError('Failed to delete timetable. Please try again.');
           });
                }
            );
       }

        // Download timetable
        function downloadTimetable(timetableId) {
            window.open(`../../backend/api/timetables/${timetableId}/download`, '_blank');
        }
        
        // Toggle filter panel
        function toggleFilterPanel() {
               filterPanel.classList.toggle('show');
        }
        
        // Update pagination UI
        function updatePaginationUI() {
            currentPageSpan.textContent = currentPage;
            totalPagesSpan.textContent = totalPages;
            
            prevPageBtn.disabled = currentPage <= 1;
            nextPageBtn.disabled = currentPage >= totalPages;
            
            pagination.style.display = totalPages > 1 ? 'flex' : 'none';
        }
        
        // Go to previous page
        function goToPrevPage() {
            if (currentPage > 1) {
                currentPage--;
                loadTimetables();
            }
        }
        
        // Go to next page
        function goToNextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                loadTimetables();
            }
        }
        
        // Show loading indicator
        function showLoading() {
            loadingIndicator.style.display = 'flex';
            timetableGrid.style.display = 'none';
            emptyState.style.display = 'none';
            errorMessage.style.display = 'none';
        }
        
        // Hide loading indicator
        function hideLoading() {
            loadingIndicator.style.display = 'none';
               }
        
        // Show error message
        function showError(message) {
            errorMessage.style.display = 'flex';
            document.getElementById('errorText').textContent = message;
            loadingIndicator.style.display = 'none';
            timetableGrid.style.display = 'none';
            emptyState.style.display = 'none';
        }
        
        // Show empty state
        function showEmptyState() {
            emptyState.style.display = 'flex';
            timetableGrid.style.display = 'none';
            errorMessage.style.display = 'none';
            pagination.style.display = 'none';
        }
        
        // Show confirmation modal
        function showConfirmationModal(title, message, onConfirm) {
            document.getElementById('confirmationTitle').textContent = title;
            document.getElementById('confirmationMessage').textContent = message;
            
            // Remove old event listener and add new one
            const confirmBtn = document.getElementById('confirmActionBtn');
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.addEventListener('click', function() {
                closeConfirmationModal();
                onConfirm();
            });
            
            confirmationModal.style.display = 'block';
        }
        
        // Close preview modal
        function closePreviewModal() {
            previewModal.style.display = 'none';
        }
        
        // Close confirmation modal
        function closeConfirmationModal() {
            confirmationModal.style.display = 'none';
        }
        
        // Helper function: Format date
        function formatDate(date) {
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        // Helper function: Format time
        function formatTime(timeString) {
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours);
            const period = hour >= 12 ? 'PM' : 'AM';
            const formattedHour = hour % 12 || 12;
            
            return `${formattedHour}:${minutes} ${period}`;
        }
        
        // Helper function: Get status class
        function getStatusClass(status) {
            switch (status) {
                case 'published':
                    return 'status-active';
                case 'draft':
                    return 'status-draft';
                case 'archived':
                    return 'status-archived';
                default:
                    return '';
            }
        }
        
        // Helper function: Capitalize first letter
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
        
        // Helper function: Debounce
        function debounce(func, delay) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target === previewModal) {
                closePreviewModal();
            }
            if (event.target === confirmationModal) {
                closeConfirmationModal();
            }
        };
   </script>
</body>
</html>