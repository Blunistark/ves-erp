<?php
session_start();
require_once __DIR__ . '/con.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Hardcoded admin data based on screenshot
$admin = [
    'first_name' => 'Vinodh',
    'last_name' => 'English School',
    'email' => 'vinodh.schl.edu@gmail.com',
    'phone' => '+917760114325',
    'department' => 'administration',
    'role' => 'School Administrator',
    'profile_image' => null,
    'two_factor_enabled' => false,
    'joined_date' => 'January 15, 2022',
    'last_login' => 'Today, 9:42 AM'
];

// Handle profile update (disabled for read-only mode)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ["success" => false, "message" => "Profile editing is currently disabled."];
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
                        <div class="avatar-container" onclick="showAvatarModal()">
                            <div class="avatar-placeholder">VS</div>
                            <div class="avatar-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" class="avatar-edit-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                        <h2 class="admin-name">Vinodh English School</h2>
                        <p class="admin-role">School Administrator</p>
                    </div>

                    <div class="profile-meta">
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="meta-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            <div class="meta-content">
                                <span class="meta-label">Email</span>
                                <span class="meta-value">vinodh.schl.edu@gmail.com</span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="meta-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <div class="meta-content">
                                <span class="meta-label">Phone</span>
                                <span class="meta-value">+917760114325</span>
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
                                    <input type="text" id="firstName" name="firstName" class="form-input" value="Vinodh" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label for="lastName" class="form-label required">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="form-input" value="English School" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label required">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-input" value="vinodh.schl.edu@gmail.com" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-input" value="+917760114325" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" id="role" name="role" class="form-input" value="School Administrator" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                    <span class="help-text">Role cannot be changed. Contact system administrator.</span>
                                </div>
                                <div class="form-group">
                                    <label for="department" class="form-label">Department</label>
                                    <select id="department" name="department" class="form-select" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <option value="administration" selected>Administration</option>
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
                                <label class="toggle-switch" style="pointer-events: none; opacity: 0.5;">
                                    <input type="checkbox" name="twoFactorEnabled" class="toggle-input" disabled>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <!-- Change Password -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="currentPassword" name="currentPassword" class="form-input" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <button type="button" class="password-toggle" disabled style="pointer-events: none; opacity: 0.5;">
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
                                        <input type="password" id="newPassword" name="newPassword" class="form-input" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <button type="button" class="password-toggle" disabled style="pointer-events: none; opacity: 0.5;">
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
                                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <button type="button" class="password-toggle" disabled style="pointer-events: none; opacity: 0.5;">
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
                            <button type="button" class="btn btn-outline" disabled style="opacity: 0.5; cursor: not-allowed;">Cancel</button>
                            <button type="submit" class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Avatar Selection Modal -->
    <div class="modal-overlay" id="avatarModal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Choose Profile Picture</h3>
                <button class="modal-close" onclick="hideAvatarModal()">
                    <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p style="margin-top: 0; margin-bottom: 1rem; color: #6b7280; font-size: 0.9375rem;">Upload your own profile picture.</p>
                
                <h4 style="font-size: 0.9375rem; color: #4b5563; margin-bottom: 0.5rem;">Upload Image</h4>
                <label class="file-upload" for="avatar-upload" style="padding: 1rem;">
                    <input type="file" id="avatar-upload" class="file-input" accept="image/*">
                    <svg style="width: 24px; height: 24px; color: #6b7280; margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    <div style="font-size: 0.875rem; color: #4b5563;">Click to upload image</div>
                    <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">JPG, PNG (Max 2MB)</div>
                </label>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="hideAvatarModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveAvatar()">Save Changes</button>
            </div>
        </div>
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

        // Disable all interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Disable form submission
            const form = document.getElementById('profileForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Profile Editing Disabled',
                    text: 'Profile editing is currently disabled for demonstration purposes.',
                    icon: 'info',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
            });

            // Disable photo upload
            document.getElementById('photo-upload').addEventListener('change', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Upload Disabled',
                    text: 'Photo upload is currently disabled.',
                    icon: 'info',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
            });
        });

        // Avatar modal functions
        function showAvatarModal() {
            document.getElementById('avatarModal').style.display = 'flex';
            Swal.fire({
                title: 'Avatar Selection Disabled',
                text: 'Avatar selection is currently disabled for demonstration purposes.',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
        }

        function hideAvatarModal() {
            document.getElementById('avatarModal').style.display = 'none';
        }

        function saveAvatar() {
            Swal.fire({
                title: 'Save Disabled',
                text: 'Avatar saving is currently disabled.',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
        }

        // Disabled password visibility toggle
        function togglePasswordVisibility(inputId) {
            // Function disabled
            return false;
        }

        function resetForm() {
            // Function disabled
            return false;
        }
    </script>
</body>
</html>
