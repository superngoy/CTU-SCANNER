<?php
session_start();

// Add authentication check
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    header('Location: login.php');
    exit();
}

require_once '../../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner - Security Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #972529;    /* Dark Red */
            --secondary-color: #E5C573;  /* Gold */
            --accent-color: #972529;     /* Dark Red for accents */
            --success-color: #27AE60;    /* Keep standard success */
            --warning-color: #E5C573;    /* Gold for warnings */
            --danger-color: #972529;     /* Dark Red for danger */
            --info-color: #E5C573;       /* Gold for info */
            
            /* Updated Gradients */
            --primary-gradient: #972529;
            --secondary-gradient: #E5C573;
            --success-gradient: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);
            --warning-gradient: #E5C573;
            --danger-gradient: #972529;
            --info-gradient: #E5C573;
            --entries-gradient: #972529;      /* Dark Red */
            --exits-gradient: #E5C573;        /* Gold */
            --student-gradient: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);      /* Green */
            --faculty-gradient: linear-gradient(135deg, #2980B9 0%, #3498DB 100%);      /* Blue */
        }
        
        body {
            background: linear-gradient(135deg, #ECF0F1 0%, #F5F6FA 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            animation: gradientShift 15s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .navbar-brand img {
            height: 45px;
            width: auto;
            margin-right: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            border-left: 4px solid #972529;
            padding: 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #333;
        }

        .stat-card p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }
        
        .stat-card.entries { border-left-color: #972529; }
        .stat-card.exits { border-left-color: #E5C573; }
        .stat-card.students { border-left-color: #28a745; }
        .stat-card.faculty { border-left-color: #007bff; }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .activity-item {
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-color);
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 12px;
            background: rgba(255,255,255,0.9);
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .activity-feed {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .activity-feed::-webkit-scrollbar {
            width: 6px;
        }
        
        .activity-feed::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
            border-radius: 3px;
        }
        
        .activity-feed::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }
        
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .alert-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
            border-radius: 20px;
            border: none;
            font-weight: 500;
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-outline-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }

        .btn-outline-primary:hover::before {
            left: 100%;
        }

        .activity-person {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .activity-id {
            font-size: 0.9rem;
            color: #666;
            background: rgba(0,0,0,0.05);
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-left: 8px;
        }
        
        .activity-time {
            font-size: 0.85rem;
            color: #888;
            flex-shrink: 0;
        }

        .activity-avatar {
            flex-shrink: 0;
            width: 45px;
            height: 45px;
            position: relative;
        }

        .activity-user-image {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary-color);
            box-shadow: 0 3px 10px rgba(229, 197, 115, 0.2);
            background: #f8f9fa;
        }

        .activity-avatar-default {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #972529;
            color: #FEFEFE;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid var(--secondary-color);
            box-shadow: 0 3px 10px rgba(229, 197, 115, 0.2);
        }

        /* Add hover effect for images */
        .activity-user-image:hover,
        .activity-avatar-default:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }
        
        .activity-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .activity-type.student {
            background: rgba(151, 37, 41, 0.1);
            color: var(--primary-color);
        }
        
        .activity-type.faculty {
            background: rgba(229, 197, 115, 0.1);
            color: var(--secondary-color);
        }
        
        .container-fluid {
            max-width: 1400px;
        }
        
        @media (max-width: 768px) {
            .stat-card h3 {
                font-size: 2rem;
            }
            
            .navbar-brand img {
                height: 35px;
                margin-right: 10px;
            }
            
            .card-header {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="/assets/images/logo.png" alt="CTU Logo">
                <div>
                    <div>CTU Security Dashboard</div>
                    <small style="font-size: 0.7rem; opacity: 0.8;">Real-time Monitoring System</small>
                </div>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-shield me-2"></i>Security: <?php echo htmlspecialchars($_SESSION['security_name'] ?? $_SESSION['security_id']); ?>
                </span>
                <a class="nav-link" href="logout.php" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>Real-time Campus Monitoring
                        </h4>
                        <div class="d-flex align-items-center">
                            <small class="text-muted me-3">
                                <i class="fas fa-clock me-1"></i>Last updated: <span id="lastUpdated">Never</span>
                            </small>
                            <button class="btn btn-sm btn-outline-primary" onclick="dashboard.loadInitialData()">
                                <i class="fas fa-sync-alt"></i> Refresh All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card entries">
                                    <h3 id="todayEntries">-</h3>
                                    <p>Today's Entries</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card exits">
                                    <h3 id="todayExits">-</h3>
                                    <p>Today's Exits</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card students">
                                    <h3 id="studentEntries">-</h3>
                                    <p>Student Entries</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card faculty">
                                    <h3 id="facultyEntries">-</h3>
                                    <p>Faculty Entries</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Visitors Section -->
        <div class="row mb-4" id="activeVisitorsSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-check me-2 text-success"></i>Currently Checked-In Visitors
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="loadActiveVisitors()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="activeVisitorsContainer">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-hourglass-start fa-3x mb-2 opacity-50"></i>
                                <p>Loading active visitors...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Registration Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>Visitor Registration
                        </h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#visitorModal">
                            <i class="fas fa-plus me-1"></i>Register New Visitor
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="visitorListContainer">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-2 opacity-50"></i>
                                <p>No visitors registered today</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-right text-success me-2"></i>Recent Entries
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshEntries()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentEntries" class="activity-feed p-3">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border text-secondary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>Loading recent entries...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-arrow-left text-warning me-2"></i>Recent Exits
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshExits()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentExits" class="activity-feed p-3">
                            <div class="text-center text-muted py-5">
                                <div class="spinner-border text-secondary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>Loading recent exits...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="row mt-5 mb-4">
            <div class="col-12">
                <h4 class="mb-3">
                    <i class="fas fa-chart-bar me-2"></i>Entry & Exit Analytics
                </h4>
            </div>
        </div>

        <!-- Entry/Exit Logs Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Entry Logs Timeline</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center" style="height: 350px;">
                        <canvas id="securityEntryLogsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Exit Logs Timeline</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center" style="height: 350px;">
                        <canvas id="securityExitLogsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entry/Exit Comparison by Hour -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-arrows-alt-h me-2"></i>Entry & Exit by Hour</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center" style="height: 350px;">
                        <canvas id="securityEntryExitHourlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/security.js"></script>
    
    <script>
        // Enhanced activity rendering with better styling
        function renderActivity(items, container, type) {
            const container_el = document.getElementById(container);
            if (!items || items.length === 0) {
                container_el.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                        <div>No ${type} recorded yet</div>
                    </div>
                `;
                return;
            }
            
            const html = items.map(item => {
                const firstName = item.StudentFName || item.FacultyFName || 'Unknown';
                const lastName = item.StudentLName || item.FacultyLName || '';
                const fullName = `${firstName} ${lastName}`.trim();
                const timestamp = new Date(item.Timestamp);
                const timeStr = timestamp.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                return `
                    <div class="activity-item fade-in">
                        <div class="d-flex align-items-center gap-3">
                            <div class="activity-avatar">
                                ${item.image ? 
                                    `<img src="${item.image}" alt="${fullName}" class="activity-user-image" onerror="this.onerror=null; this.src='../../assets/images/default-avatar.png';">` :
                                    `<div class="activity-avatar-default">${fullName.charAt(0).toUpperCase()}</div>`
                                }
                            </div>
                            <div class="flex-grow-1">
                                <div class="activity-person">${fullName}</div>
                                <div class="mt-1">
                                    <span class="activity-type ${item.PersonCategory}">${item.PersonCategory}</span>
                                    <span class="activity-id">${item.PersonID}</span>
                                </div>
                            </div>
                            <div class="activity-time">
                                <i class="fas fa-clock me-1"></i>${timeStr}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            container_el.innerHTML = html;
        }

        // Update last updated time
        function updateLastUpdatedTime() {
            const now = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            const element = document.getElementById('lastUpdated');
            if (element) {
                element.textContent = now;
            }
        }

        // Status indicator management
        function showStatus(type) {
            ['connected', 'connecting', 'disconnected'].forEach(status => {
                document.getElementById(status + 'Status').classList.add('d-none');
            });
            document.getElementById(type + 'Status').classList.remove('d-none');
        }

        // Enhanced dashboard initialization
        document.addEventListener('DOMContentLoaded', () => {
            // Show connecting status initially
            showStatus('connecting');
            
            // Initialize dashboard
            if (typeof dashboard !== 'undefined') {
                dashboard.loadInitialData();
                
                // Show connected status after successful load
                setTimeout(() => {
                    showStatus('connected');
                    updateLastUpdatedTime();
                }, 1500);
                
                // Set up auto-refresh
                setInterval(() => {
                    dashboard.loadInitialData();
                    updateLastUpdatedTime();
                }, 30000); // Refresh every 30 seconds
            }
        });

        // Override existing functions if they exist
        if (typeof refreshEntries === 'undefined') {
            window.refreshEntries = function() {
                if (typeof dashboard !== 'undefined') {
                    dashboard.loadRecentEntries();
                }
            };
        }

        if (typeof refreshExits === 'undefined') {
            window.refreshExits = function() {
                if (typeof dashboard !== 'undefined') {
                    dashboard.loadRecentExits();
                }
            };
        }
    </script>

    <!-- Visitor Registration Modal -->
    <div class="modal fade" id="visitorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card me-2"></i>Register New Visitor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="visitorForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company/Organization</label>
                                <input type="text" class="form-control" name="company">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose of Visit <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="purpose" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Type</label>
                                <select class="form-control" name="id_provided_type">
                                    <option value="">Select ID Type</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Driver's License">Driver's License</option>
                                    <option value="National ID">National ID</option>
                                    <option value="School ID">School ID</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Number</label>
                                <input type="text" class="form-control" name="id_provided_number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visitor Photo</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ID Copy</label>
                                <input type="file" class="form-control" name="id_image" accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="registerVisitor()">
                        <i class="fas fa-check me-1"></i>Register Visitor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Visitor registration handler
        function registerVisitor() {
            const form = document.getElementById('visitorForm');
            const formData = new FormData(form);

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Registering...';

            fetch('register_visitor.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    // Try to extract JSON from response
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            parsed = JSON.parse(match[0]);
                        } catch (e2) {
                            parsed = null;
                        }
                    }
                }
                return { ok: r.ok, parsed, text };
            }))
            .then(result => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Register Visitor';

                if (result.parsed && result.parsed.success) {
                    alert('Visitor registered successfully! Code: ' + result.parsed.visitor.visitor_code);
                    form.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('visitorModal'));
                    modal.hide();
                    loadVisitors();
                } else {
                    const msg = result.parsed?.message || 'Failed to register visitor';
                    alert('Error: ' + msg);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Register Visitor';
                console.error('Visitor registration error:', err);
                alert('Network error occurred');
            });
        }

        // Load and display visitors
        function loadVisitors() {
            fetch('get_visitors.php')
            .then(r => r.json())
            .then(data => {
                if (data.visitors && data.visitors.length > 0) {
                    let html = '';
                    data.visitors.forEach(visitor => {
                        const fullName = [visitor.first_name, visitor.middle_name, visitor.last_name].filter(Boolean).join(' ');
                        html += `
                            <div class="row align-items-center border-bottom py-3">
                                <div class="col-md-2 text-center">
                                    ${visitor.image ? `<img src="../../${visitor.image}" class="rounded-circle" width="50" height="50" style="object-fit: cover;">` : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-user"></i></div>`}
                                </div>
                                <div class="col-md-4">
                                    <div><strong>${fullName}</strong></div>
                                    <div class="text-muted small">Code: <span class="badge bg-info">${visitor.visitor_code}</span></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small"><i class="fas fa-phone me-1"></i>${visitor.contact_number}</div>
                                    <div class="text-muted small"><i class="fas fa-building me-1"></i>${visitor.company || 'N/A'}</div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <button class="btn btn-sm btn-success me-2" onclick="checkInVisitor(${visitor.id})">
                                        <i class="fas fa-sign-in-alt me-1"></i>Check In
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    document.getElementById('visitorListContainer').innerHTML = html;
                } else {
                    document.getElementById('visitorListContainer').innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-2 opacity-50"></i>
                            <p>No visitors registered today</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Load visitors error:', err);
            });
        }

        // Load and display active visitors
        function loadActiveVisitors() {
            fetch('get_active_visitors.php')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('activeVisitorsContainer');
                if (!container) return;

                if (data.active_visitors && data.active_visitors.length > 0) {
                    let html = '<div class="list-group">';
                    data.active_visitors.forEach(visitor => {
                        const fullName = [visitor.first_name, visitor.middle_name, visitor.last_name].filter(Boolean).join(' ');
                        const checkinTime = new Date(visitor.check_in_time).toLocaleTimeString();
                        html += `
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        ${visitor.image ? `<img src="../../${visitor.image}" class="rounded-circle" width="45" height="45" style="object-fit: cover;">` : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;"><i class="fas fa-user"></i></div>`}
                                    </div>
                                    <div class="col-md-4">
                                        <div><strong>${fullName}</strong></div>
                                        <div class="text-muted small">${visitor.company || 'N/A'}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small"><i class="fas fa-clock me-1"></i>In: ${checkinTime}</div>
                                        <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>${visitor.location}</div>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button class="btn btn-sm btn-warning" onclick="checkOutVisitor(${visitor.id})">
                                            <i class="fas fa-sign-out-alt me-1"></i>Check Out
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-2 opacity-50"></i>
                            <p>No active visitors</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Load active visitors error:', err);
            });
        }
        function checkInVisitor(visitorId) {
            const formData = new FormData();
            formData.append('action', 'check_in');
            formData.append('visitor_id', visitorId);
            formData.append('location', 'Main Entrance');

            fetch('visitor_checkin.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            parsed = JSON.parse(match[0]);
                        } catch (e2) {}
                    }
                }
                return { ok: r.ok, parsed };
            }))
            .then(result => {
                if (result.parsed && result.parsed.success) {
                    alert('Visitor ' + result.parsed.visitor.name + ' checked in successfully!');
                    loadVisitors();
                    loadActiveVisitors();
                } else {
                    alert('Error: ' + (result.parsed?.message || 'Check-in failed'));
                }
            })
            .catch(err => {
                console.error('Check-in error:', err);
                alert('Network error');
            });
        }

        // Check out visitor
        function checkOutVisitor(visitorId) {
            if (!confirm('Check out this visitor?')) return;

            const formData = new FormData();
            formData.append('action', 'check_out');
            formData.append('visitor_id', visitorId);

            fetch('visitor_checkin.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text().then(text => {
                let parsed = null;
                try {
                    parsed = JSON.parse(text);
                } catch (e) {
                    const match = text.match(/\{[\s\S]*\}/);
                    if (match) {
                        try {
                            parsed = JSON.parse(match[0]);
                        } catch (e2) {}
                    }
                }
                return { ok: r.ok, parsed };
            }))
            .then(result => {
                if (result.parsed && result.parsed.success) {
                    alert('Visitor checked out! Dwell time: ' + result.parsed.dwell_time);
                    loadActiveVisitors();
                } else {
                    alert('Error: ' + (result.parsed?.message || 'Check-out failed'));
                }
            })
            .catch(err => {
                console.error('Check-out error:', err);
                alert('Network error');
            });
        }

        // Load visitors on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadVisitors();
            loadActiveVisitors();
            // Show active visitors section
            const section = document.getElementById('activeVisitorsSection');
            if (section) section.style.display = 'block';
            // Refresh visitors every 30 seconds
            setInterval(loadVisitors, 30000);
            // Refresh active visitors every 15 seconds
            setInterval(loadActiveVisitors, 15000);
        });
    </script>
</body>
</html>