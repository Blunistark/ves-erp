<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Timetable</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/createtimetable.css">
    
    <!-- Add axios for making API requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
            <h1 class="header-title">Edit Timetable</h1>
            <span class="header-path">Dashboard > Timetable > Edit</span>
        </header>

        <main class="dashboard-content">
            <!-- Information Box -->
            <div class="info-box">
                <svg xmlns="http://www.w3.org/2000/svg" class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="info-content">
                    <p>Edit an existing timetable by selecting the class, section, and academic year. Click on any cell to add or edit a subject period. The system will automatically detect and warn about any scheduling conflicts.</p>
                </div>
            </div>

            <!-- Timetable Configuration -->
            <div class="section-heading">
                <h2 class="section-title">Timetable Configuration</h2>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="academicYear" class="form-label required">Academic Year</label>
                    <select id="academicYear" class="form-select">
                        <option value="">Select Academic Year</option>
                        <!-- Academic years will be loaded dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="class" class="form-label required">Class</label>
                    <select id="class" class="form-select">
                        <option value="">Select Class</option>
                        <!-- Classes will be loaded dynamically -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="section" class="form-label required">Section</label>
                    <select id="section" class="form-select" disabled>
                        <option value="">Select Class First</option>
                        <!-- Sections will be loaded dynamically based on class selection -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="effectiveDate" class="form-label required">Effective From</label>
                    <input type="date" id="effectiveDate" class="form-input">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" id="description" class="form-input" placeholder="Optional description for this timetable">
                </div>
                <div class="form-group">
                    <label for="status" class="form-label required">Status</label>
                    <select id="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <!-- Conflicts Warning (initially hidden, shown when conflicts detected) -->
            <div class="conflict-warning" id="conflictWarning" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="warning-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="warning-content">
                    <strong>Scheduling Conflicts Detected</strong>
                    <p>The following conflicts have been found in the timetable:</p>
                    <ul class="conflicts-list">
                        <!-- Conflicts will be dynamically populated here -->
                    </ul>
                </div>
            </div>

            <!-- Timetable Grid -->
            <div class="section-heading">
                <h2 class="section-title">Weekly Schedule</h2>
                <div>
                    <button class="btn btn-outline" id="validateBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                        </svg>
                        Validate Schedule
                    </button>
                </div>
            </div>

            <div class="timetable-container">
                <table class="timetable-grid">
                    <thead>
                        <tr>
                            <th>Time/Day</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="time-slot">8:00 - 8:45<br>Period 1</td>
                            <td class="period-cell" data-day="monday" data-period="1" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="1" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="1" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="1" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="1" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="1" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">8:50 - 9:35<br>Period 2</td>
                            <td class="period-cell" data-day="monday" data-period="2" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="2" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="2" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="2" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="2" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="2" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">9:40 - 10:25<br>Period 3</td>
                            <td class="period-cell" data-day="monday" data-period="3" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="3" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="3" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="3" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="3" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="3" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">10:25 - 10:40<br>Break</td>
                            <td colspan="6" style="background: #f3f4f6; text-align: center; font-weight: 500;">Break Time</td>
                        </tr>
                        <tr>
                            <td class="time-slot">10:40 - 11:25<br>Period 4</td>
                            <td class="period-cell" data-day="monday" data-period="4" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="4" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="4" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="4" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="4" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="4" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">11:30 - 12:15<br>Period 5</td>
                            <td class="period-cell" data-day="monday" data-period="5" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="5" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="5" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="5" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="5" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="5" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">12:20 - 1:05<br>Period 6</td>
                            <td class="period-cell" data-day="monday" data-period="6" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="6" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="6" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="6" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="6" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="6" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">1:05 - 1:45<br>Lunch</td>
                            <td colspan="6" style="background: #f3f4f6; text-align: center; font-weight: 500;">Lunch Break</td>
                        </tr>
                        <tr>
                            <td class="time-slot">1:45 - 2:30<br>Period 7</td>
                            <td class="period-cell" data-day="monday" data-period="7" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="7" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="7" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="7" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="7" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="7" onclick="openPeriodEditor(this)"></td>
                        </tr>
                        <tr>
                            <td class="time-slot">2:35 - 3:20<br>Period 8</td>
                            <td class="period-cell" data-day="monday" data-period="8" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="tuesday" data-period="8" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="wednesday" data-period="8" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="thursday" data-period="8" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="friday" data-period="8" onclick="openPeriodEditor(this)"></td>
                            <td class="period-cell" data-day="saturday" data-period="8" onclick="openPeriodEditor(this)"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="btn-container">
                <button type="button" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview
                </button>
                <button type="button" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save as Draft
                </button>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Timetable
                </button>
            </div>

            <!-- Period Editor Modal -->
            <div class="modal-overlay" id="periodModal">
                <div class="modal-container">
                    <div class="modal-header">
                        <h3 class="modal-title">Edit Period: <span id="periodTitle">Monday, Period 3</span></h3>
                        <button class="close-modal" id="closeModal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-form-row">
                            <label for="subject" class="form-label required">Subject</label>
                            <select id="subject" class="form-select">
                                <option value="">Select Subject</option>
                                <!-- Subjects will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="modal-form-row">
                            <label for="teacher" class="form-label required">Teacher</label>
                            <select id="teacher" class="form-select">
                                <option value="">Select Teacher</option>
                                <!-- Teachers will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="modal-form-row">
                            <label for="notes" class="form-label">Notes</label>
                            <input type="text" id="notes" class="form-input" placeholder="Any special instructions or notes">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" id="clearPeriod">
                            <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Period
                        </button>
                        <button type="button" class="btn btn-primary" id="savePeriod">
                            <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Table -->
            <div class="section-heading">
                <h2 class="section-title">Preview Timetable</h2>
            </div>
            <table id="previewTable">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Period</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Preview table rows will be dynamically added here -->
                </tbody>
            </table>

            <!-- Timetable Actions -->
            <div class="section-heading">
                <h2 class="section-title">Timetable Actions</h2>
            </div>
            <button id="validateButton">Validate Timetable</button>
            <button id="saveButton">Save Timetable</button>
        </main>
    </div>

    <script>
        // Global variables
        let currentCell = null;
        let timetableData = {
            academic_year_id: null,
            class_id: null,
            section_id: null,
            effective_date: null,
            description: null,
            status: 'draft',
            periods: []
        };
        // Keep isEditMode and timetableId for editing
        let isEditMode = true;
        let timetableId = null;
        
        // Form elements
        let academicYearSelect, classSelect, sectionSelect, effectiveDateInput, descriptionInput, statusSelect;
        
        // Modal elements
        let subjectSelect, teacherSelect, notesInput, periodTitle, closeModal, savePeriod, clearPeriod;
        
        // Period editor modal elements
        const periodModal = document.getElementById('periodModal');
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements
            academicYearSelect = document.getElementById('academicYear');
            classSelect = document.getElementById('class');
            sectionSelect = document.getElementById('section');
            effectiveDateInput = document.getElementById('effectiveDate');
            descriptionInput = document.getElementById('description');
            statusSelect = document.getElementById('status');
            
            // Modal elements
            closeModal = document.getElementById('closeModal');
            savePeriod = document.getElementById('savePeriod');
            clearPeriod = document.getElementById('clearPeriod');
            subjectSelect = document.getElementById('subject');
            teacherSelect = document.getElementById('teacher');
            notesInput = document.getElementById('notes');
            periodTitle = document.getElementById('periodTitle');
            
            // Button elements
            const validateButton = document.getElementById('validateButton');
            const saveButton = document.getElementById('saveButton');
            
            // Keep check for edit mode and load data
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('id')) {
                timetableId = urlParams.get('id');
                console.log("Found timetable ID in URL:", timetableId);
                // isEditMode is already true
                // We'll load the timetable data after the dropdowns are loaded in the Promise.all above
            } else {
                // Redirect if no ID is provided for editing
                alert('No timetable ID provided for editing.');
                window.location.href = 'timetablemanage.php';
            }
            
            // Set up event listeners
            classSelect.addEventListener('change', handleClassChange);
            academicYearSelect.addEventListener('change', updateTimetableData);
            sectionSelect.addEventListener('change', updateTimetableData);
            effectiveDateInput.addEventListener('change', updateTimetableData);
            descriptionInput.addEventListener('input', updateTimetableData);
            statusSelect.addEventListener('change', updateTimetableData);
            
            // Modal event listeners
            closeModal.addEventListener('click', closePeriodEditor);
            savePeriod.addEventListener('click', savePeriodData);
            clearPeriod.addEventListener('click', clearPeriodData);
            
            // Button event listeners
            validateButton.addEventListener('click', validateTimetable);
            saveButton.addEventListener('click', saveTimetable);
            
            // Added buttons from timetable grid
            const validateBtn = document.getElementById('validateBtn');
            validateBtn.addEventListener('click', validateTimetable);
            
            // Preview, Save Draft, and Update Timetable buttons
            const previewButton = document.querySelector('.btn-container .btn-outline:nth-of-type(1)');
            const saveDraftButton = document.querySelector('.btn-container .btn-outline:nth-of-type(2)');
            const updateTimetableButton = document.querySelector('.btn-container .btn-primary');
            
            previewButton.addEventListener('click', previewTimetable);
            saveDraftButton.addEventListener('click', function() {
                // Save as draft
                timetableData.status = 'draft';
                saveTimetable();
            });
            updateTimetableButton.addEventListener('click', function() {
                // Change status to published and save
                timetableData.status = 'published';
                saveTimetable();
            });
            
            // Load form data - wait for data to be loaded before attempting to set values
            Promise.all([
                loadAcademicYears(),
                loadClasses(),
                loadSubjects(),
                loadTeachers()
            ]).then(() => {
                // If in edit mode, reload the timetable data after dropdown options are available
                if (isEditMode && timetableId) {
                    loadTimetableData(timetableId);
                }
            });
        });
        
        // Keep loadTimetableData function for editing
        function loadTimetableData(id) {
            console.log(`Loading timetable data for ID: ${id}`);
            
            return axios.get(`../../backend/api/timetables/${id}`)
                .then(response => {
                    // Check if data and timetable data exist in the response
                    if (response.data && response.data.data) {
                        const timetable = response.data.data;
                        
                        console.log('Loaded timetable data:', timetable);
                        
                        // Set form values - do this after verifying elements exist
                        if (academicYearSelect) {
                            // Find the academic year option with the matching value
                            let academicYearOption = Array.from(academicYearSelect.options).find(
                                option => option.value === timetable.academic_year_id.toString()
                            );
                            
                            if (academicYearOption) {
                                console.log(`Setting academic year to ${timetable.academic_year_id} (${academicYearOption.textContent})`);
                                academicYearSelect.value = timetable.academic_year_id;
                            } else {
                                console.warn(`Academic year option for ID ${timetable.academic_year_id} not found in select`);
                                // Try adding it dynamically if missing
                                const option = document.createElement('option');
                                option.value = timetable.academic_year_id;
                                option.textContent = `Academic Year ${timetable.academic_year_id}`;
                                academicYearSelect.appendChild(option);
                                academicYearSelect.value = timetable.academic_year_id;
                            }
                            academicYearSelect.disabled = true; // Make read-only in edit mode
                        } else {
                            console.error('academicYearSelect element not found');
                        }
                        
                        if (classSelect) {
                            // Find the class option with the matching value
                            let classOption = Array.from(classSelect.options).find(
                                option => option.value === timetable.class_id.toString()
                            );
                            
                            if (classOption) {
                                console.log(`Setting class to ${timetable.class_id} (${classOption.textContent})`);
                                classSelect.value = timetable.class_id;
                            } else {
                                console.warn(`Class option for ID ${timetable.class_id} not found in select`);
                                // Try adding it dynamically if missing
                                const option = document.createElement('option');
                                option.value = timetable.class_id;
                                option.textContent = `Class ${timetable.class_id}`;
                                classSelect.appendChild(option);
                                classSelect.value = timetable.class_id;
                            }
                            classSelect.disabled = true; // Make read-only in edit mode
                        } else {
                            console.error('classSelect element not found');
                        }
                        
                        // Make sure sections is loaded for the selected class
                        if (timetable.class_id) {
                            return loadSections(timetable.class_id)
                                .then(() => {
                                    if (sectionSelect) {
                                        // Find the section option with the matching value
                                        let sectionOption = Array.from(sectionSelect.options).find(
                                            option => option.value === timetable.section_id.toString()
                                        );
                                        
                                        if (sectionOption) {
                                            console.log(`Setting section to ${timetable.section_id} (${sectionOption.textContent})`);
                                            sectionSelect.value = timetable.section_id;
                                        } else {
                                            console.warn(`Section option for ID ${timetable.section_id} not found in select`);
                                            // Try adding it dynamically if missing
                                            const option = document.createElement('option');
                                            option.value = timetable.section_id;
                                            option.textContent = `Section ${timetable.section_id}`;
                                            sectionSelect.appendChild(option);
                                            sectionSelect.value = timetable.section_id;
                                        }
                                        sectionSelect.disabled = true; // Make read-only in edit mode
                                    } else {
                                        console.error('sectionSelect element not found');
                                    }
                                    
                                    // Now set the remaining fields after sections are loaded
                                    if (effectiveDateInput) {
                                        effectiveDateInput.value = timetable.effective_date;
                                        console.log(`Set effective date to ${timetable.effective_date}`);
                                    } else {
                                        console.error('effectiveDateInput element not found');
                                    }
                                    
                                    if (descriptionInput) {
                                        descriptionInput.value = timetable.description || '';
                                    }
                                    
                                    if (statusSelect) {
                                        statusSelect.value = timetable.status || 'draft';
                                    }
                                    
                                    // Update timetable data
                                    timetableData = {
                                        id: timetable.id, // Include timetable ID
                                        academic_year_id: timetable.academic_year_id,
                                        class_id: timetable.class_id,
                                        section_id: timetable.section_id,
                                        effective_date: timetable.effective_date,
                                        description: timetable.description,
                                        status: timetable.status,
                                        periods: []
                                    };
                                    
                                    console.log('Updated timetableData object:', timetableData);
                                    
                                    // Debug: Print all period cells
                                    const allCells = document.querySelectorAll('.period-cell');
                                    console.log(`Found ${allCells.length} period cells on page`);
                                    
                                    // Populate periods
                                    if (timetable.periods && Array.isArray(timetable.periods)) {
                                        console.log(`Processing ${timetable.periods.length} periods`);
                                        
                                        timetable.periods.forEach(period => {
                                            // Add to timetableData - normalize the period number to ensure it's in valid range
                                            // Skip completely invalid periods
                                            if (!period.day_of_week || period.day_of_week.trim() === '') {
                                                console.warn(`Skipping period with empty day_of_week:`, period);
                                                return; // Skip this period
                                            }
                                            
                                            const periodNumber = parseInt(period.period_number) || 1;
                                            if (periodNumber < 1 || periodNumber > 8) {
                                                console.warn(`Period number out of range (${periodNumber}), adjusting to valid range (1-8)`);
                                            }
                                            
                                            const normalizedPeriodNumber = Math.max(1, Math.min(8, periodNumber));
                                            const normalizedDay = period.day_of_week.toLowerCase().trim();
                                            
                                            // Ensure normalized day is valid
                                            const validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            if (!validDays.includes(normalizedDay)) {
                                                console.warn(`Invalid day_of_week (${normalizedDay}), skipping period:`, period);
                                                return; // Skip this period
                                            }
                                            
                                            const periodData = {
                                                id: period.id,
                                                day_of_week: normalizedDay,
                                                period_number: normalizedPeriodNumber,
                                                start_time: period.start_time,
                                                end_time: period.end_time,
                                                subject_id: period.subject_id,
                                                teacher_id: period.teacher_id,
                                                notes: period.notes || null
                                            };
                                            
                                            timetableData.periods.push(periodData);
                                            
                                            // Find and update the corresponding cell in UI
                                            const dayString = period.day_of_week.toLowerCase();
                                            
                                            console.log(`Looking for cell with [data-day="${dayString}"][data-period="${normalizedPeriodNumber}"]`);
                                            
                                            const cellSelector = `.period-cell[data-day="${dayString}"][data-period="${normalizedPeriodNumber}"]`;
                                            const targetCell = document.querySelector(cellSelector);
                                            
                                            if (targetCell) {
                                                console.log('Found matching cell:', targetCell);
                                                
                                                // Get subject and teacher names
                                                const subjectName = period.subject_name || '';
                                                const teacherName = period.teacher_name || '';
                                                
                                                // Update cell
                                                if (subjectName && teacherName) {
                                                    targetCell.classList.add('filled');
                                                    targetCell.innerHTML = `
                                                        <div class="subject-block">
                                                            <span class="subject-name">${subjectName}</span>
                                                            <span class="subject-teacher">${teacherName}</span>
                                                        </div>
                                                    `;
                                                }
                                            } else {
                                                console.warn(`Could not find cell for day=${dayString}, period=${normalizedPeriodNumber}`);
                                            }
                                        });
                                    }
                                    
                                    return timetableData;
                                })
                                .catch(error => {
                                    console.error('Error loading sections:', error);
                                    throw error;
                                });
                        }
                    } else {
                        // Handle cases where data is not in the expected format or timetable not found
                        console.error('Unexpected response format or timetable not found:', response);
                        alert('Failed to load timetable data for editing.');
                        // Redirect to manage page if timetable not found
                        window.location.href = 'timetablemanage.php';
                        throw new Error('Failed to load timetable data');
                    }
                })
                .catch(error => {
                    console.error('Error loading timetable:', error);
                    alert(`Error loading timetable: ${error.message || 'Unknown error'}. Please try again.`);
                    // Redirect to manage page on error
                    window.location.href = 'timetablemanage.php';
                    throw error;
                });
        }
        
        // Toggle sidebar
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

        // Load academic years
        function loadAcademicYears() {
            return axios.get('../../backend/api/academic-years')
                .then(response => {
                    const academicYears = response.data.data;
                    
                    if (!academicYearSelect) {
                        console.error('academicYearSelect element not found');
                        return;
                    }
                    
                    // Clear existing options except the first one
                    while (academicYearSelect.options.length > 1) {
                        academicYearSelect.remove(1);
                    }
                    
                    // Add academic years
                    academicYears.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year.id;
                        option.textContent = year.name;
                        academicYearSelect.appendChild(option);
                    });
                    
                    console.log('Academic years loaded successfully with', academicYears.length, 'items');
                    return academicYears;
                })
                .catch(error => {
                    console.error('Error loading academic years:', error);
                    return [];
                });
        }
        
        // Load classes
        function loadClasses() {
            return axios.get('../../backend/api/classes')
                .then(response => {
                    const classes = response.data.data;
                    
                    if (!classSelect) {
                        console.error('classSelect element not found');
                        return [];
                    }
                    
                    // Clear existing options except the first one
                    while (classSelect.options.length > 1) {
                        classSelect.remove(1);
                    }
                    
                    // Add classes
                    classes.forEach(cls => {
                        const option = document.createElement('option');
                        option.value = cls.id;
                        option.textContent = cls.name;
                        classSelect.appendChild(option);
                    });
                    
                    console.log('Classes loaded successfully with', classes.length, 'items');
                    return classes;
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    return [];
                });
        }
        
        // Load sections based on selected class
        function loadSections(classId) {
            if (!sectionSelect) {
                console.error('sectionSelect element not found');
                return Promise.reject(new Error('Section select element not found'));
            }
            
            return axios.get(`../../backend/api/classes/${classId}`)
                .then(response => {
                    const classData = response.data.data;
                    const sections = classData.sections;
                    
                    // Clear existing options
                    sectionSelect.innerHTML = '';
                    
                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Select Section';
                    sectionSelect.appendChild(defaultOption);
                    
                    // Add sections
                    sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        sectionSelect.appendChild(option);
                    });
                    
                    // Enable section select
                    sectionSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading sections:', error);
                    // Re-throw the error so the caller's catch block is also triggered
                    throw error;
                });
        }
        
        // Load subjects
        function loadSubjects() {
            if (!subjectSelect) {
                console.error('subjectSelect element not found');
                return Promise.resolve([]);
            }
            
            return axios.get('../../backend/api/subjects')
                .then(response => {
                    const subjects = response.data.data;
                    
                    // Clear existing options except the first one
                    while (subjectSelect.options.length > 1) {
                        subjectSelect.remove(1);
                    }
                    
                    // Add subjects
                    subjects.forEach(subject => {
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.textContent = subject.name;
                        subjectSelect.appendChild(option);
                    });
                    
                    console.log('Subjects loaded successfully with', subjects.length, 'items');
                    return subjects;
                })
                .catch(error => {
                    console.error('Error loading subjects:', error);
                    return [];
                });
        }
        
        // Load teachers
        function loadTeachers() {
            if (!teacherSelect) {
                console.error('teacherSelect element not found');
                return Promise.resolve([]);
            }
            
            return axios.get('../../backend/api/teachers')
                .then(response => {
                    const teachers = response.data.data;
                    
                    // Clear existing options except the first one
                    while (teacherSelect.options.length > 1) {
                        teacherSelect.remove(1);
                    }
                    
                    // Add teachers
                    teachers.forEach(teacher => {
                        const option = document.createElement('option');
                        option.value = teacher.user_id;
                        // Check if subjects exist and is an array before joining
                        const subjectsText = (teacher.subjects && Array.isArray(teacher.subjects))
                            ? ` (${teacher.subjects.map(sub => sub.name).join(', ')})`
                            : '';
                        option.textContent = `${teacher.full_name}${subjectsText}`;
                        teacherSelect.appendChild(option);
                    });
                    
                    console.log('Teachers loaded successfully with', teachers.length, 'items');
                    return teachers;
                })
                .catch(error => {
                    console.error('Error loading teachers:', error);
                    return [];
                });
        }
        
        // Handle class change
        function handleClassChange() {
            const classId = classSelect.value;
            
            if (classId) {
                loadSections(classId);
                timetableData.class_id = classId;
            } else {
                // Reset section select
                sectionSelect.innerHTML = '<option value="">Select Class First</option>';
                sectionSelect.disabled = true;
                timetableData.class_id = null;
                timetableData.section_id = null;
            }
        }
        
        // Update timetable data from form
        function updateTimetableData() {
            // Make sure all form elements exist before trying to access their values
            console.log("Updating timetable data from form elements");
            
            // Always preserve the timetable ID if we're in edit mode
            if (isEditMode && timetableId) {
                timetableData.id = timetableId;
            }
            
            // Check each element exists before getting its value
            if (academicYearSelect) {
                timetableData.academic_year_id = academicYearSelect.value || null;
                console.log(`Updated academic_year_id: ${timetableData.academic_year_id}`);
            } else {
                console.error("academicYearSelect not found when updating timetable data");
            }
            
            if (classSelect) {
                timetableData.class_id = classSelect.value || null;
                console.log(`Updated class_id: ${timetableData.class_id}`);
            } else {
                console.error("classSelect not found when updating timetable data");
            }
            
            if (sectionSelect) {
                timetableData.section_id = sectionSelect.value || null;
                console.log(`Updated section_id: ${timetableData.section_id}`);
            } else {
                console.error("sectionSelect not found when updating timetable data");
            }
            
            if (effectiveDateInput) {
                timetableData.effective_date = effectiveDateInput.value || null;
                console.log(`Updated effective_date: ${timetableData.effective_date}`);
            } else {
                console.error("effectiveDateInput not found when updating timetable data");
            }
            
            if (descriptionInput) {
                timetableData.description = descriptionInput.value || null;
            }
            
            // Always ensure status has a valid value
            if (statusSelect && statusSelect.value) {
                timetableData.status = statusSelect.value;
            } else {
                // Default to draft if not set
                timetableData.status = 'draft';
            }
            
            // Log the complete updated object
            console.log("Updated timetable data:", timetableData);
        }
        
        // Open period editor
        function openPeriodEditor(cell) {
            if (!periodModal || !subjectSelect || !teacherSelect || !notesInput || !periodTitle) {
                console.error('Period modal elements not found');
                return;
            }
            
            currentCell = cell;
            
            // Debug what's coming in from the cell
            console.log("Cell data:", {
                day: cell.dataset.day,
                period: cell.dataset.period,
                cellAttributes: Object.keys(cell.dataset)
            });
            
            const day = cell.dataset.day || '';
            const periodNumber = parseInt(cell.dataset.period) || 0;
            
            if (!day || day.trim() === '') {
                console.error('Missing day value in cell dataset');
                alert('Error: This cell does not have a valid day value.');
                return;
            }
            
            if (isNaN(periodNumber) || periodNumber <= 0) {
                console.error('Invalid period number in cell dataset:', cell.dataset.period);
                alert('Error: This cell does not have a valid period number.');
                return;
            }
            
            // Format day and period for display - just capitalize first letter to avoid getDayName issues
            const formattedDay = day.charAt(0).toUpperCase() + day.slice(1).toLowerCase();
            periodTitle.textContent = `${formattedDay}, Period ${periodNumber}`;
            
            // Reset form
            subjectSelect.value = '';
            teacherSelect.value = '';
            notesInput.value = '';
            
            // Find the corresponding period data in timetableData
            const existingPeriod = timetableData.periods.find(p => 
                p.day_of_week.toLowerCase() === day.toLowerCase() && p.period_number === periodNumber
            );
            
            // If period data exists, populate the form
            if (existingPeriod) {
                subjectSelect.value = existingPeriod.subject_id || '';
                teacherSelect.value = existingPeriod.teacher_id || '';
                notesInput.value = existingPeriod.notes || '';
            }
            
            // Show modal
            periodModal.classList.add('show');
        }
        
        // Close period editor
        function closePeriodEditor() {
            if (!periodModal) {
                console.error('Period modal element not found');
                return;
            }
            
            periodModal.classList.remove('show');
            currentCell = null;
        }
        
        // Save period data
        function savePeriodData() {
            if (!currentCell || !subjectSelect || !teacherSelect || !notesInput) {
                console.error('Required elements for saving period data not found');
                return;
            }
            
            const day = currentCell.dataset.day || '';
            const periodNumber = parseInt(currentCell.dataset.period) || 0;
            
            if (!day || day.trim() === '') {
                console.error('Missing day value in cell dataset');
                alert('Error: This cell does not have a valid day value.');
                return;
            }
            
            // Ensure periodNumber is in valid range (1-8)
            if (isNaN(periodNumber) || periodNumber < 1 || periodNumber > 8) {
                console.error('Invalid period number in cell dataset:', currentCell.dataset.period);
                alert('Error: This cell does not have a valid period number (must be 1-8).');
                return;
            }
            
            const subjectId = subjectSelect.value;
            const teacherId = teacherSelect.value;
            const notes = notesInput.value.trim();
            
            // Get subject and teacher names for display
            const subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;
            const teacherFullName = teacherSelect.options[teacherSelect.selectedIndex].text;
            const teacherName = teacherFullName.split(' (')[0];
            
            // Get time slots from row
            const row = currentCell.parentElement;
            const timeSlot = row.querySelector('.time-slot').innerText;
            const timeMatch = timeSlot.match(/(\d+:\d+)\s*-\s*(\d+:\d+)/);
            let startTime = '08:00:00';  // Default values if not found
            let endTime = '08:45:00';    // Default values if not found
            
            if (timeMatch) {
                startTime = timeMatch[1] + ':00';
                endTime = timeMatch[2] + ':00';
            }

            // Find existing period index
            const existingPeriodIndex = timetableData.periods.findIndex(p => 
                p.day_of_week.toLowerCase() === day.toLowerCase() && p.period_number === periodNumber
            );

            if (subjectId && teacherId) {
                // Ensure day_of_week is a proper value from the enum in the database
                const validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                const normalizedDay = day.toLowerCase();
                
                if (!validDays.includes(normalizedDay)) {
                    console.error(`Invalid day name: ${day}`);
                    alert(`Invalid day name: ${day}. Cannot save this period.`);
                    return;
                }
                
                // Double-check that we have a valid period number between 1-8
                if (periodNumber < 1 || periodNumber > 8) {
                    console.error(`Invalid period number: ${periodNumber}`);
                    alert(`Invalid period number: ${periodNumber}. Must be between 1-8.`);
                    return;
                }
                
                // Log what we're about to save
                console.log('Saving period data:', {
                    day: normalizedDay,
                    period: periodNumber,
                    subject: subjectId,
                    teacher: teacherId
                });
                
                const periodData = {
                    day_of_week: normalizedDay,
                    period_number: periodNumber,
                    start_time: startTime,
                    end_time: endTime,
                    subject_id: subjectId,
                    teacher_id: teacherId,
                    notes: notes || null
                };

                if (existingPeriodIndex !== -1) {
                    // Update existing period
                    // Keep the existing period ID if it exists
                    if (timetableData.periods[existingPeriodIndex].id) {
                        periodData.id = timetableData.periods[existingPeriodIndex].id;
                    }
                    timetableData.periods[existingPeriodIndex] = periodData;
                } else {
                    // Add new period
                    timetableData.periods.push(periodData);
                }

                // Update cell display
                currentCell.classList.add('filled');
                currentCell.innerHTML = `
                    <div class="subject-block">
                        <span class="subject-name">${subjectName}</span>
                        <span class="subject-teacher">${teacherName}</span>
                    </div>
                `;
            } else {
                // If subject or teacher is not selected, clear the period
                if (existingPeriodIndex !== -1) {
                    // Remove period from timetableData
                    timetableData.periods.splice(existingPeriodIndex, 1);
                }
                // Clear cell display
                currentCell.innerHTML = '';
                currentCell.classList.remove('filled');
            }
            
            closePeriodEditor();
        }
        
        // Clear period data
        function clearPeriodData() {
            if (!currentCell) {
                console.error('No current cell selected');
                return;
            }
            
            const day = currentCell.dataset.day;
            const periodNumber = parseInt(currentCell.dataset.period);
            
            // Find existing period index
            const existingPeriodIndex = timetableData.periods.findIndex(p => 
                p.day_of_week.toLowerCase() === day.toLowerCase() && p.period_number === periodNumber
            );
            
            // Remove period from timetableData if it exists
            if (existingPeriodIndex !== -1) {
                timetableData.periods.splice(existingPeriodIndex, 1);
            }
            
            // Clear cell display
            currentCell.innerHTML = '';
            currentCell.classList.remove('filled');
            
            closePeriodEditor();
        }
        
        // Validate timetable
        function validateTimetable() {
            // Update data from form
            updateTimetableData();
            
            // Check if required fields are filled
            if (!timetableData.academic_year_id || !timetableData.class_id || !timetableData.section_id || !timetableData.effective_date) {
                alert('Please fill all required fields (Academic Year, Class, Section, Effective Date)');
                return;
            }
            
            // Add timetable_id to data for validation
            const validationData = {
                ...timetableData,
                timetable_id: timetableId // Use actual timetableId for validation
            };
            
            // Check for conflicts
            axios.post('../../backend/api/timetables/validate', validationData)
                .then(response => {
                    const conflicts = response.data.conflicts;
                    // Implementation needed
                })
                .catch(error => {
                    console.error('Error validating timetable:', error);
                    alert('Error validating timetable. Please try again.');
                });
        }
        
        // Save timetable
        function saveTimetable() {
            // Update timetable data from form
            updateTimetableData();
            
            // Check if required fields are filled
            if (!timetableData.academic_year_id || !timetableData.class_id || !timetableData.section_id || !timetableData.effective_date) {
                alert('Please fill all required fields (Academic Year, Class, Section, Effective Date)');
                return;
            }
            
            // Ensure status is valid
            if (!timetableData.status || timetableData.status.trim() === '') {
                timetableData.status = 'draft'; // Default to draft
            }
            
            // Validate and filter out invalid periods
            let hasInvalidPeriods = false;
            const validatedPeriods = [];
            
            // Log all periods before validation
            console.log('Periods before validation:', JSON.stringify(timetableData.periods));
            
            timetableData.periods.forEach((period, index) => {
                console.log(`Validating period ${index}:`, period);
                
                // Skip periods with empty day or invalid period number
                if (!period.day_of_week || period.day_of_week.trim() === '') {
                    console.error(`Period ${index} has empty day_of_week:`, period);
                    hasInvalidPeriods = true;
                    return;
                }
                
                // Ensure period_number is valid (1-8)
                const periodNumber = parseInt(period.period_number);
                if (isNaN(periodNumber) || periodNumber < 1 || periodNumber > 8) {
                    console.error(`Period ${index} has invalid period_number:`, period);
                    hasInvalidPeriods = true;
                    return;
                }
                
                // Ensure day_of_week is one of the enum values in the database
                const validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                const normalizedDay = period.day_of_week.toLowerCase();
                
                if (!validDays.includes(normalizedDay)) {
                    console.error(`Period ${index} has invalid day_of_week:`, period);
                    hasInvalidPeriods = true;
                    return;
                }
                
                // Ensure all required fields have values
                if (!period.start_time || !period.end_time || !period.subject_id || !period.teacher_id) {
                    console.error(`Period ${index} is missing required data:`, period);
                    hasInvalidPeriods = true;
                    return;
                }
                
                // Add the validated period
                validatedPeriods.push({
                    ...(period.id ? { id: period.id } : {}),
                    day_of_week: normalizedDay,
                    period_number: periodNumber,
                    subject_id: parseInt(period.subject_id),
                    teacher_id: parseInt(period.teacher_id),
                    start_time: period.start_time,
                    end_time: period.end_time,
                    notes: period.notes || null
                });
            });
            
            if (hasInvalidPeriods) {
                alert('Some periods have invalid data. Please check the console for details and try again.');
                return;
            }
            
            // Log validated periods
            console.log('Validated periods:', validatedPeriods);
            
            // Replace periods with validated ones
            timetableData.periods = validatedPeriods;
            
            const url = `../../backend/api/timetables/${timetableId}`;
            const method = 'put'; // Always PUT for editing
            
            console.log('Saving timetable data:', JSON.stringify(timetableData, null, 2));
            
            axios({
                method,
                url,
                data: timetableData
            })
                .then(response => {
                    alert('Timetable saved successfully!');
                    window.location.href = 'timetablemanage.php';
                })
                .catch(error => {
                    console.error('Error saving timetable:', error);
                    let errorMessage = 'Error saving timetable. Please try again.';
                    
                    // Extract more detailed error if available
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMessage += ' Details: ' + error.response.data.error;
                        console.error('Server error details:', error.response.data);
                    }
                    
                    alert(errorMessage);
                });
        }
        
        // Generate preview table rows
        function generatePreviewTableRows() {
            let rows = '';
            const timetableGrid = document.querySelector('.timetable-grid'); // Select the timetable grid
            const periodRows = timetableGrid.querySelectorAll('tbody tr'); // Get rows from the grid tbody
            
            periodRows.forEach(row => {
                if (row.querySelector('.time-slot')) {
                    const timeSlot = row.querySelector('.time-slot').innerText;
                    
                    // Skip breaks
                    if (timeSlot.includes('Break') || timeSlot.includes('Lunch')) {
                        rows += `
                            <tr>
                                <td class="time-slot">${timeSlot}</td>
                                <td colspan="6" style="background-color: #f3f4f6; text-align: center;">${timeSlot.includes('Break') ? 'Break Time' : 'Lunch Break'}</td>
                            </tr>
                        `;
                        return;
                    }
                    
                    const periodCells = row.querySelectorAll('.period-cell');
                    if (periodCells.length > 0) {
                        rows += '<tr>';
                        rows += `<td class="time-slot">${timeSlot}</td>`;
                        
                        periodCells.forEach(cell => {
                            if (cell.classList.contains('filled')) {
                                const subjectName = cell.querySelector('.subject-name').textContent;
                                const teacherName = cell.querySelector('.subject-teacher').textContent;
                                
                                rows += `
                                    <td>
                                        <div class="period">
                                            <div class="subject">${subjectName}</div>
                                            <div class="teacher">${teacherName}</div>
                                        </div>
                                    </td>
                                `;
                            } else {
                                rows += '<td class="no-period">No Class</td>';
                            }
                        });
                        
                        rows += '</tr>';
                    }
                }
            });
            
            return rows;
        }
        
        // Preview timetable
        function previewTimetable() {
            // Update data from form
            updateTimetableData();
            
            // Check if required fields are filled
            if (!timetableData.academic_year_id || !timetableData.class_id || !timetableData.section_id || !timetableData.effective_date) {
                alert('Please fill all required fields (Academic Year, Class, Section, Effective Date)');
                return;
            }
            
            // Create a preview window with timetable data
            const previewWindow = window.open('', '_blank');
            
            // Get class and section names
            const className = document.querySelector('#class option:checked').textContent;
            const sectionName = document.querySelector('#section option:checked').textContent;
            
            // Build HTML content
            let content = `
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Timetable Preview - ${className} ${sectionName}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .school-name { font-size: 24px; font-weight: bold; }
                        .timetable-title { font-size: 18px; margin: 10px 0; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        th { background-color: #f2f2f2; }
                        .period-cell { min-height: 60px; }
                        .subject-name { font-weight: bold; display: block; }
                        .subject-teacher { font-size: 12px; color: #666; }
                        .break-row { background-color: #f9f9f9; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="school-name">School Name</div>
                        <div class="timetable-title">Timetable for ${className} ${sectionName}</div>
                        <div>Effective from: ${timetableData.effective_date}</div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Time/Day</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // Get the timetable grid to copy its structure
            const timeSlots = document.querySelectorAll('.time-slot');
            const periodCells = document.querySelectorAll('.period-cell');
            
            // Keep track of row index
            let currentRow = 0;
            let cellIndex = 0;
            
            // For each time slot
            timeSlots.forEach((slot, index) => {
                const timeText = slot.textContent;
                
                // Check if this is a break row
                if (timeText.includes('Break') || timeText.includes('Lunch')) {
                    content += `
                        <tr class="break-row">
                            <td>${timeText}</td>
                            <td colspan="6">Break Time</td>
                        </tr>
                    `;
                    return; // Skip to next iteration
                }
                
                content += `<tr><td>${timeText}</td>`;
                
                // Add cells for each day of the week (6 days)
                for (let day = 0; day < 6; day++) {
                    if (cellIndex < periodCells.length) {
                        const cell = periodCells[cellIndex];
                        content += `<td>${cell.innerHTML}</td>`;
                        cellIndex++;
                    } else {
                        content += '<td></td>';
                    }
                }
                
                content += '</tr>';
                currentRow++;
            });
            
            content += `
                        </tbody>
                    </table>
                    
                    <div class="footer">
                        <p>This is a preview of the timetable. The actual timetable may include additional information.</p>
                    </div>
                </body>
                </html>
            `;
            
            previewWindow.document.write(content);
            previewWindow.document.close();
        }
        
        // Helper: Get day name from number or convert day name to standard format
        function getDayName(day) {
            // Days array
            const days = [
                'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
            ];
            
            // If day is a number string, convert to integer
            if (typeof day === 'string' && !isNaN(day)) {
                day = parseInt(day);
            }
            
            // If day is a number, convert to day name
            if (typeof day === 'number') {
                if (day >= 1 && day <= days.length) {
                    return days[day - 1];
                } else {
                    console.warn(`Invalid day number: ${day}`);
                    return 'Unknown Day';
                }
            }
            
            // If day is a string (like "monday"), capitalize first letter
            if (typeof day === 'string') {
                // Convert to lowercase first to handle any capitalization
                const dayLower = day.toLowerCase();
                
                // Check if it's a valid day name
                const dayIndex = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].indexOf(dayLower);
                if (dayIndex !== -1) {
                    return days[dayIndex];
                } else {
                    console.warn(`Invalid day name: ${day}`);
                    return 'Unknown Day';
                }
            }
            
            console.warn('Invalid day input:', day);
            return 'Unknown Day';
        }
    </script>
</body>
</html>