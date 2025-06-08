// Global variables
let startDate, endDate, classFilter, sectionFilter, feeTypeFilter, filterBtn;
let currentPage = 1;
let itemsPerPage = 10;

/**
 * Initialize DOM elements when document is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get filter elements
    startDate = document.getElementById('start-date');
    endDate = document.getElementById('end-date');
    classFilter = document.getElementById('class-filter');
    sectionFilter = document.getElementById('section-filter');
    feeTypeFilter = document.getElementById('fee-type-filter');
    filterBtn = document.getElementById('filter-btn');
    
    // Add event listeners
    filterBtn.addEventListener('click', applyFilters);
    classFilter.addEventListener('change', loadSections);
    
    // Add event listener for refresh button
    document.getElementById('refresh-stats').addEventListener('click', function() {
        loadSummary();
    });
    
    // Add event listener for manual values
    document.getElementById('apply-manual-values').addEventListener('click', function() {
        applyManualValues();
    });
    
    // Load initial data
    loadClasses();
    loadFeeTypes();
    loadSummary();
    loadReports(1);
});

/**
 * Format a number as currency
 */
function formatCurrency(value) {
    // Handle null, undefined or empty values
    if (value === null || value === undefined || value === '') {
        return '₹0';
    }
    
    // Convert string to number if needed
    let numberValue = parseFloat(value);
    
    // Handle NaN
    if (isNaN(numberValue)) {
        return '₹0';
    }
    
    // Format the number with commas for thousands
    return '₹' + numberValue.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Apply manual values from the debug form
 */
function applyManualValues() {
    let totalFees = document.getElementById('manual-total-fees').value;
    let totalCollected = document.getElementById('manual-total-collected').value;
    let pendingFees = document.getElementById('manual-pending-fees').value;
    let overdueFees = document.getElementById('manual-overdue-fees').value;
    
    // Update summary values
    document.getElementById('totalFeesValue').textContent = formatCurrency(totalFees);
    document.getElementById('totalCollectedValue').textContent = formatCurrency(totalCollected);
    document.getElementById('pendingFeesValue').textContent = formatCurrency(pendingFees);
    document.getElementById('overdueFeesValue').textContent = formatCurrency(overdueFees);
    
    // Show success message
    alert('Values updated successfully!');
}

/**
 * Load classes for filter dropdown
 */
function loadClasses() {
    fetch('fee_reports_action.php?action=get_classes')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let options = '<option value="">All Classes</option>';
                data.classes.forEach(c => {
                    options += `<option value="${c.id}">${c.name}</option>`;
                });
                classFilter.innerHTML = options;
            }
        })
        .catch(error => console.error('Error loading classes:', error));
}

/**
 * Load sections based on selected class
 */
function loadSections() {
    const classId = classFilter.value;
    
    if (!classId) {
        sectionFilter.innerHTML = '<option value="">All Sections</option>';
        sectionFilter.disabled = true;
        return;
    }
    
    fetch(`fee_reports_action.php?action=get_sections&class_id=${classId}`)
        .then(response => response.json())
        .then(data => {
            sectionFilter.disabled = false;
            let options = '<option value="">All Sections</option>';
            
            if (data.success) {
                data.sections.forEach(s => {
                    options += `<option value="${s.id}">${s.name}</option>`;
                });
            }
            
            sectionFilter.innerHTML = options;
        })
        .catch(error => console.error('Error loading sections:', error));
}

/**
 * Load fee types for filter dropdown
 */
function loadFeeTypes() {
    fetch('fee_reports_action.php?action=get_fee_types')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let options = '<option value="">All Fee Types</option>';
                data.fee_types.forEach(ft => {
                    options += `<option value="${ft.id}">${ft.title}</option>`;
                });
                feeTypeFilter.innerHTML = options;
            }
        })
        .catch(error => console.error('Error loading fee types:', error));
}

/**
 * Apply filters and reload data
 */
function applyFilters() {
    loadSummary();
    loadReports(1);
}

/**
 * Load summary statistics
 */
function loadSummary() {
    // Show debug output for troubleshooting
    document.getElementById('debug-output').style.display = 'block';
    
    // Build query params
    let params = new URLSearchParams();
    params.append('action', 'get_summary');
    
    if (startDate && startDate.value) params.append('start_date', startDate.value);
    if (endDate && endDate.value) params.append('end_date', endDate.value);
    if (classFilter && classFilter.value) params.append('class_id', classFilter.value);
    if (sectionFilter && sectionFilter.value) params.append('section_id', sectionFilter.value);
    if (feeTypeFilter && feeTypeFilter.value) params.append('fee_type_id', feeTypeFilter.value);
    
    // Log request URL for debugging
    const apiUrl = 'fee_reports_action.php?' + params.toString();
    console.log('API request URL:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            console.log('Response status:', response.status);
            return response.text(); // Get raw text first for debugging
        })
        .then(text => {
            // Display raw response 
            document.getElementById('api-response-debug').textContent = text;
            console.log('Raw API response:', text);
            
            try {
                // Try to parse the response as JSON
                const data = JSON.parse(text);
                console.log('Parsed response:', data);
                
                if (data.success) {
                    // Update summary values
                    document.getElementById('totalFeesValue').textContent = formatCurrency(data.total_fees);
                    document.getElementById('totalCollectedValue').textContent = formatCurrency(data.total_collected);
                    document.getElementById('pendingFeesValue').textContent = formatCurrency(data.pending_fees);
                    document.getElementById('overdueFeesValue').textContent = formatCurrency(data.overdue_fees);
                    
                    // Update manual input fields for debugging
                    document.getElementById('manual-total-fees').value = data.total_fees;
                    document.getElementById('manual-total-collected').value = data.total_collected;
                    document.getElementById('manual-pending-fees').value = data.pending_fees;
                    document.getElementById('manual-overdue-fees').value = data.overdue_fees;
                    
                    // Initialize charts with this data
                    window.feeSummaryData = data;
                    
                    // Dispatch event to initialize charts
                    const event = new CustomEvent('feeSummaryLoaded', { detail: data });
                    document.dispatchEvent(event);
                } else {
                    console.error('Failed to load summary:', data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('api-response-debug').textContent = 'Fetch error: ' + error.message;
        });
}

/**
 * Load fee payment reports
 */
function loadReports(page) {
    currentPage = page;
    
    // Build query params
    let params = new URLSearchParams();
    params.append('action', 'get_reports');
    params.append('page', page);
    params.append('items_per_page', itemsPerPage);
    
    if (startDate && startDate.value) params.append('start_date', startDate.value);
    if (endDate && endDate.value) params.append('end_date', endDate.value);
    if (classFilter && classFilter.value) params.append('class_id', classFilter.value);
    if (sectionFilter && sectionFilter.value) params.append('section_id', sectionFilter.value);
    if (feeTypeFilter && feeTypeFilter.value) params.append('fee_type_id', feeTypeFilter.value);
    
    // Show loading state
    document.getElementById('reports-table-body').innerHTML = '<tr><td colspan="8" class="text-center">Loading...</td></tr>';
    
    fetch('fee_reports_action.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderReports(data.reports);
                renderPagination(data.total_items);
            } else {
                document.getElementById('reports-table-body').innerHTML = 
                    '<tr><td colspan="8" class="text-center">No reports found</td></tr>';
                document.getElementById('pagination').innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Error loading reports:', error);
            document.getElementById('reports-table-body').innerHTML = 
                '<tr><td colspan="8" class="text-center">Error loading reports</td></tr>';
        });
}

/**
 * Render reports in the table
 */
function renderReports(reports) {
    const tableBody = document.getElementById('reports-table-body');
    
    if (!reports || reports.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No reports found</td></tr>';
        return;
    }
    
    let html = '';
    
    reports.forEach(report => {
        let statusClass = '';
        
        switch(report.status) {
            case 'paid':
                statusClass = 'success';
                break;
            case 'partial':
                statusClass = 'warning';
                break;
            case 'pending':
                statusClass = 'danger';
                break;
        }
        
        html += `
            <tr>
                <td>${report.payment_id}</td>
                <td>${report.student_name}</td>
                <td>${report.admission_number}</td>
                <td>${report.class_name} ${report.section_name}</td>
                <td>${report.fee_title}</td>
                <td>${report.payment_date}</td>
                <td>${formatCurrency(report.amount_paid)}</td>
                <td>${formatCurrency(report.remaining_amount)}</td>
                <td><span class="badge badge-${statusClass}">${report.status}</span></td>
                <td>
                    <a href="fee_receipt.php?id=${report.payment_id}" class="btn btn-sm btn-primary" target="_blank">
                        <i class="fas fa-file-invoice"></i> Receipt
                    </a>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

/**
 * Render pagination links
 */
function renderPagination(totalItems) {
    const pagination = document.getElementById('pagination');
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadReports(${currentPage - 1}); return false;">
                Previous
            </a>
        </li>
    `;
    
    // Page numbers
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadReports(${i}); return false;">
                    ${i}
                </a>
            </li>
        `;
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadReports(${currentPage + 1}); return false;">
                Next
            </a>
        </li>
    `;
    
    html += '</ul>';
    pagination.innerHTML = html;
}

/**
 * Export reports to Excel or PDF
 */
function exportReports(format) {
    // Build query params
    let params = new URLSearchParams();
    params.append('action', 'export_reports');
    params.append('format', format);
    
    if (startDate && startDate.value) params.append('start_date', startDate.value);
    if (endDate && endDate.value) params.append('end_date', endDate.value);
    if (classFilter && classFilter.value) params.append('class_id', classFilter.value);
    if (sectionFilter && sectionFilter.value) params.append('section_id', sectionFilter.value);
    if (feeTypeFilter && feeTypeFilter.value) params.append('fee_type_id', feeTypeFilter.value);
    
    // Redirect to export URL
    window.location.href = 'fee_reports_action.php?' + params.toString();
} 