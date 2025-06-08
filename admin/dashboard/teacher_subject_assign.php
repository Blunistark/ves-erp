<?php include "sidebar.php"?>

<?php
require_once 'con.php';

// Fetch all teachers
$teachers = [];
$query = "SELECT u.id, u.full_name, t.employee_number 
          FROM users u 
          JOIN teachers t ON u.id = t.user_id 
          WHERE u.role = 'teacher' AND u.status = 'active'
          ORDER BY u.full_name";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $result->free();
}

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

// Get teacher subject assignments
$teacherSubjects = [];
if (!empty($teachers) && !empty($subjects)) {
    $query = "SELECT ts.teacher_user_id, ts.subject_id FROM teacher_subjects ts";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $teacherId = $row['teacher_user_id'];
            $subjectId = $row['subject_id'];
            if (!isset($teacherSubjects[$teacherId])) {
                $teacherSubjects[$teacherId] = [];
            }
            $teacherSubjects[$teacherId][] = $subjectId;
        }
        $result->free();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Assign Subjects to Teachers</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        /* CSS Variables */
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --surface-color: #ffffff;
            --background-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --transition: all 0.2s ease;
        }

        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            color: var(--text-primary);
            background-color: var(--background-color);
        }

        /* Dashboard Layout */
        .dashboard-container {
            padding: var(--spacing-lg);
            max-width: 1400px;
            margin: 0 auto;
            margin-left: 260px; /* Slightly reduced margin */
            transition: margin-left 0.3s ease;
            position: relative;
            z-index: 1; /* Ensure content is above sidebar overlay */
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 40;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Adjust sidebar styles */
        .sidebar {
            z-index: 50; /* Higher than overlay */
        }

        /* Hamburger button */
        .hamburger-btn {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 60; /* Higher than sidebar */
            display: none;
        }

        @media (max-width: 768px) {
            .hamburger-btn {
                display: block;
            }
            
            .dashboard-container {
                margin-left: 0;
                padding: var(--spacing-md);
                padding-top: 60px; /* Add space for hamburger button */
            }

            .sidebar.show ~ .dashboard-container {
                margin-left: 0;
            }
        }

        /* Main Content Wrapper */
        .main-content-wrapper {
            position: relative;
            min-height: 100vh;
            background: var(--background-color);
            padding-bottom: var(--spacing-xl);
        }

        /* Dashboard Content */
        .dashboard-content {
            position: relative;
            z-index: 2;
            background: var(--background-color);
            border-radius: var(--radius-lg);
            margin-top: var(--spacing-lg);
        }

        /* Action Bar Adjustments */
        .action-bar {
            position: relative;
            z-index: 3;
            margin-top: var(--spacing-lg);
        }

        /* Header Adjustments */
        .dashboard-header {
            position: relative;
            z-index: 3;
            padding: var(--spacing-lg) var(--spacing-lg) 0;
            background: var(--background-color);
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header,
            .dashboard-content,
            .action-bar {
                padding-left: var(--spacing-md);
                padding-right: var(--spacing-md);
            }
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }

        .header-path {
            color: var(--text-secondary);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .header-path span:not(:last-child)::after {
            content: ">";
            margin-left: var(--spacing-sm);
            color: var(--text-muted);
        }

        /* Search and Actions Bar */
        .action-bar {
            background: var(--surface-color);
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--spacing-md);
        }

        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
                padding: var(--spacing-md);
            }
        }

        /* Search Input */
        .search-container {
            flex: 1;
            max-width: 500px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            transition: var(--transition);
            background-color: var(--background-color);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Action Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        /* Teacher Cards */
        .teacher-card {
            background: var(--surface-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .teacher-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
            background-color: var(--background-color);
        }

        .teacher-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }

        .teacher-id {
            color: var(--text-secondary);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        /* Subject Grid */
        .teacher-content {
            padding: var(--spacing-lg);
        }

        .subject-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--spacing-md);
        }

        @media (max-width: 640px) {
            .subject-list {
                grid-template-columns: 1fr;
            }
        }

        /* Subject Items */
        .subject-item {
            position: relative;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            transition: var(--transition);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .subject-item:hover {
            border-color: var(--primary-color);
            background-color: rgba(99, 102, 241, 0.05);
        }

        .subject-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            width: 100%;
            cursor: pointer;
        }

        .subject-checkbox {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            position: relative;
            cursor: pointer;
            transition: var(--transition);
        }

        .subject-checkbox:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .subject-checkbox:checked::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 0.75rem;
            height: 0.75rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
        }

        .subject-info {
            flex: 1;
        }

        .subject-label {
            font-weight: 500;
            color: var(--text-primary);
            display: block;
            margin-bottom: var(--spacing-xs);
        }

        .subject-code {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Card Actions */
        .form-actions {
            padding: var(--spacing-md) var(--spacing-lg);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            background-color: var(--background-color);
        }

        /* Success Alert */
        .save-success {
            position: fixed;
            top: var(--spacing-lg);
            right: var(--spacing-lg);
            background-color: #34d399;
            color: white;
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            z-index: 50;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--spacing-xl);
            color: var(--text-secondary);
        }

        .empty-state svg {
            width: 4rem;
            height: 4rem;
            margin-bottom: var(--spacing-md);
            color: var(--text-muted);
        }

        /* Loading Spinner */
        .spinner {
            animation: spin 1s linear infinite;
            width: 1.25rem;
            height: 1.25rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 480px) {
            .header-title {
                font-size: 1.25rem;
            }

            .btn {
                width: 100%;
            }

            .search-container {
                max-width: none;
            }

            .teacher-header {
                padding: var(--spacing-md);
            }

            .teacher-content {
                padding: var(--spacing-md);
            }

            .form-actions {
                padding: var(--spacing-md);
            }

            .save-success {
                left: var(--spacing-md);
                right: var(--spacing-md);
                bottom: var(--spacing-lg);
                top: auto;            }
        }
        
        /* Fix SweetAlert2 z-index to appear above dashboard header and sidebar */
        .swal2-container {
            z-index: 99999 !important;
        }
        .swal2-popup {
            z-index: 99999 !important;
        }
        .swal2-backdrop-show {
            z-index: 99998 !important;
        }
        
        /* Ensure dashboard header doesn't interfere with modals */
        .dashboard-header {
            z-index: 50 !important;
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
            <h1 class="header-title">Assign Subjects to Teachers</h1>
            <span class="header-path">Dashboard > Academic > Teacher Subject Assignment</span>
        </header>

        <main class="dashboard-content">
            <!-- Success message (hidden by default) -->
            <div class="save-success" id="successAlert" style="display: none;">
                <div class="success-message">
                    <svg xmlns="http://www.w3.org/2000/svg" class="success-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>Changes saved successfully</span>
                </div>
                <button class="close-alert" id="closeAlert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="action-bar">
                <div class="search-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="searchTeachers" class="search-input" placeholder="Search teachers by name...">
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" id="saveAllBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save All Changes
                    </button>
                </div>
            </div>
            
            <!-- Teacher Cards -->
            <div id="teacherCards">
                <?php if (empty($teachers)): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 20 20" fill="none" stroke="currentColor" class="mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p>No teachers found</p>
                        <a href="teachersadd.php" class="btn btn-primary">Add Teachers</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                    <div class="teacher-card" data-teacher-name="<?php echo htmlspecialchars(strtolower($teacher['full_name'])); ?>">
                        <div class="teacher-header">
                            <div>
                                <h3 class="teacher-name"><?php echo htmlspecialchars($teacher['full_name']); ?></h3>
                                <span class="teacher-id">Employee ID: <?php echo htmlspecialchars($teacher['employee_number']); ?></span>
                            </div>
                        </div>
                        <div class="teacher-content">
                            <?php if (empty($subjects)): ?>
                                <div class="empty-state">
                                    <p>No subjects available to assign</p>
                                    <a href="subject_manage.php" class="btn btn-primary">Add Subjects</a>
                                </div>
                            <?php else: ?>
                                <div class="subject-list">
                                    <?php foreach ($subjects as $subject): ?>
                                    <div class="subject-item">
                                        <label class="subject-checkbox-wrapper">
                                            <input type="checkbox" 
                                                   id="subject_<?php echo $teacher['id']; ?>_<?php echo $subject['id']; ?>" 
                                                   class="subject-checkbox"
                                                   data-teacher-id="<?php echo $teacher['id']; ?>"
                                                   data-subject-id="<?php echo $subject['id']; ?>"
                                                   <?php echo (isset($teacherSubjects[$teacher['id']]) && in_array($subject['id'], $teacherSubjects[$teacher['id']])) ? 'checked' : ''; ?>>
                                            <div class="subject-info">
                                                <span class="subject-label"><?php echo htmlspecialchars($subject['name']); ?></span>
                                                <span class="subject-code"><?php echo htmlspecialchars($subject['code']); ?></span>
                                            </div>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary save-teacher-btn" data-teacher-id="<?php echo $teacher['id']; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        }
        
        // Search functionality with debounce
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        const searchTeachers = debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const teacherCards = document.querySelectorAll('.teacher-card');
            
            teacherCards.forEach(card => {
                const teacherName = card.getAttribute('data-teacher-name');
                if (teacherName.includes(searchTerm)) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.3s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        }, 300);
        
        document.getElementById('searchTeachers').addEventListener('input', searchTeachers);
        
        // Save single teacher's subject assignments
        document.querySelectorAll('.save-teacher-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const teacherId = this.getAttribute('data-teacher-id');
                try {
                    await saveTeacherSubjects(teacherId);
                    showSuccessMessage('Changes saved successfully');
                } catch (error) {
                    showErrorMessage('Failed to save changes. Please try again.');
                }
            });
        });
        
        // Save all teachers' subject assignments
        document.getElementById('saveAllBtn').addEventListener('click', async function() {
            const saveAllBtn = this;
            const originalText = saveAllBtn.innerHTML;
            
            try {
                // Show loading state
                saveAllBtn.disabled = true;
                saveAllBtn.innerHTML = `
                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                `;
                
                const teacherIds = [...document.querySelectorAll('.save-teacher-btn')].map(btn => 
                    btn.getAttribute('data-teacher-id')
                );
                
                await Promise.all(teacherIds.map(id => saveTeacherSubjects(id, false)));
                showSuccessMessage('All changes saved successfully');
            } catch (error) {
                showErrorMessage('Failed to save some changes. Please try again.');
            } finally {
                // Restore button state
                saveAllBtn.disabled = false;
                saveAllBtn.innerHTML = originalText;
            }
        });
        
        // Close success alert
        document.getElementById('closeAlert').addEventListener('click', function() {
            const alert = document.getElementById('successAlert');
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
        
        function saveTeacherSubjects(teacherId, showMessage = true) {
            return new Promise((resolve, reject) => {
                const checkboxes = document.querySelectorAll(`input[data-teacher-id="${teacherId}"]`);
                const subjectIds = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.getAttribute('data-subject-id'));
                
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('teacher_id', teacherId);
                formData.append('subject_ids', JSON.stringify(subjectIds));
                
                fetch('teacher_subject_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (showMessage) {
                            showSuccessMessage('Subject assignments saved successfully');
                        }
                        resolve(data);
                    } else {
                        throw new Error(data.message || 'Failed to save assignments');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    reject(error);
                });
            });
        }
        
        function showSuccessMessage(message) {
            const alert = document.getElementById('successAlert');
            alert.querySelector('.success-message span').textContent = message;
            alert.style.display = 'flex';
            alert.style.animation = 'slideIn 0.3s ease';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        }
          function showErrorMessage(message) {
            // Use SweetAlert2 for better error display with proper z-index
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'error-modal-popup'
                },
                backdrop: true,
                allowOutsideClick: true
            });
        }
    </script>
    
    <!-- Add SweetAlert2 for better notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
</body>
</html>