<?php
require_once '../includes/config.php';
require_once '../includes/connection.php';
require_once '../includes/functions.php';

// Require authentication and manage_admissions permission
requirePermission('manage_admissions');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $currentUser = getCurrentUser();
        
        // Sanitize and validate input data
        $data = array(
            'child_name' => trim($_POST['child_name'] ?? ''),
            'parent_name' => trim($_POST['parent_name'] ?? ''),
            'parent_email' => trim($_POST['parent_email'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'desired_class' => $_POST['desired_class'] ?? '',
            'address' => trim($_POST['address'] ?? ''),
            'specific_requirements' => trim($_POST['specific_requirements'] ?? ''),
            'parent_notes' => trim($_POST['parent_notes'] ?? ''),
            'created_by' => $currentUser['id']
        );

        // Comprehensive validation
        $errors = [];
        
        if (empty($data['child_name'])) {
            $errors[] = "Child's name is required";
        } elseif (strlen($data['child_name']) < 2) {
            $errors[] = "Child's name must be at least 2 characters long";
        } elseif (strlen($data['child_name']) > 100) {
            $errors[] = "Child's name cannot exceed 100 characters";
        }

        if (empty($data['parent_name'])) {
            $errors[] = "Parent's name is required";
        } elseif (strlen($data['parent_name']) < 2) {
            $errors[] = "Parent's name must be at least 2 characters long";
        } elseif (strlen($data['parent_name']) > 100) {
            $errors[] = "Parent's name cannot exceed 100 characters";
        }

        if (empty($data['phone_number'])) {
            $errors[] = "Phone number is required";
        } elseif (!validatePhone($data['phone_number'])) {
            $errors[] = "Please enter a valid 10-digit phone number";
        }

        if (!empty($data['parent_email']) && !filter_var($data['parent_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }

        if (empty($data['desired_class'])) {
            $errors[] = "Please select a desired class";
        }

        if (!empty($errors)) {
            throw new Exception(implode('. ', $errors));
        }

        // Check for duplicate phone number (optional business rule)
        $existingInquiry = $db->fetchOne(
            "SELECT id FROM admission_inquiries WHERE phone_number = ? AND DATE(inquiry_date) = CURDATE()",
            [$data['phone_number']]
        );
        
        if ($existingInquiry) {
            throw new Exception("An admission inquiry with this phone number has already been submitted today. Reference ID: " . $existingInquiry['id']);
        }

        // Insert the record
        $id = $db->insert('admission_inquiries', $data);
        
        if (!$id) {
            throw new Exception("Failed to save admission inquiry. Please try again.");
        }

        $message = "Successfully submitted";
        $messageType = 'success';
        
        logActivity('Created Admission Inquiry', "ID: $id, Child: {$data['child_name']}, Parent: {$data['parent_name']}, Phone: {$data['phone_number']}");
        
        // Clear form data after successful submission
        $_POST = array();
        
    } catch (Exception $e) {
        $message = "❌ " . $e->getMessage();
        $messageType = 'error';
        
        // Log error for debugging (in production)
        error_log("Admission Inquiry Error: " . $e->getMessage() . " | User: " . $currentUser['id'] . " | Data: " . json_encode($_POST));
    } catch (Throwable $e) {
        $message = "❌ An unexpected error occurred. Please try again or contact the administrator.";
        $messageType = 'error';
        
        // Log critical error
        error_log("Critical Admission Inquiry Error: " . $e->getMessage() . " | User: " . $currentUser['id']);
    }
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Admission Inquiry - Vinodh English School</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans:wght@400;500;700;900&family=Public+Sans:wght@400;500;700;900">
<style>
/* Bootstrap-Inspired Premium Dashboard with Modern Gradients */

/* CSS Variables - Premium Color Palette */
:root {
    --primary-bg: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    --secondary-bg: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e2e8f0 100%);
    --accent-bg: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    --card-bg: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.8) 100%);
    --border-color: rgba(226, 232, 240, 0.6);
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --text-muted: #94a3b8;
    --button-primary: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    --button-hover: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
    --success-bg: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    --success-text: #065f46;
    --success-border: rgba(167, 243, 208, 0.5);
    --error-bg: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    --error-text: #991b1b;
    --error-border: rgba(252, 165, 165, 0.5);
    --warning-bg: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    --warning-text: #92400e;
    --warning-border: rgba(252, 211, 77, 0.5);
    --required-color: #dc2626;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Reset and Base Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: var(--primary-bg);
    color: var(--text-primary);
    line-height: 1.6;
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Layout Components */
.main-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: var(--primary-bg);
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2.5rem;
    background: var(--secondary-bg);
    border-bottom: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 1030;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-primary);
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -0.02em;
    margin: 0;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-section {
    display: flex;
    flex: 1;
    justify-content: flex-end;
    gap: 2rem;
    align-items: center;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.nav-link {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s;
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link:hover {
    color: var(--text-primary);
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-1px);
}

.nav-link.active {
    font-weight: 600;
    color: #3b82f6;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
}

/* Mobile Hamburger Menu */
.mobile-menu-btn {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 24px;
    height: 18px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hamburger-line {
    width: 100%;
    height: 2px;
    background: var(--text-secondary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 2px;
}

.mobile-menu-btn.active .hamburger-line:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
    background: #3b82f6;
}

.mobile-menu-btn.active .hamburger-line:nth-child(2) {
    opacity: 0;
}

.mobile-menu-btn.active .hamburger-line:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
    background: #3b82f6;
}

.mobile-nav-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    backdrop-filter: blur(5px);
    opacity: 0;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-nav-overlay.active {
    opacity: 1;
}

.mobile-nav-menu {
    display: none;
    position: fixed;
    top: 0;
    right: -100%;
    width: 300px;
    height: 100vh;
    background: var(--secondary-bg);
    z-index: 1050;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 2rem 1rem;
    box-shadow: var(--shadow-xl);
    backdrop-filter: blur(10px);
    overflow-y: auto;
}

.mobile-nav-menu.active {
    right: 0;
}

.mobile-nav-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-nav-close:hover {
    background: rgba(59, 130, 246, 0.1);
    color: var(--text-primary);
    transform: rotate(90deg);
}

.mobile-nav-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 2rem;
}

.mobile-nav-link {
    color: var(--text-secondary);
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    padding: 1rem;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    display: block;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.2);
    transform: translateX(4px);
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border: 2px solid rgba(59, 130, 246, 0.2);
    box-shadow: var(--shadow-md);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.user-avatar:hover {
    transform: scale(1.05);
    border-color: #3b82f6;
    box-shadow: var(--shadow-lg);
}

/* Content Layout */
.content-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 2rem;
    background: var(--primary-bg);
}

.content-container {
    display: flex;
    flex-direction: column;
    max-width: 800px;
    width: 100%;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-header {
    margin-bottom: 2rem;
    padding: 1.5rem 0;
    text-align: center;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
    background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

/* Form Components */
#admissionForm {
    background: var(--card-bg);
    border-radius: 1.25rem;
    border: 1px solid var(--border-color);
    padding: 2.5rem;
    box-shadow: var(--shadow-md);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

#admissionForm::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
    border-radius: 1.25rem 1.25rem 0 0;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.5rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.form-label {
    color: var(--text-primary);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.875rem;
}

.form-label.required::after {
    content: " *";
    color: var(--required-color);
    font-weight: 700;
}

.form-input {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.8);
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(5px);
    min-height: 3.5rem;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
    background: rgba(255, 255, 255, 0.95);
}

.form-input::placeholder {
    color: var(--text-muted);
    font-weight: 400;
}

.form-select {
    background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2724px%27 height=%2724px%27 fill=%27rgb(100,116,139)%27 viewBox=%270 0 24 24%27%3e%3cpath d=%27M7 10l5 5 5-5z%27/%3e%3c/svg%3e');
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1.5rem;
    appearance: none;
    padding-right: 3rem;
    cursor: pointer;
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.5;
}

.form-input.error {
    border-color: var(--required-color);
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-error {
    color: var(--required-color);
    font-size: 0.875rem;
    margin-top: 0.5rem;
    font-weight: 500;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.btn-primary {
    padding: 1rem 3rem;
    background: var(--button-primary);
    color: white;
    border: none;
    border-radius: 0.75rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-md);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    min-height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    min-width: 200px;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    background: var(--button-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: var(--shadow-md);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Messages */
.message {
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    border-radius: 0.75rem;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    animation: slideInDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
}

.message.success {
    background: var(--success-bg);
    color: var(--success-text);
    border: 1px solid var(--success-border);
}

.message.error {
    background: var(--error-bg);
    color: var(--error-text);
    border: 1px solid var(--error-border);
}

.message.warning {
    background: var(--warning-bg);
    color: var(--warning-text);
    border: 1px solid var(--warning-border);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .content-wrapper {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .content-wrapper {
        padding: 1rem;
    }
    
    .header {
        padding: 1rem;
    }
    
    .nav-links {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
    
    .mobile-nav-overlay.active,
    .mobile-nav-menu {
        display: block;
    }
    
    .nav-section {
        gap: 1rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .content-container {
        max-width: 100%;
    }
    
    #admissionForm {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-input {
        padding: 0.875rem 1rem;
        min-height: 3rem;
    }
    
    .btn-primary {
        width: 100%;
        min-width: auto;
    }
}

@media (max-width: 480px) {
    .content-wrapper {
        padding: 0.5rem;
    }
    
    .page-title {
        font-size: 1.75rem;
    }
    
    #admissionForm {
        padding: 1rem;
    }
    
    .form-input {
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .form-label {
        font-size: 0.8rem;
    }
    
    .mobile-nav-menu {
        width: 280px;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(241, 245, 249, 0.5);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(139, 92, 246, 0.3));
    border-radius: 4px;
    transition: all 0.3s;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.5), rgba(139, 92, 246, 0.5));
}

/* Focus Styles for Accessibility */
*:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
}

button:focus,
input:focus,
select:focus,
textarea:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
}

/* Form Enhancement */
.form-input:hover {
    border-color: rgba(59, 130, 246, 0.3);
}

.form-field:focus-within .form-label {
    color: #3b82f6;
}

/* Loading State */
.btn-primary.loading {
    position: relative;
    color: transparent;
}

.btn-primary.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced Mobile Navigation */
.mobile-nav-overlay.active {
    display: block;
}

.mobile-nav-menu.active {
    display: block;
}
</style>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
</head>
<body>
    <div class="main-container">
        <header class="header">
            <div class="logo-section">
                <h2 class="logo-text">Vinodh English School</h2>
            </div>
           <div class="nav-section">
    <nav class="nav-links">
        <a class="nav-link" href="dashboard.php">Dashboard</a>
        <a class="nav-link active" href="admission_inquiry.php">Admissions</a>
        <?php if (hasPermission('manage_visitors')): ?>
        <a class="nav-link" href="log_visitor.php">Visitors</a>
        <?php endif; ?>
        <a class="nav-link" href="logout.php">Logout</a>
    </nav>
    
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>
    
    <div class="user-avatar" style="background-image: url('https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['full_name']); ?>&background=f4c653&color=1c170d');"></div>
</div>
        </header>
               <div class="mobile-nav-overlay" onclick="closeMobileMenu()"></div>
        
        <!-- Mobile Navigation Menu -->
        <div class="mobile-nav-menu">
            <button class="mobile-nav-close" onclick="closeMobileMenu()">×</button>
            <div class="mobile-nav-links">
                <a class="mobile-nav-link" href="dashboard.php">Dashboard</a>
                <a class="mobile-nav-link active" href="admission_inquiry.php">Admissions</a>
                <?php if (hasPermission('manage_visitors')): ?>
                <a class="mobile-nav-link" href="log_visitor.php">Visitors</a>
                <?php endif; ?>
                <a class="mobile-nav-link" href="logout.php">Logout</a>
            </div>
        </div>
        <div class="content-wrapper">
            <div class="content-container">
                <div class="page-header">
                    <h1 class="page-title">New Admission Inquiry</h1>
                </div>

                <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>" id="message">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>

                <form id="admissionForm" method="POST" action="">
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label required" for="child_name">Child's Name</label>
                            <input
                                name="child_name"
                                id="child_name"
                                placeholder="Enter child's name"
                                class="form-input"
                                value="<?php echo isset($_POST['child_name']) ? htmlspecialchars($_POST['child_name']) : ''; ?>"
                                required
                                autocomplete="name"
                            />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label required" for="parent_name">Parent's Name</label>
                            <input
                                name="parent_name"
                                id="parent_name"
                                placeholder="Enter parent's name"
                                class="form-input"
                                value="<?php echo isset($_POST['parent_name']) ? htmlspecialchars($_POST['parent_name']) : ''; ?>"
                                required
                                autocomplete="name"
                            />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label" for="parent_email">Parent's Email</label>
                            <input
                                name="parent_email"
                                id="parent_email"
                                type="email"
                                placeholder="Enter parent's email"
                                class="form-input"
                                value="<?php echo isset($_POST['parent_email']) ? htmlspecialchars($_POST['parent_email']) : ''; ?>"
                                autocomplete="email"
                            />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label required" for="phone_number">Phone Number</label>
                            <input
                                name="phone_number"
                                id="phone_number"
                                placeholder="Enter phone number"
                                class="form-input"
                                value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>"
                                required
                                autocomplete="tel"
                            />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label required" for="desired_class">Desired Class</label>
                            <select
                                name="desired_class"
                                id="desired_class"
                                class="form-input form-select"
                                required
                                autocomplete="off"
                            >
                                <option value="">Select desired class</option>
                                <option value="Pre-KG" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'Pre-KG') ? 'selected' : ''; ?>>Pre-KG</option>
                                <option value="LKG" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'LKG') ? 'selected' : ''; ?>>LKG</option>
                                <option value="UKG" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'UKG') ? 'selected' : ''; ?>>UKG</option>
                                <option value="I" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'I') ? 'selected' : ''; ?>>I</option>
                                <option value="II" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'II') ? 'selected' : ''; ?>>II</option>
                                <option value="III" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'III') ? 'selected' : ''; ?>>III</option>
                                <option value="IV" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'IV') ? 'selected' : ''; ?>>IV</option>
                                <option value="V" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'V') ? 'selected' : ''; ?>>V</option>
                                <option value="VI" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'VI') ? 'selected' : ''; ?>>VI</option>
                                <option value="VII" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'VII') ? 'selected' : ''; ?>>VII</option>
                                <option value="VIII" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'VIII') ? 'selected' : ''; ?>>VIII</option>
                                <option value="IX" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'IX') ? 'selected' : ''; ?>>IX</option>
                                <option value="X" <?php echo (isset($_POST['desired_class']) && $_POST['desired_class'] == 'X') ? 'selected' : ''; ?>>X</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label" for="address">Address</label>
                            <input
                                name="address"
                                id="address"
                                placeholder="Enter address"
                                class="form-input"
                                value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                                autocomplete="street-address"
                            />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-field">
                            <label class="form-label" for="parent_notes">Parent Notes</label>
                            <textarea
                                name="parent_notes"
                                id="parent_notes"
                                placeholder="Enter parent notes"
                                class="form-input form-textarea"
                                autocomplete="off"
                            ><?php echo isset($_POST['parent_notes']) ? htmlspecialchars($_POST['parent_notes']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            Save Inquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
   <script>
        // Mobile menu functions
        function toggleMobileMenu() {
            const btn = document.querySelector('.mobile-menu-btn');
            const overlay = document.querySelector('.mobile-nav-overlay');
            const menu = document.querySelector('.mobile-nav-menu');
            
            btn.classList.toggle('active');
            overlay.classList.toggle('active');
            menu.classList.toggle('active');
            
            if (menu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        function closeMobileMenu() {
            const btn = document.querySelector('.mobile-menu-btn');
            const overlay = document.querySelector('.mobile-nav-overlay');
            const menu = document.querySelector('.mobile-nav-menu');
            
            btn.classList.remove('active');
            overlay.classList.remove('active');
            menu.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Form validation on submit
        const admissionForm = document.getElementById('admissionForm');
        if (admissionForm) {
            admissionForm.addEventListener('submit', function(e) {
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                const errors = FormValidator.validateAdmissionForm(data);
                
                if (Object.keys(errors).length > 0) {
                    e.preventDefault();
                    FormValidator.showFormErrors(errors);
                    Utils.showError('Please correct the errors below.');
                    return;
                }
            });
        }

        // Clear form errors on input change
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorElement = this.parentNode.querySelector('.form-error');
                if (errorElement) {
                    errorElement.remove();
                }
            });
        });
    </script>
</body>
</html>