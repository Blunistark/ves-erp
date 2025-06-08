<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Fees</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/feesgenerate.css">
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
            <h1 class="header-title">Generate Fees</h1>
            <span class="header-path">Dashboard > Admin > Fees > Generate</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="feeTemplateSearch" class="search-input" placeholder="Search templates by name, class, academic year...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-primary" id="createTemplateBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Create Template
                    </button>
                    <button class="btn btn-success" id="generateFeesBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Generate Fees
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Templates</h3>
                <form class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Academic Year</label>
                        <select class="filter-select" id="yearFilter">
                            <option value="all">All Years</option>
                            <option value="2024-25" selected>2024-2025</option>
                            <option value="2023-24">2023-2024</option>
                            <option value="2022-23">2022-2023</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Class/Grade</label>
                        <select class="filter-select" id="classFilter">
                            <option value="all">All Classes</option>
                            <option value="7a">Grade 7A</option>
                            <option value="7b">Grade 7B</option>
                            <option value="8a">Grade 8A</option>
                            <option value="8b">Grade 8B</option>
                            <option value="9a">Grade 9A</option>
                            <option value="9b">Grade 9B</option>
                            <option value="10a">Grade 10A</option>
                            <option value="10b">Grade 10B</option>
                            <option value="11a">Grade 11A</option>
                            <option value="11b">Grade 11B</option>
                            <option value="12a">Grade 12A</option>
                            <option value="12b">Grade 12B</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Fee Type</label>
                        <select class="filter-select" id="feeTypeFilter">
                            <option value="all">All Types</option>
                            <option value="tuition">Tuition Fee</option>
                            <option value="admission">Admission Fee</option>
                            <option value="annual">Annual Fee</option>
                            <option value="exam">Examination Fee</option>
                            <option value="transport">Transportation Fee</option>
                            <option value="library">Library Fee</option>
                            <option value="lab">Laboratory Fee</option>
                            <option value="sports">Sports Fee</option>
                            <option value="other">Other Fees</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Created Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="filter-input" id="startDateFilter" style="flex: 1;">
                            <span style="align-self: center;">to</span>
                            <input type="date" class="filter-input" id="endDateFilter" style="flex: 1;">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Created By</label>
                        <select class="filter-select" id="createdByFilter">
                            <option value="all">All Users</option>
                            <option value="current">Current User</option>
                            <option value="admin">Admin</option>
                            <option value="finance">Finance Department</option>
                        </select>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>

            <!-- Tab System -->
            <div class="fees-tabs">
                <div class="fees-tab active" data-tab="templates">Fee Templates</div>
                <div class="fees-tab" data-tab="generate">Generate Fees</div>
                <div class="fees-tab" data-tab="history">Generation History</div>
            </div>

            <!-- Fee Template Creation Form -->
            <div class="fee-form-container" id="templateForm" style="display: none;">
                <h2 class="form-title">Create New Fee Template</h2>
                <form id="createTemplateForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="templateName">Template Name</label>
                            <input type="text" class="form-input" id="templateName" name="templateName" placeholder="Enter a descriptive name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="academicYear">Academic Year</label>
                            <select class="form-select" id="academicYear" name="academicYear" required>
                                <option value="">Select Academic Year</option>
                                <option value="2024-25" selected>2024-2025</option>
                                <option value="2023-24">2023-2024</option>
                                <option value="2022-23">2022-2023</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="feeType">Fee Type</label>
                            <select class="form-select" id="feeType" name="feeType" required>
                                <option value="">Select Fee Type</option>
                                <option value="tuition">Tuition Fee</option>
                                <option value="admission">Admission Fee</option>
                                <option value="annual">Annual Fee</option>
                                <option value="exam">Examination Fee</option>
                                <option value="transport">Transportation Fee</option>
                                <option value="library">Library Fee</option>
                                <option value="lab">Laboratory Fee</option>
                                <option value="sports">Sports Fee</option>
                                <option value="other">Other Fees</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="applicableClass">Applicable Class/Grade</label>
                            <select class="form-select" id="applicableClass" name="applicableClass" required>
                                <option value="">Select Class</option>
                                <option value="all">All Classes</option>
                                <option value="7a">Grade 7A</option>
                                <option value="7b">Grade 7B</option>
                                <option value="8a">Grade 8A</option>
                                <option value="8b">Grade 8B</option>
                                <option value="9a">Grade 9A</option>
                                <option value="9b">Grade 9B</option>
                                <option value="10a">Grade 10A</option>
                                <option value="10b">Grade 10B</option>
                                <option value="11a">Grade 11A</option>
                                <option value="11b">Grade 11B</option>
                                <option value="12a">Grade 12A</option>
                                <option value="12b">Grade 12B</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="dueDate">Due Date</label>
                            <input type="date" class="form-input" id="dueDate" name="dueDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="templateStatus">Status</label>
                            <select class="form-select" id="templateStatus" name="templateStatus" required>
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="templateDescription">Description</label>
                            <textarea class="form-textarea" id="templateDescription" name="templateDescription" placeholder="Enter a brief description of this fee template..."></textarea>
                        </div>
                    </div>
                    
                    <div class="fee-components-section">
                        <div class="fee-components-header">
                            <h3 class="fee-components-title">Fee Components</h3>
                        </div>
                        
                        <div class="fee-components-list">
                            <div class="fee-component-item">
                                <span class="component-name">Tuition Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$8,500.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fee-component-item">
                                <span class="component-name">Library Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$500.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fee-component-item">
                                <span class="component-name">Laboratory Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$750.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fee-component-item">
                                <span class="component-name">Examination Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$300.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; font-size: 1rem; font-weight: 600;">Add New Component</h4>
                        <div class="add-component-form">
                            <div class="form-group">
                                <label class="form-label" for="componentName">Component Name</label>
                                <input type="text" class="form-input" id="componentName" placeholder="Enter fee component name">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="componentAmount">Amount</label>
                                <input type="number" class="form-input" id="componentAmount" placeholder="Enter amount" min="0" step="0.01">
                            </div>
                            <button type="button" class="btn btn-primary" style="height: 42px;">Add Component</button>
                        </div>
                    </div>
                    
                    <div class="payment-schedule-section">
                        <div class="payment-schedule-header">
                            <h3 class="payment-schedule-title">Payment Schedule</h3>
                        </div>
                        
                        <div class="payment-schedule-list">
                            <div class="payment-schedule-item">
                                <div class="schedule-info">
                                    <span class="schedule-name">First Installment</span>
                                    <span class="schedule-details">Due on: April 15, 2025</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="schedule-amount">$5,025.00 (50%)</span>
                                    <div class="schedule-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-schedule-item">
                                <div class="schedule-info">
                                    <span class="schedule-name">Second Installment</span>
                                    <span class="schedule-details">Due on: July 15, 2025</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="schedule-amount">$5,025.00 (50%)</span>
                                    <div class="schedule-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; font-size: 1rem; font-weight: 600;">Add New Installment</h4>
                        <div class="add-schedule-form">
                            <div class="form-group">
                                <label class="form-label" for="installmentName">Installment Name</label>
                                <input type="text" class="form-input" id="installmentName" placeholder="Enter installment name">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="installmentDueDate">Due Date</label>
                                <input type="date" class="form-input" id="installmentDueDate">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="installmentAmount">Amount or Percentage</label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="number" class="form-input" id="installmentAmount" placeholder="Enter amount or %" min="0" style="flex: 1;">
                                    <select class="form-select" id="installmentType" style="width: 100px;">
                                        <option value="amount">Amount</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary">Add Installment</button>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelTemplateBtn">Cancel</button>
                        <button type="button" class="btn btn-outline" id="saveAsDraftBtn">Save as Draft</button>
                        <button type="submit" class="btn btn-primary">Create Template</button>
                    </div>
                </form>
            </div>

            <!-- Fee Generation Form -->
            <div class="fee-form-container" id="generateForm" style="display: none;">
                <h2 class="form-title">Generate Student Fees</h2>
                <form id="feeGenerationForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="feeTemplateSelect">Select Fee Template</label>
                            <select class="form-select" id="feeTemplateSelect" name="feeTemplateSelect" required>
                                <option value="">Select Template</option>
                                <option value="1">Annual Tuition Fees 2024-2025 (Grade 10)</option>
                                <option value="2">Admission Fees 2024-2025 (All Grades)</option>
                                <option value="3">Transportation Fees 2024-2025 (All Grades)</option>
                                <option value="4">Examination Fees 2024-2025 (All Grades)</option>
                                <option value="5">Laboratory Fees 2024-2025 (Grades 9-12)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="generateMode">Generation Mode</label>
                            <select class="form-select" id="generateMode" name="generateMode" required>
                                <option value="individual">Individual Student</option>
                                <option value="class">Entire Class</option>
                                <option value="batch">Batch of Students</option>
                            </select>
                        </div>
                    </div>

                    <div class="student-selection-section">
                        <div class="student-selection-header">
                            <h3 class="student-selection-title">Select Students</h3>
                            <div class="student-selection-toggle">
                                <button type="button" class="selection-toggle-btn active" data-toggle="individual">Individual</button>
                                <button type="button" class="selection-toggle-btn" data-toggle="class">Class</button>
                                <button type="button" class="selection-toggle-btn" data-toggle="batch">Batch</button>
                            </div>
                        </div>
                        
                        <!-- Individual Student Selection -->
                        <div class="student-selection-mode" id="individualSelection">
                            <div class="form-row">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label class="form-label" for="individualStudentSelect">Select Student</label>
                                    <select class="form-select" id="individualStudentSelect" name="individualStudentSelect">
                                        <option value="">Select Student</option>
                                        <option value="1">Alex Brown (ST001) - Grade 10A</option>
                                        <option value="2">Emma Smith (ST002) - Grade 10A</option>
                                        <option value="3">Michael Johnson (ST003) - Grade 10A</option>
                                        <option value="4">Sophia Davis (ST004) - Grade 10A</option>
                                        <option value="5">William Miller (ST005) - Grade 10A</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Class Selection -->
                        <div class="student-selection-mode" id="classSelection" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="classSelect">Select Class</label>
                                    <select class="form-select" id="classSelect" name="classSelect">
                                        <option value="">Select Class</option>
                                        <option value="7a">Grade 7A</option>
                                        <option value="7b">Grade 7B</option>
                                        <option value="8a">Grade 8A</option>
                                        <option value="8b">Grade 8B</option>
                                        <option value="9a">Grade 9A</option>
                                        <option value="9b">Grade 9B</option>
                                        <option value="10a">Grade 10A</option>
                                        <option value="10b">Grade 10B</option>
                                        <option value="11a">Grade 11A</option>
                                        <option value="11b">Grade 11B</option>
                                        <option value="12a">Grade 12A</option>
                                        <option value="12b">Grade 12B</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="totalStudents">Total Students</label>
                                    <input type="text" class="form-input" id="totalStudents" value="35" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Batch Selection -->
                        <div class="student-selection-mode" id="batchSelection" style="display: none;">
                            <div class="student-selection-controls">
                                <div class="student-filter-controls">
                                    <select class="filter-select">
                                        <option value="all">All Classes</option>
                                        <option value="10a">Grade 10A</option>
                                        <option value="10b">Grade 10B</option>
                                        <option value="11a">Grade 11A</option>
                                        <option value="11b">Grade 11B</option>
                                    </select>
                                    <select class="filter-select">
                                        <option value="all">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <input type="text" class="filter-input" placeholder="Search students...">
                                </div>
                                <div>
                                    <span><input type="checkbox" id="selectAllStudents"> Select All</span>
                                </div>
                            </div>
                            
                            <div class="student-table-container">
                                <table class="student-table">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAllInTable"></th>
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Status</th>
                                            <th>Last Fee Generated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox"></td>
                                            <td>
                                                <div class="student-info">
                                                    <div class="student-avatar">AB</div>
                                                    <div class="student-details">
                                                        <span class="student-name">Alex Brown</span>
                                                        <span class="student-id">ID: ST001</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Grade 10A</td>
                                            <td><span class="status-badge status-active">Active</span></td>
                                            <td>Mar 12, 2025</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox"></td>
                                            <td>
                                                <div class="student-info">
                                                    <div class="student-avatar">ES</div>
                                                    <div class="student-details">
                                                        <span class="student-name">Emma Smith</span>
                                                        <span class="student-id">ID: ST002</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Grade 10A</td>
                                            <td><span class="status-badge status-active">Active</span></td>
                                            <td>Mar 12, 2025</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox"></td>
                                            <td>
                                                <div class="student-info">
                                                    <div class="student-avatar">MJ</div>
                                                    <div class="student-details">
                                                        <span class="student-name">Michael Johnson</span>
                                                        <span class="student-id">ID: ST003</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Grade 10A</td>
                                            <td><span class="status-badge status-active">Active</span></td>
                                            <td>Mar 12, 2025</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox"></td>
                                            <td>
                                                <div class="student-info">
                                                    <div class="student-avatar">SD</div>
                                                    <div class="student-details">
                                                        <span class="student-name">Sophia Davis</span>
                                                        <span class="student-id">ID: ST004</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Grade 10A</td>
                                            <td><span class="status-badge status-active">Active</span></td>
                                            <td>Mar 12, 2025</td>
                                        </tr>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox"></td>
                                            <td>
                                                <div class="student-info">
                                                    <div class="student-avatar">WM</div>
                                                    <div class="student-details">
                                                        <span class="student-name">William Miller</span>
                                                        <span class="student-id">ID: ST005</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Grade 10A</td>
                                            <td><span class="status-badge status-active">Active</span></td>
                                            <td>Mar 12, 2025</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="student-selection-actions">
                                <span>5 students selected</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="batch-generation-section">
                        <div class="batch-generation-header">
                            <h3 class="batch-generation-title">Generation Options</h3>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="generationDate">Generation Date</label>
                                <input type="date" class="form-input" id="generationDate" name="generationDate" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="overwriteExisting">Existing Fees Handling</label>
                                <select class="form-select" id="overwriteExisting" name="overwriteExisting">
                                    <option value="skip">Skip if already exists</option>
                                    <option value="overwrite">Overwrite existing</option>
                                    <option value="append">Add as new (duplicate)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="discountPercent">Apply Discount (Optional)</label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="number" class="form-input" id="discountPercent" name="discountPercent" min="0" max="100" placeholder="Enter discount percentage" style="flex: 1;">
                                    <span style="align-self: center;">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="discountReason">Discount Reason</label>
                                <input type="text" class="form-input" id="discountReason" name="discountReason" placeholder="Enter reason for discount (optional)">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label class="form-label" for="generationNotes">Generation Notes</label>
                                <textarea class="form-textarea" id="generationNotes" name="generationNotes" placeholder="Enter any notes related to this fee generation..."></textarea>
                            </div>
                        </div>
                        
                        <div class="batch-summary">
                            <div class="batch-summary-header">
                                <h4 class="batch-summary-title">Batch Summary</h4>
                            </div>
                            <div class="batch-summary-stats">
                                <div class="batch-stat-item">
                                    <span class="batch-stat-label">Template</span>
                                    <span class="batch-stat-value">Annual Tuition 2024-25</span>
                                </div>
                                <div class="batch-stat-item">
                                    <span class="batch-stat-label">Students</span>
                                    <span class="batch-stat-value">1 Selected</span>
                                </div>
                                <div class="batch-stat-item">
                                    <span class="batch-stat-label">Total Amount</span>
                                    <span class="batch-stat-value">$10,050.00</span>
                                </div>
                                <div class="batch-stat-item">
                                    <span class="batch-stat-label">After Discount</span>
                                    <span class="batch-stat-value">$10,050.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="fee-preview-section">
                        <div class="fee-preview-header">
                            <h3 class="fee-preview-title">Preview Fee Structure</h3>
                            <button type="button" class="btn btn-outline">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Preview
                            </button>
                        </div>
                        
                        <div class="fee-preview-container">
                            <div class="fee-preview-school">
                                <h2 class="school-name">Vinod Henglish School</h2>
                                <p class="school-address">123 Education Lane, Academic District, City, 12345</p>
                            </div>
                            
                            <div class="fee-preview-title-section">
                                <h3 class="preview-title">Fee Structure: Annual Tuition Fees 2024-2025</h3>
                                <p class="preview-subtitle">Grade 10A</p>
                            </div>
                            
                            <div class="fee-preview-details">
                                <div class="preview-student-details">
                                    <h4 style="margin-bottom: 0.5rem; color: #4b5563;">Student Information</h4>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Student Name:</span>
                                        <span class="preview-detail-value">Alex Brown</span>
                                    </div>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Student ID:</span>
                                        <span class="preview-detail-value">ST001</span>
                                    </div>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Class/Grade:</span>
                                        <span class="preview-detail-value">Grade 10A</span>
                                    </div>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Academic Year:</span>
                                        <span class="preview-detail-value">2024-2025</span>
                                    </div>
                                </div>
                                
                                <div class="preview-fee-details">
                                    <h4 style="margin-bottom: 0.5rem; color: #4b5563;">Fee Details</h4>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Fee Type:</span>
                                        <span class="preview-detail-value">Annual Tuition</span>
                                    </div>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Issue Date:</span>
                                        <span class="preview-detail-value">March 15, 2025</span>
                                    </div>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Due Date:</span>
                                        <span class="preview-detail-value">April 15, 2025</span>
                                    </div>
                                    <div class="preview-detail-item">
                                        <span class="preview-detail-label">Fee ID:</span>
                                        <span class="preview-detail-value">FEE-2025-10001</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="fee-preview-components">
                                <h4 class="preview-components-title">Fee Components</h4>
                                <table class="preview-components-table">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Component</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Tuition Fee</td>
                                            <td>Annual Tuition Fee for Academic Year 2024-25</td>
                                            <td>$8,500.00</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Library Fee</td>
                                            <td>Annual Library Fee</td>
                                            <td>$500.00</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Laboratory Fee</td>
                                            <td>Science Laboratory Fee</td>
                                            <td>$750.00</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Examination Fee</td>
                                            <td>Annual Examination Fee</td>
                                            <td>$300.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="preview-totals">
                                    <div class="preview-total-item preview-subtotal">
                                        <span>Subtotal:</span>
                                        <span>$10,050.00</span>
                                    </div>
                                    <div class="preview-total-item preview-discount">
                                        <span>Discount:</span>
                                        <span>$0.00 (0%)</span>
                                    </div>
                                    <div class="preview-total-item preview-grand-total">
                                        <span>Grand Total:</span>
                                        <span>$10,050.00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="preview-payment-schedule">
                                <h4 class="preview-schedule-title">Payment Schedule</h4>
                                <table class="preview-schedule-table">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Installment</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>First Installment</td>
                                            <td>April 15, 2025</td>
                                            <td>$5,025.00</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Second Installment</td>
                                            <td>July 15, 2025</td>
                                            <td>$5,025.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="preview-notes">
                                <p><strong>Note:</strong> Please make payments before the due date to avoid late fees. Payments can be made through bank transfer, online payment portal, or at the school finance office.</p>
                                <p><strong>Bank Details:</strong> Bank Name - International Bank, Account No - 1234567890, IFSC - INTL0001234</p>
                            </div>
                            
                            <div class="preview-signature">
                                <div class="signature-box">
                                    <div class="signature-line"></div>
                                    <div class="signature-name">Parent/Guardian</div>
                                    <div class="signature-title">Signature</div>
                                </div>
                                <div class="signature-box">
                                    <div class="signature-line"></div>
                                    <div class="signature-name">Finance Officer</div>
                                    <div class="signature-title">Signature & Seal</div>
                                </div>
                                <div class="signature-box">
                                    <div class="signature-line"></div>
                                    <div class="signature-name">Principal</div>
                                    <div class="signature-title">Signature & Seal</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelGenerationBtn">Cancel</button>
                        <button type="button" class="btn btn-outline" id="saveAsDraftGenerationBtn">Save as Draft</button>
                        <button type="submit" class="btn btn-primary">Generate Fees</button>
                    </div>
                </form>
            </div>

            <!-- Fee Templates Tab Content -->
            <div class="tab-content active" id="templates-tab">
                <div class="performance-metrics">
                    <div class="metric-card metric-template">
                        <h3 class="metric-title">Total Templates</h3>
                        <div class="metric-value">12</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">3 added this year</span>
                        </div>
                    </div>
                    <div class="metric-card metric-generated">
                        <h3 class="metric-title">Fees Generated</h3>
                        <div class="metric-value">1,456</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">15% from last term</span>
                        </div>
                    </div>
                    <div class="metric-card metric-pending">
                        <h3 class="metric-title">Active Templates</h3>
                        <div class="metric-value">8</div>
                        <div class="metric-indicator">
                            <span style="color: #6b7280;">For academic year 2024-25</span>
                        </div>
                    </div>
                    <div class="metric-card metric-discounts">
                        <h3 class="metric-title">Total Discounts</h3>
                        <div class="metric-value">$32,450</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">5.2% from last term</span>
                        </div>
                    </div>
                </div>

                <div class="fee-template-cards">
                    <div class="fee-template-card">
                        <div class="fee-template-header">
                            <div class="fee-template-name">Annual Tuition Fees 2024-2025</div>
                            <span class="fee-template-badge status-active">Active</span>
                        </div>
                        
                        <div class="fee-template-details">
                            <div class="fee-template-detail">
                                <span class="detail-label">Applicable Class:</span>
                                <span class="detail-value">Grade 10</span>
                            </div>
<span class="detail-label">Academic Year:</span>
                                <span class="detail-value">2024-2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Fee Type:</span>
                                <span class="detail-value">Tuition Fee</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Due Date:</span>
                                <span class="detail-value">April 15, 2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">$10,050</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-components">
                            <h4 class="components-title">Components (4)</h4>
                            <div class="template-component-item">
                                <span>Tuition Fee</span>
                                <span>$8,500</span>
                            </div>
                            <div class="template-component-item">
                                <span>Library Fee</span>
                                <span>$500</span>
                            </div>
                            <div class="template-component-item">
                                <span>Laboratory Fee</span>
                                <span>$750</span>
                            </div>
                            <div class="template-component-item">
                                <span>Examination Fee</span>
                                <span>$300</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-actions">
                            <span style="color: #6b7280; font-size: 0.875rem;">Created: Mar 5, 2025</span>
                            <div class="template-btn-group">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Edit</button>
                                <button class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Generate</button>
                            </div>
                        </div>
                    </div>

                    <div class="fee-template-card">
                        <div class="fee-template-header">
                            <div class="fee-template-name">Admission Fees 2024-2025</div>
                            <span class="fee-template-badge status-active">Active</span>
                        </div>
                        
                        <div class="fee-template-details">
                            <div class="fee-template-detail">
                                <span class="detail-label">Applicable Class:</span>
                                <span class="detail-value">All Classes</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Academic Year:</span>
                                <span class="detail-value">2024-2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Fee Type:</span>
                                <span class="detail-value">Admission Fee</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Due Date:</span>
                                <span class="detail-value">Upon Admission</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">$5,000</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-components">
                            <h4 class="components-title">Components (3)</h4>
                            <div class="template-component-item">
                                <span>Admission Fee</span>
                                <span>$3,500</span>
                            </div>
                            <div class="template-component-item">
                                <span>Registration Fee</span>
                                <span>$1,000</span>
                            </div>
                            <div class="template-component-item">
                                <span>Development Fee</span>
                                <span>$500</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-actions">
                            <span style="color: #6b7280; font-size: 0.875rem;">Created: Feb 15, 2025</span>
                            <div class="template-btn-group">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Edit</button>
                                <button class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Generate</button>
                            </div>
                        </div>
                    </div>

                    <div class="fee-template-card">
                        <div class="fee-template-header">
                            <div class="fee-template-name">Transportation Fees 2024-2025</div>
                            <span class="fee-template-badge status-active">Active</span>
                        </div>
                        
                        <div class="fee-template-details">
                            <div class="fee-template-detail">
                                <span class="detail-label">Applicable Class:</span>
                                <span class="detail-value">All Classes</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Academic Year:</span>
                                <span class="detail-value">2024-2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Fee Type:</span>
                                <span class="detail-value">Transportation Fee</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Due Date:</span>
                                <span class="detail-value">Quarterly</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">$2,400</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-components">
                            <h4 class="components-title">Components (1)</h4>
                            <div class="template-component-item">
                                <span>Transportation Fee</span>
                                <span>$2,400</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-actions">
                            <span style="color: #6b7280; font-size: 0.875rem;">Created: Feb 20, 2025</span>
                            <div class="template-btn-group">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Edit</button>
                                <button class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Generate</button>
                            </div>
                        </div>
                    </div>

                    <div class="fee-template-card">
                        <div class="fee-template-header">
                            <div class="fee-template-name">Laboratory Fees 2024-2025</div>
                            <span class="fee-template-badge status-active">Active</span>
                        </div>
                        
                        <div class="fee-template-details">
                            <div class="fee-template-detail">
                                <span class="detail-label">Applicable Class:</span>
                                <span class="detail-value">Grades 9-12</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Academic Year:</span>
                                <span class="detail-value">2024-2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Fee Type:</span>
                                <span class="detail-value">Laboratory Fee</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Due Date:</span>
                                <span class="detail-value">April 15, 2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">$750</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-components">
                            <h4 class="components-title">Components (3)</h4>
                            <div class="template-component-item">
                                <span>Physics Lab</span>
                                <span>$250</span>
                            </div>
                            <div class="template-component-item">
                                <span>Chemistry Lab</span>
                                <span>$250</span>
                            </div>
                            <div class="template-component-item">
                                <span>Biology Lab</span>
                                <span>$250</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-actions">
                            <span style="color: #6b7280; font-size: 0.875rem;">Created: Mar 2, 2025</span>
                            <div class="template-btn-group">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Edit</button>
                                <button class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Generate</button>
                            </div>
                        </div>
                    </div>

                    <div class="fee-template-card">
                        <div class="fee-template-header">
                            <div class="fee-template-name">Annual Tuition Fees 2023-2024</div>
                            <span class="fee-template-badge status-archived">Archived</span>
                        </div>
                        
                        <div class="fee-template-details">
                            <div class="fee-template-detail">
                                <span class="detail-label">Applicable Class:</span>
                                <span class="detail-value">Grade 10</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Academic Year:</span>
                                <span class="detail-value">2023-2024</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Fee Type:</span>
                                <span class="detail-value">Tuition Fee</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Due Date:</span>
                                <span class="detail-value">April 15, 2024</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">$9,500</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-components">
                            <h4 class="components-title">Components (4)</h4>
                            <div class="template-component-item">
                                <span>Tuition Fee</span>
                                <span>$8,000</span>
                            </div>
                            <div class="template-component-item">
                                <span>Library Fee</span>
                                <span>$500</span>
                            </div>
                            <div class="template-component-item">
                                <span>Laboratory Fee</span>
                                <span>$700</span>
                            </div>
                            <div class="template-component-item">
                                <span>Examination Fee</span>
                                <span>$300</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-actions">
                            <span style="color: #6b7280; font-size: 0.875rem;">Created: Mar 2, 2024</span>
                            <div class="template-btn-group">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Clone</button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">View</button>
                            </div>
                        </div>
                    </div>

                    <div class="fee-template-card">
                        <div class="fee-template-header">
                            <div class="fee-template-name">Special Events Fee 2024</div>
                            <span class="fee-template-badge status-draft">Draft</span>
                        </div>
                        
                        <div class="fee-template-details">
                            <div class="fee-template-detail">
                                <span class="detail-label">Applicable Class:</span>
                                <span class="detail-value">All Classes</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Academic Year:</span>
                                <span class="detail-value">2024-2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Fee Type:</span>
                                <span class="detail-value">Event Fee</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Due Date:</span>
                                <span class="detail-value">May 30, 2025</span>
                            </div>
                            <div class="fee-template-detail">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value">$350</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-components">
                            <h4 class="components-title">Components (3)</h4>
                            <div class="template-component-item">
                                <span>Annual Day</span>
                                <span>$150</span>
                            </div>
                            <div class="template-component-item">
                                <span>Sports Day</span>
                                <span>$100</span>
                            </div>
                            <div class="template-component-item">
                                <span>Educational Trip</span>
                                <span>$100</span>
                            </div>
                        </div>
                        
                        <div class="fee-template-actions">
                            <span style="color: #6b7280; font-size: 0.875rem;">Created: Mar 10, 2025</span>
                            <div class="template-btn-group">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Edit</button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Publish</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="pagination">
                    <div class="pagination-info">
                        Showing 1-6 of 12 templates
                    </div>
                    <div class="pagination-buttons">
                        <button class="page-btn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Generate Fees Tab Content -->
            <div class="tab-content" id="generate-tab">
                <div class="fee-structure-container">
                    <div class="fee-structure-header">
                        <h3 class="fee-structure-title">Generate Student Fees</h3>
                        <button class="btn btn-primary" id="openGenerateFormBtn">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Generate New Fees
                        </button>
                    </div>
                    
                    <p>Select a template to generate fees for students. You can generate fees for individual students, entire classes, or a batch of selected students.</p>
                    
                    <div class="fee-table-container">
                        <table class="fee-table">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Class</th>
                                    <th>Academic Year</th>
                                    <th>Fee Type</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Annual Tuition Fees 2024-2025</td>
                                    <td>Grade 10</td>
                                    <td>2024-2025</td>
                                    <td>Tuition Fee</td>
                                    <td>$10,050</td>
                                    <td><span class="status-badge status-active">Active</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Template">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Generate Fees">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Admission Fees 2024-2025</td>
                                    <td>All Classes</td>
                                    <td>2024-2025</td>
                                    <td>Admission Fee</td>
                                    <td>$5,000</td>
                                    <td><span class="status-badge status-active">Active</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Template">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Generate Fees">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Transportation Fees 2024-2025</td>
                                    <td>All Classes</td>
                                    <td>2024-2025</td>
                                    <td>Transportation Fee</td>
                                    <td>$2,400</td>
                                    <td><span class="status-badge status-active">Active</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Template">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Generate Fees">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Laboratory Fees 2024-2025</td>
                                    <td>Grades 9-12</td>
                                    <td>2024-2025</td>
                                    <td>Laboratory Fee</td>
                                    <td>$750</td>
                                    <td><span class="status-badge status-active">Active</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Template">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Generate Fees">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Generation History Tab Content -->
            <div class="tab-content" id="history-tab">
                <div class="fee-structure-container">
                    <div class="fee-structure-header">
                        <h3 class="fee-structure-title">Fee Generation History</h3>
                        <div>
                            <select class="filter-select" style="margin-right: 0.5rem;">
                                <option value="all">All Time</option>
                                <option value="today" selected>Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                            <button class="btn btn-outline">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                    
                    <div class="fee-table-container">
                        <table class="fee-table">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Template</th>
                                    <th>Generated For</th>
                                    <th>Total Students</th>
                                    <th>Total Amount</th>
                                    <th>Generated On</th>
                                    <th>Generated By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>BG-25031501</td>
                                    <td>Annual Tuition Fees 2024-2025</td>
                                    <td>Grade 10A</td>
                                    <td>35</td>
                                    <td>$351,750</td>
                                    <td>Mar 15, 2025</td>
                                    <td>John Smith (Admin)</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Download Report">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Notifications">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>BG-25031502</td>
                                    <td>Annual Tuition Fees 2024-2025</td>
                                    <td>Grade 10B</td>
                                    <td>33</td>
                                    <td>$331,650</td>
                                    <td>Mar 15, 2025</td>
                                    <td>John Smith (Admin)</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Download Report">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Notifications">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>BG-25031503</td>
                                    <td>Transportation Fees 2024-2025</td>
                                    <td>All Enrolled Students</td>
                                    <td>256</td>
                                    <td>$614,400</td>
                                    <td>Mar 15, 2025</td>
                                    <td>Jane Doe (Finance)</td>
                                    <td><span class="status-badge status-pending">Processing</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Refresh Status">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>BG-25031401</td>
                                    <td>Laboratory Fees 2024-2025</td>
                                    <td>Grades 9-12</td>
                                    <td>145</td>
                                    <td>$108,750</td>
                                    <td>Mar 14, 2025</td>
                                    <td>Jane Doe (Finance)</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Download Report">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Notifications">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>BG-25031001</td>
                                    <td>Annual Tuition Fees 2024-2025</td>
                                    <td>Grade 11A</td>
                                    <td>30</td>
                                    <td>$301,500</td>
                                    <td>Mar 10, 2025</td>
                                    <td>John Smith (Admin)</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                    <td class="fee-actions">
                                        <button class="action-btn" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Download Report">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Notifications">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination">
                        <div class="pagination-info">
                            Showing 1-5 of 25 generation batches
                        </div>
                        <div class="pagination-buttons">
                            <button class="page-btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">4</button>
                            <button class="page-btn">5</button>
                            <button class="page-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            // Set current date as default for date inputs
            const today = new Date().toISOString().split('T')[0];
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                if (!input.value) {
                    input.value = today;
                }
            });
            
            // Tab Switching
            const tabs = document.querySelectorAll('.fees-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
            
            // Filter Panel Toggle
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterPanel = document.getElementById('filterPanel');
            
            filterToggleBtn.addEventListener('click', function() {
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            });
            
            // Template Form Toggle
            const createTemplateBtn = document.getElementById('createTemplateBtn');
            const templateForm = document.getElementById('templateForm');
            const cancelTemplateBtn = document.getElementById('cancelTemplateBtn');
            
            createTemplateBtn.addEventListener('click', function() {
                // Reset form
                document.getElementById('createTemplateForm').reset();
                
                // Set current date as due date
                document.getElementById('dueDate').value = today;
                
                // Show form
                templateForm.style.display = 'block';
                
                // Scroll to form
                templateForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            cancelTemplateBtn.addEventListener('click', function() {
                templateForm.style.display = 'none';
            });
            
            // Generate Fees Form Toggle
            const generateFeesBtn = document.getElementById('generateFeesBtn');
            const openGenerateFormBtn = document.getElementById('openGenerateFormBtn');
            const generateForm = document.getElementById('generateForm');
            const cancelGenerationBtn = document.getElementById('cancelGenerationBtn');
            
            function openGenerateForm() {
                // Reset form
                document.getElementById('feeGenerationForm').reset();
                
                // Set current date as generation date
                document.getElementById('generationDate').value = today;
                
                // Show form
                generateForm.style.display = 'block';
                
                // Scroll to form
                generateForm.scrollIntoView({ behavior: 'smooth' });
                
                // Switch to Templates tab first (since the form is in that tab)
                document.querySelector('.fees-tab[data-tab="generate"]').click();
            }
            
            generateFeesBtn.addEventListener('click', openGenerateForm);
            openGenerateFormBtn.addEventListener('click', openGenerateForm);
            
            cancelGenerationBtn.addEventListener('click', function() {
                generateForm.style.display = 'none';
            });
            
            // Generate mode toggling
            const generateMode = document.getElementById('generateMode');
            const selectionModes = document.querySelectorAll('.student-selection-mode');
            const selectionToggleBtns = document.querySelectorAll('.selection-toggle-btn');
            
            generateMode.addEventListener('change', function() {
                const mode = this.value;
                
                // Hide all selection modes
                selectionModes.forEach(mode => {
                    mode.style.display = 'none';
                });
                
                // Show the selected mode
                document.getElementById(`${mode}Selection`).style.display = 'block';
                
                // Update toggle buttons
                selectionToggleBtns.forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-toggle') === mode) {
                        btn.classList.add('active');
                    }
                });
            });
            
            // Selection toggle buttons
            selectionToggleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const mode = this.getAttribute('data-toggle');
                    
                    // Update generate mode select
                    generateMode.value = mode;
                    
                    // Hide all selection modes
                    selectionModes.forEach(mode => {
                        mode.style.display = 'none';
                    });
                    
                    // Show the selected mode
                    document.getElementById(`${mode}Selection`).style.display = 'block';
                    
                    // Update toggle buttons
                    selectionToggleBtns.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
            
            // Class selection change
            const classSelect = document.getElementById('classSelect');
            const totalStudents = document.getElementById('totalStudents');
            
            if (classSelect) {
                classSelect.addEventListener('change', function() {
                    // This would typically fetch the student count from the server
                    // For demo purposes, we'll use a static value
                    const classStudentCounts = {
                        '7a': 32,
                        '7b': 30,
                        '8a': 34,
                        '8b': 31,
                        '9a': 36,
                        '9b': 35,
                        '10a': 35,
                        '10b': 33,
                        '11a': 30,
                        '11b': 28,
                        '12a': 25,
                        '12b': 24
                    };
                    
                    const selectedClass = this.value;
                    if (selectedClass && classStudentCounts[selectedClass]) {
                        totalStudents.value = classStudentCounts[selectedClass];
                    } else {
                        totalStudents.value = '0';
                    }
                });
            }
            
            // Batch selection checkboxes
            const selectAllStudents = document.getElementById('selectAllStudents');
            const selectAllInTable = document.getElementById('selectAllInTable');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');
            
            if (selectAllStudents) {
                selectAllStudents.addEventListener('change', function() {
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    
                    if (selectAllInTable) {
                        selectAllInTable.checked = this.checked;
                    }
                    
                    updateSelectedCount();
                });
            }
            
            if (selectAllInTable) {
                selectAllInTable.addEventListener('change', function() {
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    
                    if (selectAllStudents) {
                        selectAllStudents.checked = this.checked;
                    }
                    
                    updateSelectedCount();
                });
            }
            
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount();
                    
                    // Update "Select All" checkbox state
                    if (selectAllInTable) {
                        selectAllInTable.checked = Array.from(studentCheckboxes).every(c => c.checked);
                    }
                    
                    if (selectAllStudents) {
                        selectAllStudents.checked = Array.from(studentCheckboxes).every(c => c.checked);
                    }
                });
            });
            
            function updateSelectedCount() {
                const selectedStudentsSpan = document.querySelector('.student-selection-actions span');
                if (selectedStudentsSpan) {
                    const selectedCount = Array.from(studentCheckboxes).filter(c => c.checked).length;
                    selectedStudentsSpan.textContent = `${selectedCount} students selected`;
                }
            }
            
            // Fee component management
            const addComponentBtn = document.querySelector('.add-component-form .btn-primary');
            const componentName = document.getElementById('componentName');
            const componentAmount = document.getElementById('componentAmount');
            const componentsContainer = document.querySelector('.fee-components-list');
            
            if (addComponentBtn && componentName && componentAmount) {
                addComponentBtn.addEventListener('click', function() {
                    if (!componentName.value || !componentAmount.value) {
                        alert('Please enter both component name and amount');
                        return;
                    }
                    
                    // Create new component item
                    const newComponent = document.createElement('div');
                    newComponent.className = 'fee-component-item';
                    newComponent.innerHTML = `
                        <span class="component-name">${componentName.value}</span>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="component-amount">$${parseFloat(componentAmount.value).toFixed(2)}</span>
                            <div class="component-actions">
                                <button type="button" class="action-btn" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" class="action-btn" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Add event listeners to the new component's buttons
                    const editBtn = newComponent.querySelector('.action-btn[title="Edit"]');
                    const deleteBtn = newComponent.querySelector('.action-btn[title="Delete"]');
                    
                    editBtn.addEventListener('click', function() {
                        const componentElement = this.closest('.fee-component-item');
                        const nameElement = componentElement.querySelector('.component-name');
                        const amountElement = componentElement.querySelector('.component-amount');
                        
                        // Get current values
                        const currentName = nameElement.textContent;
                        const currentAmount = amountElement.textContent.replace('$', '');
                        
                        // Set the form fields
                        componentName.value = currentName;
                        componentAmount.value = currentAmount;
                        
                        // Remove the component (will be re-added when the "Add" button is clicked)
                        componentElement.remove();
                    });
                    
                    deleteBtn.addEventListener('click', function() {
                        if (confirm('Are you sure you want to remove this component?')) {
                            this.closest('.fee-component-item').remove();
                        }
                    });
                    
                    // Add to the container
                    componentsContainer.appendChild(newComponent);
                    
                    // Clear the form fields
                    componentName.value = '';
                    componentAmount.value = '';
                });
            }
            
            // Add event listeners to existing component buttons
            const editComponentBtns = document.querySelectorAll('.fee-component-item .action-btn[title="Edit"]');
            const deleteComponentBtns = document.querySelectorAll('.fee-component-item .action-btn[title="Delete"]');
            
            editComponentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const componentElement = this.closest('.fee-component-item');
                    const nameElement = componentElement.querySelector('.component-name');
                    const amountElement = componentElement.querySelector('.component-amount');
                    
                    // Get current values
                    const currentName = nameElement.textContent;
                    const currentAmount = amountElement.textContent.replace('$', '');
                    
                    // Set the form fields
                    componentName.value = currentName;
                    componentAmount.value = currentAmount;
                    
                    // Remove the component (will be re-added when the "Add" button is clicked)
                    componentElement.remove();
                });
            });
            
            deleteComponentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove this component?')) {
                        this.closest('.fee-component-item').remove();
                    }
                });
            });
            
            // Payment schedule management
            const addInstallmentBtn = document.querySelector('.add-schedule-form .btn-primary');
            const installmentName = document.getElementById('installmentName');
            const installmentDueDate = document.getElementById('installmentDueDate');
            const installmentAmount = document.getElementById('installmentAmount');
            const installmentType = document.getElementById('installmentType');
            const scheduleContainer = document.querySelector('.payment-schedule-list');
            
            if (addInstallmentBtn && installmentName && installmentDueDate && installmentAmount && installmentType) {
                addInstallmentBtn.addEventListener('click', function() {
                    if (!installmentName.value || !installmentDueDate.value || !installmentAmount.value) {
                        alert('Please fill all installment fields');
                        return;
                    }
                    
                    // Format the amount based on type
                    let formattedAmount;
                    if (installmentType.value === 'percentage') {
                        formattedAmount = `$5,025.00 (${installmentAmount.value}%)`;
                    } else {
                        formattedAmount = `$${parseFloat(installmentAmount.value).toFixed(2)}`;
                    }
                    
                    // Format the due date
                    const dueDate = new Date(installmentDueDate.value);
                    const formattedDate = dueDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    
                    // Create new installment item
                    const newInstallment = document.createElement('div');
                    newInstallment.className = 'payment-schedule-item';
                    newInstallment.innerHTML = `
                        <div class="schedule-info">
                            <span class="schedule-name">${installmentName.value}</span>
                            <span class="schedule-details">Due on: ${formattedDate}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="schedule-amount">${formattedAmount}</span>
                            <div class="schedule-actions">
                                <button type="button" class="action-btn" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" class="action-btn" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Add event listeners to the new installment's buttons
                    const editBtn = newInstallment.querySelector('.action-btn[title="Edit"]');
                    const deleteBtn = newInstallment.querySelector('.action-btn[title="Delete"]');
                    
                    editBtn.addEventListener('click', function() {
                        const installmentElement = this.closest('.payment-schedule-item');
                        const nameElement = installmentElement.querySelector('.schedule-name');
                        const detailsElement = installmentElement.querySelector('.schedule-details');
                        const amountElement = installmentElement.querySelector('.schedule-amount');
                        
                        // Get current values
                        const currentName = nameElement.textContent;
                        const currentDueDate = detailsElement.textContent.replace('Due on: ', '');
                        let currentAmount = amountElement.textContent;
                        let currentType = 'amount';
                        
                        // Parse the amount
                        if (currentAmount.includes('(')) {
                            const percentage = currentAmount.match(/\((\d+)%\)/)[1];
                            installmentAmount.value = percentage;
                            installmentType.value = 'percentage';
                        } else {
                            const amount = currentAmount.replace('$', '').replace(',', '');
                            installmentAmount.value = amount;
                            installmentType.value = 'amount';
                        }
                        
                        // Set form fields
                        installmentName.value = currentName;
                        
                        // Parse and set date
                        const dateParts = currentDueDate.split(' ');
                        const month = new Date(Date.parse(dateParts[0] + ' 1, 2000')).getMonth() + 1;
                        const day = dateParts[1].replace(',', '');
                        const year = dateParts[2];
                        const isoDate = `${year}-${month.toString().padStart(2, '0')}-${day.padStart(2, '0')}`;
                        installmentDueDate.value = isoDate;
                        
                        // Remove the installment
                        installmentElement.remove();
                    });
                    
                    deleteBtn.addEventListener('click', function() {
                        if (confirm('Are you sure you want to remove this installment?')) {
                            this.closest('.payment-schedule-item').remove();
                        }
                    });
                    
                    // Add to container
                    scheduleContainer.appendChild(newInstallment);
                    
                    // Clear form fields
                    installmentName.value = '';
                    installmentDueDate.value = today;
                    installmentAmount.value = '';
                });
            }
            
            // Discount calculations
            const discountPercent = document.getElementById('discountPercent');
            const previewDiscount = document.querySelector('.preview-discount');
            const previewGrandTotal = document.querySelector('.preview-grand-total');
            const batchSummaryDiscount = document.querySelector('.batch-summary-stats .batch-stat-value:last-child');
            
            if (discountPercent && previewDiscount && previewGrandTotal) {
                discountPercent.addEventListener('input', function() {
                    const discount = parseFloat(this.value) || 0;
                    const subtotal = 10050; // Hardcoded for demo
                    
                    const discountAmount = (subtotal * discount) / 100;
                    const grandTotal = subtotal - discountAmount;
                    
                    previewDiscount.innerHTML = `<span>Discount:</span><span>$${discountAmount.toFixed(2)} (${discount}%)</span>`;
                    previewGrandTotal.innerHTML = `<span>Grand Total:</span><span>$${grandTotal.toFixed(2)}</span>`;
                    
                    if (batchSummaryDiscount) {
                        batchSummaryDiscount.textContent = `$${grandTotal.toFixed(2)}`;
                    }
                });
            }
            
            // Template search
            const templateSearch = document.getElementById('feeTemplateSearch');
            const templateCards = document.querySelectorAll('.fee-template-card');
            const templateTableRows = document.querySelectorAll('.fee-table tbody tr');
            
            if (templateSearch) {
                templateSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    // Search in template cards
                    if (templateCards.length > 0) {
                        templateCards.forEach(card => {
                            const templateName = card.querySelector('.fee-template-name').textContent.toLowerCase();
                            const classValue = card.querySelector('.detail-value').textContent.toLowerCase();
                            const yearValue = card.querySelectorAll('.detail-value')[1].textContent.toLowerCase();
                            
                            const matchFound = 
                                templateName.includes(searchTerm) || 
                                classValue.includes(searchTerm) || 
                                yearValue.includes(searchTerm);
                            
                            card.style.display = matchFound ? '' : 'none';
                        });
                    }
                    
                    // Search in table rows
                    if (templateTableRows.length > 0) {
                        templateTableRows.forEach(row => {
                            const columns = row.querySelectorAll('td');
                            const templateName = columns[0].textContent.toLowerCase();
                            const className = columns[1].textContent.toLowerCase();
                            const academicYear = columns[2].textContent.toLowerCase();
                            
                            const matchFound = 
                                templateName.includes(searchTerm) || 
                                className.includes(searchTerm) || 
                                academicYear.includes(searchTerm);
                            
                            row.style.display = matchFound ? '' : 'none';
                        });
                    }
                });
            }
            
            // Filter functionality
            const filterForm = document.querySelector('.filter-form');
            const filterApplyBtn = document.querySelector('.filter-btn-apply');
            const filterResetBtn = document.querySelector('.filter-btn-reset');
            
            if (filterApplyBtn) {
                filterApplyBtn.addEventListener('click', function() {
                    // Get filter values
                    const yearFilter = document.getElementById('yearFilter').value;
                    const classFilter = document.getElementById('classFilter').value;
                    const feeTypeFilter = document.getElementById('feeTypeFilter').value;
                    const statusFilter = document.getElementById('statusFilter').value;
                    const startDateFilter = document.getElementById('startDateFilter').value;
                    const endDateFilter = document.getElementById('endDateFilter').value;
                    const createdByFilter = document.getElementById('createdByFilter').value;
                    
                    // In a real implementation, this would filter the templates based on the filters
                    
                    // For this demo, just show an alert with the selected filters
                    let filterMessage = 'Applied filters:\n';
                    filterMessage += yearFilter !== 'all' ? `Academic Year: ${yearFilter}\n` : 'Academic Year: All\n';
                    filterMessage += classFilter !== 'all' ? `Class: ${classFilter}\n` : 'Class: All\n';
                    filterMessage += feeTypeFilter !== 'all' ? `Fee Type: ${feeTypeFilter}\n` : 'Fee Type: All\n';
                    filterMessage += statusFilter !== 'all' ? `Status: ${statusFilter}\n` : 'Status: All\n';
                    filterMessage += startDateFilter ? `Start Date: ${startDateFilter}\n` : 'Start Date: -\n';
                    filterMessage += endDateFilter ? `End Date: ${endDateFilter}\n` : 'End Date: -\n';
                    filterMessage += createdByFilter !== 'all' ? `Created By: ${createdByFilter}\n` : 'Created By: All\n';
                    
                    alert(filterMessage);
                    
                    // Hide the filter panel
                    filterPanel.style.display = 'none';
                });
            }
            
            if (filterResetBtn) {
                filterResetBtn.addEventListener('click', function() {
                    if (filterForm) {
                        filterForm.reset();
                    }
                });
            }
            
            // Form submissions
            const createTemplateForm = document.getElementById('createTemplateForm');
            const feeGenerationForm = document.getElementById('feeGenerationForm');
            
            if (createTemplateForm) {
                createTemplateForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate required fields
                    const templateName = document.getElementById('templateName').value;
                    const academicYear = document.getElementById('academicYear').value;
                    const feeType = document.getElementById('feeType').value;
                    const applicableClass = document.getElementById('applicableClass').value;
                    
                    if (!templateName || !academicYear || !feeType || !applicableClass) {
                        alert('Please fill in all required fields');
                        return;
                    }
                    
                    // In a real implementation, this would submit the form via AJAX or redirect
                    alert('Fee template created successfully!');
                    
                    // Hide the form
                    templateForm.style.display = 'none';
                    
                    // Reload the page to show the new template
                    // window.location.reload();
                });
            }
            
            if (feeGenerationForm) {
                feeGenerationForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate required fields
                    const feeTemplateSelect = document.getElementById('feeTemplateSelect').value;
                    const generateMode = document.getElementById('generateMode').value;
                    
                    let selectedStudents = false;
                    if (generateMode === 'individual') {
                        selectedStudents = document.getElementById('individualStudentSelect').value;
                    } else if (generateMode === 'class') {
                        selectedStudents = document.getElementById('classSelect').value;
                    } else if (generateMode === 'batch') {
                        selectedStudents = Array.from(document.querySelectorAll('.student-checkbox')).some(c => c.checked);
                    }
                    
                    if (!feeTemplateSelect) {
                        alert('Please select a fee template');
                        return;
                    }
                    
                    if (!selectedStudents) {
                        alert('Please select at least one student');
                        return;
                    }
                    
                    // In a real implementation, this would submit the form via AJAX or redirect
                    alert('Fees generated successfully!');
                    
                    // Hide the form
                    generateForm.style.display = 'none';
                    
                    // Switch to the History tab to show the new generation
                    document.querySelector('.fees-tab[data-tab="history"]').click();
                });
            }
            
            // Saving as draft
            const saveAsDraftBtn = document.getElementById('saveAsDraftBtn');
            const saveAsDraftGenerationBtn = document.getElementById('saveAsDraftGenerationBtn');
            
            if (saveAsDraftBtn) {
                saveAsDraftBtn.addEventListener('click', function() {
                    alert('Template saved as draft');
                    templateForm.style.display = 'none';
                });
            }
            
            if (saveAsDraftGenerationBtn) {
                saveAsDraftGenerationBtn.addEventListener('click', function() {
                    alert('Fee generation saved as draft');
                    generateForm.style.display = 'none';
                });
            }
            
            // Template card actions
            const templateEditBtns = document.querySelectorAll('.fee-template-card .btn-outline');
            const templateGenerateBtns = document.querySelectorAll('.fee-template-card .btn-primary');
            
            templateEditBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const templateCard = this.closest('.fee-template-card');
                    const templateName = templateCard.querySelector('.fee-template-name').textContent;
                    
                    // In a real implementation, this would load the template data into the form
                    
                    // For this demo, just show an alert
                    if (this.textContent.trim() === 'Clone') {
                        alert(`Cloning template: ${templateName}`);
                    } else if (this.textContent.trim() === 'Publish') {
                        alert(`Publishing template: ${templateName}`);
                    } else {
                        alert(`Editing template: ${templateName}`);
                    }
                });
            });
            
            templateGenerateBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const templateCard = this.closest('.fee-template-card');
                    const templateName = templateCard.querySelector('.fee-template-name').textContent;
                    
                    // Open generation form and set the template
                    openGenerateForm();
                    
                    // In a real implementation, this would select the template in the dropdown
                    // For this demo, we'll just show an alert
                    alert(`Selected template: ${templateName} for generation`);
                });
            });
            
            // Generation table view details buttons
            const viewDetailsBtns = document.querySelectorAll('.action-btn[title="View Details"]');
            
            viewDetailsBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    let entityName = '';
                    
                    if (row.cells[0].textContent.startsWith('BG-')) {
                        // Generation history table
                        entityName = row.cells[1].textContent;
                        alert(`Viewing details for batch: ${entityName}`);
                    } else {
                        // Template table
                        entityName = row.cells[0].textContent;
                        alert(`Viewing details for template: ${entityName}`);
                    }
                });
            });
            
            // Pagination
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const parentPagination = this.closest('.pagination');
                    const pageButtons = parentPagination.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
                    
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert(`Loading page ${this.textContent}...`);
                    }
                });
            });
        });
    </script>
</body>
</html>