<?php
session_start();
require_once '../../includes/functions.php';
$scanner = new CTUScanner();
$stats = $scanner->getDailyStats();
$peakHours = $scanner->getPeakHours();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cogs me-2"></i>CTU Admin Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="#"><i class="fas fa-user me-1"></i>Admin</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Today's Overview</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-card bg-primary">
                                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['total_entries']; ?></h3>
                                        <p>Total Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-success">
                                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['student_entries']; ?></h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-info">
                                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['faculty_entries']; ?></h3>
                                        <p>Faculty Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-warning">
                                    <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['total_exits']; ?></h3>
                                        <p>Total Exits</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Peak Hours Analysis</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="peakHoursChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-download me-2"></i>Export Data</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="exportExcel()">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button class="btn btn-danger" onclick="exportPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Date Range:</label>
                                <input type="date" id="startDate" class="form-control mb-2" value="<?php echo date('Y-m-d'); ?>">
                                <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <button class="btn btn-primary w-100" onclick="generateReport()">
                                <i class="fas fa-chart-bar me-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i>Department Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyTrendChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>System Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="management-card" onclick="manageUsers('students')">
                                    <i class="fas fa-user-graduate"></i>
                                    <h6>Manage Students</h6>
                                    <p>Add, edit, or deactivate student accounts</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="management-card" onclick="manageUsers('faculty')">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <h6>Manage Faculty</h6>
                                    <p>Add, edit, or deactivate faculty accounts</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="management-card" onclick="manageUsers('security')">
                                    <i class="fas fa-shield-alt"></i>
                                    <h6>Manage Security</h6>
                                    <p>Add, edit, or deactivate security accounts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script>
        // Initialize charts with PHP data
        const peakHoursData = <?php echo json_encode($peakHours); ?>;
    </script>
</body>
</html>