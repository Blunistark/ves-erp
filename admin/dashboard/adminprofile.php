<?php
session_start();
require_once __DIR__ . '/con.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch admin data from both tables
$stmt = $conn->prepare("
    SELECT u.*, a.first_name, a.last_name, a.phone, a.department, a.profile_image, a.two_factor_enabled
    FROM users u
    LEFT JOIN admins a ON u.id = a.user_id
    WHERE u.id = ? AND u.role = 'admin'
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../login.php');
    exit;
}

$admin = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ["success" => false, "message" => "Unknown error."];
    
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $file['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $response['message'] = 'Only JPG and PNG images are allowed.';
            echo json_encode($response);
            exit();
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'Image size should not exceed 2MB.';
            echo json_encode($response);
            exit();
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'admin_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_dir = '../uploads/admin_photos/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Update database with new photo filename
            $stmt = $conn->prepare("UPDATE admins SET profile_image = ? WHERE user_id = ?");
            $stmt->bind_param("si", $filename, $user_id);
            
            if (!$stmt->execute()) {
                $response['message'] = 'Failed to update profile photo in database.';
                echo json_encode($response);
                exit();
            }
            
            $response['success'] = true;
            $response['message'] = 'Profile photo updated successfully.';
            $response['filename'] = $filename;
            echo json_encode($response);
            exit();
        } else {
            $response['message'] = 'Failed to upload photo.';
            echo json_encode($response);
            exit();
        }
    }
    
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate required fields
    if ($firstName === '' || $lastName === '' || $email === '') {
        $response['message'] = "First name, last name, and email are required.";
        echo json_encode($response);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET email = ?, full_name = ? WHERE id = ?");
        $fullName = $firstName . ' ' . $lastName;
        $stmt->bind_param('ssi', $email, $fullName, $user_id);
        $stmt->execute();

        // Update admins table
        $stmt = $conn->prepare("
            INSERT INTO admins (user_id, first_name, last_name, phone, department) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            phone = VALUES(phone),
            department = VALUES(department)
        ");
        $stmt->bind_param('issss', $user_id, $firstName, $lastName, $phone, $department);
        $stmt->execute();

        // Handle password change if requested
        if ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '') {
            if ($newPassword !== $confirmPassword) {
                throw new Exception("New passwords do not match.");
            }

            // Verify current password
            if (!password_verify($currentPassword, $admin['password_hash'])) {
                throw new Exception("Current password is incorrect.");
            }

            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $newPasswordHash, $user_id);
            $stmt->execute();
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Profile updated successfully.";
        
        // Update session data
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $fullName;
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/adminprofile.css">
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <h1 class="header-title">Update Profile</h1>
            <span class="header-path">Dashboard > Profile > Update</span>
        </header>

        <main class="dashboard-content">
            <div class="profile-layout">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-avatar">
                        <div class="avatar-container">
                            <?php if ($admin['profile_image']): ?>
                                <img src="../uploads/admin_photos/<?php echo htmlspecialchars($admin['profile_image']); ?>" alt="Profile" class="avatar-image">
                            <?php else: ?>
                                <div class="avatar-placeholder">JD</div>
                            <?php endif; ?>
                            <label for="photo-upload" class="avatar-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" class="avatar-edit-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </label>
                            <input type="file" id="photo-upload" name="photo" accept="image/jpeg,image/png,image/jpg" style="display: none;">
                        </div>
                        <h2 class="admin-name">John Doe</h2>
                        <p class="admin-role">School Administrator</p>
                    </div>

                    <div class="profile-meta">
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="meta-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            <div class="meta-content">
                                <span class="meta-label">Email</span>
                                <span class="meta-value">john.doe@school.edu</span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="meta-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <div class="meta-content">
                                <span class="meta-label">Phone</span>
                                <span class="meta-value">+1 (555) 123-4567</span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="meta-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="meta-content">
                                <span class="meta-label">Joined</span>
                                <span class="meta-value">January 15, 2022</span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="meta-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div class="meta-content">
                                <span class="meta-label">Last Login</span>
                                <span class="meta-value">Today, 9:42 AM</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Container -->
                <div class="form-container">
                    <form id="profileForm" method="POST">
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h2 class="section-title">Personal Information</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName" class="form-label required">First Name</label>
                                    <input type="text" id="firstName" name="firstName" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName" class="form-label required">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="form-input" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label required">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-input">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" id="role" name="role" class="form-input" value="School Administrator" disabled>
                                    <span class="help-text">Role cannot be changed. Contact system administrator.</span>
                                </div>
                                <div class="form-group">
                                    <label for="department" class="form-label">Department</label>
                                    <select id="department" name="department" class="form-select">
                                        <option value="administration">Administration</option>
                                        <option value="academics">Academics</option>
                                        <option value="finance">Finance</option>
                                        <option value="hr">Human Resources</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="form-section">
                            <h2 class="section-title">Security Settings</h2>
                            
                            <div class="two-factor-section">
                                <div class="two-factor-content">
                                    <h3 class="two-factor-title">Two-Factor Authentication</h3>
                                    <p class="two-factor-desc">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="twoFactorEnabled" class="toggle-input">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <!-- Change Password -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="currentPassword" name="currentPassword" class="form-input">
                                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('currentPassword')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="password-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="newPassword" name="newPassword" class="form-input">
                                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('newPassword')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="password-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input">
                                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirmPassword')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="password-toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="resetForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
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

        // Password strength meter
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('newPassword');
            const strengthMeter = document.querySelector('.strength-progress');
            const strengthText = document.querySelector('.strength-text');

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                if (password.length === 0) {
                    strengthMeter.className = 'strength-progress';
                    strengthMeter.style.width = '0';
                    strengthText.textContent = 'Password strength: Not set';
                    return;
                }

                // Simple password strength calculation
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                
                // Character variety checks
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[a-z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;

                // Update UI based on strength
                if (strength < 3) {
                    strengthMeter.className = 'strength-progress weak';
                    strengthText.textContent = 'Password strength: Weak';
                } else if (strength < 5) {
                    strengthMeter.className = 'strength-progress medium';
                    strengthText.textContent = 'Password strength: Medium';
                } else {
                    strengthMeter.className = 'strength-progress strong';
                    strengthText.textContent = 'Password strength: Strong';
                }
            });

            // AJAX profile update
            const form = document.getElementById('profileForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to update profile. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                });
            });
        });
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268-2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}

        // Update the profile information display
        document.addEventListener('DOMContentLoaded', function() {
            // Set the avatar initials
            const firstName = <?php echo json_encode($admin['first_name']); ?>;
            const lastName = <?php echo json_encode($admin['last_name']); ?>;
            const initials = (firstName[0] + lastName[0]).toUpperCase();
            document.querySelector('.avatar-placeholder').textContent = initials;
            
            // Update admin name and role
            document.querySelector('.admin-name').textContent = firstName + ' ' + lastName;
            
            // Update meta information
            document.querySelector('.meta-value').textContent = <?php echo json_encode($admin['email']); ?>;
            document.querySelector('.meta-item:nth-child(2) .meta-value').textContent = <?php echo json_encode($admin['phone'] ?: 'Not set'); ?>;
            
            // Set form values
            document.getElementById('firstName').value = firstName;
            document.getElementById('lastName').value = lastName;
            document.getElementById('email').value = <?php echo json_encode($admin['email']); ?>;
            document.getElementById('phone').value = <?php echo json_encode($admin['phone'] ?: ''); ?>;
            document.getElementById('department').value = <?php echo json_encode($admin['department'] ?: 'administration'); ?>;
            
            // Set two-factor authentication toggle
            document.querySelector('.toggle-input').checked = <?php echo $admin['two_factor_enabled'] ? 'true' : 'false'; ?>;
        });

        function resetForm() {
            document.getElementById('profileForm').reset();
            // Reset form to original values
            const firstName = <?php echo json_encode($admin['first_name']); ?>;
            const lastName = <?php echo json_encode($admin['last_name']); ?>;
            document.getElementById('firstName').value = firstName;
            document.getElementById('lastName').value = lastName;
            document.getElementById('email').value = <?php echo json_encode($admin['email']); ?>;
            document.getElementById('phone').value = <?php echo json_encode($admin['phone'] ?: ''); ?>;
            document.getElementById('department').value = <?php echo json_encode($admin['department'] ?: 'administration'); ?>;
        }

        // Initialize form with current values when page loads
        document.addEventListener('DOMContentLoaded', function() {
            resetForm();
        });

        // Handle photo upload
        document.getElementById('photo-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Only JPG and PNG images are allowed.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Image size should not exceed 2MB.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Create FormData and send request
            const formData = new FormData();
            formData.append('photo', file);
            
            // Show loading state
            Swal.fire({
                title: 'Uploading...',
                text: 'Please wait while we upload your photo.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    // Update the avatar image
                    const avatarContainer = document.querySelector('.avatar-container');
                    const img = document.createElement('img');
                    img.src = '../uploads/admin_photos/' + data.filename;
                    img.alt = 'Profile';
                    img.className = 'avatar-image';
                    
                    // Remove placeholder if it exists
                    const placeholder = avatarContainer.querySelector('.avatar-placeholder');
                    if (placeholder) {
                        placeholder.remove();
                    }
                    
                    // Remove existing image if it exists
                    const existingImg = avatarContainer.querySelector('.avatar-image');
                    if (existingImg) {
                        existingImg.remove();
                    }
                    
                    // Add new image before the overlay
                    const overlay = avatarContainer.querySelector('.avatar-overlay');
                    avatarContainer.insertBefore(img, overlay);

                    Swal.fire({
                        title: 'Success!',
                        text: 'Profile photo updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to update profile photo.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(() => {
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to upload photo. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            });
        });
    </script>
</body>
</html>