<?php include "sidebar.php"?>

<?php
require_once 'con.php';

// Fetch all academic years
$academicYears = [];
$query = "SELECT * FROM academic_years ORDER BY start_date DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $academicYears[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Academic Years</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/manage_student.css">
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
        
        .year-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .year-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        
        .year-header {
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .year-info {
            flex: 1;
        }
        
        .year-name {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .year-dates {
            margin: 0;
            font-size: 14px;
            color: #718096;
        }
        
        .year-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-active {
            background: #c6f6d5;
            color: #276749;
        }
        
        .badge-inactive {
            background: #fed7d7;
            color: #9b2c2c;
        }
        
        .year-content {
            padding: 16px;
        }
        
        .duration {
            margin-bottom: 8px;
            color: #4a5568;
            font-size: 14px;
        }
        
        .year-footer {
            padding: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .action-menu {
            position: relative;
            display: inline-block;
        }
        
        .action-trigger {
            background: none;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        
        .action-trigger:hover {
            background-color: #f3f4f6;
        }
        
        .dropdown-icon {
            width: 16px;
            height: 16px;
        }
        
        .action-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            min-width: 160px;
            z-index: 1000;
            margin-top: 4px;
            border: 1px solid #e5e7eb;
            display: none;
            transform: translateY(0);
        }
        
        .action-menu.active .action-dropdown {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 8px 16px;
            color: #1f2937;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background-color 0.2s;
            white-space: nowrap;
        }
        
        .dropdown-item:hover {
            background-color: #f3f4f6;
        }
        
        .dropdown-item.warning {
            color: #ef4444;
        }
        
        .dropdown-item.warning:hover {
            background-color: #fef2f2;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 4px 0;
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
        
        .form-error {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 4px;
        }
        
        .no-data {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 32px;
            text-align: center;
            color: #718096;
        }
        
        .no-data-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            color: #cbd5e0;
        }
        
        .no-data-text {
            font-size: 16px;
            margin-bottom: 24px;
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
            <h1 class="header-title">Manage Academic Years</h1>
            <span class="header-path">Dashboard > Academic > Academic Years</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="searchYears" class="search-input" placeholder="Search academic years...">
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="showAddModal()">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Academic Year
                    </button>
                </div>
            </div>
            
            <?php if (empty($academicYears)): ?>
                <div class="no-data">
                    <svg class="no-data-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="no-data-text">No academic years found. Click "Add Academic Year" to create one.</p>
                    <button class="btn btn-primary" onclick="showAddModal()">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Academic Year
                    </button>
                </div>
            <?php else: ?>
                <div class="year-cards">
                    <?php foreach ($academicYears as $year): ?>
                        <?php
                        // Determine if the academic year is current
                        $today = date('Y-m-d');
                        $is_current = ($today >= $year['start_date'] && $today <= $year['end_date']);
                        ?>
                        <div class="year-card" data-name="<?php echo strtolower(htmlspecialchars($year['name'])); ?>">
                            <div class="year-header">
                                <div class="year-info">
                                    <h3 class="year-name"><?php echo htmlspecialchars($year['name']); ?></h3>
                                    <p class="year-dates">
                                        <?php 
                                        $start = date('M d, Y', strtotime($year['start_date']));
                                        $end = date('M d, Y', strtotime($year['end_date']));
                                        echo $start . ' - ' . $end; 
                                        ?>
                                    </p>
                                </div>
                                <span class="year-badge <?php echo $is_current ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $is_current ? 'Current' : 'Inactive'; ?>
                                </span>
                            </div>
                            <div class="year-content">
                                <div class="duration">
                                    <?php
                                    $start = new DateTime($year['start_date']);
                                    $end = new DateTime($year['end_date']);
                                    $interval = $start->diff($end);
                                    echo $interval->format('%m months, %d days');
                                    ?>
                                </div>
                            </div>
                            <div class="year-footer">
                                <div class="action-menu" id="actionMenu<?php echo $year['id']; ?>">
                                    <button class="action-trigger" onclick="toggleActionMenu('actionMenu<?php echo $year['id']; ?>')">
                                        <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="action-dropdown">
                                        <a href="#" class="dropdown-item" onclick="showEditModal(<?php echo $year['id']; ?>, '<?php echo htmlspecialchars(addslashes($year['name'])); ?>', '<?php echo $year['start_date']; ?>', '<?php echo $year['end_date']; ?>')">Edit Year</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item warning" onclick="showDeleteModal(<?php echo $year['id']; ?>, '<?php echo htmlspecialchars(addslashes($year['name'])); ?>')">Delete Year</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add Academic Year Modal -->
    <div class="modal-overlay" id="addYearModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Add New Academic Year</h3>
                <button class="close-modal" onclick="hideAddModal()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="addYearForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="yearName" class="form-label">Academic Year Name</label>
                        <input type="text" id="yearName" name="name" class="form-input" placeholder="e.g. 2023-2024" required>
                        <div class="form-error" id="nameError" style="display: none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" id="startDate" name="start_date" class="form-input" required>
                        <div class="form-error" id="startDateError" style="display: none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" id="endDate" name="end_date" class="form-input" required>
                        <div class="form-error" id="endDateError" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Academic Year</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Academic Year Modal -->
    <div class="modal-overlay" id="editYearModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Edit Academic Year</h3>
                <button class="close-modal" onclick="hideEditModal()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editYearForm">
                <div class="modal-body">
                    <input type="hidden" id="editYearId" name="id">
                    <div class="form-group">
                        <label for="editYearName" class="form-label">Academic Year Name</label>
                        <input type="text" id="editYearName" name="name" class="form-input" placeholder="e.g. 2023-2024" required>
                        <div class="form-error" id="editNameError" style="display: none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="editStartDate" class="form-label">Start Date</label>
                        <input type="date" id="editStartDate" name="start_date" class="form-input" required>
                        <div class="form-error" id="editStartDateError" style="display: none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="editEndDate" class="form-label">End Date</label>
                        <input type="date" id="editEndDate" name="end_date" class="form-input" required>
                        <div class="form-error" id="editEndDateError" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Academic Year Modal -->
    <div class="modal-overlay" id="deleteYearModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Delete Academic Year</h3>
                <button class="close-modal" onclick="hideDeleteModal()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="deleteYearForm">
                <div class="modal-body">
                    <input type="hidden" id="deleteYearId" name="id">
                    <p>Are you sure you want to delete <strong id="deleteYearName"></strong>?</p>
                    <p>This action cannot be undone.</p>
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
        
        // Action menu toggle
        function toggleActionMenu(menuId) {
            const menu = document.getElementById(menuId);
            
            // Close other open menus
            document.querySelectorAll('.action-menu.active').forEach(activeMenu => {
                if (activeMenu.id !== menuId) {
                    activeMenu.classList.remove('active');
                }
            });
            
            menu.classList.toggle('active');
        }
        
        // Close dropdown menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-menu')) {
                document.querySelectorAll('.action-menu.active').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });
        
        // Search functionality
        document.getElementById('searchYears').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.year-card');
            
            cards.forEach(card => {
                const yearName = card.getAttribute('data-name');
                if (yearName.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Add Year Modal
        function showAddModal() {
            document.getElementById('addYearModal').style.display = 'flex';
        }
        
        function hideAddModal() {
            document.getElementById('addYearModal').style.display = 'none';
            document.getElementById('addYearForm').reset();
            clearFormErrors('addYearForm');
        }
        
        // Edit Year Modal
        function showEditModal(id, name, startDate, endDate) {
            document.getElementById('editYearId').value = id;
            document.getElementById('editYearName').value = name;
            document.getElementById('editStartDate').value = startDate;
            document.getElementById('editEndDate').value = endDate;
            document.getElementById('editYearModal').style.display = 'flex';
        }
        
        function hideEditModal() {
            document.getElementById('editYearModal').style.display = 'none';
            document.getElementById('editYearForm').reset();
            clearFormErrors('editYearForm');
        }
        
        // Delete Year Modal
        function showDeleteModal(id, name) {
            document.getElementById('deleteYearId').value = id;
            document.getElementById('deleteYearName').textContent = name;
            document.getElementById('deleteYearModal').style.display = 'flex';
        }
        
        function hideDeleteModal() {
            document.getElementById('deleteYearModal').style.display = 'none';
        }
        
        // Clear form errors
        function clearFormErrors(formId) {
            const form = document.getElementById(formId);
            const errorElements = form.querySelectorAll('.form-error');
            errorElements.forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
        }
        
        // Form Validation
        function validateYearForm(name, startDate, endDate) {
            let isValid = true;
            const errors = {};
            
            // Name validation
            if (!name || name.trim() === '') {
                errors.name = 'Academic year name is required';
                isValid = false;
            }
            
            // Date validations
            if (!startDate) {
                errors.startDate = 'Start date is required';
                isValid = false;
            }
            
            if (!endDate) {
                errors.endDate = 'End date is required';
                isValid = false;
            }
            
            if (startDate && endDate && startDate > endDate) {
                errors.endDate = 'End date must be after start date';
                isValid = false;
            }
            
            return { isValid, errors };
        }
        
        // Form Submissions
        document.getElementById('addYearForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormErrors('addYearForm');
            
            const name = document.getElementById('yearName').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            const { isValid, errors } = validateYearForm(name, startDate, endDate);
            
            if (!isValid) {
                if (errors.name) {
                    document.getElementById('nameError').textContent = errors.name;
                    document.getElementById('nameError').style.display = 'block';
                }
                if (errors.startDate) {
                    document.getElementById('startDateError').textContent = errors.startDate;
                    document.getElementById('startDateError').style.display = 'block';
                }
                if (errors.endDate) {
                    document.getElementById('endDateError').textContent = errors.endDate;
                    document.getElementById('endDateError').style.display = 'block';
                }
                return;
            }
            
            // AJAX request to save the new academic year
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'academic_year_actions.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status === 200 && response.success) {
                        // Reload the page to show the new academic year
                        window.location.reload();
                    } else if (xhr.status === 422 && response.errors) {
                        // Display validation errors
                        if (response.errors.name) {
                            document.getElementById('nameError').textContent = response.errors.name;
                            document.getElementById('nameError').style.display = 'block';
                        }
                        if (response.errors.start_date) {
                            document.getElementById('startDateError').textContent = response.errors.start_date;
                            document.getElementById('startDateError').style.display = 'block';
                        }
                        if (response.errors.end_date) {
                            document.getElementById('endDateError').textContent = response.errors.end_date;
                            document.getElementById('endDateError').style.display = 'block';
                        }
                        if (response.errors.date_overlap) {
                            document.getElementById('endDateError').textContent = response.errors.date_overlap;
                            document.getElementById('endDateError').style.display = 'block';
                        }
                    } else {
                        alert(response.message || 'An error occurred while adding the academic year.');
                    }
                } catch (e) {
                    alert('An unexpected error occurred. Please try again.');
                }
            };
            xhr.send('action=add&name=' + encodeURIComponent(name) + '&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate));
        });
        
        document.getElementById('editYearForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormErrors('editYearForm');
            
            const id = document.getElementById('editYearId').value;
            const name = document.getElementById('editYearName').value;
            const startDate = document.getElementById('editStartDate').value;
            const endDate = document.getElementById('editEndDate').value;
            
            const { isValid, errors } = validateYearForm(name, startDate, endDate);
            
            if (!isValid) {
                if (errors.name) {
                    document.getElementById('editNameError').textContent = errors.name;
                    document.getElementById('editNameError').style.display = 'block';
                }
                if (errors.startDate) {
                    document.getElementById('editStartDateError').textContent = errors.startDate;
                    document.getElementById('editStartDateError').style.display = 'block';
                }
                if (errors.endDate) {
                    document.getElementById('editEndDateError').textContent = errors.endDate;
                    document.getElementById('editEndDateError').style.display = 'block';
                }
                return;
            }
            
            // AJAX request to update the academic year
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'academic_year_actions.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Reload the page to show the updated academic year
                            window.location.reload();
                        } else {
                            alert(response.message || 'An error occurred while updating the academic year.');
                        }
                    } catch (e) {
                        alert('An unexpected error occurred. Please try again.');
                    }
                } else {
                    alert('Server error. Please try again later.');
                }
            };
            xhr.send('action=update&id=' + id + '&name=' + encodeURIComponent(name) + '&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate));
        });
        
        document.getElementById('deleteYearForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('deleteYearId').value;
            
            // AJAX request to delete the academic year
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'academic_year_actions.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Reload the page to update the academic year list
                            window.location.reload();
                        } else {
                            alert(response.message || 'An error occurred while deleting the academic year.');
                        }
                    } catch (e) {
                        alert('An unexpected error occurred. Please try again.');
                    }
                } else {
                    alert('Server error. Please try again later.');
                }
            };
            xhr.send('action=delete&id=' + id);
        });
    </script>
</body>
</html> 