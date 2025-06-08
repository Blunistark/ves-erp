<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Timetable</title>
    
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
            <h1 class="header-title">Create Timetable</h1>
            <span class="header-path">Dashboard > Timetable > Create</span>
        </header>

        <main class="dashboard-content">
            <!-- Information Box -->
            <div class="info-box">
                <svg xmlns="http://www.w3.org/2000/svg" class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="info-content">
                    <p>Create a new timetable by selecting the class, section, and academic year. Click on any cell to add or edit a subject period. The system will automatically detect and warn about any scheduling conflicts.</p>
                </div>
            </div>

            <!-- Timetable Configuration -->
            <div class="section-heading">
                <h2 class="section-title">Timetable Configuration</h2>
            </div>

                                    <div class="form-row">                <div class="form-group">                    <label for="academicYear" class="form-label required">Academic Year</label>                    <select id="academicYear" class="form-select">                        <option value="">Select Academic Year</option>                        <!-- Academic years will be loaded dynamically -->                    </select>                </div>                <div class="form-group">                    <label for="class" class="form-label required">Class</label>                    <select id="class" class="form-select">                        <option value="">Select Class</option>                        <!-- Classes will be loaded dynamically -->                    </select>                </div>                <div class="form-group">                    <label for="section" class="form-label required">Section</label>                    <select id="section" class="form-select" disabled>                        <option value="">Select Class First</option>                        <!-- Sections will be loaded dynamically based on class selection -->                    </select>                </div>                <div class="form-group">                    <label for="effectiveDate" class="form-label required">Effective From</label>                    <input type="date" id="effectiveDate" class="form-input">                </div>            </div>                        <div class="form-row">                <div class="form-group">                    <label for="description" class="form-label">Description</label>                    <input type="text" id="description" class="form-input" placeholder="Optional description for this timetable">                </div>                <div class="form-group">                    <label for="status" class="form-label required">Status</label>                    <select id="status" class="form-select">                        <option value="draft">Draft</option>                        <option value="published">Published</option>                    </select>                </div>            </div>

            <!-- Conflicts Warning (initially hidden, shown when conflicts detected) -->
            <div class="conflict-warning" id="conflictWarning" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="warning-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="warning-content">
                    <strong>Scheduling Conflicts Detected</strong>
                    <p>The following conflicts have been found in the timetable:</p>
                    <ul class="conflicts-list">
                        <li class="conflict-item">Mr. John Davis (Mathematics) is scheduled for Class 9-B at the same time on Monday, Period 3.</li>
                        <li class="conflict-item">Computer Lab is double-booked on Wednesday, Period 2.</li>
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
                   Publish Timetable
               </button>
           </div>
       </main>
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
       
       // Period editor modal elements
       const periodModal = document.getElementById('periodModal');
       const closeModal = document.getElementById('closeModal');
       const savePeriod = document.getElementById('savePeriod');
       const clearPeriod = document.getElementById('clearPeriod');
       const subjectSelect = document.getElementById('subject');
       const teacherSelect = document.getElementById('teacher');
       const notesInput = document.getElementById('notes');
       const periodTitle = document.getElementById('periodTitle');
       
       // Form elements
       const academicYearSelect = document.getElementById('academicYear');
       const classSelect = document.getElementById('class');
       const sectionSelect = document.getElementById('section');
       const effectiveDateInput = document.getElementById('effectiveDate');
       const descriptionInput = document.getElementById('description');
       const statusSelect = document.getElementById('status');
       const validateBtn = document.getElementById('validateBtn');
       
       // Initialize
       document.addEventListener('DOMContentLoaded', function() {
           console.log('DOM loaded, initializing timetable creation form...');
           
           // Set current date as default effective date
           const today = new Date();
           effectiveDateInput.value = today.toISOString().split('T')[0];
           
           // Set up event listeners
           closeModal.addEventListener('click', closePeriodEditor);
           savePeriod.addEventListener('click', savePeriodData);
           clearPeriod.addEventListener('click', clearPeriodData);
           
           classSelect.addEventListener('change', handleClassChange);
           academicYearSelect.addEventListener('change', updateTimetableData);
           sectionSelect.addEventListener('change', updateTimetableData);
           effectiveDateInput.addEventListener('change', updateTimetableData);
           descriptionInput.addEventListener('input', updateTimetableData);
           statusSelect.addEventListener('change', updateTimetableData);
           
           validateBtn.addEventListener('click', validateTimetable);
           
           // Save draft and publish buttons
           document.querySelector('.btn-outline:nth-of-type(2)').addEventListener('click', saveDraft);
           document.querySelector('.btn-primary').addEventListener('click', publishTimetable);
           
           // Preview button
           document.querySelector('.btn-outline:nth-of-type(1)').addEventListener('click', previewTimetable);
           
           // Load form data using Promise.all to ensure all data is loaded concurrently
           Promise.all([
               loadAcademicYears(),
               loadClasses(),
               loadSubjects(),
               loadTeachers()
           ]).then(() => {
               console.log('All dropdown data loaded successfully');
               updateTimetableData(); // Initialize timetable data with form values
           }).catch(error => {
               console.error('Error loading dropdown data:', error);
               alert('There was an error loading form data. Please refresh the page and try again.');
           });
       });
       
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
       
       // Validate timetable
       function validateTimetable() {
           // Update data from form
           updateTimetableData();
           
           // Check if required fields are filled
           if (!timetableData.academic_year_id || !timetableData.class_id || !timetableData.section_id || !timetableData.effective_date) {
               alert('Please fill all required fields (Academic Year, Class, Section, Effective Date)');
               return;
           }
           
           // Add timetable_id to data for validation, using 0 for new timetables
           const validationData = {
               ...timetableData,
               timetable_id: 0 // Use 0 for new timetables
           };
           
           // Check for conflicts
           axios.post('../../backend/api/timetables/validate', validationData)
               .then(response => {
                   const conflicts = response.data.conflicts;
                   
                   if (conflicts && conflicts.length > 0) {
                       // Show conflict warning
                       const conflictWarning = document.getElementById('conflictWarning');
                       const conflictsList = conflictWarning.querySelector('.conflicts-list');
                       
                       // Clear previous conflicts
                       conflictsList.innerHTML = '';
                       
                       // Add conflicts
                       conflicts.forEach(conflict => {
                           const li = document.createElement('li');
                           li.className = 'conflict-item';
                           li.textContent = conflict.message;
                           conflictsList.appendChild(li);
                       });
                       
                       conflictWarning.style.display = 'flex';
                   } else {
                       // Hide conflict warning
                       document.getElementById('conflictWarning').style.display = 'none';
                       
                       // Show success message
                       alert('Timetable validation passed! No conflicts found.');
                   }
               })
               .catch(error => {
                   console.error('Error validating timetable:', error);
                   alert('Error validating timetable. Please try again.');
               });
       }
       
       // Save as draft
       function saveDraft() {
           // Update data from form
           updateTimetableData();
           timetableData.status = 'draft';
           
           // Check if required fields are filled
           if (!timetableData.academic_year_id || !timetableData.class_id || !timetableData.section_id || !timetableData.effective_date) {
               alert('Please fill all required fields (Academic Year, Class, Section, Effective Date)');
               return;
           }
           
           saveTimetable();
       }
       
       // Publish timetable
       function publishTimetable() {
           // Update data from form
           updateTimetableData();
           timetableData.status = 'published';
           
           // Check if required fields are filled
           if (!timetableData.academic_year_id || !timetableData.class_id || !timetableData.section_id || !timetableData.effective_date) {
               alert('Please fill all required fields (Academic Year, Class, Section, Effective Date)');
               return;
           }
           
           // Optional: Add a confirmation for publishing
           if (confirm('Are you sure you want to publish this timetable? Published timetables are visible to students and teachers.')) {
               saveTimetable();
           }
       }
       
       // Save timetable
       function saveTimetable() {
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
               console.warn('Some periods have invalid data and will be removed');
           }
           
           // Log validated periods
           console.log('Validated periods:', validatedPeriods);
           
           // Replace periods with validated ones
           timetableData.periods = validatedPeriods;
           
           // Check for duplicate timetable by checking academic_year_id, class_id, section_id, and effective_date
           console.log(`Sending timetable data to API: ${JSON.stringify(timetableData)}`);
           
           axios.post('../../backend/api/timetables', timetableData)
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
                       
                       // Handle duplicate entry error
                       if (error.response.data.error.includes('Duplicate entry') && 
                           error.response.data.error.includes('uq_timetable')) {
                           errorMessage = 'A timetable already exists for this class, section, academic year, and effective date. Please modify one of these values.';
                       }
                   }
                   
                   alert(errorMessage);
               });
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
           
           // Open preview in new tab (would typically use a modal instead)
           const previewWindow = window.open('', '_blank');
           previewWindow.document.write(`
               <html>
               <head>
                   <title>Timetable Preview</title>
                   <style>
                       body { font-family: Arial, sans-serif; margin: 20px; }
                       h1 { color: #4f46e5; }
                       table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                       th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
                       th { background-color: #f9fafb; font-weight: 600; }
                       .time-slot { background-color: #f9fafb; font-weight: 500; }
                       .period { background-color: #eef2ff; padding: 8px; border-radius: 4px; }
                       .subject { font-weight: 500; }
                       .teacher { font-size: 12px; color: #6b7280; margin-top: 4px; }
                       .no-period { color: #9ca3af; font-style: italic; text-align: center; }
                   </style>
               </head>
               <body>
                   <h1>Timetable Preview</h1>
                   <div>
                       <p><strong>Class:</strong> ${classSelect.options[classSelect.selectedIndex].text}</p>
                       <p><strong>Section:</strong> ${sectionSelect.options[sectionSelect.selectedIndex].text}</p>
                       <p><strong>Academic Year:</strong> ${academicYearSelect.options[academicYearSelect.selectedIndex].text}</p>
                       <p><strong>Effective From:</strong> ${effectiveDateInput.value}</p>
                       ${descriptionInput.value ? `<p><strong>Description:</strong> ${descriptionInput.value}</p>` : ''}
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
                           ${generatePreviewTableRows()}
                       </tbody>
                   </table>
               </body>
               </html>
           `);
           previewWindow.document.close();
       }
       
       // Generate preview table rows
       function generatePreviewTableRows() {
           let rows = '';
           const periodRows = document.querySelectorAll('.timetable-grid tbody tr');
           
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
       
       // Open period editor
       function openPeriodEditor(cell) {
           currentCell = cell;
           const day = cell.dataset.day;
           const period = cell.dataset.period;
           
           // Format day and period for display
           const formattedDay = day.charAt(0).toUpperCase() + day.slice(1);
           periodTitle.textContent = `${formattedDay}, Period ${period}`;
           
           // Reset form
           subjectSelect.value = '';
           teacherSelect.value = '';
           notesInput.value = '';
           
           // If cell already has content, populate form with existing data
           if (cell.classList.contains('filled')) {
               const subjectBlock = cell.querySelector('.subject-block');
               if (subjectBlock) {
               const subjectName = subjectBlock.querySelector('.subject-name').textContent;
               const teacherName = subjectBlock.querySelector('.subject-teacher').textContent;
               
               // Find and select the subject based on name
               Array.from(subjectSelect.options).forEach(option => {
                       if (option.text === subjectName) {
                       subjectSelect.value = option.value;
                   }
               });
               
               // Find and select the teacher based on name
               Array.from(teacherSelect.options).forEach(option => {
                   if (option.text.includes(teacherName)) {
                       teacherSelect.value = option.value;
                   }
               });
               
                   // Get existing period data
                   const dayOfWeek = getDayOfWeekNumber(day);
                   const periodNumber = parseInt(period);
                   const existingPeriod = timetableData.periods.find(p => 
                       p.day_of_week === dayOfWeek && p.period_number === periodNumber);
                   
                   if (existingPeriod) {
                       notesInput.value = existingPeriod.notes || '';
                   }
               }
           }
           
           // Show modal
           periodModal.classList.add('show');
       }
       
       // Close period editor
       function closePeriodEditor() {
           periodModal.classList.remove('show');
           currentCell = null;
       }
       
       // Save period data
       function savePeriodData() {
           if (!currentCell) {
               console.error('No current cell selected');
               return;
           }
           
           const day = currentCell.dataset.day;
           const period = currentCell.dataset.period;
           
           if (!day || day.trim() === '') {
               console.error('Missing day value in cell dataset');
               alert('Error: This cell does not have a valid day value.');
               return;
           }
           
           const normalizedDay = day.toLowerCase().trim();
           const periodNumber = parseInt(period);
           
           // Ensure periodNumber is in valid range (1-8)
           if (isNaN(periodNumber) || periodNumber < 1 || periodNumber > 8) {
               console.error('Invalid period number in cell dataset:', period);
               alert('Error: This cell does not have a valid period number (must be 1-8).');
               return;
           }
           
           const subjectId = subjectSelect.value;
           const teacherId = teacherSelect.value;
           const notes = notesInput.value.trim();
           
           // Validate the day is one of the valid days
           const validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
           if (!validDays.includes(normalizedDay)) {
               console.error(`Invalid day name: ${normalizedDay}`);
               alert(`Invalid day name: ${normalizedDay}. Cannot save this period.`);
               return;
           }
           
           if (subjectId && teacherId) {
               // Get subject and teacher names
               const subjectName = subjectSelect.options[subjectSelect.selectedIndex].text;
               const teacherFullName = teacherSelect.options[teacherSelect.selectedIndex].text;
               const teacherName = teacherFullName.split(' (')[0];
               
               // Create or update subject block
               let subjectBlock = currentCell.querySelector('.subject-block');
               if (!subjectBlock) {
                   subjectBlock = document.createElement('div');
                   subjectBlock.className = 'subject-block';
                   currentCell.innerHTML = '';
                   currentCell.appendChild(subjectBlock);
               }
               
               subjectBlock.innerHTML = `
                   <span class="subject-name">${subjectName}</span>
                   <span class="subject-teacher">${teacherName}</span>
               `;
               
               currentCell.classList.add('filled');
               
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
               
               console.log('Saving period data:', {
                   day: normalizedDay,
                   period: periodNumber,
                   subject: subjectId,
                   teacher: teacherId
               });
               
               // Update or add period data
               const existingPeriodIndex = timetableData.periods.findIndex(p => 
                   p.day_of_week === normalizedDay && p.period_number === periodNumber);
               
               if (existingPeriodIndex !== -1) {
                   // Update existing period
                   timetableData.periods[existingPeriodIndex] = {
                       ...timetableData.periods[existingPeriodIndex],
                       day_of_week: normalizedDay,
                       period_number: periodNumber,
                       start_time: startTime,
                       end_time: endTime,
                       subject_id: subjectId,
                       teacher_id: teacherId,
                       notes: notes || null
                   };
               } else {
                   // Add new period
                   timetableData.periods.push({
                       day_of_week: normalizedDay,
                       period_number: periodNumber,
                       start_time: startTime,
                       end_time: endTime,
                       subject_id: subjectId,
                       teacher_id: teacherId,
                       notes: notes || null
                   });
               }
           } else {
               clearPeriodData();
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
           const period = currentCell.dataset.period;
           
           if (!day || day.trim() === '') {
               console.error('Missing day value in cell dataset');
               return;
           }
           
           const normalizedDay = day.toLowerCase().trim();
           const periodNumber = parseInt(period);
           
           if (isNaN(periodNumber)) {
               console.error('Invalid period number:', period);
               return;
           }
           
           // Remove period from timetableData
           timetableData.periods = timetableData.periods.filter(p => 
               !(p.day_of_week === normalizedDay && p.period_number === periodNumber));
           
           console.log(`Cleared period for ${normalizedDay}, period ${periodNumber}`);
           
           // Clear cell
           currentCell.innerHTML = '';
           currentCell.classList.remove('filled');
           
           closePeriodEditor();
       }
       
       // Helper: Get day of week string from name or number
       function getDayOfWeekString(day) {
           // Map of valid day strings
           const dayMap = {
               'monday': 'monday',
               'tuesday': 'tuesday',
               'wednesday': 'wednesday',
               'thursday': 'thursday',
               'friday': 'friday',
               'saturday': 'saturday',
               'sunday': 'sunday',
               '1': 'monday',
               '2': 'tuesday',
               '3': 'wednesday',
               '4': 'thursday',
               '5': 'friday',
               '6': 'saturday',
               '7': 'sunday'
           };
           
           // If it's a number, convert to string
           if (typeof day === 'number') {
               day = day.toString();
           }
           
           // If it's a string, normalize
           if (typeof day === 'string') {
               // Try direct lookup first (for both lowercase day names and numeric strings)
               if (dayMap[day.toLowerCase()]) {
                   return dayMap[day.toLowerCase()];
               }
               
               // Try parsing as number if it looks like a number
               if (!isNaN(parseInt(day))) {
                   const dayNumber = parseInt(day);
                   if (dayNumber >= 1 && dayNumber <= 7) {
                       return dayMap[dayNumber.toString()];
                   }
               }
           }
           
           console.warn(`Invalid day value: ${day}, defaulting to monday`);
           return 'monday';
       }
       
       // Helper: Get day name from number
       function getDayName(dayNumber) {
           const days = [
               'Monday',
               'Tuesday',
               'Wednesday',
               'Thursday',
               'Friday',
               'Saturday',
               'Sunday'
           ];
           
           return days[dayNumber - 1] || 'Monday';
       }
   </script>
</body>
</html>