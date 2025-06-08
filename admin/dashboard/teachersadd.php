<?php include 'sidebar.php'; ?>
<?php
require_once 'con.php';
$subjects = [];
$result = $conn->query("SELECT id, name, code FROM subjects ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/teachersadd.css">
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
            <h1 class="header-title">Add New Teacher</h1>
            <span class="header-path">Dashboard > Teachers > Add New</span>
        </header>

        <main class="dashboard-content">
            <form class="form-container" id="addTeacherForm" enctype="multipart/form-data">
                <!-- Personal Information -->
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName" class="form-label required">Full Name</label>
                            <input type="text" id="fullName" name="fullName" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="employeeNumber" class="form-label">Employee Number</label>
                            <input type="text" id="employeeNumber" name="employeeNumber" class="form-input" readonly placeholder="Auto-generated (VES2025T001)">
                            <span class="help-text">Employee ID will be auto-generated in format VES2025T001</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateOfBirth" class="form-label required">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" required>
                            <span class="help-text">Required for teacher records and profile</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="photo" class="form-label">Profile Photo</label>
                            <div class="file-input-wrapper">
                                <label for="photo" class="file-input-label">
                                    <div class="file-input-text">
                                        <svg class="file-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Click to upload</span>
                                        <span class="help-text">JPEG, PNG or JPG (MAX. 2MB)</span>
                                    </div>
                                </label>
                                <input type="file" id="photo" name="photo" class="file-input" accept="image/jpeg, image/png, image/jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="form-section">
                    <h2 class="section-title">Professional Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="joiningDate" class="form-label required">Joining Date</label>
                            <input type="date" id="joiningDate" name="joiningDate" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="qualification" class="form-label required">Highest Qualification</label>
                            <select id="qualification" name="qualification" class="form-select" required>
                                <option value="">Select Qualification</option>
                                <option value="phd">Ph.D</option>
                                <option value="masters">Masters</option>
                                <option value="bachelors">Bachelors</option>
                                <option value="diploma">Diploma</option>
                                <option value="certificate">Certificate</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h2 class="section-title">Contact Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" required>
                            <span class="help-text">School communications will be sent to this email</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address" class="form-label required">Address</label>
                            <input type="text" id="address" name="address" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="city" class="form-label required">City</label>
                            <input type="text" id="city" name="city" class="form-input" required>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="form-section">
                    <h2 class="section-title">Account Settings</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password (Optional)</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="password" name="password" class="form-input">
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="password-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <span class="help-text">If not provided, date of birth will be used as password (YYYYMMDD format)</span>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-input">
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirmPassword')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="password-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <span class="help-text">Only required if password is provided above</span>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="sendCredentials" name="sendCredentials" class="checkbox-input" checked>
                        <label for="sendCredentials" class="checkbox-label">Send login credentials to the teacher's email</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="window.location.href='teachersmanage.php'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Teacher</button>
                </div>
            </form>
            <div id="formFeedback" style="margin:1em 0;"></div>
        </main>
    </div>

    <script>
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

        // Password visibility toggle
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = passwordInput.nextElementSibling;
            const toggleIcon = toggleButton.querySelector('.password-toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Display file name when selected
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('photo');
            const fileLabel = document.querySelector('.file-input-text');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const fileName = this.files[0].name;
                    fileLabel.innerHTML = `
                        <svg class="file-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                       </svg>
                       <span>${fileName}</span>
                       <button type="button" class="btn-remove-file">Remove</button>
                   `;
                   
                   const removeBtn = document.querySelector('.btn-remove-file');
                   if (removeBtn) {
                       removeBtn.addEventListener('click', function(e) {
                           e.preventDefault();
                           e.stopPropagation();
                           fileInput.value = '';
                           fileLabel.innerHTML = `
                               <svg class="file-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                               </svg>
                               <span>Click to upload</span>
                               <span class="help-text">JPEG, PNG or JPG (MAX. 2MB)</span>
                           `;
                       });
                   }
               }
           });
       });

       document.getElementById('addTeacherForm').onsubmit = async function(e) {
           e.preventDefault();
           const form = e.target;
           const formData = new FormData(form);
           const feedback = document.getElementById('formFeedback');
           feedback.innerHTML = '';
           
           // Show loading state
           const submitBtn = form.querySelector('button[type="submit"]');
           const originalText = submitBtn.textContent;
           submitBtn.textContent = 'Adding Teacher...';
           submitBtn.disabled = true;
           
           try {
               const res = await fetch('teachersadd_ction.php', {
                   method: 'POST',
                   body: formData
               });
               const data = await res.json();
               if (data.success) {
                   feedback.innerHTML = `<div class='success'>${data.message}</div>`;
                   form.reset();
                   // Clear the employee number field placeholder
                   document.getElementById('employeeNumber').placeholder = 'Auto-generated (VES2025T001)';
               } else {
                   feedback.innerHTML = `<div class='error'>${data.message}<br>${(data.errors||[]).join('<br>')}</div>`;
               }
           } catch (err) {
               feedback.innerHTML = `<div class='error'>Submission failed. Please try again.</div>`;
           } finally {
               // Reset button state
               submitBtn.textContent = originalText;
               submitBtn.disabled = false;
           }
       };
   </script>
</body>
</html>