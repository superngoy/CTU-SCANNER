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
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --purple-color: #9b59b6;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        .navbar-brand img {
            height: 50px;
            width: auto;
            margin-right: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .navbar-text {
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 25px;
            backdrop-filter: blur(15px);
            font-weight: 500;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(15px);
            background: rgba(255,255,255,0.95);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(52, 73, 94, 0.1) 0%, rgba(44, 62, 80, 0.05) 100%);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            border-radius: 20px 20px 0 0 !important;
            padding: 25px;
        }
        
        .card-header h4, .card-header h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .stat-card {
            padding: 30px;
            border-radius: 20px;
            color: white;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            transition: all 0.4s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
            transition: all 0.4s ease;
        }
        
        .stat-card:hover::after {
            transform: scale(1.5);
        }
        
        .stat-card .stat-icon {
            font-size: 3rem;
            opacity: 0.9;
            float: right;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .stat-card h3 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .stat-card p {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 2;
            text-shadow: 0 1px 5px rgba(0,0,0,0.3);
        }
        
        .bg-primary { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .bg-success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
        .bg-info { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
        .bg-warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        
        .management-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.8) 100%);
            border: 2px solid rgba(52, 73, 94, 0.1);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .management-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
            transition: left 0.5s;
        }
        
        .management-card:hover::before {
            left: 100%;
        }
        
        .management-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }
        
        .management-card i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .management-card:hover i {
            transform: scale(1.2);
            color: var(--secondary-color);
        }
        
        .management-card h6 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .management-card p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 25px;
            transition: all 0.3s ease;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid rgba(52, 73, 94, 0.1);
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 73, 94, 0.25);
        }
        
        .container-fluid {
            max-width: 1400px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .export-section {
            background: linear-gradient(135deg, rgba(52, 73, 94, 0.05) 0%, rgba(44, 62, 80, 0.03) 100%);
            border-radius: 15px;
            padding: 20px;
        }
        
        .date-input-group {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .stat-card h3 {
                font-size: 2.5rem;
            }
            
            .navbar-brand img {
                height: 40px;
                margin-right: 10px;
            }
            
            .card-header, .card-body {
                padding: 20px;
            }
            
            .management-card {
                padding: 25px 20px;
            }
            
            .management-card i {
                font-size: 2.5rem;
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
                    <div>CTU Admin Dashboard</div>
                    <small style="font-size: 0.75rem; opacity: 0.9;">System Administration Panel</small>
                </div>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-shield me-2"></i>Administrator
                </span>
                <a class="nav-link" href="logout.php" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card fade-in-up">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-bar me-3"></i>Today's Campus Overview
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-primary">
                                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['total_entries']; ?></h3>
                                        <p>Total Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-success">
                                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['student_entries']; ?></h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-info">
                                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['faculty_entries']; ?></h3>
                                        <p>Faculty Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
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
        <div class="analytics-grid">
            <div class="card fade-in-up" style="animation-delay: 0.1s;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Peak Hours Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="peakHoursChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card fade-in-up" style="animation-delay: 0.2s;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Data Export Center
                    </h5>
                </div>
                <div class="card-body">
                    <div class="export-section">
                        <div class="d-grid gap-3 mb-4">
                            <button class="btn btn-success" onclick="exportExcel()">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                                <span class="loading-spinner d-none" id="excelLoading"></span>
                            </button>
                            <button class="btn btn-danger" onclick="exportPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                                <span class="loading-spinner d-none" id="pdfLoading"></span>
                            </button>
                        </div>
                        
                        <div class="date-input-group">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-2"></i>Date Range:
                            </label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 mt-3" onclick="generateReport()">
                                <i class="fas fa-chart-bar me-2"></i>Generate Custom Report
                                <span class="loading-spinner d-none" id="reportLoading"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card fade-in-up" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-pie-chart me-2"></i>Department Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card fade-in-up" style="animation-delay: 0.4s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-week me-2"></i>Weekly Activity Trends
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="weeklyTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Section -->
        <div class="row">
            <div class="col-12">
                <div class="card fade-in-up" style="animation-delay: 0.5s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>User Management System
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="management-card" onclick="manageUsers('students')">
                                    <i class="fas fa-user-graduate"></i>
                                    <h6>Manage Students</h6>
                                    <p>Add, edit, or manage student accounts and information</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="management-card" onclick="manageUsers('faculty')">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <h6>Manage Faculty</h6>
                                    <p>Add, edit, or manage faculty accounts and information</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="management-card" onclick="manageUsers('security')">
                                    <i class="fas fa-shield-alt"></i>
                                    <h6>Manage Security</h6>
                                    <p>Add, edit, or manage security personnel accounts</p>
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
        
        // Enhanced management functions
        function manageUsers(type) {
            // Add loading animation
            const managementCards = document.querySelectorAll('.management-card');
            const clickedCard = event.currentTarget;
            
            clickedCard.style.transform = 'scale(0.95)';
            setTimeout(() => {
                window.location.href = `manage_users.php?type=${type}`;
            }, 200);
        }
        
        // Enhanced export functions with loading states
        function exportExcel() {
            const loading = document.getElementById('excelLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            
            setTimeout(() => {
                window.open(`export_excel.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
            }, 1000);
        }
        
        function exportPDF() {
            const loading = document.getElementById('pdfLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            
            setTimeout(() => {
                window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
            }, 1000);
        }
        
        function generateReport() {
            const loading = document.getElementById('reportLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            
            loading.classList.remove('d-none');
            
            // Simulate report generation
            setTimeout(() => {
                alert(`Report generated for ${startDate} to ${endDate}`);
                loading.classList.add('d-none');
            }, 2000);
        }
        
        // Initialize page animations
        document.addEventListener('DOMContentLoaded', function() {
            // Stagger animation for cards
            const cards = document.querySelectorAll('.fade-in-up');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Initialize charts if admin.js is loaded
            if (typeof initializeCharts === 'function') {
                initializeCharts();
            }
        });
    </script>
</body>
</html>