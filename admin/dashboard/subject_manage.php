<?php
include "sidebar.php";
require_once 'con.php';

// Fetch all subjects
$subjects = [];
$query = "SELECT * FROM subjects ORDER BY name";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Subjects</title>
    
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
            width: 20px;
            height: 20px;
            color: #718096;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-icon {
            width: 18px;
            height: 18px;
        }
        
        .btn-outline {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .btn-outline:hover {
            border-color: #cbd5e0;
            background: #f8fafc;
        }
        
        .btn-primary {
            background: #4299e1;
            border: 1px solid #3182ce;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #3182ce;
        }
        
        .subjects-table {
            width: 100%;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
        }
        
        .subjects-table th,
        .subjects-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .subjects-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }
        
        .subjects-table tr:last-child td {
            border-bottom: none;
        }
        
        .subject-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .action-btn {
            padding: 6px;
            border-radius: 4px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #4a5568;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: #f0f4f8;
        }
        
        .action-btn.edit:hover {
            color: #4299e1;
        }
        
        .action-btn.delete:hover {
            color: #e53e3e;
        }
        
        .action-icon {
            width: 18px;
            height: 18px;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal-container {
            background: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .modal-title {
            margin: 0;
            font-size: 18px;
            color: #2d3748;
        }
        
        .close-modal {
            background: transparent;
            border: none;
            cursor: pointer;
            color: #718096;
            padding: 4px;
            border-radius: 4px;
        }
        
        .close-icon {
            width: 20px;
            height: 20px;
        }
        
        .close-modal:hover {
            background: #f0f4f8;
        }
        
        .modal-body {
            padding: 16px;
        }
        
        .modal-footer {
            padding: 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #e2e8f0;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
        }
        
        .pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        
        .page-btn, .page-nav {
            min-width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #4a5568;
            transition: all 0.2s;
        }
        
        .page-btn:hover, .page-nav:hover {
            background: #f0f4f8;
        }
        
        .page-btn.active {
            background: #4299e1;
            border-color: #3182ce;
            color: #fff;
        }
        
        /* SweetAlert2 Custom Styles */
        .swal2-popup {
            border-radius: 12px;
        }
        
        .swal2-title {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
        }
        
        .swal2-html-container {
            font-size: 0.875rem !important;
        }
        
        .swal2-confirm {
            background-color: #4299e1 !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
        }
        
        .swal2-confirm.swal2-styled.danger {
            background-color: #ef4444 !important;
        }
        
        .swal2-cancel {
            background-color: #e2e8f0 !important;
            color: #4a5568 !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
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
            <h1 class="header-title">Manage Subjects</h1>
            <span class="header-path">Dashboard > Academic > Subjects</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="searchSubjects" class="search-input" placeholder="Search subjects...">
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" onclick="showAddModal()">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Subject
                    </button>
                </div>
            </div>
            
            <table class="subjects-table" id="subjectsTable">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th style="width: 120px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 30px;">
                            No subjects found. Click "Add Subject" to create one.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($subjects as $subject): ?>
                    <tr data-id="<?php echo $subject['id']; ?>">
                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                        <td><?php echo htmlspecialchars($subject['code']); ?></td>
                        <td>
                            <div class="subject-actions">
                                <button class="action-btn edit" onclick="showEditModal(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars(addslashes($subject['name'])); ?>', '<?php echo htmlspecialchars(addslashes($subject['code'])); ?>')">
                                    <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button class="action-btn delete" onclick="showDeleteModal(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars(addslashes($subject['name'])); ?>')">
                                    <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="pagination">
                <button class="page-nav">
                    <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button class="page-btn active">1</button>
                <button class="page-nav">
                    <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </main>
    </div>
    
    <!-- Add Subject Modal -->
    <div class="modal-overlay" id="addSubjectModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Add New Subject</h3>
                <button class="close-modal" onclick="hideAddModal()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="addSubjectForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="subjectName" class="form-label">Subject Name</label>
                        <input type="text" id="subjectName" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="subjectCode" class="form-label">Subject Code</label>
                        <input type="text" id="subjectCode" name="code" class="form-input" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Subject Modal -->
    <div class="modal-overlay" id="editSubjectModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Edit Subject</h3>
                <button class="close-modal" onclick="hideEditModal()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editSubjectForm">
                <div class="modal-body">
                    <input type="hidden" id="editSubjectId" name="id">
                    <div class="form-group">
                        <label for="editSubjectName" class="form-label">Subject Name</label>
                        <input type="text" id="editSubjectName" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="editSubjectCode" class="form-label">Subject Code</label>
                        <input type="text" id="editSubjectCode" name="code" class="form-input" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Subject Modal -->
    <div class="modal-overlay" id="deleteSubjectModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Delete Subject</h3>
                <button class="close-modal" onclick="hideDeleteModal()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="deleteSubjectForm">
                <div class="modal-body">
                    <input type="hidden" id="deleteSubjectId" name="id">
                    <p>Are you sure you want to delete the subject "<span id="deleteSubjectName"></span>"?</p>
                    <p>This action cannot be undone. All data associated with this subject will also be deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #e53e3e; border-color: #c53030;">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        }
        
        // Add Subject Modal
        function showAddModal() {
            document.getElementById('addSubjectModal').style.display = 'flex';
        }
        
        function hideAddModal() {
            document.getElementById('addSubjectModal').style.display = 'none';
            document.getElementById('addSubjectForm').reset();
        }
        
        // Edit Subject Modal
        function showEditModal(id, name, code) {
            document.getElementById('editSubjectId').value = id;
            document.getElementById('editSubjectName').value = name;
            document.getElementById('editSubjectCode').value = code;
            document.getElementById('editSubjectModal').style.display = 'flex';
        }
        
        function hideEditModal() {
            document.getElementById('editSubjectModal').style.display = 'none';
            document.getElementById('editSubjectForm').reset();
        }
        
        // Delete Subject Modal
        function showDeleteModal(id, name) {
            document.getElementById('deleteSubjectId').value = id;
            document.getElementById('deleteSubjectName').textContent = name;
            document.getElementById('deleteSubjectModal').style.display = 'flex';
        }
        
        function hideDeleteModal() {
            document.getElementById('deleteSubjectModal').style.display = 'none';
            document.getElementById('deleteSubjectForm').reset();
        }
        
        // Form Submissions with SweetAlert2
        document.getElementById('addSubjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('subjectName').value;
            const code = document.getElementById('subjectCode').value;
            
            Swal.fire({
                title: 'Add New Subject',
                text: `Are you sure you want to add ${name} (${code})?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Add Subject',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'subject_actions.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Subject added successfully',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to add subject',
                                    icon: 'error'
                                });
                            }
                        }
                    };
                    xhr.send('action=add&name=' + encodeURIComponent(name) + '&code=' + encodeURIComponent(code));
                }
            });
        });
        
        document.getElementById('editSubjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editSubjectId').value;
            const name = document.getElementById('editSubjectName').value;
            const code = document.getElementById('editSubjectCode').value;
            
            Swal.fire({
                title: 'Update Subject',
                text: `Are you sure you want to update ${name}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'subject_actions.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Subject updated successfully',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to update subject',
                                    icon: 'error'
                                });
                            }
                        }
                    };
                    xhr.send('action=update&id=' + id + '&name=' + encodeURIComponent(name) + '&code=' + encodeURIComponent(code));
                }
            });
        });
        
        document.getElementById('deleteSubjectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('deleteSubjectId').value;
            const name = document.getElementById('deleteSubjectName').textContent;
            
            Swal.fire({
                title: 'Delete Subject',
                text: `Are you sure you want to delete ${name}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'subject_actions.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Subject has been deleted successfully',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to delete subject',
                                    icon: 'error'
                                });
                            }
                        }
                    };
                    xhr.send('action=delete&id=' + id);
                }
            });
        });
        
        // Search functionality with success notification
        document.getElementById('searchSubjects').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#subjectsTable tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const code = row.cells[1].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || code.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (searchTerm && visibleCount > 0) {
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                toast.fire({
                    icon: 'success',
                    title: `Found ${visibleCount} matching subjects`
                });
            }
        });
    </script>
</body>
</html> 