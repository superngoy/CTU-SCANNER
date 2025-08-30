<?php
session_start();
// Add authentication check here
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>CTU Security Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Real-time Monitoring</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-card bg-success">
                                    <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                                    <div class="stat-info">
                                        <h3 id="todayEntries">0</h3>
                                        <p>Today's Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-warning">
                                    <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    <div class="stat-info">
                                        <h3 id="todayExits">0</h3>
                                        <p>Today's Exits</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-info">
                                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                    <div class="stat-info">
                                        <h3 id="studentEntries">0</h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-secondary">
                                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <div class="stat-info">
                                        <h3 id="facultyEntries">0</h3>
                                        <p>Faculty Entries</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-arrow-right text-success me-2"></i>Recent Entries</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshEntries()">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="recentEntries" class="activity-feed">
                            <!-- Real-time entries will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-arrow-left text-warning me-2"></i>Recent Exits</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshExits()">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="recentExits" class="activity-feed">
                            <!-- Real-time exits will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/security.js"></script>
</body>
</html>