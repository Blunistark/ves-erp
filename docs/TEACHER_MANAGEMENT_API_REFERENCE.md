# Teacher Management API Reference

This document provides a comprehensive reference for all available API endpoints in the `teacher_management_api.php` file.

## Base Information

- **File Location**: `teachers/dashboard/teacher_management_api.php`
- **Authentication**: Requires admin or headmaster role
- **Content Type**: Returns JSON responses
- **HTTP Methods**: POST and GET (varies by endpoint)

## API Endpoints

### 1. Teacher Information Management

#### Get Teacher Details
**Endpoint**: `action=get_teacher_details`
**Method**: GET/POST
**Parameters**:
- `teacher_id` (required): ID of the teacher

**Response**: Returns comprehensive teacher information including:
- Basic teacher info (name, email, status, etc.)
- Assigned subjects with classes/sections
- Class teacher assignments
- Timetable/schedule organized by days
- Workload statistics

```javascript
// Example usage
fetch('teacher_management_api.php?action=get_teacher_details&teacher_id=123')
    .then(response => response.json())
    .then(data => console.log(data));
```

#### Get All Teachers
**Endpoint**: `action=get_teachers`
**Method**: GET/POST
**Parameters**: None required

**Response**: Returns list of all teachers with basic information

#### Get Available Teachers
**Endpoint**: `action=get_available_teachers`
**Method**: GET/POST
**Parameters**: None required

**Response**: Returns list of active teachers available for assignments

#### Add Teacher
**Endpoint**: `action=add_teacher`
**Method**: POST
**Parameters**:
- Teacher details (full_name, email, etc.)

#### Update Teacher
**Endpoint**: `action=update_teacher`
**Method**: POST
**Parameters**:
- `teacher_id` (required)
- Updated teacher fields

#### Update Teacher Status
**Endpoint**: `action=update_teacher_status`
**Method**: POST
**Parameters**:
- `teacher_id` (required)
- `status` (required): new status value

#### Delete Teacher
**Endpoint**: `action=delete_teacher`
**Method**: POST
**Parameters**:
- `teacher_id` (required)

### 2. Subject Assignment Management

#### Get Teacher Subjects
**Endpoint**: `action=get_teacher_subjects`
**Method**: GET/POST
**Parameters**:
- `teacher_id` (required): ID of the teacher

**Response**: Returns subjects assigned to the teacher

#### Update Subject Assignments
**Endpoint**: `action=update_subject_assignments`
**Method**: POST
**Parameters**:
- `teacher_id` (required): ID of the teacher
- `subject_ids` (required): JSON array of subject IDs

```javascript
// Example usage
const formData = new FormData();
formData.append('action', 'update_subject_assignments');
formData.append('teacher_id', '123');
formData.append('subject_ids', JSON.stringify([1, 2, 3]));

fetch('teacher_management_api.php', {
    method: 'POST',
    body: formData
});
```

#### Get All Subject Assignments
**Endpoint**: `action=get_all_subject_assignments`
**Method**: GET/POST

#### Remove Subject Assignment
**Endpoint**: `action=remove_subject_assignment`
**Method**: POST
**Parameters**:
- Assignment details for removal

### 3. Class Teacher Management

#### Assign Class Teacher
**Endpoint**: `action=assign_class_teacher`
**Method**: POST
**Parameters**:
- `teacher_id` (required)
- `section_id` (required)

#### Get Class Assignments
**Endpoint**: `action=get_class_assignments`
**Method**: GET/POST

#### Reassign Class Teacher
**Endpoint**: `action=reassign_class_teacher`
**Method**: POST
**Parameters**:
- `section_id` (required)
- `teacher_id` (required)
- `reason` (optional): reason for reassignment

#### Remove Class Teacher
**Endpoint**: `action=remove_class_teacher`
**Method**: POST
**Parameters**:
- Class teacher assignment details

### 4. Timetable Management

#### Get Teacher Timetables
**Endpoint**: `action=get_teacher_timetables`
**Method**: GET/POST
**Parameters**:
- `teacher_id` (optional): specific teacher ID

**Response**: Returns timetable information for teacher(s)

#### Get Teacher Schedules
**Endpoint**: `action=get_teacher_schedules`
**Method**: GET/POST

**Response**: Returns all teachers with their weekly schedules

#### Get Teacher Schedule (Individual)
**Endpoint**: `action=get_teacher_schedule`
**Method**: GET/POST
**Parameters**:
- `teacher_id` (required)

#### Save Teacher Period
**Endpoint**: `action=save_teacher_period`
**Method**: POST
**Content-Type**: application/json
**Parameters** (JSON body):
- `teacher_id` (required)
- `day_of_week` (required): e.g., 'monday', 'tuesday'
- `period_number` (required): numeric period number
- `start_time` (required): e.g., '09:00'
- `end_time` (required): e.g., '10:00'
- `subject_id` (required)
- `class_id` (required)
- `section_id` (required)
- `notes` (optional)

```javascript
// Example usage
const periodData = {
    teacher_id: 123,
    day_of_week: 'monday',
    period_number: 1,
    start_time: '09:00',
    end_time: '10:00',
    subject_id: 5,
    class_id: 2,
    section_id: 3,
    notes: 'Regular class'
};

fetch('teacher_management_api.php?action=save_teacher_period', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(periodData)
});
```

#### Update Teacher Period
**Endpoint**: `action=update_teacher_period`
**Method**: POST
**Parameters**:
- Period details for update

#### Delete Teacher Period
**Endpoint**: `action=delete_teacher_period`
**Method**: POST
**Content-Type**: application/json
**Parameters** (JSON body):
- `teacher_id` (required)
- `day_of_week` (required)
- `period_number` (required)

```javascript
// Example usage
const deleteData = {
    teacher_id: 123,
    day_of_week: 'monday',
    period_number: 1
};

fetch('teacher_management_api.php?action=delete_teacher_period', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(deleteData)
});
```

### 5. Utility and Support Functions

#### Get Sections
**Endpoint**: `action=get_sections`
**Method**: GET/POST
**Parameters**:
- `class_id` (optional): filter by specific class

**Response**: Returns available sections, optionally filtered by class

#### Get Subjects
**Endpoint**: `action=get_subjects`
**Method**: GET/POST

**Response**: Returns all available subjects

#### Get Classes with Sections
**Endpoint**: `action=get_classes_with_sections`
**Method**: GET/POST

**Response**: Returns classes with their sections

#### Get Statistics
**Endpoint**: `action=get_statistics`
**Method**: GET/POST

**Response**: Returns teacher management statistics

#### Get Teacher Workload
**Endpoint**: `action=get_teacher_workload`
**Method**: GET/POST
**Parameters**:
- `teacher_id` (required)

**Response**: Returns workload information for the teacher

#### Get Teachers with Workload
**Endpoint**: `action=get_teachers_with_workload`
**Method**: GET/POST

**Response**: Returns all teachers with their workload information

### 6. Conflict Management

#### Check Conflicts
**Endpoint**: `action=check_conflicts`
**Method**: GET/POST

#### Get Timetable Conflicts
**Endpoint**: `action=get_timetable_conflicts`
**Method**: GET/POST

#### Check Teacher Conflicts
**Endpoint**: `action=check_teacher_conflicts`
**Method**: GET/POST
**Parameters**:
- Teacher and schedule details

#### Auto Resolve Conflicts
**Endpoint**: `action=auto_resolve_conflicts`
**Method**: POST

#### Resolve Single Conflict
**Endpoint**: `action=resolve_single_conflict`
**Method**: POST

### 7. Advanced Features

#### Get Available Slots
**Endpoint**: `action=get_available_slots`
**Method**: GET/POST
**Parameters**:
- Slot search criteria

#### Get Published Timetables
**Endpoint**: `action=get_published_timetables`
**Method**: GET/POST

#### Get Class Timetable Status
**Endpoint**: `action=get_class_timetable_status`
**Method**: GET/POST

## Common Response Format

All endpoints return JSON responses with the following structure:

```json
{
    "success": true/false,
    "message": "Description of result",
    "data": {
        // Response data (varies by endpoint)
    }
}
```

## Error Handling

All endpoints include comprehensive error handling and return appropriate error messages:

```json
{
    "success": false,
    "message": "Error description"
}
```

## Authentication Requirements

- All endpoints require authentication
- User must have 'admin' or 'headmaster' role
- Session must be active and valid

## Usage Examples

### Complete Teacher Details Integration

```javascript
// Get comprehensive teacher details
async function loadTeacherDetails(teacherId) {
    try {
        const response = await fetch(`teacher_management_api.php?action=get_teacher_details&teacher_id=${teacherId}`);
        const result = await response.json();
        
        if (result.success) {
            const { teacher, subjects, class_assignments, timetable, workload } = result.data;
            
            // Display teacher information
            displayTeacherInfo(teacher);
            displaySubjects(subjects);
            displayClassAssignments(class_assignments);
            displayTimetable(timetable);
            displayWorkload(workload);
        } else {
            console.error('Error:', result.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
    }
}

// Update subject assignments
async function updateSubjectAssignments(teacherId, subjectIds) {
    const formData = new FormData();
    formData.append('action', 'update_subject_assignments');
    formData.append('teacher_id', teacherId);
    formData.append('subject_ids', JSON.stringify(subjectIds));

    try {
        const response = await fetch('teacher_management_api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            console.log('Subjects updated successfully');
            // Reload teacher details
            loadTeacherDetails(teacherId);
        } else {
            console.error('Update failed:', result.message);
        }
    } catch (error) {
        console.error('Update error:', error);
    }
}
```

This API provides comprehensive functionality for managing all aspects of teacher information, assignments, and timetables within the ERP system.
