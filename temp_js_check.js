// Global variables
const userRole = 'headmaster';
const sectionsData = [];

// Initialize the application
$(document).ready(function() {
    initializeTabs();
    initializeFormHandlers();
    loadTeachers();
    loadClassAssignments();
    loadStatistics();
});

// Tab management
function initializeTabs() {
    $('.tab-button').click(function() {
        const tabId = $(this).data('tab');
        switchTab(tabId);
    });
}

function switchTab(tabId) {
    // Update active states
    $('.tab-button').removeClass('active');
    $('.tab-content').removeClass('active');
    
    $(`[data-tab="${tabId}"]`).addClass('active');
    $(`#${tabId}`).addClass('active');
    
    // Load tab-specific data
    switch(tabId) {
        case 'manage-teachers':
            loadTeachers();
            break;
        case 'class-assignments':
            loadClassAssignments();
            break;
        case 'subject-assignments':
            // Loaded when teacher is selected
            break;
        case 'overview':
            loadStatistics();
            break;
    }
}

// Form handlers
function initializeFormHandlers() {
    // Class assignment
    $('#assignClass').change(updateSections);
    $('#assignClassTeacher').click(handleClassAssignment);
    
    // Subject assignment
    $('#subjectTeacher').change(loadTeacherSubjects);
    $('#updateSubjectAssignments').click(handleSubjectAssignment);
    $('#clearSubjectAssignments').click(clearSubjectSelections);
    
    // Search
    $('#teacherSearch').on('input', debounce(searchTeachers, 300));
}

// Load teachers
function loadTeachers() {
    $('#teachersLoading').show();
    $('#teachersTableBody').empty();
    
    $.ajax({
        url: 'teacher_management_api.php',
        method: 'GET',
        data: { action: 'get_teachers' },
        success: function(response) {
            $('#teachersLoading').hide();
            
            if (response.success) {
                displayTeachers(response.data);
            } else {
                showNotification('Failed to load teachers', 'error');
            }
        },
        error: function() {
            $('#teachersLoading').hide();
            showNotification('An error occurred while loading teachers', 'error');
        }
    });
}

function displayTeachers(teachers) {
    const tbody = $('#teachersTableBody');
    tbody.empty();
    
    teachers.forEach(teacher => {
        const row = `
            <tr>
                <td>${escapeHtml(teacher.full_name)}</td>
                <td>${escapeHtml(teacher.employee_number)}</td>
                <td>${escapeHtml(teacher.email)}</td>
                <td><span class="status-badge status-${teacher.status}">${teacher.status}</span></td>
                <td class="actions">
                    <button class="btn btn-outline btn-sm" onclick="viewTeacherAssignments(${teacher.id})">
                        <i class="fas fa-eye"></i> View Assignments
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Class assignment functionality
function updateSections() {
    const classId = $('#assignClass').val();
    const sectionSelect = $('#assignSection');
    
    sectionSelect.empty().append('<option value="">Select Section</option>');
    
    if (classId) {
        const classSections = sectionsData.filter(section => section.class_id == classId);
        classSections.forEach(section => {
            sectionSelect.append(`<option value="${section.id}">${escapeHtml(section.name)}</option>`);
        });
        sectionSelect.prop('disabled', false);
    } else {
        sectionSelect.prop('disabled', true);
    }
}

function handleClassAssignment() {
    const data = {
        action: 'assign_class_teacher',
        teacher_id: $('#assignTeacher').val(),
        class_id: $('#assignClass').val(),
        section_id: $('#assignSection').val()
    };
    
    if (!data.teacher_id || !data.class_id || !data.section_id) {
        showNotification('Please select teacher, class, and section', 'error');
        return;
    }
    
    $.ajax({
        url: 'teacher_management_api.php',
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                showNotification('Class teacher assigned successfully!', 'success');
                loadClassAssignments();
                // Reset form
                $('#assignClass, #assignSection, #assignTeacher').val('');
                $('#assignSection').prop('disabled', true);
            } else {
                showNotification(response.message || 'Failed to assign class teacher', 'error');
            }
        },
        error: function() {
            showNotification('An error occurred while assigning class teacher', 'error');
        }
    });
}

function loadClassAssignments() {
    $.ajax({
        url: 'teacher_management_api.php',
        method: 'GET',
        data: { action: 'get_class_assignments' },
        success: function(response) {
            if (response.success) {
                displayClassAssignments(response.data);
            }
        }
    });
}

function displayClassAssignments(assignments) {
    const container = $('#classAssignmentsTable');
    
    if (assignments.length === 0) {
        container.html('<p class="help-text">No class teacher assignments found</p>');
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Teacher</th>
                    <th>Employee ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    assignments.forEach(assignment => {
        html += `
            <tr>
                <td>${escapeHtml(assignment.class_name)}</td>
                <td>${escapeHtml(assignment.section_name)}</td>
                <td>${escapeHtml(assignment.teacher_name)}</td>
                <td>${escapeHtml(assignment.employee_number)}</td>
                <td class="actions">
                    <button class="btn btn-outline btn-sm" onclick="reassignClassTeacher(${assignment.section_id})">
                        <i class="fas fa-exchange-alt"></i> Reassign
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.html(html);
}

// Subject assignment functionality
function loadTeacherSubjects() {
    const teacherId = $('#subjectTeacher').val();
    
    if (!teacherId) {
        $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
        $('input[name="subjects[]"]').prop('checked', false);
        return;
    }
    
    $.ajax({
        url: 'teacher_management_api.php',
        method: 'GET',
        data: { action: 'get_teacher_subjects', teacher_id: teacherId },
        success: function(response) {
            if (response.success) {
                // Update checkboxes
                $('input[name="subjects[]"]').prop('checked', false);
                response.data.forEach(subjectId => {
                    $(`input[name="subjects[]"][value="${subjectId}"]`).prop('checked', true);
                });
                
                // Display current assignments
                displayTeacherSubjects(response.subjects_details || []);
            }
        }
    });
}

function displayTeacherSubjects(subjects) {
    const container = $('#subjectAssignmentsDisplay');
    
    if (subjects.length === 0) {
        container.html('<p class="help-text">No subjects assigned to this teacher</p>');
        return;
    }
    
    let html = '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
    subjects.forEach(subject => {
        html += `<span class="status-badge status-active">${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>`;
    });
    html += '</div>';
    
    container.html(html);
}

function handleSubjectAssignment() {
    const teacherId = $('#subjectTeacher').val();
    const selectedSubjects = $('input[name="subjects[]"]:checked').map(function() {
        return this.value;
    }).get();
    
    if (!teacherId) {
        showNotification('Please select a teacher', 'error');
        return;
    }
    
    $.ajax({
        url: 'teacher_management_api.php',
        method: 'POST',
        data: {
            action: 'update_subject_assignments',
            teacher_id: teacherId,
            subject_ids: JSON.stringify(selectedSubjects)
        },
        success: function(response) {
            if (response.success) {
                showNotification('Subject assignments updated successfully!', 'success');
                loadTeacherSubjects(); // Refresh display
                loadStatistics(); // Update statistics
            } else {
                showNotification(response.message || 'Failed to update subject assignments', 'error');
            }
        },
        error: function() {
            showNotification('An error occurred while updating subject assignments', 'error');
        }
    });
}

function clearSubjectSelections() {
    $('input[name="subjects[]"]').prop('checked', false);
}

function loadStatistics() {
    $.ajax({
        url: 'teacher_management_api.php',
        method: 'GET',
        data: { action: 'get_statistics' },
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#totalTeachers').text(stats.total_teachers);
                $('#activeTeachers').text(stats.active_teachers);
                $('#assignedClasses').text(stats.assigned_classes);
                $('#subjectAssignments').text(stats.subject_assignments);
            }
        }
    });
}

// Utility functions
function searchTeachers() {
    const query = $('#teacherSearch').val().toLowerCase();
    $('#teachersTableBody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.includes(query));
    });
}

function showNotification(message, type) {
    const notification = $('#notification');
    notification.removeClass('success error').addClass(type);
    notification.text(message);
    notification.show();
    
    setTimeout(() => {
        notification.fadeOut();
    }, 5000);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

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

// External functions for button clicks
function viewTeacherAssignments(teacherId) {
    // Switch to subject assignments tab and load teacher
    switchTab('subject-assignments');
    $('#subjectTeacher').val(teacherId);
    loadTeacherSubjects();
}

function reassignClassTeacher(sectionId) {
    // Implementation for reassigning class teacher
    showNotification('Please use the assignment form above to reassign the class teacher', 'error');
}
