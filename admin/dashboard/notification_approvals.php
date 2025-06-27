<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Approvals - Admin Dashboard</title>
    <link rel="stylesheet" href="css/sidebar.css">    <style>
        /* Sidebar optimization and main content spacing */
        .main-content {
            margin-left: 250px;
            padding: 0;
            background-color: #f8fafc;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 70px;
        }
        
        /* Mobile responsive for sidebar */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 0;
            }
            
            .sidebar.active ~ .main-content {
                margin-left: 0;
            }
        }

        .approval-dashboard {
            padding: 2rem;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .header-section {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-section h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .header-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }

        .pending-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .pending-header {
            background: #fef3c7;
            padding: 1rem 1.5rem;
            border-left: 4px solid #f59e0b;
        }

        .pending-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.5rem;
        }

        .pending-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #78716c;
            flex-wrap: wrap;
        }

        .pending-content {
            padding: 1.5rem;
        }

        .notification-preview {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .notification-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .notification-message {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .notification-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            font-size: 0.875rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }

        .detail-value {
            color: #111827;
        }

        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .priority-normal { background: #dcfce7; color: #166534; }
        .priority-important { background: #fef3c7; color: #92400e; }
        .priority-urgent { background: #fee2e2; color: #991b1b; }

        .action-section {
            border-top: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
            background: #f9fafb;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-approve {
            background-color: #10b981;
            color: white;
        }

        .btn-approve:hover {
            background-color: #059669;
        }

        .btn-reject {
            background-color: #ef4444;
            color: white;
        }

        .btn-reject:hover {
            background-color: #dc2626;
        }

        .comments-section {
            margin-top: 1rem;
        }

        .comments-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
            min-height: 80px;
        }

        .comments-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .no-approvals {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .tabs-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-button {
            flex: 1;
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s ease;
            border-radius: 12px 12px 0 0;
            position: relative;
        }

        .tab-button.active {
            background-color: #3b82f6;
            color: white;
        }

        .tab-button:hover:not(.active) {
            background-color: #f3f4f6;
            color: #374151;
        }

        .tab-content {
            display: none;
            padding: 2rem;
        }

        .tab-content.active {
            display: block;
        }

        .processed-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .approved-header {
            background: #ecfdf5;
            padding: 1rem 1.5rem;
            border-left: 4px solid #10b981;
        }

        .rejected-header {
            background: #fef2f2;
            padding: 1rem 1.5rem;
            border-left: 4px solid #ef4444;
        }

        .processed-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .approved-header .processed-title {
            color: #065f46;
        }

        .rejected-header .processed-title {
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .approval-dashboard {
                padding: 1rem;
            }

            .pending-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }    </style>
</head>
<body>
    <div class="main-content">
        <div class="approval-dashboard">
        <div class="header-section">
            <h1>Notification Approvals</h1>
            <p>Review and approve teacher notification requests</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div id="pendingCount" class="stat-number">0</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
            <div class="stat-card">
                <div id="approvedToday" class="stat-number">0</div>
                <div class="stat-label">Approved Today</div>
            </div>
            <div class="stat-card">
                <div id="rejectedToday" class="stat-number">0</div>
                <div class="stat-label">Rejected Today</div>
            </div>
        </div>        <div id="alertContainer"></div>
        
        <div class="tabs-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="switchTab('pending')">
                    Pending Approvals (<span id="pendingTabCount">0</span>)
                </button>
                <button class="tab-button" onclick="switchTab('approved')">
                    Approved (<span id="approvedTabCount">0</span>)
                </button>
                <button class="tab-button" onclick="switchTab('rejected')">
                    Rejected (<span id="rejectedTabCount">0</span>)
                </button>
            </div>

            <div id="pending-tab" class="tab-content active">
                <div id="pendingApprovals">
                    <div id="loading" class="loading">Loading pending approvals...</div>
                </div>
            </div>

            <div id="approved-tab" class="tab-content">
                <div id="approvedNotifications">
                    <div id="approvedLoading" class="loading" style="display: none;">Loading approved notifications...</div>
                </div>
            </div>

            <div id="rejected-tab" class="tab-content">
                <div id="rejectedNotifications">
                    <div id="rejectedLoading" class="loading" style="display: none;">Loading rejected notifications...</div>
                </div>
            </div>        </div>
    </div> <!-- End approval-dashboard -->
    </div> <!-- End main-content -->

    <script>
        let pendingApprovals = [];        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadPendingApprovals();
            loadStats();
        });

        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');

            // Load data for the selected tab
            if (tabName === 'pending') {
                loadPendingApprovals();
            } else if (tabName === 'approved') {
                loadApprovedNotifications();
            } else if (tabName === 'rejected') {
                loadRejectedNotifications();
            }
        }

        function loadPendingApprovals() {
            fetch('/erp/backend/api/notifications.php?action=get_pending_approvals')
                .then(response => response.json())
                .then(data => {
                    const loading = document.getElementById('loading');
                    const container = document.getElementById('pendingApprovals');
                    
                    loading.style.display = 'none';
                    
                    if (data.success) {
                        pendingApprovals = data.data;
                        renderPendingApprovals(pendingApprovals);
                        updatePendingCount(pendingApprovals.length);
                        document.getElementById('pendingTabCount').textContent = pendingApprovals.length;
                    } else {
                        container.innerHTML = `<div class="alert alert-error">Error loading approvals: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('pendingApprovals').innerHTML = 
                        '<div class="alert alert-error">Network error loading approvals</div>';
                });
        }

        function renderPendingApprovals(approvals) {
            const container = document.getElementById('pendingApprovals');
            
            if (approvals.length === 0) {
                container.innerHTML = `
                    <div class="no-approvals">
                        <h3>No Pending Approvals</h3>
                        <p>All teacher notification requests have been processed.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            approvals.forEach(approval => {
                html += renderApprovalCard(approval);
            });
            
            container.innerHTML = html;
        }

        function renderApprovalCard(approval) {
            const submittedDate = new Date(approval.submitted_at).toLocaleDateString();
            const submittedTime = new Date(approval.submitted_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const priorityClass = `priority-${approval.priority}`;
            
            return `
                <div class="pending-card" id="approval-${approval.approval_id}">
                    <div class="pending-header">
                        <div class="pending-title">Request from ${approval.teacher_name}</div>
                        <div class="pending-meta">
                            <span>📧 ${approval.teacher_email}</span>
                            <span>📅 ${submittedDate} at ${submittedTime}</span>
                            <span>📝 ${approval.teacher_request_message || 'No message provided'}</span>
                        </div>
                    </div>
                    
                    <div class="pending-content">
                        <div class="notification-preview">
                            <div class="notification-title">${approval.title}</div>
                            <div class="notification-message">${approval.message}</div>
                            
                            <div class="notification-details">
                                <div class="detail-item">
                                    <span class="detail-label">Type:</span>
                                    <span class="detail-value">${approval.type}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Priority:</span>
                                    <span class="detail-value">
                                        <span class="priority-badge ${priorityClass}">${approval.priority}</span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Target:</span>
                                    <span class="detail-value">${formatTargetType(approval.target_type, approval.target_value)}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Requires Ack:</span>
                                    <span class="detail-value">${approval.requires_acknowledgment ? 'Yes' : 'No'}</span>
                                </div>
                                ${approval.expires_at ? `
                                <div class="detail-item">
                                    <span class="detail-label">Expires:</span>
                                    <span class="detail-value">${new Date(approval.expires_at).toLocaleDateString()}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div class="action-section">
                            <div class="action-buttons">
                                <button class="btn btn-approve" onclick="approveNotification(${approval.approval_id})">
                                    ✅ Approve & Send
                                </button>
                                <button class="btn btn-reject" onclick="rejectNotification(${approval.approval_id})">
                                    ❌ Reject
                                </button>
                            </div>
                            
                            <div class="comments-section">
                                <label for="comments-${approval.approval_id}">Admin Comments (optional):</label>
                                <textarea 
                                    id="comments-${approval.approval_id}" 
                                    class="comments-textarea" 
                                    placeholder="Add any comments for the teacher..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function formatTargetType(type, value) {
            switch(type) {
                case 'all_school': return 'Entire School';
                case 'role': return `Role: ${value}`;
                case 'class': return `Class: ${value}`;
                case 'section': return `Section: ${value}`;
                case 'multiple_classes': return 'Multiple Classes/Sections';
                default: return `${type}: ${value}`;
            }
        }

        function approveNotification(approvalId) {
            const comments = document.getElementById(`comments-${approvalId}`).value;
            
            if (confirm('Are you sure you want to approve this notification? It will be sent immediately.')) {
                const data = {
                    approval_id: approvalId,
                    admin_comments: comments
                };

                fetch('/erp/backend/api/notifications.php?action=approve_notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showAlert('Notification approved and sent successfully!', 'success');
                        document.getElementById(`approval-${approvalId}`).remove();
                        updatePendingCount(document.querySelectorAll('.pending-card').length);
                        loadStats(); // Refresh stats
                    } else {
                        showAlert(`Error approving notification: ${result.message}`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Network error while approving notification', 'error');
                });
            }
        }

        function rejectNotification(approvalId) {
            const comments = document.getElementById(`comments-${approvalId}`).value;
            
            if (!comments.trim()) {
                alert('Please provide a reason for rejection in the comments field.');
                document.getElementById(`comments-${approvalId}`).focus();
                return;
            }
            
            if (confirm('Are you sure you want to reject this notification?')) {
                const data = {
                    approval_id: approvalId,
                    admin_comments: comments
                };

                fetch('/erp/backend/api/notifications.php?action=reject_notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showAlert('Notification rejected successfully!', 'success');
                        document.getElementById(`approval-${approvalId}`).remove();
                        updatePendingCount(document.querySelectorAll('.pending-card').length);
                        loadStats(); // Refresh stats
                    } else {
                        showAlert(`Error rejecting notification: ${result.message}`, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Network error while rejecting notification', 'error');                });
            }
        }        function loadStats() {
            fetch('/erp/backend/api/notifications.php?action=get_approval_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('pendingCount').textContent = data.data.pending_count;
                        document.getElementById('approvedToday').textContent = data.data.approved_today;
                        document.getElementById('rejectedToday').textContent = data.data.rejected_today;
                        
                        // Update tab counts
                        document.getElementById('pendingTabCount').textContent = data.data.pending_count;
                    } else {
                        console.error('Error loading stats:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading stats:', error);
                });
        }

        function updatePendingCount(count) {
            document.getElementById('pendingCount').textContent = count;
            // Also refresh all stats when pending count changes
            loadStats();
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass}`;
            alert.textContent = message;
            
            alertContainer.appendChild(alert);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }        function loadApprovedNotifications() {
            const loading = document.getElementById('approvedLoading');
            const container = document.getElementById('approvedNotifications');
            
            // Show loading and clear container content except loading
            if (loading) loading.style.display = 'block';
            // Clear any existing content but keep the loading div
            const loadingHtml = '<div id="approvedLoading" class="loading">Loading approved notifications...</div>';
            container.innerHTML = loadingHtml;
            
            fetch('/erp/backend/api/notifications.php?action=get_processed_approvals&status=approved&limit=50')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.data.length === 0) {
                            container.innerHTML = `
                                <div class="no-approvals">
                                    <h3>No Approved Notifications</h3>
                                    <p>No notifications have been approved yet.</p>
                                </div>
                            `;
                            if (document.getElementById('approvedTabCount')) {
                                document.getElementById('approvedTabCount').textContent = '0';
                            }
                            return;
                        }
                        
                        let html = '';
                        data.data.forEach(approval => {
                            html += renderProcessedCard(approval, 'approved');
                        });
                        container.innerHTML = html;
                        if (document.getElementById('approvedTabCount')) {
                            document.getElementById('approvedTabCount').textContent = data.data.length;
                        }
                    } else {
                        container.innerHTML = `<div class="alert alert-error">Error loading approved notifications: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="alert alert-error">Network error loading approved notifications</div>';
                });
        }        function loadRejectedNotifications() {
            const loading = document.getElementById('rejectedLoading');
            const container = document.getElementById('rejectedNotifications');
            
            // Show loading and clear container content except loading
            if (loading) loading.style.display = 'block';
            // Clear any existing content but keep the loading div
            const loadingHtml = '<div id="rejectedLoading" class="loading">Loading rejected notifications...</div>';
            container.innerHTML = loadingHtml;
            
            fetch('/erp/backend/api/notifications.php?action=get_processed_approvals&status=rejected&limit=50')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.data.length === 0) {
                            container.innerHTML = `
                                <div class="no-approvals">
                                    <h3>No Rejected Notifications</h3>
                                    <p>No notifications have been rejected yet.</p>
                                </div>
                            `;
                            if (document.getElementById('rejectedTabCount')) {
                                document.getElementById('rejectedTabCount').textContent = '0';
                            }
                            return;
                        }
                        
                        let html = '';
                        data.data.forEach(approval => {
                            html += renderProcessedCard(approval, 'rejected');
                        });
                        container.innerHTML = html;
                        if (document.getElementById('rejectedTabCount')) {
                            document.getElementById('rejectedTabCount').textContent = data.data.length;
                        }
                    } else {
                        container.innerHTML = `<div class="alert alert-error">Error loading rejected notifications: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="alert alert-error">Network error loading rejected notifications</div>';
                });
        }

        function renderProcessedCard(approval, status) {
            const headerClass = status === 'approved' ? 'approved-header' : 'rejected-header';
            const statusIcon = status === 'approved' ? '✅' : '❌';
            const statusText = status === 'approved' ? 'Approved & Sent' : 'Rejected';
            const processedDate = new Date(approval.approved_at).toLocaleDateString();
            const processedTime = new Date(approval.approved_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const submittedDate = new Date(approval.submitted_at).toLocaleDateString();
            const submittedTime = new Date(approval.submitted_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            return `
                <div class="processed-card">
                    <div class="${headerClass}">
                        <div class="processed-title">
                            ${statusIcon} ${approval.title || 'Untitled'} - ${statusText}
                        </div>
                        <div class="pending-meta">
                            <span><strong>From:</strong> ${approval.teacher_name} (${approval.teacher_role})</span>
                            <span><strong>Submitted:</strong> ${submittedDate} at ${submittedTime}</span>
                            <span><strong>Processed:</strong> ${processedDate} at ${processedTime}</span>
                            <span><strong>By:</strong> ${approval.admin_name || 'System'}</span>
                            <span><strong>Type:</strong> ${approval.type || 'N/A'}</span>
                            <span><strong>Priority:</strong> ${approval.priority || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="pending-content">
                        <div class="notification-preview">
                            <div class="notification-message">
                                ${approval.message || 'No message content'}
                            </div>
                        </div>
                        
                        ${approval.teacher_request_message ? `
                            <div style="margin-top: 1rem; padding: 1rem; background-color: #f9fafb; border-radius: 8px; border-left: 4px solid #6b7280;">
                                <strong>Teacher's Request Message:</strong><br>
                                ${approval.teacher_request_message}
                            </div>
                        ` : ''}
                        
                        ${approval.admin_comments ? `
                            <div style="margin-top: 1rem; padding: 1rem; background-color: #f9fafb; border-radius: 8px; border-left: 4px solid ${status === 'approved' ? '#10b981' : '#ef4444'};">
                                <strong>Admin Comments:</strong><br>
                                ${approval.admin_comments}
                            </div>
                        ` : ''}
                    </div>
                </div>            `;
        }
    </script>
</body>
</html>
