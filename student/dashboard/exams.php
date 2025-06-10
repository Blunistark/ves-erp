<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Exam Timetable</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/exams.css">
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
        <h1 class="header-title">Exam Timetable</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="exam-selector">
            <div class="exam-option active">Final Exams</div>
            <div class="exam-option">Mid-Term Exams</div>
            <div class="exam-option">Unit Tests</div>
            <div class="exam-option">Practical Exams</div>
            <div class="exam-option">Mock Tests</div>
        </div>

        <div class="exam-info">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Exam Period</div>
                    <div class="info-value">May 15 - May 26, 2025</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Class</div>
                    <div class="info-value">Class X-A</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Reporting Time</div>
                    <div class="info-value">30 minutes before exam</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Results Date</div>
                    <div class="info-value">June 10, 2025</div>
                </div>
            </div>
            
            <div class="info-note">
                <strong>Important:</strong> Students must carry their school ID cards and admit cards to the examination hall. Electronic devices, including mobile phones, are strictly prohibited. Please ensure you have all necessary stationery items for your exams.
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Final Exams Schedule</h2>
            
            <div class="timetable-container">
                <table class="timetable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Time</th>
                            <th>Syllabus</th>
                            <th>Study Material</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Mathematics -->
                        <tr>
                            <td>
                                <div class="date-column">
                                    <div class="date-day">Monday</div>
                                    <div class="date-full">May 15, 2025</div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-column">
                                    <div class="subject-name">
                                        <div class="subject-icon math-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        Mathematics
                                    </div>
                                </div>
                            </td>
                            <td class="time-column">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                9:00 AM - 12:00 PM
                            </td>
                            <td class="syllabus-column">
                                <ul class="syllabus-list">
                                    <li>Algebra: Quadratic Equations, Polynomials</li>
                                    <li>Geometry: Circles, Triangles, Constructions</li>
                                    <li>Trigonometry: Heights and Distances</li>
                                    <li>Statistics: Mean, Median, Mode</li>
                                </ul>
                            </td>
                            <td class="download-column">
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Science -->
                        <tr>
                            <td>
                                <div class="date-column">
                                    <div class="date-day">Wednesday</div>
                                    <div class="date-full">May 17, 2025</div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-column">
                                    <div class="subject-name">
                                        <div class="subject-icon science-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                            </svg>
                                        </div>
                                        Science
                                    </div>
                                </div>
                            </td>
                            <td class="time-column">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                9:00 AM - 12:00 PM
                            </td>
                            <td class="syllabus-column">
                                <ul class="syllabus-list">
                                    <li>Physics: Light, Electricity, Magnetism</li>
                                    <li>Chemistry: Periodic Table, Chemical Reactions</li>
                                    <li>Biology: Human Body Systems, Heredity</li>
                                    <li>Environmental Science: Ecology, Pollution</li>
                                </ul>
                            </td>
                            <td class="download-column">
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        
                        <!-- English -->
                        <tr>
                            <td>
                                <div class="date-column">
                                    <div class="date-day">Friday</div>
                                    <div class="date-full">May 19, 2025</div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-column">
                                    <div class="subject-name">
                                        <div class="subject-icon language-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                            </svg>
                                        </div>
                                        English
                                    </div>
                                </div>
                            </td>
                            <td class="time-column">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                9:00 AM - 12:00 PM
                            </td>
                            <td class="syllabus-column">
                                <ul class="syllabus-list">
                                    <li>Literature: Shakespeare, Modern Poetry</li>
                                    <li>Grammar: Tenses, Reported Speech</li>
                                    <li>Writing: Essays, Letters, Applications</li>
                                    <li>Comprehension: Prose, Poetry</li>
                                </ul>
                            </td>
                            <td class="download-column">
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        
                        <!-- History -->
                        <tr>
                            <td>
                                <div class="date-column">
                                    <div class="date-day">Monday</div>
                                    <div class="date-full">May 22, 2025</div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-column">
                                    <div class="subject-name">
                                        <div class="subject-icon history-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        History
                                    </div>
                                </div>
                            </td>
                            <td class="time-column">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                9:00 AM - 12:00 PM
                            </td>
                            <td class="syllabus-column">
                                <ul class="syllabus-list">
                                    <li>Ancient Civilizations: Egypt, Mesopotamia</li>
                                    <li>Medieval History: Feudalism, Renaissance</li>
                                    <li>Modern History: Industrial Revolution</li>
                                    <li>World Wars I & II: Causes and Effects</li>
                                </ul>
                            </td>
                            <td class="download-column">
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Arts -->
                        <tr>
                            <td>
                                <div class="date-column">
                                    <div class="date-day">Wednesday</div>
                                    <div class="date-full">May 24, 2025</div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-column">
                                    <div class="subject-name">
                                        <div class="subject-icon arts-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </div>
                                        Arts
                                    </div>
                                </div>
                            </td>
                            <td class="time-column">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                9:00 AM - 12:00 PM
                            </td>
                            <td class="syllabus-column">
                                <ul class="syllabus-list">
                                    <li>Drawing: Perspective, Shading Techniques</li>
                                    <li>Painting: Color Theory, Watercolors</li>
                                    <li>Art History: Major Movements and Artists</li>
                                    <li>Practical: Still Life Composition</li>
                                </ul>
                            </td>
                            <td class="download-column">
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Physics -->
                        <tr>
                            <td>
                                <div class="date-column">
                                    <div class="date-day">Friday</div>
                                    <div class="date-full">May 26, 2025</div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-column">
                                    <div class="subject-name">
                                        <div class="subject-icon science-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        Physics
                                    </div>
                                </div>
                            </td>
                            <td class="time-column">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                9:00 AM - 12:00 PM
                            </td>
                            <td class="syllabus-column">
                                <ul class="syllabus-list">
                                    <li>Mechanics: Force, Motion, Energy</li>
                                    <li>Electricity: Circuits, Magnetism</li>
                                    <li>Optics: Light, Lenses, Reflection</li>
                                    <li>Modern Physics: Atoms, Radiation</li>
                                </ul>
                            </td>
                            <td class="download-column">
                                <a href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="calendar-header">
                <h3 class="calendar-title">May 2025</h3>
                <div class="calendar-nav">
                    <button class="calendar-nav-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="calendar-nav-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="calendar-grid">
                <div class="calendar-weekday">Sun</div>
                <div class="calendar-weekday">Mon</div>
                <div class="calendar-weekday">Tue</div>
                <div class="calendar-weekday">Wed</div>
                <div class="calendar-weekday">Thu</div>
                <div class="calendar-weekday">Fri</div>
                <div class="calendar-weekday">Sat</div>
                
                <!-- Week 1 -->
                <div class="calendar-day different-month">
                    <div class="day-number">30</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">1</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">2</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">3</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">4</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">5</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">6</div>
                </div>
                
                <!-- Week 2 -->
                <div class="calendar-day">
                    <div class="day-number">7</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">8</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">9</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">10</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">11</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">12</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">13</div>
                </div>
                
                <!-- Week 3 -->
                <div class="calendar-day">
                    <div class="day-number">14</div>
                </div>
                <div class="calendar-day has-exam">
                    <div class="day-number">15</div>
                    <div class="exam-marker">Mathematics</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">16</div>
                </div>
                <div class="calendar-day has-exam">
                    <div class="day-number">17</div>
                    <div class="exam-marker">Science</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">18</div>
                </div>
                <div class="calendar-day has-exam">
                    <div class="day-number">19</div>
                    <div class="exam-marker">English</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">20</div>
                </div>
                
                <!-- Week 4 -->
                <div class="calendar-day">
                    <div class="day-number">21</div>
                </div>
                <div class="calendar-day has-exam">
                    <div class="day-number">22</div>
                    <div class="exam-marker">History</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">23</div>
                </div>
                <div class="calendar-day has-exam">
                    <div class="day-number">24</div>
                    <div class="exam-marker">Arts</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">25</div>
                </div>
                <div class="calendar-day has-exam">
                    <div class="day-number">26</div>
                    <div class="exam-marker">Physics</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">27</div>
                </div>
                
                <!-- Week 5 -->
                <div class="calendar-day">
                    <div class="day-number">28</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">29</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">30</div>
                </div>
                <div class="calendar-day">
                    <div class="day-number">31</div>
                </div>
                <div class="calendar-day different-month">
                    <div class="day-number">1</div>
                </div>
                <div class="calendar-day different-month">
                    <div class="day-number">2</div>
                </div>
                <div class="calendar-day different-month">
                    <div class="day-number">3</div>
                </div>
            </div>
        </div>
        
        <div class="preparation-tips">
            <h3 class="tips-title">Exam Preparation Tips</h3>
            
            <ul class="tips-list">
                <li class="tips-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong>Create a Study Schedule</strong>: Plan your study time efficiently. Allocate more time to subjects you find challenging and break down topics into manageable segments.
                    </div>
                </li>
                
                <li class="tips-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong>Use Practice Papers</strong>: Solve previous years' question papers to understand the exam pattern and identify important topics. Time yourself to improve speed and accuracy.
                    </div>
                </li>
                
                <li class="tips-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong>Take Regular Breaks</strong>: Follow the Pomodoro technique - study for 25 minutes, then take a 5-minute break. After four cycles, take a longer break of 15-30 minutes.
                    </div>
                </li>
                
                <li class="tips-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong>Stay Healthy</strong>: Ensure you get enough sleep, eat nutritious food, and stay hydrated. Physical exercise can help reduce stress and improve concentration.
                    </div>
                </li>
                
                <li class="tips-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong>Form Study Groups</strong>: Collaborate with classmates to discuss difficult concepts. Teaching others can reinforce your own understanding of the subject.
                    </div>
                </li>
            </ul>
        </div>
    </main>
</div>

<script>
    // Handle exam selector
    document.querySelectorAll('.exam-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.exam-option').forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');
            
            // In a real app, you would load the corresponding exam details here
            document.querySelector('.card-title').textContent = this.textContent + ' Schedule';
        });
    });
    
    // Handle calendar navigation
    document.querySelectorAll('.calendar-nav-btn').forEach((button, index) => {
        button.addEventListener('click', function() {
            // In a real app, you would navigate to previous/next month
            const months = ['April 2025', 'May 2025', 'June 2025'];
            let currentMonth = document.querySelector('.calendar-title').textContent;
            let currentIndex = months.indexOf(currentMonth);
            
            if (index === 0 && currentIndex > 0) {
                // Previous month
                document.querySelector('.calendar-title').textContent = months[currentIndex - 1];
            } else if (index === 1 && currentIndex < months.length - 1) {
                // Next month
                document.querySelector('.calendar-title').textContent = months[currentIndex + 1];
            }
        });
    });
    
    // Handle study material download
    document.querySelectorAll('.download-column a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const subject = this.closest('tr').querySelector('.subject-name').textContent.trim();
            alert(`Downloading study material for ${subject}... (This would be a real download in a production environment)`);
        });
    });
</script>
</body>
</html>