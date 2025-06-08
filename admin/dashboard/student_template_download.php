<?php
require_once 'con.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="student_import_template.csv"');
header('Pragma: no-cache');

// Create CSV file
$output = fopen('php://output', 'w');

// Define CSV headers that match our expected import format
$headers = [
    'Admission Number',
    'First Name',
    'Middle Name',
    'Last Name',
    'Full Name',
    'Date of Birth',
    'Gender',
    'Blood Group',
    'Class',
    'Section',
    'Admission Date',
    'Roll Number',
    'Email',
    'Phone',
    'Alt Phone',
    'Medical Conditions',
    'Father\'s Name',
    'Mother\'s Name',
    'Address',
    'Pincode',
    'City',
    'Nationality',
    'Aadhar Card',
    'Academic Year',
    'Mother Tongue'
];

// Write headers
fputcsv($output, $headers);

// Sample data (optional)
$sample = [
    'ADMN001',              // Admission Number
    'John',                 // First Name
    '',                     // Middle Name
    'Doe',                  // Last Name
    'John Doe',             // Full Name
    '2010-05-15',           // Date of Birth
    'male',                 // Gender
    'A+',                   // Blood Group
    'Class 1',              // Class
    'A',                    // Section
    date('Y-m-d'),          // Admission Date
    '1',                    // Roll Number
    'johndoe@example.com',  // Email
    '9876543210',           // Phone
    '',                     // Alt Phone
    '',                     // Medical Conditions
    'James Doe',            // Father's Name
    'Jane Doe',             // Mother's Name
    '123 Main St',          // Address
    '500001',               // Pincode
    'Hyderabad',            // City
    'Indian',               // Nationality
    '',                     // Aadhar Card
    '2023-2024',            // Academic Year
    'English'               // Mother Tongue
];

// Write sample row (uncomment if you want a sample row)
fputcsv($output, $sample);

// Close the output stream
fclose($output);
?> 