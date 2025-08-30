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
        .activity-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .activity-item:hover {
            background-color: #f8f9fa;
            border-left-color: #007bff;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            margin-bottom: 15px;
        }
        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
            float: right;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .activity-feed {
            max-height: 600px;
            overflow-y: auto;
        }
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Status indicator for connection -->
    <div id="statusIndicator" class="status-indicator">
        <div class="alert alert-success alert-sm d-none" id="connectedStatus">
            <i class="fas fa-wifi me-1"></i>Connected
        </div>
        <div class="alert alert-warning alert-sm d-none" id="connectingStatus">
            <i class="fas fa-spinner fa-spin me-1"></i>Connecting...
        </div>
        <div class="alert alert-danger alert-sm d-none" id="disconnectedStatus">
            <i class="fas fa-exclamation-triangle me-1"></i>Connection Error
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>CTU Security Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>Security: <?php echo htmlspecialchars($_SESSION['security_name'] ?? $_SESSION['security_id']); ?>
                </span>
                <a class="nav-link" href="logout.php">
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
                            <i class="fas fa-tachometer-alt me-2"></i>Real-time Monitoring
                        </h4>
                        <div>
                            <small class="text-muted me-2">Last updated: <span id="lastUpdated">Never</span></small>
                            <button class="btn btn-sm btn-outline-primary" onclick="dashboard.loadInitialData()">
                                <i class="fas fa-sync"></i> Refresh All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-card bg-success">
                                    <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                                    <div class="stat-info">
                                        <h3 id="todayEntries">-</h3>
                                        <p>Today's Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-warning">
                                    <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    <div class="stat-info">
                                        <h3 id="todayExits">-</h3>
                                        <p>Today's Exits</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-info">
                                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                    <div class="stat-info">
                                        <h3 id="studentEntries">-</h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-secondary">
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
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentEntries" class="activity-feed p-3">
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-spinner fa-spin me-2"></i>Loading entries...
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
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="recentExits" class="activity-feed p-3">
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-spinner fa-spin me-2"></i>Loading exits...
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
        // Update last updated time
        function updateLastUpdatedTime() {
            const now = new Date().toLocaleTimeString();
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

        // Override the dashboard methods to include status updates
        document.addEventListener('DOMContentLoaded', () => {
            // Show connecting status initially
            showStatus('connecting');
            
            // Show connected status after successful load
            setTimeout(() => {
                showStatus('connected');
                updateLastUpdatedTime();
            }, 1000);
            
            // Update last updated time on successful requests
            const originalUpdateStats = window.dashboard?.updateStats;
            if (originalUpdateStats) {
                window.dashboard.updateStats = function() {
                    originalUpdateStats.call(this);
                    updateLastUpdatedTime();
                };
            }
        });
    </script>
</body>
</html>