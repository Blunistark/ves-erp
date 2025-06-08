// Global functions
window.loadCurrentAssignments = async function() {
    const loadingSpinner = document.getElementById('loadingSpinner') || createLoadingSpinner();
    try {
        loadingSpinner.style.display = 'block';
        const response = await fetch('teachersassign_class_action.php?type=assignments');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        if (data.status === 'success') {
            window.allAssignments = data.data;
            renderAssignments(data.data);
        } else {
            throw new Error(data.message || 'Failed to load assignments');
        }
    } catch (error) {
        console.error('Error loading assignments:', error);
        showError('Failed to load current assignments. Please try refreshing the page.');
    } finally {
        loadingSpinner.style.display = 'none';
    }
};

window.deleteAssignment = async function(assignmentId) {
    if (!confirm('Are you sure you want to delete this assignment?')) {
        return;
    }

    const loadingSpinner = document.getElementById('loadingSpinner') || createLoadingSpinner();
    try {
        loadingSpinner.style.display = 'block';
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', assignmentId);

        const response = await fetch('teachersassign_class_action.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.status === 'success') {
            showSuccess(data.message);
            window.loadCurrentAssignments();
        } else {
            throw new Error(data.message || 'Failed to delete assignment');
        }
    } catch (error) {
        console.error('Error deleting assignment:', error);
        showError(error.message || 'An error occurred while deleting the assignment');
    } finally {
        loadingSpinner.style.display = 'none';
    }
};

// Helper functions
function createLoadingSpinner() {
    const spinner = document.createElement('div');
    spinner.id = 'loadingSpinner';
    spinner.className = 'loading-spinner';
    spinner.style.cssText = `
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background: rgba(255, 255, 255, 0.8);
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    `;
    spinner.innerHTML = '<div class="spinner"></div><div style="margin-top: 10px;">Loading...</div>';
    document.body.appendChild(spinner);
    return spinner;
}

function showError(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function showSuccess(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

function populateDropdown(select, data, defaultText) {
    if (!select) {
        console.error('Select element not found');
        return;
    }
    select.innerHTML = `<option value="">${defaultText}</option>`;
    if (Array.isArray(data)) {
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    } else {
        console.error('Invalid data format for dropdown:', data);
    }
}

function renderAssignments(assignments) {
    const cardsContainer = document.querySelector('.assignment-cards');
    const tableBody = document.querySelector('.assignments-table tbody');
    
    // Clear existing content
    cardsContainer.innerHTML = '';
    tableBody.innerHTML = '';

    if (!assignments || assignments.length === 0) {
        cardsContainer.innerHTML = '<div class="no-data">No assignments found</div>';
        tableBody.innerHTML = '<tr><td colspan="6" class="no-data">No assignments found</td></tr>';
        return;
    }

    assignments.forEach(assignment => {
        // Create card
        const card = document.createElement('div');
        card.className = 'assignment-card';
        card.innerHTML = `
            <div class="assignment-card-header">
                <h3 class="assignment-title">${assignment.class_name} - ${assignment.section_name}</h3>
            </div>
            <div class="assignment-info">
                <div class="info-item">
                    <span class="label">Teacher:</span>
                    <span class="value">${assignment.teacher_name || 'Not assigned'}</span>
                </div>
                <div class="info-item">
                    <span class="label">Students:</span>
                    <span class="value">${assignment.student_count}</span>
                </div>
                <div class="info-item">
                    <span class="label">Status:</span>
                    <span class="value status-${assignment.status.toLowerCase()}">${assignment.status}</span>
                </div>
            </div>
            <div class="assignment-footer">
                <div class="card-actions">
                    <button class="btn btn-sm btn-delete" onclick="deleteAssignment(${assignment.id})">Delete</button>
                </div>
            </div>
        `;
        cardsContainer.appendChild(card);

        // Create table row
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${assignment.class_name}</td>
            <td>${assignment.section_name}</td>
            <td>${assignment.teacher_name || 'Not assigned'}</td>
            <td>${assignment.student_count}</td>
            <td><span class="status-badge status-${assignment.status.toLowerCase()}">${assignment.status}</span></td>
            <td class="actions">
                <button class="btn btn-sm btn-delete" onclick="deleteAssignment(${assignment.id})" ${!assignment.teacher_name ? 'disabled' : ''}>Remove</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form elements
    const form = document.querySelector('#teacherAssignmentForm');
    const teacherSelect = document.getElementById('teacher');
    const classSelect = document.getElementById('class');
    const sectionSelect = document.getElementById('section');
    // const subjectSelect = document.getElementById('subject'); // Not needed for class teacher assignment
    const assignmentForm = document.getElementById('assignmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    // Initialize window.allAssignments
    window.allAssignments = [];

    // Load initial data
    loadTeachers();
    loadClasses();
    window.loadCurrentAssignments();

    // Event listeners
    classSelect?.addEventListener('change', function() {
        loadSections(this.value);
    });

    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Form validation
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);
        
        try {
            // Show loading state
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-flex';
            submitBtn.disabled = true;

            const response = await fetch('teachersassign_class_action.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showSuccess(data.message);
                form.reset();
                form.classList.remove('was-validated');
                window.loadCurrentAssignments();
                assignmentForm.style.display = 'none';
            } else {
                throw new Error(data.message || 'Failed to submit assignment');
            }
        } catch (error) {
            console.error('Error submitting assignment:', error);
            showError(error.message || 'An error occurred while submitting the assignment');
        } finally {
            // Reset button state
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    document.getElementById('newAssignmentBtn')?.addEventListener('click', function() {
        form.reset();
        form.classList.remove('was-validated');
        assignmentForm.style.display = 'block';
    });

    document.getElementById('cancelBtn')?.addEventListener('click', function() {
        form.reset();
        form.classList.remove('was-validated');
        assignmentForm.style.display = 'none';
    });

    document.getElementById('closeFormBtn')?.addEventListener('click', function() {
        form.reset();
        form.classList.remove('was-validated');
        assignmentForm.style.display = 'none';
    });

    document.getElementById('assignmentSearch')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        if (!searchTerm) {
            window.loadCurrentAssignments();
            return;
        }

        const filtered = window.allAssignments.filter(assignment => {
            return (
                (assignment.teacher_name && assignment.teacher_name.toLowerCase().includes(searchTerm)) ||
                assignment.class_name.toLowerCase().includes(searchTerm) ||
                assignment.section_name.toLowerCase().includes(searchTerm) ||
                assignment.status.toLowerCase().includes(searchTerm)
            );
        });

        renderAssignments(filtered);
    });

    document.getElementById('filterToggleBtn')?.addEventListener('click', function() {
        const filterPanel = document.getElementById('filterPanel');
        if (filterPanel) {
            filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
        }
    });

    document.getElementById('createFirstAssignment')?.addEventListener('click', function() {
        assignmentForm.style.display = 'block';
    });

    // Functions to load dropdown data
    async function loadTeachers() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        try {
            loadingSpinner.style.display = 'block';
            console.log('Fetching teachers...');
            const response = await fetch('teachersassign_class_action.php?type=teachers');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('Teachers data:', data);
            if (data.status === 'success') {
                populateDropdown(teacherSelect, data.data, 'Select a teacher');
                // Also populate the filter dropdown
                populateDropdown(document.getElementById('filterTeacher'), data.data, 'All');
            } else {
                throw new Error(data.message || 'Failed to load teachers');
            }
        } catch (error) {
            console.error('Error loading teachers:', error);
            showError('Failed to load teachers. Please try refreshing the page.');
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }

    async function loadClasses() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        try {
            loadingSpinner.style.display = 'block';
            console.log('Fetching classes...');
            const response = await fetch('teachersassign_class_action.php?type=classes');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('Classes data:', data);
            if (data.status === 'success') {
                populateDropdown(classSelect, data.data, 'Select Class');
                // Also populate the filter dropdown
                populateDropdown(document.getElementById('filterClass'), data.data, 'All');
            } else {
                throw new Error(data.message || 'Failed to load classes');
            }
        } catch (error) {
            console.error('Error loading classes:', error);
            showError('Failed to load classes. Please try refreshing the page.');
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }

    async function loadSections(classId) {
        if (!classId) {
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            return;
        }
        const loadingSpinner = document.getElementById('loadingSpinner');
        try {
            loadingSpinner.style.display = 'block';
            console.log('Fetching sections for class:', classId);
            const response = await fetch(`teachersassign_class_action.php?type=sections&class_id=${classId}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('Sections data:', data);
            if (data.status === 'success') {
                populateDropdown(sectionSelect, data.data, 'Select Section');
            } else {
                throw new Error(data.message || 'Failed to load sections');
            }
        } catch (error) {
            console.error('Error loading sections:', error);
            showError('Failed to load sections. Please try refreshing the page.');
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }
}); 