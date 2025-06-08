// Homework Management JavaScript

// Global variables
let currentTab = 'all';
let currentFilters = {
    class: '',
    section: '',
    subject: '',
    time: '',
    search: ''
};

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Load stored tab and filters from session storage if available
    loadStoredTabAndFilters();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load filter options
    loadFilterOptions();
    
    // Load homework list after a slight delay to ensure all setup is complete
    setTimeout(loadHomeworkList, 100);
});

// Load stored tab and filters
function loadStoredTabAndFilters() {
    // Load tab
    const storedTab = sessionStorage.getItem('currentHomeworkTab');
    if (storedTab) {
        currentTab = storedTab;
        console.log('Loaded stored tab:', currentTab);
    }
    
    // Load filters
    const storedFilters = sessionStorage.getItem('homeworkFilters');
    if (storedFilters) {
        try {
            const filters = JSON.parse(storedFilters);
            currentFilters = filters;
            console.log('Loaded stored filters:', currentFilters);
        } catch (e) {
            console.error('Error parsing stored filters:', e);
        }
    }
}

// Setup event listeners
function setupEventListeners() {
    // Tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            switchTab(this.dataset.tab);
        });
    });

    // New Assignment button
    const newAssignmentBtn = document.getElementById('newAssignmentBtn');
    if (newAssignmentBtn) {
        newAssignmentBtn.addEventListener('click', function() {
            toggleNewAssignmentForm();
        });
    }

    // Cancel button
    document.getElementById('cancelAssignment')?.addEventListener('click', function() {
        toggleNewAssignmentForm();
    });

    // Assignment form submission
    document.getElementById('assignmentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('homework_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toggleNewAssignmentForm();
                this.reset();
                loadHomeworkList();
                alert('Assignment created successfully!');
            } else {
                alert('Error creating assignment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error creating assignment:', error);
            alert('Error creating assignment. Please try again.');
        });
    });

    // Filter button
    document.getElementById('applyFiltersBtn')?.addEventListener('click', function() {
        applyFilters();
    });

    // Search input
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
    }

    // Class change event for new assignment form
    document.getElementById('assignmentClass')?.addEventListener('change', function() {
        const classId = this.value;
        if (!classId) return;

        fetch(`homework_actions.php?action=get_sections&class_id=${classId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const sectionSelect = document.getElementById('assignmentSection');
                    sectionSelect.innerHTML = '<option value="">Select a section</option>';
                    data.sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        sectionSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading sections:', error));
    });
}

// Load filter options from server
function loadFilterOptions() {
    // Load classes for both filter and new assignment form
    fetch('homework_actions.php?action=get_classes')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const classSelects = ['filterClass', 'assignmentClass'];
                classSelects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    if (!select) return;
                    
                    select.innerHTML = '<option value="">' + (selectId === 'filterClass' ? 'All Classes' : 'Select a class') + '</option>';
                    data.classes.forEach(cls => {
                        const option = document.createElement('option');
                        option.value = cls.id;
                        option.textContent = cls.name;
                        select.appendChild(option);
                    });
                    
                    // Restore selected value if it exists in currentFilters
                    if (selectId === 'filterClass' && currentFilters.class) {
                        select.value = currentFilters.class;
                    }
                });
            }
        })
        .catch(error => console.error('Error loading classes:', error));

    // Load subjects for both filter and new assignment form
    fetch('homework_actions.php?action=get_subjects')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const subjectSelects = ['filterSubject', 'assignmentSubject'];
                subjectSelects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    if (!select) return;
                    
                    select.innerHTML = '<option value="">' + (selectId === 'filterSubject' ? 'All Subjects' : 'Select a subject') + '</option>';
                    data.subjects.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.textContent = subject.name;
                        select.appendChild(option);
                    });
                    
                    // Restore selected value if it exists in currentFilters
                    if (selectId === 'filterSubject' && currentFilters.subject) {
                        select.value = currentFilters.subject;
                    }
                });
            }
        })
        .catch(error => console.error('Error loading subjects:', error));
    
    // Restore section and time values from stored filters
    if (currentFilters.section) {
        const sectionSelect = document.getElementById('filterSection');
        if (sectionSelect) sectionSelect.value = currentFilters.section;
    }
    
    if (currentFilters.time) {
        const timeSelect = document.getElementById('filterTime');
        if (timeSelect) timeSelect.value = currentFilters.time;
    }
    
    if (currentFilters.search) {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) searchInput.value = currentFilters.search;
    }
}

// Switch between tabs
function switchTab(tabName) {
    currentTab = tabName;
    
    // Store the current tab in session storage to persist between page reloads
    sessionStorage.setItem('currentHomeworkTab', currentTab);
    
    // Update active tab
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.tab === tabName) {
            tab.classList.add('active');
        }
    });

    // Update tab content visibility
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    const tabContent = document.getElementById(`${tabName}-content`);
    if (tabContent) {
        tabContent.classList.add('active');
    }

    // Reload homework list with current tab
    loadHomeworkList();
}

// Load homework list from server
function loadHomeworkList() {
    const searchInput = document.querySelector('.search-input');
    const searchQuery = searchInput ? searchInput.value : '';
    
    // Update current filters
    currentFilters = {
        action: 'list_homework',
        class: document.getElementById('filterClass')?.value || '',
        section: document.getElementById('filterSection')?.value || '',
        subject: document.getElementById('filterSubject')?.value || '',
        time: document.getElementById('filterTime')?.value || '',
        search: searchQuery,
        tab: currentTab
    };
    
    // Store filters in sessionStorage to persist between page reloads
    sessionStorage.setItem('homeworkFilters', JSON.stringify(currentFilters));

    // Show loading state for the current tab
    const currentGrid = document.querySelector(`#${currentTab}-content .card-grid`);
    if (currentGrid) {
        currentGrid.innerHTML = '<div class="loading">Loading assignments...</div>';
    }

    console.log('Fetching homework with filters:', currentFilters);

    // Fetch homework data
    fetch('homework_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(currentFilters)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Homework data received:', data);
        
        if (data.status === 'success') {
            // Filter and render homework for the current tab
            renderTabContent(currentTab, data.homework);
            // Update quick stats
            updateStats(data.homework);
        } else {
            console.error('Error from server:', data.message);
            if (currentGrid) {
                currentGrid.innerHTML = '<div class="error">Error loading assignments: ' + (data.message || 'Unknown error') + '</div>';
            }
        }
    })
    .catch(error => {
        console.error('Error fetching homework:', error);
        if (currentGrid) {
            currentGrid.innerHTML = '<div class="error">Error loading assignments. Please try again.</div>';
        }
    });
}

// Render homework data for the specified tab
function renderTabContent(tabName, homeworkList) {
    if (!homeworkList || !Array.isArray(homeworkList)) {
        console.error('Invalid homework data:', homeworkList);
        return;
    }
    
    const cardGrid = document.querySelector(`#${tabName}-content .card-grid`);
    if (!cardGrid) {
        console.error('Could not find grid for tab:', tabName);
        return;
    }
    
    // Filter homework based on the tab
    const now = new Date();
    const filteredHomework = homeworkList.filter(homework => {
        try {
            const dueDate = new Date(homework.due_date);
            switch (tabName) {
                case 'pending':
                    // A homework is pending if its due date is in the future AND it has no submission yet
                    return dueDate >= now && (!homework.submission || homework.submission.status === 'not_submitted');
                case 'grading':
                    // A homework needs grading if it has submissions and the submission status is not 'graded'
                    return homework.has_submissions === 1 && homework.submission && homework.submission.status !== 'graded';
                case 'completed':
                    // A homework is completed if it has been graded OR its due date is in the past (implies pending/grading stages are over)
                    return homework.submission?.status === 'graded' || dueDate < now;
                default: // 'all'
                    return true;
            }
        } catch (e) {
            console.error('Error filtering homework:', e, homework);
            return false;
        }
    });
    
    // Render the filtered homework
    if (filteredHomework.length === 0) {
        cardGrid.innerHTML = '<div class="no-data">No assignments found.</div>';
        return;
    }
    
    cardGrid.innerHTML = filteredHomework.map(homework => `
        <div class="homework-card" data-id="${homework.id}">
            <div class="card-header">
                <h3 class="card-title">${escapeHtml(homework.title)}</h3>
                <span class="due-date ${isOverdue(homework.due_date) ? 'overdue' : ''}">${formatDate(homework.due_date)}</span>
            </div>
            <div class="card-body">
                <div class="class-info">
                    <span class="class">${escapeHtml(homework.class_name)}</span>
                    <span class="section">${escapeHtml(homework.section_name)}</span>
                    <span class="subject">${escapeHtml(homework.subject_name)}</span>
                </div>
                <p class="description">${escapeHtml(truncateText(homework.description, 150))}</p>
                ${homework.attachment ? `<div class="attachment">
                    <a href="${homework.attachment}" target="_blank">View Attachment</a>
                </div>` : ''}
            </div>
            <div class="card-footer">
                <button class="btn btn-secondary view-submissions" onclick="viewSubmissions(${homework.id})">
                    View Submissions
                </button>
                <button class="btn btn-danger delete-homework" onclick="deleteHomework(${homework.id})">
                    Delete
                </button>
            </div>
        </div>
    `).join('');
}

// Apply filters
function applyFilters() {
    loadHomeworkList();
}

// Toggle new assignment form
function toggleNewAssignmentForm() {
    console.log('toggleNewAssignmentForm function called.');
    const form = document.getElementById('newAssignmentForm');
    console.log('New Assignment form element found:', form);
    if (!form) return;
    
    // Toggle the 'hidden-form' class
    form.classList.toggle('hidden-form');
    
    // Optional: Scroll to form if showing
    if (!form.classList.contains('hidden-form')) {
        form.scrollIntoView({ behavior: 'smooth' });
        console.log('New Assignment form displayed.');
    } else {
        const assignmentForm = document.getElementById('assignmentForm');
        if (assignmentForm) assignmentForm.reset();
        console.log('New Assignment form hidden.');
    }
}

// Helper functions
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;')
             .replace(/</g, '&lt;')
             .replace(/>/g, '&gt;')
             .replace(/"/g, '&quot;')
             .replace(/'/g, '&#039;');
}

function truncateText(text, maxLength) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function isOverdue(dateStr) {
    const dueDate = new Date(dateStr);
    return dueDate < new Date();
}

function isSameDay(date1, date2) {
    return date1.getFullYear() === date2.getFullYear() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getDate() === date2.getDate();
}

function isThisWeek(date) {
    const now = new Date();
    const firstDay = new Date(now.setDate(now.getDate() - now.getDay()));
    const lastDay = new Date(firstDay);
    lastDay.setDate(lastDay.getDate() + 6);
    return date >= firstDay && date <= lastDay;
}

function isThisMonth(date) {
    const now = new Date();
    return date.getMonth() === now.getMonth() &&
           date.getFullYear() === now.getFullYear();
}

// View submissions for a homework
function viewSubmissions(homeworkId) {
    window.location.href = `homework_submissions.php?id=${homeworkId}`;
}

// Delete a homework
function deleteHomework(homeworkId) {
    if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
        fetch('homework_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_homework',
                homework_id: homeworkId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadHomeworkList();
                alert('Assignment deleted successfully!');
            } else {
                alert('Error deleting assignment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting assignment. Please try again.');
        });
    }
}

// --- Dynamic Statistics ---
function updateStats(homeworkList) {
    console.log('Updating stats with homework list:', homeworkList);
    const now = new Date();
    // Reset all counts
    let pending = 0;
    let dueThisWeek = 0;
    let submissionsToGrade = 0;
    let gradedThisMonth = 0;

    homeworkList.forEach(hw => {
        const dueDate = new Date(hw.due_date);

        // Pending: due in future AND no submission or submission status is not 'submitted'
        const hasSubmission = hw.submission && hw.submission.status !== 'not_submitted';
        if (dueDate >= now && !hasSubmission) {
             pending++;
        }

        // Due This Week: due date is within the current week
        if (isThisWeek(dueDate)) {
            dueThisWeek++;
        }

        // Submissions to Grade: assignment has submissions and submission status is not 'graded'
        if (hw.has_submissions === 1 && hw.submission && hw.submission.status !== 'graded') {
            submissionsToGrade++;
        }

        // Graded This Month: assignment has a graded submission and it was graded this month
        if (hw.submission?.status === 'graded' && hw.submission.graded_at) {
            const gradedDate = new Date(hw.submission.graded_at);
            if (!isNaN(gradedDate) && isThisMonth(gradedDate)) {
                 gradedThisMonth++;
            }
        }

    });

    // Update the displayed values
    document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = pending;
    document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = dueThisWeek;
    document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = submissionsToGrade;
    document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = gradedThisMonth;
}