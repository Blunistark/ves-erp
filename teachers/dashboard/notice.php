<?php 
include 'sidebar.php';

include 'con.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'headmaster'])) {
    header('Location: ../index.php');
    exit();
}

// Get current date for default value
$today = date('Y-m-d');

// Get teacher ID
$teacher_id = $_SESSION['user_id'];

// Get teacher's assigned classes
$query = "SELECT DISTINCT c.id, c.name as class_name, s.id as section_id, s.name as section_name 
        FROM classes c 
        JOIN sections s ON c.id = s.class_id 
        LEFT JOIN teacher_assignments ta ON ta.class_id = c.id AND ta.section_id = s.id
        WHERE (s.class_teacher_user_id = ? OR ta.teacher_user_id = ?)
        AND ta.academic_year_id = (SELECT id FROM academic_years WHERE is_current = 1)
        ORDER BY c.name, s.name";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$assigned_classes = [];
while ($row = $result->fetch_assoc()) {
    $assigned_classes[] = $row;
}

// Fetch notices posted by this teacher
$query = "SELECT n.*, c.name as class_name, s.name as section_name 
        FROM class_notices n 
        JOIN classes c ON n.class_id = c.id 
        LEFT JOIN sections s ON n.section_id = s.id 
        WHERE n.teacher_id = ? 
        ORDER BY n.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$notices = [];
while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}


?>

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Class Notice Board</title>
   
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/notice.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        <h1 class="header-title">Class Notice Board</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>
    <main class="dashboard-content">
        
        <!-- Action Button and Filters -->
        <div class="action-button-container">
            <?php if (!empty($assigned_classes)): ?>
                <button class="create-notice-btn" id="createNoticeBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Class Notice
                </button>
            <?php endif; ?>
            
            <div class="filters-container">
                <select id="classFilter" class="filter-select">
                    <option value="">All Classes</option>
                    <?php foreach ($assigned_classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Your Class Notices</h2>
            
            <div class="notice-list" id="noticeList">
                <?php if (empty($notices)): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="empty-title">No notices yet</h3>
                        <p class="empty-description">You haven't posted any notices for your classes yet. Click "Create Class Notice" to get started.</p>
                        <?php if (!empty($assigned_classes)): ?>
                            <button class="create-notice-btn" onclick="document.getElementById('createNoticeBtn').click()">
                                Create Your First Notice
                            </button>
                        <?php else: ?>
                            <p>You need to be assigned as a class teacher to create notices.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($notices as $notice): ?>
                        <?php
                            // Format date
                            $createdDate = new DateTime($notice['created_at']);
                            $formattedDate = $createdDate->format('M j, Y');
                            
                            // Determine if notice is active or expired
                            $isActive = true;
                            if (!$notice['is_active'] || 
                                ($notice['expiry_date'] && new DateTime($notice['expiry_date']) < new DateTime())) {
                                $isActive = false;
                            }
                            
                            $statusClass = $isActive ? 'status-active' : 'status-expired';
                            $statusText = $isActive ? 'Active' : 'Expired';
                        ?>
                        <div class="notice-card" data-class="<?php echo $notice['class_id']; ?>" data-status="<?php echo $isActive ? 'active' : 'expired'; ?>">
                            <div class="notice-header">
                                <div class="notice-title-area">
                                    <div class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></div>
                                    <div class="notice-meta">
                                        <div class="notice-class">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <?php echo htmlspecialchars($notice['class_name'] . ' - ' . $notice['section_name']); ?>
                                        </div>
                                        <div class="notice-date">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <?php echo $formattedDate; ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="notice-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </div>

                            <div class="notice-content">
                                <?php echo $notice['content']; ?>
                            </div>
                            
                            <div class="notice-actions">
                                <button class="action-button edit-btn" data-id="<?php echo $notice['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button class="action-button delete-btn" data-id="<?php echo $notice['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Create/Edit Notice Modal -->
<div class="modal-overlay" id="noticeModal">
    <div class="modal-content">
                <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Create New Class Notice</h3>
            <button class="close-modal" id="closeModal">&times;</button>
                </div>
        
        <form id="noticeForm" action="notice_actions.php" method="POST">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="noticeId" value="">
            <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
            
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
                <label for="class_section" class="form-label">Class & Section</label>
                <select id="class_section" name="class_section" class="form-select" required>
                    <option value="">Select Class & Section</option>
                    <?php foreach ($assigned_classes as $class): ?>
                        <option value="<?php echo $class['id'] . '_' . $class['section_id']; ?>">
                            <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section_name']); ?>
                        </option>
                    <?php endforeach; ?>
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
                <button type="submit" class="submit-btn" id="submitBtn">Create Notice</button>
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

        <p>Are you sure you want to delete this notice? This action cannot be undone.</p>
        
        <form id="deleteForm" action="notice_actions.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteNoticeId" value="">
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" id="cancelDeleteBtn">Cancel</button>
                <button type="submit" class="submit-btn" style="background-color: #ef4444;">Delete</button>
            </div>
        </form>
        </div>
</div>

<!-- Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Initialize Components -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Summernote rich text editor
    if (jQuery().summernote) {
        $('#content').summernote({
            placeholder: 'Write your notice content here...',
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
    }
    
    // Initialize Flatpickr date picker
    if (typeof flatpickr === 'function') {
        flatpickr("#expiry_date", {
            dateFormat: "Y-m-d",
            minDate: "today"
        });
    }
    
    // Set up filter change events
    $('#classFilter, #statusFilter').change(function() {
        filterNotices();
    });

    // Function to filter notices
    function filterNotices() {
        const classFilter = $('#classFilter').val();
        const statusFilter = $('#statusFilter').val();
        
        $('.notice-card').each(function() {
            const classId = $(this).data('class');
            const status = $(this).data('status');
            
            let showClass = true;
            let showStatus = true;
            
            if (classFilter && classId != classFilter) {
                showClass = false;
            }
            
            if (statusFilter && status != statusFilter) {
                showStatus = false;
            }
            
            if (showClass && showStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Check if any notices are visible
        const visibleNotices = $('.notice-card:visible').length;
        if (visibleNotices === 0) {
            if ($('.no-notices').length === 0) {
                $('#noticeList').append('<div class="no-notices">No notices match your filters.</div>');
            }
        } else {
            $('.no-notices').remove();
        }
    }

    // Create Notice Button Click Handler
    document.getElementById('createNoticeBtn').addEventListener('click', function() {
        // Reset the form
        document.getElementById('noticeForm').reset();
        if (jQuery().summernote) {
            $('#content').summernote('code', '');
        }
        
        // Set form action to create
        document.getElementById('formAction').value = 'create';
        document.getElementById('noticeId').value = '';
        
        // Hide status field (it's only for editing)
        document.getElementById('statusGroup').style.display = 'none';
        
        // Update modal title and button text
        document.getElementById('modalTitle').textContent = 'Create New Class Notice';
        document.getElementById('submitBtn').textContent = 'Create Notice';
        
        // Show the modal
        document.getElementById('noticeModal').style.display = 'block';
    });

    // Edit Notice Button Click Handlers
    document.querySelectorAll('.edit-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const noticeId = this.getAttribute('data-id');
            
            fetch('notice_actions.php?action=get_notice&id=' + noticeId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    // Fill the form with notice data
                    document.getElementById('title').value = data.title;
                    if (jQuery().summernote) {
                        $('#content').summernote('code', data.content);
                    }
                    document.getElementById('class_section').value = data.class_id + '_' + data.section_id;
                    document.getElementById('expiry_date').value = data.expiry_date;
                    document.getElementById('is_active').checked = data.is_active == 1;
                    
                    // Set form action to update
                    document.getElementById('formAction').value = 'update';
                    document.getElementById('noticeId').value = data.id;
                    
                    // Show status field for editing
                    document.getElementById('statusGroup').style.display = 'block';
                    
                    // Update modal title and button text
                    document.getElementById('modalTitle').textContent = 'Edit Class Notice';
                    document.getElementById('submitBtn').textContent = 'Update Notice';
                    
                    // Show the modal
                    document.getElementById('noticeModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Error loading notice data. Please try again.');
                });
        });
    });

    // Delete Notice Button Click Handlers
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const noticeId = this.getAttribute('data-id');
            document.getElementById('deleteNoticeId').value = noticeId;
            document.getElementById('deleteModal').style.display = 'block';
        });
    });

    // Modal Close Handlers
    document.querySelectorAll('.close-modal, .cancel-btn').forEach(function(element) {
        element.addEventListener('click', function() {
            document.getElementById('noticeModal').style.display = 'none';
            document.getElementById('deleteModal').style.display = 'none';
        });
    });

    // Close modal when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
});
</script>

</body>
</html>
