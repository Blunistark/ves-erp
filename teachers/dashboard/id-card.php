<?php 
include 'sidebar.php'; 
require_once 'con.php';

// Get current user's profile data
$user_id = $_SESSION['user_id'];
$query = "SELECT u.id, u.email, u.full_name, u.created_at,
                 t.employee_number, t.joined_date, t.qualification, t.date_of_birth,
                 t.profile_photo, t.address, t.city, t.phone, t.alt_email,
                 t.emergency_contact, t.gender, t.state, t.zip_code, t.country,
                 t.department, t.position, t.experience_years, t.bio
          FROM users u 
          LEFT JOIN teachers t ON u.id = t.user_id 
          WHERE u.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Default values for missing data
$fullName = $profile['full_name'] ?? 'Teacher Name';
$employeeId = $profile['employee_number'] ?? 'TCH-' . date('Y') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
$department = ucfirst($profile['department'] ?? 'General');
$position = $profile['position'] ?? 'Teacher';
$joinedDate = $profile['joined_date'] ? date('F j, Y', strtotime($profile['joined_date'])) : 'Not Set';
$phone = $profile['phone'] ?? 'Not Available';
$email = $profile['email'] ?? 'Not Available';
$address = $profile['address'] ? $profile['address'] : 'Not Available';
$city = $profile['city'] ?? '';
$state = $profile['state'] ?? '';
$emergencyContact = $profile['emergency_contact'] ?? 'Not Available';

// Profile photo
$profilePhoto = !empty($profile['profile_photo']) ? '../../' . $profile['profile_photo'] : '../../assets/img/default-profile.png';

// Format address
$fullAddress = trim($address);
if ($city) $fullAddress .= ($fullAddress ? ', ' : '') . $city;
if ($state) $fullAddress .= ($fullAddress ? ', ' : '') . $state;
if (!$fullAddress) $fullAddress = 'Not Available';

// Blood group (would need to be added to database in real implementation)
$bloodGroup = 'O+'; // Default value

// Emergency contact details (simplified)
$emergencyName = 'Emergency Contact';
$emergencyRelation = 'Contact Person';
if ($emergencyContact && $emergencyContact !== 'Not Available') {
    // Try to extract name from phone if it's formatted like "Name - Phone"
    if (strpos($emergencyContact, ' - ') !== false) {
        $parts = explode(' - ', $emergencyContact, 2);
        $emergencyName = $parts[0];
        $emergencyContact = $parts[1];
    }
}

// Generate QR code data
$qrData = urlencode($employeeId . '|' . $fullName . '|' . $department);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Digital ID Card</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/id-card.css">
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
        <h1 class="header-title">Digital ID Card</h1>
        <span class="header-subtitle">View and access your digital identification</span>
    </header>

    <main class="dashboard-content">
        <!-- Features Section -->
        <div class="id-card-features">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Digital Identity</h3>
                    <p>Your official digital identification for school premises access and verification</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Quick Access</h3>
                    <p>Scan the QR code for instant verification and digital attendance marking</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Secure Verification</h3>
                    <p>Tamper-proof digital verification with encrypted information for security</p>
                </div>
            </div>
        </div>

        <!-- ID Card Main Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Your Teacher ID Card</h2>
                <button class="btn btn-primary" id="downloadBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download ID Card
                </button>
            </div>
            <div class="card-body">
                <div class="id-card-container">
                    <div id="teacherIdCard" class="id-card">
                        <!-- Front of ID Card -->
                        <div class="id-card-front">
                            <div class="id-card-header">
                                <div class="school-logo">
                                    <img src="../../assets/images/school-logo.png" class="logo-image" alt="School Logo">
                                </div>
                                <h1 class="id-card-title">Vinodh English School</h1>
                                <p class="id-card-subtitle">Est. 1995</p>
                            </div>
                            <div class="id-card-content">
                                <div class="teacher-photo-container">
                                    <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Teacher Photo" class="teacher-photo">
                                </div>
                                <div class="teacher-info">
                                    <h2 class="teacher-name"><?php echo htmlspecialchars($fullName); ?></h2>
                                    <p class="teacher-role"><?php echo htmlspecialchars($position); ?></p>
                                    <div class="info-grid">
                                        <span class="info-label">Employee ID:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($employeeId); ?></span>
                                        
                                        <span class="info-label">Department:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($department); ?> Department</span>
                                        
                                        <span class="info-label">Date of Joining:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($joinedDate); ?></span>
                                        
                                        <span class="info-label">Blood Group:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($bloodGroup); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <div class="id-card-actions">
                    <button class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Share ID Card
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Function to toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');
        const body = document.querySelector('body');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        dashboardContainer.classList.toggle('sidebar-open');
    }
    
    // Function to handle page transitions
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event listeners to all navigation links
        const navLinks = document.querySelectorAll('a[href]:not([href^="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href.includes(window.location.hostname)) {
                    e.preventDefault();
                    
                    // Add exit animation class
                    document.body.classList.add('fade-out');
                    
                    // Navigate to new page after animation completes
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 500); // Match animation duration
                }
            });
        });
        
        // Initialize card search functionality
        const searchInput = document.getElementById('cardSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('.nav-card');
                
                cards.forEach(card => {
                    const cardTitle = card.querySelector('h3').textContent.toLowerCase();
                    const cardDesc = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
                    
                    if (cardTitle.includes(searchTerm) || cardDesc.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
        
        // Download ID Card functionality
        const downloadBtn = document.getElementById('downloadBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                alert('Your ID card is being prepared for download. It will be available shortly.');
                // In a real implementation, this would trigger HTML2Canvas or similar
                // to capture the ID card and convert it to an image for download
            });
        }
    });
</script>