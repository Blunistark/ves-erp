<?php
include "sidebar.php";
require_once 'con.php';

// Fetch all classes
$classes = [];
$class_query = "SELECT * FROM classes ORDER BY name";
$class_result = $conn->query($class_query);
if ($class_result) {
    while ($row = $class_result->fetch_assoc()) {
        $classes[] = $row;
    }
    $class_result->free();
}

// Fetch all subjects
$subjects = [];
$subject_query = "SELECT * FROM subjects ORDER BY name";
$subject_result = $conn->query($subject_query);
if ($subject_result) {
    while ($row = $subject_result->fetch_assoc()) {
        $subjects[] = $row;
    }
    $subject_result->free();
}

// Fetch current class-subject mappings
$class_subjects = [];
$mapping_query = "SELECT cs.class_id, cs.subject_id, c.name as class_name, s.name as subject_name, s.code as subject_code
                  FROM class_subjects cs
                  JOIN classes c ON cs.class_id = c.id
                  JOIN subjects s ON cs.subject_id = s.id
                  ORDER BY c.name, s.name";
$mapping_result = $conn->query($mapping_query);
if ($mapping_result) {
    while ($row = $mapping_result->fetch_assoc()) {
        $class_subjects[] = $row;
    }
    $mapping_result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Class Subjects</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/manage_student.css">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-content {
            padding: 20px;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .search-container {
            position: relative;
            max-width: 400px;
            width: 100%;
        }
        
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            width: 20px;
            height: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #4299e1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .class-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.2s;
        }
        
        .class-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .class-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
        }
        
        .class-card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .class-card-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin: 4px 0 0 0;
        }
        
        .class-card-body {
            padding: 16px;
        }
        
        .subjects-list {
            margin-bottom: 16px;
        }
        
        .subject-tag {
            display: inline-block;
            background: #edf2f7;
            color: #4a5568;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin: 2px;
            position: relative;
        }
        
        .subject-tag .remove-btn {
            margin-left: 6px;
            color: #e53e3e;
            cursor: pointer;
            font-weight: bold;
        }
        
        .subject-tag .remove-btn:hover {
            color: #c53030;
        }
        
        .add-subject-btn {
            background: #48bb78;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .add-subject-btn:hover {
            background: #38a169;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
        
        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            opacity: 0.5;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .modal {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .form-select, .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: #4299e1;
        }
        
        .checkbox-group {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 6px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .checkbox-item:hover {
            background: #f7fafc;
        }
        
        .checkbox-item input {
            margin-right: 8px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .stats-bar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #718096;
        }
        
        @media (max-width: 768px) {
            .cards-container {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container {
                max-width: none;
            }
        }
    </style>
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
            <h1 class="header-title">Manage Class Subjects</h1>
            <span class="header-path">Dashboard > Academic Structure > Manage Class Subjects</span>
        </header>

        <main class="dashboard-content">
            <!-- Statistics Bar -->
            <div class="stats-bar">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($classes); ?></div>
                        <div class="stat-label">Total Classes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($subjects); ?></div>
                        <div class="stat-label">Total Subjects</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($class_subjects); ?></div>
                        <div class="stat-label">Subject Mappings</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count(array_unique(array_column($class_subjects, 'class_id'))); ?></div>
                        <div class="stat-label">Classes with Subjects</div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="search-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search classes...">
                </div>
                <button class="btn-primary" onclick="showBulkAssignModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin-right: 8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Bulk Assign Subjects
                </button>
            </div>

            <!-- Classes Cards -->
            <div class="cards-container" id="classesContainer">
                <?php if (empty($classes)): ?>
                    <div class="empty-state">
                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3>No Classes Found</h3>
                        <p>Please create classes first in the Manage Classes section.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <?php
                        // Get subjects for this class
                        $class_subject_list = array_filter($class_subjects, function($cs) use ($class) {
                            return $cs['class_id'] == $class['id'];
                        });
                        ?>
                        <div class="class-card" data-class-name="<?php echo strtolower($class['name']); ?>">
                            <div class="class-card-header">
                                <h3 class="class-card-title">Class <?php echo htmlspecialchars($class['name']); ?></h3>
                                <p class="class-card-subtitle"><?php echo count($class_subject_list); ?> subjects assigned</p>
                            </div>
                            <div class="class-card-body">
                                <div class="subjects-list">
                                    <?php if (empty($class_subject_list)): ?>
                                        <p style="color: #718096; font-style: italic; margin: 0;">No subjects assigned yet</p>
                                    <?php else: ?>
                                        <?php foreach ($class_subject_list as $cs): ?>
                                            <span class="subject-tag">
                                                <?php echo htmlspecialchars($cs['subject_name']); ?> (<?php echo htmlspecialchars($cs['subject_code']); ?>)
                                                <span class="remove-btn" onclick="removeSubjectFromClass(<?php echo $class['id']; ?>, <?php echo $cs['subject_id']; ?>)">×</span>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button class="add-subject-btn" onclick="showAddSubjectModal(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['name']); ?>')">
                                    + Add Subjects
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal-overlay" id="addSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add Subjects to <span id="modalClassName"></span></h3>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalClassId">
                <div class="form-group">
                    <label class="form-label">Select Subjects:</label>
                    <div class="checkbox-group" id="subjectsCheckboxGroup">
                        <?php foreach ($subjects as $subject): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" value="<?php echo $subject['id']; ?>">
                                <span><?php echo htmlspecialchars($subject['name']); ?> (<?php echo htmlspecialchars($subject['code']); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="hideAddSubjectModal()">Cancel</button>
                <button class="btn-primary" onclick="addSubjectsToClass()">Add Selected Subjects</button>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div class="modal-overlay" id="bulkAssignModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Bulk Assign Subjects</h3>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Classes:</label>
                    <div class="checkbox-group" style="max-height: 150px;">
                        <?php foreach ($classes as $class): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="bulk_classes" value="<?php echo $class['id']; ?>">
                                <span>Class <?php echo htmlspecialchars($class['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Select Subjects:</label>
                    <div class="checkbox-group" style="max-height: 150px;">
                        <?php foreach ($subjects as $subject): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="bulk_subjects" value="<?php echo $subject['id']; ?>">
                                <span><?php echo htmlspecialchars($subject['name']); ?> (<?php echo htmlspecialchars($subject['code']); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="hideBulkAssignModal()">Cancel</button>
                <button class="btn-primary" onclick="bulkAssignSubjects()">Assign to Selected Classes</button>
            </div>
        </div>
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

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.class-card');
            
            cards.forEach(card => {
                const className = card.getAttribute('data-class-name');
                if (className.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Modal functions
        function showAddSubjectModal(classId, className) {
            document.getElementById('modalClassId').value = classId;
            document.getElementById('modalClassName').textContent = 'Class ' + className;
            
            // Reset checkboxes
            const checkboxes = document.querySelectorAll('#subjectsCheckboxGroup input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
            
            // Disable already assigned subjects
            fetch('class_subjects_actions.php?action=get_class_subjects&class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.subjects.forEach(subjectId => {
                            const checkbox = document.querySelector(`#subjectsCheckboxGroup input[value="${subjectId}"]`);
                            if (checkbox) {
                                checkbox.disabled = true;
                                checkbox.parentElement.style.opacity = '0.5';
                                checkbox.parentElement.title = 'Already assigned';
                            }
                        });
                    }
                });
            
            document.getElementById('addSubjectModal').style.display = 'flex';
        }

        function hideAddSubjectModal() {
            document.getElementById('addSubjectModal').style.display = 'none';
            // Re-enable all checkboxes
            const checkboxes = document.querySelectorAll('#subjectsCheckboxGroup input[type="checkbox"]');
            checkboxes.forEach(cb => {
                cb.disabled = false;
                cb.parentElement.style.opacity = '1';
                cb.parentElement.title = '';
            });
        }

        function showBulkAssignModal() {
            // Reset checkboxes
            const classCheckboxes = document.querySelectorAll('input[name="bulk_classes"]');
            const subjectCheckboxes = document.querySelectorAll('input[name="bulk_subjects"]');
            
            classCheckboxes.forEach(cb => cb.checked = false);
            subjectCheckboxes.forEach(cb => cb.checked = false);
            
            document.getElementById('bulkAssignModal').style.display = 'flex';
        }

        function hideBulkAssignModal() {
            document.getElementById('bulkAssignModal').style.display = 'none';
        }

        function addSubjectsToClass() {
            const classId = document.getElementById('modalClassId').value;
            const selectedSubjects = [];
            
            const checkboxes = document.querySelectorAll('#subjectsCheckboxGroup input[type="checkbox"]:checked');
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    selectedSubjects.push(cb.value);
                }
            });
            
            if (selectedSubjects.length === 0) {
                Swal.fire({
                    title: 'No Subjects Selected',
                    text: 'Please select at least one subject to add.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'add_subjects_to_class');
            formData.append('class_id', classId);
            formData.append('subject_ids', JSON.stringify(selectedSubjects));
            
            fetch('class_subjects_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while adding subjects.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
            
            hideAddSubjectModal();
        }

        function removeSubjectFromClass(classId, subjectId) {
            Swal.fire({
                title: 'Remove Subject?',
                text: 'Are you sure you want to remove this subject from the class?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Remove',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'remove_subject_from_class');
                    formData.append('class_id', classId);
                    formData.append('subject_id', subjectId);
                    
                    fetch('class_subjects_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Removed!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }

        function bulkAssignSubjects() {
            const selectedClasses = [];
            const selectedSubjects = [];
            
            const classCheckboxes = document.querySelectorAll('input[name="bulk_classes"]:checked');
            const subjectCheckboxes = document.querySelectorAll('input[name="bulk_subjects"]:checked');
            
            classCheckboxes.forEach(cb => selectedClasses.push(cb.value));
            subjectCheckboxes.forEach(cb => selectedSubjects.push(cb.value));
            
            if (selectedClasses.length === 0 || selectedSubjects.length === 0) {
                Swal.fire({
                    title: 'Incomplete Selection',
                    text: 'Please select at least one class and one subject.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'bulk_assign_subjects');
            formData.append('class_ids', JSON.stringify(selectedClasses));
            formData.append('subject_ids', JSON.stringify(selectedSubjects));
            
            fetch('class_subjects_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
            
            hideBulkAssignModal();
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.style.display = 'none';
            }
        });
    </script>
</body>
</html>
