<?php include 'sidebar.php'; ?>
<?php include 'con.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
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
    <!-- Add Summernote CSS for rich text editing -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Add DatePicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Additional styles for the create announcement form */
        .action-button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .create-announcement-btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .create-announcement-btn:hover {
            background-color: #4338ca;
        }
        
        .filters-container {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background-color: white;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .close-modal {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .cancel-btn {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .submit-btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .note {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        .badge-all {
            background-color: #6366f1;
        }
        
        .badge-teachers {
            background-color: #2563eb;
        }
        
        .badge-students {
            background-color: #7c3aed;
        }
        
        .announcement-target {
            display: inline-block;
            margin-left: 1rem;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            color: white;
        }
        
        .no-announcements {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .summernote-container .note-editor {
            border-radius: 6px;
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
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Action Button and Filters -->
        <div class="action-button-container">
            <button class="create-announcement-btn" id="createAnnouncementBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Announcement
            </button>
            
            <div class="filters-container">
                <select id="audienceFilter" class="filter-select">
                    <option value="">All Audiences</option>
                    <option value="all">Everyone</option>
                    <option value="teachers">Teachers Only</option>
                    <option value="students">Students Only</option>
                </select>
                
                <select id="priorityFilter" class="filter-select">
                    <option value="">All Priorities</option>
                    <option value="normal">Normal</option>
                    <option value="important">Important</option>
                    <option value="urgent">Urgent</option>
                </select>
                
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">All Announcements</h2>
            
            <div class="announcement-list" id="announcementList">
                <!-- Announcements will be loaded dynamically here -->
                <div class="no-announcements">
                    <p>Loading announcements...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Create/Edit Announcement Modal -->
<div class="modal-overlay" id="announcementModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Create New Announcement</h3>
            <button class="close-modal" id="closeModal">&times;</button>
        </div>
        
        <form id="announcementForm" action="announcement_actions.php" method="POST">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="announcementId" value="">
            
            <div class="form-group">
                <label for="title" class="form-label">Title</label>
                <input type="text" id="title" name="title" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="content" class="form-label">Content</label>
                <div class="summernote-container">
                    <textarea id="content" name="content" required></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label for="target_audience" class="form-label">Target Audience</label>
                <select id="target_audience" name="target_audience" class="form-select" required>
                    <option value="all">All Users</option>
                    <option value="teachers">Teachers Only</option>
                    <option value="students">Students Only</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="priority" class="form-label">Priority</label>
                <select id="priority" name="priority" class="form-select" required>
                    <option value="normal">Normal</option>
                    <option value="important">Important</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="expiry_date" class="form-label">Expiry Date (Optional)</label>
                <input type="text" id="expiry_date" name="expiry_date" class="form-input" placeholder="Select date">
                <p class="note">Leave blank for no expiration</p>
            </div>
            
            <div class="form-group" id="statusGroup" style="display: none;">
                <label for="is_active" class="form-label">Status</label>
                <div>
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                    <label for="is_active">Active</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
                <button type="submit" class="submit-btn" id="submitBtn">Create Announcement</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Delete</h3>
            <button class="close-modal" id="closeDeleteModal">&times;</button>
        </div>
        
        <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
        
        <form id="deleteForm" action="announcement_actions.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteAnnouncementId" value="">
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" id="cancelDeleteBtn">Cancel</button>
                <button type="submit" class="submit-btn" style="background-color: #ef4444;">Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery and Summernote JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- DatePicker JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize Summernote rich text editor
    $(document).ready(function() {
        $('#content').summernote({
            placeholder: 'Write your announcement content here...',
            tabsize: 2,
            height: 250,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
        
        // Initialize Flatpickr date picker
        flatpickr("#expiry_date", {
            dateFormat: "Y-m-d",
            minDate: "today"
        });
        
        // Load announcements on page load
        loadAnnouncements();
        
        // Set up filter change events
        $('#audienceFilter, #priorityFilter, #statusFilter').change(function() {
            loadAnnouncements();
        });
    });
    
    // Function to load announcements via AJAX
    function loadAnnouncements() {
        const audienceFilter = $('#audienceFilter').val();
        const priorityFilter = $('#priorityFilter').val();
        const statusFilter = $('#statusFilter').val();
        
        $.ajax({
            url: 'announcement_actions.php',
            type: 'GET',
            data: {
                action: 'list',
                target_audience: audienceFilter,
                priority: priorityFilter,
                status: statusFilter
            },
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    console.error('Error:', data.error);
                    $('#announcementList').html('<div class="no-announcements">Error: ' + data.error + '</div>');
                    return;
                }
                renderAnnouncements(data.announcements);
            },
            error: function(xhr, status, error) {
                console.error('Error loading announcements:', error);
                $('#announcementList').html('<div class="no-announcements">Error loading announcements. Please try again.</div>');
            }
        });
    }
    
    // Function to render announcements in the list
    function renderAnnouncements(announcements) {
        const listContainer = $('#announcementList');
        
        if (!announcements || announcements.length === 0) {
            listContainer.html('<div class="no-announcements">No announcements found.</div>');
            return;
        }
        
        let html = '';
        
        announcements.forEach(announcement => {
            // Format date
            const createdDate = new Date(announcement.created_at);
            const formattedDate = createdDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            // Priority badge class
            let priorityClass = 'badge-general';
            if (announcement.priority === 'important') {
                priorityClass = 'badge-important';
            } else if (announcement.priority === 'urgent') {
                priorityClass = 'badge-important';
            }
            
            // Target audience badge
            let targetAudienceText = '';
            let targetAudienceClass = '';
            if (announcement.target_audience === 'all') {
                targetAudienceText = 'Everyone';
                targetAudienceClass = 'badge-all';
            } else if (announcement.target_audience === 'teachers') {
                targetAudienceText = 'Teachers Only';
                targetAudienceClass = 'badge-teachers';
            } else if (announcement.target_audience === 'students') {
                targetAudienceText = 'Students Only';
                targetAudienceClass = 'badge-students';
            }
            
            // Status indicator for inactive announcements
            const statusIndicator = announcement.is_active == 1 ? '' : ' (Inactive)';
            
            html += `
                <div class="announcement-card" data-id="${announcement.id}">
                    <div class="announcement-header">
                        <div class="announcement-title-area">
                            <div class="announcement-title">${announcement.title}${statusIndicator}</div>
                            <div class="announcement-meta">
                                <div class="announcement-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    ${formattedDate}
                                </div>
                                <div class="announcement-author">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    ${announcement.created_by_name}
                                </div>
                            </div>
                        </div>
                        <span class="announcement-badge ${priorityClass}">
                            ${announcement.priority.charAt(0).toUpperCase() + announcement.priority.slice(1)}
                            <span class="announcement-target ${targetAudienceClass}">${targetAudienceText}</span>
                        </span>
                    </div>
                    
                    <div class="announcement-content">
                        <div class="content-preview">
                            ${announcement.content}
                        </div>
                    </div>
                    
                    <div class="announcement-actions">
                        <div class="action-buttons">
                            <button class="action-button edit-btn" data-id="${announcement.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="action-button delete-btn" data-id="${announcement.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            <button class="action-button toggle-status-btn" data-id="${announcement.id}" data-status="${announcement.is_active}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4 4m4-4l-4-4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        listContainer.html(html);
        
        // Set up edit button click events
        $('.edit-btn').click(function() {
            const announcementId = $(this).data('id');
            editAnnouncement(announcementId);
        });
        
        // Set up delete button click events
        $('.delete-btn').click(function() {
            const announcementId = $(this).data('id');
            confirmDelete(announcementId);
        });
        
        // Set up toggle status button click events
        $('.toggle-status-btn').click(function() {
            const announcementId = $(this).data('id');
            const currentStatus = $(this).data('status');
            toggleAnnouncementStatus(announcementId, currentStatus);
        });
    }
    
    // Function to open the modal for creating a new announcement
    $('#createAnnouncementBtn').click(function() {
        // Reset the form
        $('#announcementForm')[0].reset();
        $('#content').summernote('code', '');
        
        // Set form action to create
        $('#formAction').val('create');
        $('#announcementId').val('');
        
        // Hide status field (it's only for editing)
        $('#statusGroup').hide();
        
        // Update modal title and button text
        $('#modalTitle').text('Create New Announcement');
        $('#submitBtn').text('Create Announcement');
        
        // Show the modal
        $('#announcementModal').fadeIn(300);
    });
    
    // Function to edit an existing announcement
    function editAnnouncement(id) {
        $.ajax({
            url: 'announcement_actions.php',
            type: 'GET',
            data: {
                action: 'get_announcement',
                id: id
            },
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                // Fill the form with announcement data
                $('#title').val(data.title);
                $('#content').summernote('code', data.content);
                $('#target_audience').val(data.target_audience);
                $('#priority').val(data.priority);
                $('#expiry_date').val(data.expiry_date);
                $('#is_active').prop('checked', data.is_active == 1);
                
                // Set form action to update
                $('#formAction').val('update');
                $('#announcementId').val(data.id);
                
                // Show status field for editing
                $('#statusGroup').show();
                
                // Update modal title and button text
                $('#modalTitle').text('Edit Announcement');
                $('#submitBtn').text('Update Announcement');
                
                // Show the modal
                $('#announcementModal').fadeIn(300);
            },
            error: function(xhr, status, error) {
                alert('Error loading announcement data. Please try again.');
            }
        });
    }
    
    // Function to confirm deletion of an announcement
    function confirmDelete(id) {
        $('#deleteAnnouncementId').val(id);
        $('#deleteModal').fadeIn(300);
    }
    
    // Function to toggle announcement status
    function toggleAnnouncementStatus(id, currentStatus) {
        // Create form data
        const formData = new FormData();
        formData.append('action', 'toggle_status');
        formData.append('id', id);
        formData.append('status', currentStatus);
        
        // Submit form data via fetch API
        fetch('announcement_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            window.location.reload();
        })
        .catch(error => {
            alert('Error toggling announcement status. Please try again.');
        });
    }
    
    // Close modal when clicking outside or on the close button
    $('#closeModal, #cancelBtn').click(function() {
        $('#announcementModal').fadeOut(300);
    });
    
    $('#closeDeleteModal, #cancelDeleteBtn').click(function() {
        $('#deleteModal').fadeOut(300);
    });
    
    // Close modal when clicking outside the modal content
    $('.modal-overlay').click(function(e) {
        if (e.target === this) {
            $(this).fadeOut(300);
        }
    });
</script>
</body>
</html>