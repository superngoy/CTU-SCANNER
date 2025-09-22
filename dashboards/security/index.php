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
            --primary-color: #8A2125;    /* Dark Red */
            --secondary-color: #DFBB65;  /* Gold */
            --accent-color: #8A2125;     /* Dark Red for accents */
            --success-color: #27AE60;    /* Keep standard success */
            --warning-color: #DFBB65;    /* Gold for warnings */
            --danger-color: #8A2125;     /* Dark Red for danger */
            --info-color: #DFBB65;       /* Gold for info */
            
            /* Updated Gradients */
            --primary-gradient: linear-gradient(135deg, #8A2125 0%, #9c262b 100%);
            --secondary-gradient: linear-gradient(135deg, #DFBB65 0%, #e6c876 100%);
            --success-gradient: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);
            --warning-gradient: linear-gradient(135deg, #DFBB65 0%, #e6c876 100%);
            --danger-gradient: linear-gradient(135deg, #8A2125 0%, #9c262b 100%);
            --info-gradient: linear-gradient(135deg, #DFBB65 0%, #e6c876 100%);
            --entries-gradient: linear-gradient(135deg, #8A2125 0%, #9c262b 100%);      /* Dark Red */
            --exits-gradient: linear-gradient(135deg, #DFBB65 0%, #e6c876 100%);        /* Gold */
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
            padding: 25px;
            border-radius: 15px;
            color: white;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            float: right;
            position: relative;
            z-index: 2;
            animation: pulse 2s infinite;
        }
        
        .stat-card h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }
        
        .stat-card p {
            font-size: 1rem;
            font-weight: 500;
            margin: 0;
            position: relative;
            z-index: 2;
        }
        
        .stat-card.bg-success { background: var(--success-gradient); }
        .stat-card.bg-warning { background: var(--warning-gradient); }
        .stat-card.bg-info { background: var(--info-gradient); }
        .stat-card.bg-secondary { background: var(--secondary-gradient); }
        .stat-card.entries { background: var(--entries-gradient); }
        .stat-card.exits { background: var(--exits-gradient); }
        .stat-card.students { background: var(--student-gradient); }
        .stat-card.faculty { background: var(--faculty-gradient); }

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
            float: right;
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
            background: rgba(138, 33, 37, 0.1);
            color: var(--primary-color);
        }
        
        .activity-type.faculty {
            background: rgba(223, 187, 101, 0.1);
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
                                    <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                                    <div class="stat-info">
                                        <h3 id="todayEntries">-</h3>
                                        <p>Today's Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card exits">
                                    <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    <div class="stat-info">
                                        <h3 id="todayExits">-</h3>
                                        <p>Today's Exits</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card students">
                                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                    <div class="stat-info">
                                        <h3 id="studentEntries">-</h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="stat-card faculty">
                                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <div class="stat-info">
                                        <h3 id="facultyEntries">-</h3>
                                        <p>Faculty Entries</p>
                                    </div>
                                </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                        <div class="d-flex justify-content-between align-items-start">
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
</body>
</html>