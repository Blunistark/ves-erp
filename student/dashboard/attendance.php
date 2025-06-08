<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Attendance Tracker</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/attendance.css">
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
        <h1 class="header-title">Attendance Tracker</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="card">
            <h2 class="card-title">Your Attendance Summary</h2>
            
            <div class="attendance-stats">
                <div class="stat-card">
                    <div class="stat-title">Present Days</div>
                    <div class="stat-value">18 / 22</div>
                    <div class="stat-progress">
                        <div class="stat-progress-bar progress-present" style="width: 82%;"></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Absent Days</div>
                    <div class="stat-value">0</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Attendance Percentage</div>
                    <div class="stat-value">82%</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Required Minimum</div>
                    <div class="stat-value">75%</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Monthly Attendance</h2>
            
            <div class="calendar-container">
                <table class="attendance-calendar">
                    <thead>
                        <tr>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                            <th>Sunday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="attendance-present">1</td>
                            <td class="attendance-present">2</td>
                            <td class="attendance-present">3</td>
                            <td class="attendance-holiday">4</td>
                            <td class="attendance-holiday">5</td>
                        </tr>
                        <tr>
                            <td class="attendance-present">6</td>
                            <td class="attendance-present">7</td>
                            <td class="attendance-present">8</td>
                            <td class="attendance-absent">9</td>
                            <td class="attendance-present">10</td>
                            <td class="attendance-holiday">11</td>
                            <td class="attendance-holiday">12</td>
                        </tr>
                        <tr>
                            <td class="attendance-present">13</td>
                            <td class="attendance-present">14</td>
                            <td class="attendance-present">15</td>
                            <td class="attendance-present">16</td>
                            <td class="attendance-present">17</td>
                            <td class="attendance-holiday">18</td>
                            <td class="attendance-holiday">19</td>
                        </tr>
                        <tr>
                            <td class="attendance-present">20</td>
                            <td class="attendance-absent">21</td>
                            <td class="attendance-absent">22</td>
                            <td class="attendance-present">23</td>
                            <td class="attendance-present">24</td>
                            <td class="attendance-holiday">25</td>
                            <td class="attendance-holiday">26</td>
                        </tr>
                        <tr>
                            <td class="attendance-present">27</td>
                            <td class="attendance-present">28</td>
                            <td class="attendance-future">29</td>
                            <td class="attendance-future">30</td>
                            <td class="attendance-future">31</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 16px; height: 16px; background: #dcfce7; border-radius: 4px;"></div>
                    <span style="font-size: 0.875rem; color: #4b5563;">Present</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 16px; height: 16px; background: #fee2e2; border-radius: 4px;"></div>
                    <span style="font-size: 0.875rem; color: #4b5563;">Absent</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 16px; height: 16px; background: #dbeafe; border-radius: 4px;"></div>
                    <span style="font-size: 0.875rem; color: #4b5563;">Holiday</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 16px; height: 16px; background: #f3f4f6; border-radius: 4px;"></div>
                    <span style="font-size: 0.875rem; color: #4b5563;">Upcoming</span>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Fetch and Render Attendance Summary ---
        function loadAttendanceSummary() {
            fetch('student_attendance_actions.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'get_summary' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const s = data.summary;
                    const total = data.total;
                    document.querySelectorAll('.stat-card')[0].querySelector('.stat-value').textContent = `${s.present} / ${total}`;
                    document.querySelectorAll('.stat-card')[1].querySelector('.stat-value').textContent = `${s.absent}`;
                    document.querySelectorAll('.stat-card')[2].querySelector('.stat-value').textContent = `${data.percentage}%`;
                }
            });
        }

        // --- Fetch and Render Monthly Attendance Calendar ---
        function loadMonthlyAttendance() {
            fetch('student_attendance_actions.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'get_monthly' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const days = data.days;
                    // Clear all calendar cells
                    document.querySelectorAll('.attendance-calendar td').forEach(td => {
                        td.className = '';
                    });
                    // Fill in attendance status
                    Object.entries(days).forEach(([date, status]) => {
                        // Find the cell with the day number matching the date
                        const d = new Date(date);
                        const dayNum = d.getDate();
                        document.querySelectorAll('.attendance-calendar td').forEach(td => {
                            if (td.textContent == dayNum) {
                                td.classList.add('attendance-' + status);
                            }
                        });
                    });
                }
            });
        }

        loadAttendanceSummary();
        loadMonthlyAttendance();
    });
</script>
</body>
</html>