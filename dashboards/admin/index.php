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
            --sidebar-width: 280px;
            --header-height: 70px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            margin-bottom: 10px;
            border: 3px solid rgba(255,255,255,0.2);
        }

        .sidebar-title {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            margin-top: 5px;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .nav-link:hover::before {
            width: 100%;
        }

        .nav-link:hover {
            color: #fff;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .nav-link span {
            position: relative;
            z-index: 1;
            font-weight: 500;
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: #fff;
            transform: translateX(5px);
        }

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .header-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: #666;
            font-size: 18px;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            background: #ff4757;
            border-radius: 50%;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            border-radius: 25px;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: scale(1.05);
        }

        /* Main Content */
        .main-content {
            margin-top: var(--header-height);
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }

        /* Enhanced Cards */
        .enhanced-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .enhanced-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .enhanced-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-card {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.6s ease;
        }

        .stat-card:hover::before {
            transform: scale(1);
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
        }

        .stat-card.bg-success { background: var(--success-gradient); }
        .stat-card.bg-warning { background: var(--warning-gradient); }
        .stat-card.bg-info { background: var(--secondary-gradient); }

        .stat-icon {
            font-size: 36px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .stat-info h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .stat-info p {
            margin: 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* Animations */
        .slide-in-left {
            animation: slideInLeft 0.6s ease-out forwards;
        }

        .slide-in-right {
            animation: slideInRight 0.6s ease-out forwards;
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        .bounce-in {
            animation: bounceIn 0.6s ease-out forwards;
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* QR Generator Modal */
        .qr-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            z-index: 2000;
            padding: 20px;
        }

        .qr-modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            position: relative;
            animation: bounceIn 0.5s ease-out;
        }

        .qr-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .top-header {
                left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: #666;
            font-size: 20px;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mobile-toggle:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar slide-in-left">
        <div class="sidebar-header">
            <img src="../../assets/images/logo.png" alt="CTU Logo" class="sidebar-logo">
            <h3 class="sidebar-title">CTU Admin</h3>
            <p class="sidebar-subtitle">System Dashboard</p>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="#dashboard" class="nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#qr-generator" class="nav-link" data-section="qr-generator">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Generator</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#analytics" class="nav-link" data-section="analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#reports" class="nav-link" data-section="reports">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#users" class="nav-link" data-section="users">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#settings" class="nav-link" data-section="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>
        
        <div style="position: absolute; bottom: 20px; left: 20px; right: 20px;">
            <div class="nav-item">
                <a href="logout.php" class="nav-link" style="background: rgba(255,82,82,0.1); color: #ff5252;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Top Header -->
    <header class="top-header slide-in-right">
        <div class="d-flex align-items-center">
            <button class="mobile-toggle me-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">Campus Management System</h1>
        </div>
        
        <div class="header-actions">
            <button class="notification-btn">
                <i class="fas fa-bell"></i>
                <div class="notification-badge"></div>
            </button>
            <a href="#profile" class="user-profile">
                <i class="fas fa-user-shield"></i>
                <span>Administrator</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Section -->
        <div id="dashboard-section" class="content-section">
            <!-- Statistics Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h4 class="mb-0">
                                <i class="fas fa-chart-bar me-3"></i>Today's Campus Overview
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bounce-in" style="animation-delay: 0.1s;">
                                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                                        <div class="stat-info">
                                            <h3><?php echo $stats['total_entries']; ?></h3>
                                            <p>Total Entries</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-success bounce-in" style="animation-delay: 0.2s;">
                                        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                        <div class="stat-info">
                                            <h3><?php echo $stats['student_entries']; ?></h3>
                                            <p>Student Entries</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-info bounce-in" style="animation-delay: 0.3s;">
                                        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                        <div class="stat-info">
                                            <h3><?php echo $stats['faculty_entries']; ?></h3>
                                            <p>Faculty Entries</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-warning bounce-in" style="animation-delay: 0.4s;">
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
        </div>

        <!-- QR Generator Section -->
        <div id="qr-generator-section" class="content-section" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h4 class="mb-0">
                                <i class="fas fa-qrcode me-3"></i>QR Code Generator & Management
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <!-- Quick QR Generator -->
                            <div class="row mb-4">
                                <div class="col-lg-8 mx-auto">
                                    <div class="enhanced-card" style="border: 2px solid var(--primary-color) !important;">
                                        <div class="card-header bg-light border-0 p-3">
                                            <h6 class="mb-0">
                                                <i class="fas fa-magic me-2"></i>Quick QR Generator
                                            </h6>
                                        </div>
                                        <div class="card-body p-4">
                                            <form id="quickGenerateForm">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control" id="idInput" placeholder="Enter ID" required>
                                                            <label for="idInput">Enter Student ID or Faculty ID</label>
                                                        </div>
                                                        <small class="text-muted">
                                                            Examples: 2024-001, FAC-001, or create your own test ID
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <button type="submit" class="btn btn-primary w-100 h-100" style="background: var(--primary-gradient); border: none;">
                                                            <i class="fas fa-qrcode me-2"></i>Generate QR
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                            
                                            <!-- Quick Generate Result -->
                                            <div id="quickResult" style="display: none;" class="mt-4">
                                                <!-- Result will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Batch Generation Options -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <button class="btn w-100 py-3" onclick="generateAll('students')" style="background: var(--success-gradient); border: none; color: white; border-radius: 15px;">
                                        <i class="fas fa-user-graduate me-2"></i>Generate All Student QR Codes
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn w-100 py-3" onclick="generateAll('faculty')" style="background: var(--secondary-gradient); border: none; color: white; border-radius: 15px;">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>Generate All Faculty QR Codes
                                    </button>
                                </div>
                            </div>
                            
                            <!-- QR Results Area -->
                            <div id="qrResultsArea">
                                <!-- Generated QR codes will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div id="analytics-section" class="content-section" style="display: none;">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Peak Hours Analysis
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="chart-container">
                                <canvas id="peakHoursChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="enhanced-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h5 class="mb-0">
                                <i class="fas fa-pie-chart me-2"></i>Department Distribution
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="chart-container">
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Section -->
        <div id="reports-section" class="content-section" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h4 class="mb-0">
                                <i class="fas fa-download me-3"></i>Data Export Center
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="d-grid gap-3 mb-4">
                                        <button class="btn btn-success py-3" onclick="exportExcel()" style="border-radius: 15px;">
                                            <i class="fas fa-file-excel me-2"></i>Export to Excel
                                            <span class="loading-spinner d-none" id="excelLoading"></span>
                                        </button>
                                        <button class="btn btn-danger py-3" onclick="exportPDF()" style="border-radius: 15px;">
                                            <i class="fas fa-file-pdf me-2"></i>Export to PDF
                                            <span class="loading-spinner d-none" id="pdfLoading"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="date-input-group">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt me-2"></i>Date Range:
                                        </label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="border-radius: 10px;">
                                            </div>
                                            <div class="col-6">
                                                <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="border-radius: 10px;">
                                            </div>
                                        </div>
                                        <button class="btn btn-primary w-100 mt-3 py-3" onclick="generateReport()" style="background: var(--primary-gradient); border: none; border-radius: 15px;">
                                            <i class="fas fa-chart-bar me-2"></i>Generate Custom Report
                                            <span class="loading-spinner d-none" id="reportLoading"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Management Section -->
        <div id="users-section" class="content-section" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header bg-transparent border-0 p-4">
                            <h4 class="mb-0">
                                <i class="fas fa-users me-3"></i>User Management System
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('students')" style="cursor: pointer; transition: all 0.3s ease;">
                                        <div style="font-size: 48px; color: var(--success-color); margin-bottom: 20px;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <h6>Manage Students</h6>
                                        <p class="text-muted">Add, edit, or manage student accounts and information</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('faculty')" style="cursor: pointer; transition: all 0.3s ease;">
                                        <div style="font-size: 48px; color: var(--info-color); margin-bottom: 20px;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <h6>Manage Faculty</h6>
                                        <p class="text-muted">Add, edit, or manage faculty accounts and information</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('security')" style="cursor: pointer; transition: all 0.3s ease;">
                                        <div style="font-size: 48px; color: var(--warning-color); margin-bottom: 20px;">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <h6>Manage Security</h6>
                                        <p class="text-muted">Add, edit, or manage security personnel accounts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin.js"></script>
    
    <script>
        // Initialize charts with PHP data
        const peakHoursData = <?php echo json_encode($peakHours); ?>;
        
        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            const contentSections = document.querySelectorAll('.content-section');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all nav links
                    navLinks.forEach(navLink => navLink.classList.remove('active'));
                    
                    // Add active class to clicked nav link
                    this.classList.add('active');
                    
                    // Hide all content sections
                    contentSections.forEach(section => {
                        section.style.display = 'none';
                        section.classList.remove('fade-in-up');
                    });
                    
                    // Show selected section with animation
                    const targetSection = document.getElementById(this.dataset.section + '-section');
                    if (targetSection) {
                        targetSection.style.display = 'block';
                        setTimeout(() => {
                            targetSection.classList.add('fade-in-up');
                        }, 50);
                    }
                });
            });
            
            // Initialize animations
            const animatedElements = document.querySelectorAll('.fade-in-up, .bounce-in, .slide-in-left, .slide-in-right');
            animatedElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
            
            if (typeof initializeCharts === 'function') {
                initializeCharts();
            }
        });
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        
        // QR Generator Functions
        document.getElementById('quickGenerateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('idInput').value.trim();
            
            if (!id) {
                showNotification('Please enter an ID', 'warning');
                return;
            }
            
            generateSingleQR(id);
        });
        
        function generateSingleQR(id) {
            const resultDiv = document.getElementById('quickResult');
            resultDiv.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary mb-3"></div>
                    <p class="mb-0">Generating QR code...</p>
                </div>
            `;
            resultDiv.style.display = 'block';
            resultDiv.classList.add('fade-in-up');
            
            fetch(`../../qr_generator_api.php?action=generate_by_id&id=${encodeURIComponent(id)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const person = data.person;
                        resultDiv.innerHTML = `
                            <div class="enhanced-card border-success" style="border-width: 2px !important;">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">${person.FName} ${person.MName ? person.MName + ' ' : ''}${person.LName}</h5>
                                                    <small class="text-muted">QR Code Generated Successfully</small>
                                                </div>
                                            </div>
                                            
                                            <div class="info-grid">
                                                <p><strong>ID:</strong> <span class="badge bg-primary">${person.ID}</span></p>
                                                <p><strong>Type:</strong> <span class="badge bg-info">${person.Type}</span></p>
                                                ${person.Course ? `<p><strong>Course:</strong> ${person.Course}</p>` : ''}
                                                ${person.YearLvl ? `<p><strong>Year:</strong> ${person.YearLvl} - Section ${person.Section}</p>` : ''}
                                                <p><strong>Department:</strong> ${person.Department}</p>
                                            </div>
                                            
                                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                                <button class="btn btn-success" onclick="testScanner('${person.ID}')" style="border-radius: 10px;">
                                                    <i class="fas fa-camera me-1"></i>Test Scanner
                                                </button>
                                                <button class="btn btn-primary" onclick="downloadQR('${person.ID}', '${person.FName} ${person.LName}')" style="border-radius: 10px;">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </button>
                                                <button class="btn btn-outline-secondary" onclick="shareQR('${person.ID}')" style="border-radius: 10px;">
                                                    <i class="fas fa-share me-1"></i>Share
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <div class="qr-container" style="background: linear-gradient(45deg, #667eea, #764ba2); padding: 20px; border-radius: 20px; display: inline-block;">
                                                <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 200px; border: 4px solid white; border-radius: 15px;">
                                            </div>
                                            <p class="mt-3 text-muted">
                                                <i class="fas fa-mobile-alt me-1"></i>Scan with CTU Scanner
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        showNotification('QR Code generated successfully!', 'success');
                    } else {
                        resultDiv.innerHTML = `
                            <div class="enhanced-card border-warning" style="border-width: 2px !important;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">ID Not Found</h5>
                                            <small class="text-muted">${data.message}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info border-0" style="background: rgba(13, 202, 240, 0.1);">
                                        <p class="mb-2"><strong>Testing Mode:</strong> Generate QR for testing purposes.</p>
                                        <button class="btn btn-warning" onclick="generateTestQR('${id}')" style="border-radius: 10px;">
                                            <i class="fas fa-vial me-1"></i>Generate Test QR
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="enhanced-card border-danger" style="border-width: 2px !important;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Error</h5>
                                        <p class="mb-0">Error generating QR code: ${error.message}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    showNotification('Error generating QR code', 'error');
                });
        }
        
        function generateTestQR(id) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(id)}`;
            const resultDiv = document.getElementById('quickResult');
            
            resultDiv.innerHTML = `
                <div class="enhanced-card border-warning" style="border-width: 2px !important;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Test QR Code</h5>
                                        <small class="text-muted">Generated for testing purposes</small>
                                    </div>
                                </div>
                                
                                <div class="info-grid">
                                    <p><strong>ID:</strong> <span class="badge bg-warning text-dark">${id}</span></p>
                                    <p><strong>Type:</strong> <span class="badge bg-secondary">Test/Unknown</span></p>
                                </div>
                                
                                <div class="alert alert-warning border-0" style="background: rgba(255, 193, 7, 0.1);">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        This ID is not in the database but can be used for testing.
                                    </small>
                                </div>
                                
                                <div class="mt-4 d-flex gap-2 flex-wrap">
                                    <button class="btn btn-success" onclick="testScanner('${id}')" style="border-radius: 10px;">
                                        <i class="fas fa-camera me-1"></i>Test Scanner
                                    </button>
                                    <button class="btn btn-primary" onclick="downloadQR('${id}', 'Test ID')" style="border-radius: 10px;">
                                        <i class="fas fa-download me-1"></i>Download
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="qr-container" style="background: linear-gradient(45deg, #ffc107, #ff8f00); padding: 20px; border-radius: 20px; display: inline-block;">
                                    <img src="${qrUrl}" alt="QR Code" class="img-fluid" style="max-width: 200px; border: 4px solid white; border-radius: 15px;">
                                </div>
                                <p class="mt-3 text-muted">
                                    <i class="fas fa-flask me-1"></i>Test QR Code
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            showNotification('Test QR Code generated!', 'warning');
        }
        
        function generateAll(type) {
            const resultsArea = document.getElementById('qrResultsArea');
            resultsArea.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h5>Loading all ${type} QR codes...</h5>
                    <p class="text-muted">This may take a moment</p>
                </div>
            `;
            
            fetch(`../../qr_generator_api.php?action=${type}`)
                .then(response => response.text())
                .then(html => {
                    resultsArea.innerHTML = html;
                    resultsArea.classList.add('fade-in-up');
                    showNotification(`${type} QR codes loaded successfully!`, 'success');
                })
                .catch(error => {
                    resultsArea.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading ${type} codes: ${error.message}
                        </div>
                    `;
                    showNotification(`Error loading ${type} codes`, 'error');
                });
        }
        
        function testScanner(id) {
            window.open(`../scanner/index.php?test_id=${encodeURIComponent(id)}`, '_blank');
            showNotification('Scanner opened in new tab', 'info');
        }
        
        function downloadQR(id, name) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(id)}`;
            
            showNotification('Downloading QR code...', 'info');
            
            fetch(qrUrl)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `QR_${id}_${name.replace(/\s+/g, '_')}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    showNotification('QR code downloaded successfully!', 'success');
                })
                .catch(error => {
                    console.error('Download failed:', error);
                    showNotification('Download failed. Right-click the QR code to save manually.', 'error');
                });
        }
        
        function shareQR(id) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(id)}`;
            
            if (navigator.share) {
                navigator.share({
                    title: `QR Code for ${id}`,
                    text: `QR Code for ID: ${id}`,
                    url: qrUrl
                });
            } else {
                navigator.clipboard.writeText(qrUrl).then(() => {
                    showNotification('QR code URL copied to clipboard!', 'success');
                });
            }
        }
        
        // Enhanced management functions
        function manageUsers(type) {
            const managementCards = document.querySelectorAll('.management-card');
            const clickedCard = event.currentTarget;
            
            clickedCard.style.transform = 'scale(0.95)';
            showNotification(`Loading ${type} management...`, 'info');
            
            setTimeout(() => {
                window.location.href = `manage_users.php?type=${type}`;
            }, 300);
        }
        
        // Enhanced export functions with loading states
        function exportExcel() {
            const loading = document.getElementById('excelLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            showNotification('Preparing Excel export...', 'info');
            
            setTimeout(() => {
                window.open(`export_excel.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
                showNotification('Excel file downloaded!', 'success');
            }, 1500);
        }
        
        function exportPDF() {
            const loading = document.getElementById('pdfLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            showNotification('Preparing PDF export...', 'info');
            
            setTimeout(() => {
                window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
                showNotification('PDF file downloaded!', 'success');
            }, 1500);
        }
        
        function generateReport() {
            const loading = document.getElementById('reportLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showNotification('Please select both start and end dates.', 'warning');
                return;
            }
            
            loading.classList.remove('d-none');
            showNotification('Generating custom report...', 'info');
            
            setTimeout(() => {
                showNotification(`Custom report generated for ${startDate} to ${endDate}`, 'success');
                loading.classList.add('d-none');
            }, 2500);
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'bg-success',
                error: 'bg-danger',
                warning: 'bg-warning text-dark',
                info: 'bg-info'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            notification.className = `alert ${colors[type]} border-0 shadow-lg position-fixed`;
            notification.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                border-radius: 15px;
                animation: slideInRight 0.5s ease-out;
            `;
            
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${icons[type]} me-2"></i>
                    <span>${message}</span>
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.5s ease-out';
                    setTimeout(() => notification.remove(), 500);
                }
            }, 5000);
        }
        
        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>