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
            --sidebar-collapsed-width: 70px;
            --header-height: 70px;
            
            /* Updated Color Palette */
            --primary-color: #D8AC41;
            --danger-color: #E00000;
            --warning-color: #FF9600;
            --secondary-color: #DB362D;
            
            /* Gradients with new colors */
            --primary-gradient: linear-gradient(135deg, #D8AC41 0%, #FF9600 100%);
            --secondary-gradient: linear-gradient(135deg, #DB362D 0%, #E00000 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #FF9600 0%, #D8AC41 100%);
            --danger-gradient: linear-gradient(135deg, #E00000 0%, #DB362D 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            padding-left: var(--sidebar-width);
            transition: padding-left 0.2s ease;
            overflow-x: hidden;
        }

        body.sidebar-collapsed {
            padding-left: var(--sidebar-collapsed-width);
        }

        /* Enhanced Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            box-shadow: 4px 0 20px rgba(0,0,0,0.2);
            z-index: 1050;
            transition: all 0.2s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            transition: all 0.2s ease;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 20px 10px;
        }

        .sidebar-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            margin-bottom: 10px;
            border: 2px solid var(--primary-color);
            transition: all 0.2s ease;
        }

        .sidebar.collapsed .sidebar-logo {
            width: 40px;
            height: 40px;
            margin-bottom: 5px;
        }

        .sidebar-title, .sidebar-subtitle {
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-title,
        .sidebar.collapsed .sidebar-subtitle {
            opacity: 0;
            transform: scale(0);
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

        /* Toggle Button */
        .sidebar-toggle {
            position: absolute;
            top: 25px;
            right: -15px;
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            color: #fff;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .sidebar-toggle:hover {
            background: var(--warning-color);
            transform: scale(1.1);
        }

        .sidebar-nav {
            padding: 15px 0;
        }

        .nav-item {
            margin: 3px 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.15s ease;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: var(--primary-gradient);
            transition: width 0.15s ease;
            border-radius: 10px;
        }

        .nav-link:hover::before {
            width: 100%;
        }

        .nav-link:hover {
            color: #fff;
            transform: translateX(3px);
        }

        .sidebar.collapsed .nav-link:hover {
            transform: translateX(0) scale(1.05);
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
            position: relative;
            z-index: 1;
            transition: margin 0.2s ease;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .nav-link span {
            position: relative;
            z-index: 1;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar.collapsed .nav-link span {
            opacity: 0;
            transform: scale(0);
            width: 0;
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: #fff;
            transform: translateX(3px);
        }

        .sidebar.collapsed .nav-link.active {
            transform: translateX(0) scale(1.05);
        }

        /* Tooltip for collapsed sidebar */
        .nav-link-tooltip {
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.15s ease;
            z-index: 1000;
        }

        .nav-link-tooltip::before {
            content: '';
            position: absolute;
            left: -5px;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #333;
        }

        .sidebar.collapsed .nav-link:hover .nav-link-tooltip {
            opacity: 1;
            left: 75px;
        }

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            z-index: 1040;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: left 0.2s ease;
        }

        body.sidebar-collapsed .top-header {
            left: var(--sidebar-collapsed-width);
        }

        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: #666;
            font-size: 18px;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.15s ease;
        }

        .notification-btn:hover {
            background: rgba(216, 172, 65, 0.1);
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: var(--danger-color);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(216, 172, 65, 0.1);
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.15s ease;
            font-size: 14px;
        }

        .user-profile:hover {
            background: rgba(216, 172, 65, 0.2);
            transform: scale(1.05);
            color: var(--primary-color);
        }

        /* Main Content */
        .main-content {
            margin-top: var(--header-height);
            padding: 20px;
            min-height: calc(100vh - var(--header-height));
            transition: all 0.2s ease;
        }

        /* Enhanced Cards */
        .enhanced-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.15s ease;
            overflow: hidden;
            position: relative;
        }

        .enhanced-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .enhanced-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .stat-card {
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: all 0.15s ease;
            position: relative;
            overflow: hidden;
            color: white;
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
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scale(1);
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .stat-card.bg-primary { background: var(--primary-gradient); }
        .stat-card.bg-success { background: var(--success-gradient); }
        .stat-card.bg-warning { background: var(--warning-gradient); }
        .stat-card.bg-info { background: var(--secondary-gradient); }
        .stat-card.bg-danger { background: var(--danger-gradient); }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .stat-info p {
            margin: 0;
            opacity: 0.95;
            position: relative;
            z-index: 1;
            font-size: 14px;
        }

        /* Fast Animations */
        .slide-in-left {
            animation: slideInLeft 0.3s ease-out forwards;
        }

        .slide-in-right {
            animation: slideInRight 0.3s ease-out forwards;
        }

        .fade-in-up {
            animation: fadeInUp 0.4s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .bounce-in {
            animation: bounceIn 0.3s ease-out forwards;
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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        /* Button Styles */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary:hover {
            background: var(--warning-gradient);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(216, 172, 65, 0.3);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
            transition: all 0.15s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-warning {
            background: var(--warning-gradient);
            border: none;
            color: #fff;
            transition: all 0.15s ease;
        }

        .btn-warning:hover {
            background: var(--primary-gradient);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 150, 0, 0.3);
            color: #fff;
        }

        .btn-danger {
            background: var(--danger-gradient);
            border: none;
            transition: all 0.15s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(224, 0, 0, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 1199px) {
            .main-content {
                padding: 15px;
            }
        }

        @media (max-width: 991px) {
            .header-title {
                font-size: 18px;
            }
            
            .stat-card {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .stat-info h3 {
                font-size: 24px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }
            
            body.sidebar-collapsed {
                padding-left: 0;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }
            
            .sidebar.mobile-active {
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                width: var(--sidebar-width);
            }
            
            .top-header {
                left: 0;
            }
            
            body.sidebar-collapsed .top-header {
                left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
            
            .sidebar-toggle {
                display: none;
            }
            
            .main-content {
                padding: 15px 10px;
            }
            
            .header-title {
                font-size: 16px;
            }
            
            .user-profile span {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 12px;
            }
            
            .stat-icon {
                font-size: 28px;
                margin-bottom: 8px;
            }
            
            .stat-info h3 {
                font-size: 20px;
            }
            
            .stat-info p {
                font-size: 12px;
            }
            
            .header-actions {
                gap: 8px;
            }
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: #666;
            font-size: 18px;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .mobile-toggle:hover {
            background: rgba(216, 172, 65, 0.1);
            color: var(--primary-color);
        }

        /* Mobile Overlay - Enhanced */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.6);
            z-index: 1030;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            cursor: pointer;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: all;
        }

        /* Ensure sidebar is above overlay but overlay is clickable */
        .sidebar {
            z-index: 1050;
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .mobile-overlay.active {
                display: block !important;
            }
            
            /* Make sure overlay covers everything */
            .mobile-overlay {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }
        }

        /* Mobile Close Button */
        .mobile-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            color: rgba(255,255,255,0.8);
            font-size: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 1;
        }

        .mobile-close-btn:hover {
            background: rgba(255,255,255,0.3);
            color: #fff;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .mobile-close-btn {
                display: flex;
            }
            
            .sidebar-toggle {
                display: none !important;
            }
        }

        /* Management Cards */
        .management-card {
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .management-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /* Form Controls */
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(216, 172, 65, 0.25);
        }

        /* Loading Spinner */
        .loading-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Notification Animations */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        /* Enhanced card content */
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,0.08);
            background: transparent;
        }

        /* Quick access buttons */
        .quick-action-btn {
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(0,0,0,0.1);
            padding: 15px;
            border-radius: 12px;
            transition: all 0.15s ease;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .quick-action-btn:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar slide-in-left" id="sidebar">
        <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <i class="fas fa-chevron-left" id="toggleIcon"></i>
        </button>
        
        <!-- Mobile Close Button -->
        <button class="mobile-close-btn" onclick="closeMobileSidebar()" title="Close Menu">
            <i class="fas fa-times"></i>
        </button>
        
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
                    <div class="nav-link-tooltip">Dashboard</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="#qr-generator" class="nav-link" data-section="qr-generator">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Generator</span>
                    <div class="nav-link-tooltip">QR Generator</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="#analytics" class="nav-link" data-section="analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                    <div class="nav-link-tooltip">Analytics</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="#reports" class="nav-link" data-section="reports">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                    <div class="nav-link-tooltip">Reports</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="#users" class="nav-link" data-section="users">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                    <div class="nav-link-tooltip">User Management</div>
                </a>
            </div>
            <div class="nav-item">
                <a href="#settings" class="nav-link" data-section="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                    <div class="nav-link-tooltip">Settings</div>
                </a>
            </div>
        </div>
        
        <div style="position: absolute; bottom: 20px; left: 8px; right: 8px;">
            <div class="nav-item">
                <a href="logout.php" class="nav-link" style="background: rgba(224, 0, 0, 0.1); color: var(--danger-color);">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                    <div class="nav-link-tooltip">Logout</div>
                </a>
            </div>
        </div>
    </nav>

    <!-- Top Header -->
    <header class="top-header slide-in-right">
        <div class="d-flex align-items-center">
            <button class="mobile-toggle me-3" onclick="openMobileSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">Campus Management System</h1>
        </div>
        
        <div class="header-actions">
            <button class="notification-btn" title="Notifications">
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
                        <div class="card-header p-3">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Today's Campus Overview
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-primary bounce-in" style="animation-delay: 0.05s;">
                                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                                        <div class="stat-info">
                                            <h3><?php echo $stats['total_entries']; ?></h3>
                                            <p>Total Entries</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-success bounce-in" style="animation-delay: 0.1s;">
                                        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                        <div class="stat-info">
                                            <h3><?php echo $stats['student_entries']; ?></h3>
                                            <p>Student Entries</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-info bounce-in" style="animation-delay: 0.15s;">
                                        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                        <div class="stat-info">
                                            <h3><?php echo $stats['faculty_entries']; ?></h3>
                                            <p>Faculty Entries</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card bg-warning bounce-in" style="animation-delay: 0.2s;">
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

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="#qr-generator" class="quick-action-btn" data-section="qr-generator">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-qrcode" style="font-size: 24px; color: var(--primary-color);"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Generate QR</h6>
                                <small class="text-muted">Create QR codes</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="#analytics" class="quick-action-btn" data-section="analytics">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line" style="font-size: 24px; color: var(--secondary-color);"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Analytics</h6>
                                <small class="text-muted">View reports</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="#users" class="quick-action-btn" data-section="users">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users" style="font-size: 24px; color: var(--warning-color);"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Manage Users</h6>
                                <small class="text-muted">User accounts</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="#reports" class="quick-action-btn" data-section="reports">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-download" style="font-size: 24px; color: var(--danger-color);"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Export Data</h6>
                                <small class="text-muted">Download reports</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- QR Generator Section -->
        <div id="qr-generator-section" class="content-section" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header p-3">
                            <h5 class="mb-0">
                                <i class="fas fa-qrcode me-2"></i>QR Code Generator & Management
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <!-- Quick QR Generator -->
                            <div class="row mb-4">
                                <div class="col-lg-8 mx-auto">
                                    <div class="enhanced-card" style="border: 2px solid var(--primary-color) !important;">
                                        <div class="card-header bg-light p-3">
                                            <h6 class="mb-0">
                                                <i class="fas fa-magic me-2"></i>Quick QR Generator
                                            </h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <form id="quickGenerateForm">
                                                <div class="row align-items-end">
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
                                                        <button type="submit" class="btn btn-primary w-100 py-3">
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
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-success w-100 py-3" onclick="generateAll('students')" style="border-radius: 12px;">
                                        <i class="fas fa-user-graduate me-2"></i>Generate All Student QR Codes
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-info w-100 py-3" onclick="generateAll('faculty')" style="border-radius: 12px;">
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
                        <div class="card-header p-3">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Peak Hours Analysis
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart-container">
                                <canvas id="peakHoursChart" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="enhanced-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="card-header p-3">
                            <h6 class="mb-0">
                                <i class="fas fa-pie-chart me-2"></i>Department Distribution
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart-container">
                                <canvas id="departmentChart" style="max-height: 300px;"></canvas>
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
                        <div class="card-header p-3">
                            <h5 class="mb-0">
                                <i class="fas fa-download me-2"></i>Data Export Center
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="d-grid gap-3 mb-4">
                                        <button class="btn btn-success py-3" onclick="exportExcel()" style="border-radius: 12px;">
                                            <i class="fas fa-file-excel me-2"></i>Export to Excel
                                            <span class="loading-spinner d-none ms-2" id="excelLoading"></span>
                                        </button>
                                        <button class="btn btn-danger py-3" onclick="exportPDF()" style="border-radius: 12px;">
                                            <i class="fas fa-file-pdf me-2"></i>Export to PDF
                                            <span class="loading-spinner d-none ms-2" id="pdfLoading"></span>
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
                                                <input type="date" id="startDate" class="form-control mb-2" value="<?php echo date('Y-m-d'); ?>" style="border-radius: 8px;">
                                            </div>
                                            <div class="col-6">
                                                <input type="date" id="endDate" class="form-control mb-2" value="<?php echo date('Y-m-d'); ?>" style="border-radius: 8px;">
                                            </div>
                                        </div>
                                        <button class="btn btn-primary w-100 py-3" onclick="generateReport()" style="border-radius: 12px;">
                                            <i class="fas fa-chart-bar me-2"></i>Generate Custom Report
                                            <span class="loading-spinner d-none ms-2" id="reportLoading"></span>
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
                        <div class="card-header p-3">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>User Management System
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('students')" style="cursor: pointer;">
                                        <div style="font-size: 42px; color: var(--primary-color); margin-bottom: 15px;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <h6>Manage Students</h6>
                                        <p class="text-muted small">Add, edit, or manage student accounts and information</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('faculty')" style="cursor: pointer;">
                                        <div style="font-size: 42px; color: var(--secondary-color); margin-bottom: 15px;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <h6>Manage Faculty</h6>
                                        <p class="text-muted small">Add, edit, or manage faculty accounts and information</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('security')" style="cursor: pointer;">
                                        <div style="font-size: 42px; color: var(--warning-color); margin-bottom: 15px;">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <h6>Manage Security</h6>
                                        <p class="text-muted small">Add, edit, or manage security personnel accounts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Section -->
        <div id="settings-section" class="content-section" style="display: none;">
            <div class="row">
                <div class="col-12">
                    <div class="enhanced-card fade-in-up">
                        <div class="card-header p-3">
                            <h5 class="mb-0">
                                <i class="fas fa-cog me-2"></i>System Settings
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6 class="mb-3">
                                        <i class="fas fa-palette me-2"></i>Theme Settings
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label">Color Scheme</label>
                                        <select class="form-select" style="border-radius: 8px;">
                                            <option>CTU Gold Theme (Current)</option>
                                            <option>Dark Theme</option>
                                            <option>Light Theme</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h6 class="mb-3">
                                        <i class="fas fa-bell me-2"></i>Notification Settings
                                    </h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                        <label class="form-check-label" for="emailNotif">
                                            Email Notifications
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="pushNotif" checked>
                                        <label class="form-check-label" for="pushNotif">
                                            Push Notifications
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-database me-2"></i>System Information
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Version</small>
                                            <p class="mb-2">CTU Scanner v2.1</p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Last Update</small>
                                            <p class="mb-2"><?php echo date('M d, Y'); ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Database</small>
                                            <p class="mb-2">MySQL 8.0</p>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Status</small>
                                            <p class="mb-2">
                                                <span class="badge bg-success">Active</span>
                                            </p>
                                        </div>
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
        let sidebarCollapsed = false;
        
        // Enhanced Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link[data-section], .quick-action-btn[data-section]');
            const contentSections = document.querySelectorAll('.content-section');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all nav links
                    document.querySelectorAll('.nav-link[data-section]').forEach(navLink => navLink.classList.remove('active'));
                    
                    // Add active class to clicked nav link (if it's a nav-link)
                    if (this.classList.contains('nav-link')) {
                        this.classList.add('active');
                    } else {
                        // Find corresponding nav link and activate it
                        const correspondingNavLink = document.querySelector(`.nav-link[data-section="${this.dataset.section}"]`);
                        if (correspondingNavLink) {
                            correspondingNavLink.classList.add('active');
                        }
                    }
                    
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
                    
                    // Close mobile sidebar if open
                    if (window.innerWidth <= 768) {
                        toggleMobileSidebar(false);
                    }
                });
            });
            
            // Initialize animations with faster delays
            const animatedElements = document.querySelectorAll('.fade-in-up, .bounce-in');
            animatedElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.05}s`;
            });
            
            // Initialize charts if function exists
            if (typeof initializeCharts === 'function') {
                setTimeout(initializeCharts, 300);
            }
            
            // Handle window resize
            window.addEventListener('resize', handleWindowResize);
            handleWindowResize(); // Initial call
        });
        
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            const toggleIcon = document.getElementById('toggleIcon');
            
            sidebarCollapsed = !sidebarCollapsed;
            
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                body.classList.add('sidebar-collapsed');
                toggleIcon.className = 'fas fa-chevron-right';
            } else {
                sidebar.classList.remove('collapsed');
                body.classList.remove('sidebar-collapsed');
                toggleIcon.className = 'fas fa-chevron-left';
            }
            
            // Store preference
            localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
        }
        
        // SIMPLE MOBILE SIDEBAR FUNCTIONS
        function openMobileSidebar() {
            console.log('Opening mobile sidebar');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.add('mobile-active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileSidebar() {
            console.log('Closing mobile sidebar');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Mobile Detection and Sidebar Toggle
        function isMobile() {
            return window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
        
        // Keep this for backward compatibility
        function toggleMobileSidebar(force = null) {
            if (force === false) {
                closeMobileSidebar();
            } else if (force === true) {
                openMobileSidebar();
            } else {
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('mobile-active')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            }
        }
        
        // Force setup overlay handlers after DOM is fully loaded
        window.addEventListener('load', function() {
            console.log('Window loaded - simple setup');
        });
        
        // Handle window resize and cleanup
        function handleWindowResize() {
            if (window.innerWidth > 768) {
                // Desktop: cleanup mobile overlay
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('mobileOverlay');
                
                sidebar.classList.remove('mobile-active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                
                // Restore sidebar collapsed state
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true' && !sidebarCollapsed) {
                    toggleSidebar();
                }
            } else {
                // Mobile: ensure sidebar is not in collapsed state
                const sidebar = document.getElementById('sidebar');
                const body = document.body;
                
                if (sidebarCollapsed) {
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    sidebarCollapsed = false;
                }
            }
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
            
            // Simulate API call
            setTimeout(() => {
                fetch(`../../qr_generator_api.php?action=generate_by_id&id=${encodeURIComponent(id)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const person = data.person;
                            resultDiv.innerHTML = `
                                <div class="enhanced-card border-success" style="border-width: 2px !important;">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">${person.FName} ${person.MName ? person.MName + ' ' : ''}${person.LName}</h6>
                                                        <small class="text-muted">QR Code Generated</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="info-grid">
                                                    <p class="mb-1"><strong>ID:</strong> <span class="badge bg-primary">${person.ID}</span></p>
                                                    <p class="mb-1"><strong>Type:</strong> <span class="badge bg-info">${person.Type}</span></p>
                                                    ${person.Course ? `<p class="mb-1"><strong>Course:</strong> ${person.Course}</p>` : ''}
                                                    ${person.YearLvl ? `<p class="mb-1"><strong>Year:</strong> ${person.YearLvl} - Section ${person.Section}</p>` : ''}
                                                    <p class="mb-3"><strong>Department:</strong> ${person.Department}</p>
                                                </div>
                                                
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <button class="btn btn-success btn-sm" onclick="testScanner('${person.ID}')">
                                                        <i class="fas fa-camera me-1"></i>Test
                                                    </button>
                                                    <button class="btn btn-primary btn-sm" onclick="downloadQR('${person.ID}', '${person.FName} ${person.LName}')">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </button>
                                                    <button class="btn btn-outline-secondary btn-sm" onclick="shareQR('${person.ID}')">
                                                        <i class="fas fa-share me-1"></i>Share
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-center">
                                                <div class="qr-container" style="background: var(--primary-gradient); padding: 15px; border-radius: 15px; display: inline-block;">
                                                    <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 150px; border: 3px solid white; border-radius: 10px;">
                                                </div>
                                                <p class="mt-2 small text-muted">
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
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">ID Not Found</h6>
                                                <small class="text-muted">${data.message}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-info border-0 p-3" style="background: rgba(13, 202, 240, 0.1);">
                                            <p class="mb-2 small"><strong>Testing Mode:</strong> Generate QR for testing purposes.</p>
                                            <button class="btn btn-warning btn-sm" onclick="generateTestQR('${id}')">
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
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Error</h6>
                                            <p class="mb-0 small">Error generating QR code: ${error.message}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        showNotification('Error generating QR code', 'error');
                    });
            }, 800);
        }
        
        function generateTestQR(id) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(id)}`;
            const resultDiv = document.getElementById('quickResult');
            
            resultDiv.innerHTML = `
                <div class="enhanced-card border-warning" style="border-width: 2px !important;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-vial"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Test QR Code</h6>
                                        <small class="text-muted">Generated for testing</small>
                                    </div>
                                </div>
                                
                                <div class="info-grid">
                                    <p class="mb-1"><strong>ID:</strong> <span class="badge bg-warning text-dark">${id}</span></p>
                                    <p class="mb-3"><strong>Type:</strong> <span class="badge bg-secondary">Test/Unknown</span></p>
                                </div>
                                
                                <div class="alert alert-warning border-0 p-2" style="background: rgba(255, 193, 7, 0.1);">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        This ID is not in the database but can be used for testing.
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-success btn-sm" onclick="testScanner('${id}')">
                                        <i class="fas fa-camera me-1"></i>Test
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="downloadQR('${id}', 'Test ID')">
                                        <i class="fas fa-download me-1"></i>Download
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="qr-container" style="background: var(--warning-gradient); padding: 15px; border-radius: 15px; display: inline-block;">
                                    <img src="${qrUrl}" alt="QR Code" class="img-fluid" style="max-width: 150px; border: 3px solid white; border-radius: 10px;">
                                </div>
                                <p class="mt-2 small text-muted">
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
                <div class="text-center p-4">
                    <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                    <h6>Loading all ${type} QR codes...</h6>
                    <p class="text-muted small">This may take a moment</p>
                </div>
            `;
            
            setTimeout(() => {
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
            }, 1000);
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
            const clickedCard = event.currentTarget;
            
            clickedCard.style.transform = 'scale(0.95)';
            showNotification(`Loading ${type} management...`, 'info');
            
            setTimeout(() => {
                clickedCard.style.transform = '';
                // window.location.href = `manage_users.php?type=${type}`;
                showNotification(`${type} management would open here`, 'info');
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
                // window.open(`export_excel.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
                showNotification('Excel file would download here!', 'success');
            }, 1500);
        }
        
        function exportPDF() {
            const loading = document.getElementById('pdfLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            showNotification('Preparing PDF export...', 'info');
            
            setTimeout(() => {
                // window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
                showNotification('PDF file would download here!', 'success');
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
            }, 2000);
        }
        
        // Enhanced notification system with faster animations
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'bg-success',
                error: 'bg-danger',
                warning: 'bg-warning text-dark',
                info: 'bg-primary'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            notification.className = `alert ${colors[type]} border-0 shadow-lg position-fixed`;
            notification.style.cssText = `
                top: 90px;
                right: 20px;
                z-index: 9999;
                min-width: 280px;
                border-radius: 12px;
                animation: slideInRight 0.3s ease-out;
                font-size: 14px;
            `;
            
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${icons[type]} me-2"></i>
                    <span class="flex-grow-1">${message}</span>
                    <button type="button" class="btn-close btn-close-${type === 'warning' ? 'dark' : 'white'} ms-2" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 4000);
        }
        
        // Initialize chart data (placeholder)
        function initializeCharts() {
            // Peak Hours Chart
            const peakCtx = document.getElementById('peakHoursChart');
            if (peakCtx) {
                new Chart(peakCtx, {
                    type: 'line',
                    data: {
                        labels: ['6AM', '8AM', '10AM', '12PM', '2PM', '4PM', '6PM', '8PM'],
                        datasets: [{
                            label: 'Campus Entries',
                            data: [12, 45, 78, 125, 89, 156, 67, 23],
                            borderColor: '#D8AC41',
                            backgroundColor: 'rgba(216, 172, 65, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
            
            // Department Chart
            const deptCtx = document.getElementById('departmentChart');
            if (deptCtx) {
                new Chart(deptCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Engineering', 'Business', 'Education', 'Arts & Sciences', 'Others'],
                        datasets: [{
                            data: [35, 25, 20, 15, 5],
                            backgroundColor: [
                                '#D8AC41',
                                '#E00000',
                                '#FF9600',
                                '#DB362D',
                                '#6c757d'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
        
        // Load saved sidebar state
        document.addEventListener('DOMContentLoaded', function() {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true' && window.innerWidth > 768) {
                setTimeout(() => toggleSidebar(), 100);
            }
        });
        
        // Enhanced keyboard shortcuts and touch handling
        document.addEventListener('keydown', function(e) {
            // ESC to close mobile sidebar
            if (e.key === 'Escape') {
                closeMobileSidebar();
            }
            
            // Ctrl + B to toggle sidebar
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                if (!isMobile()) {
                    toggleSidebar();
                } else {
                    toggleMobileSidebar();
                }
            }
        });
    </script>
    
    <style>
        /* Additional notification animations */
        @keyframes slideInRight {
            from { 
                transform: translateX(100%); 
                opacity: 0; 
            }
            to { 
                transform: translateX(0); 
                opacity: 1; 
            }
        }
        
        @keyframes slideOutRight {
            from { 
                transform: translateX(0); 
                opacity: 1; 
            }
            to { 
                transform: translateX(100%); 
                opacity: 0; 
            }
        }
        
        /* Smooth transitions for all interactive elements */
        * {
            transition-duration: 0.15s !important;
        }
        
        /* Enhanced mobile responsiveness */
        @media (max-width: 480px) {
            .main-content {
                padding: 10px 8px;
            }
            
            .enhanced-card {
                margin-bottom: 15px;
            }
            
            .stat-card {
                padding: 15px 10px;
            }
            
            .btn {
                font-size: 13px;
            }
            
            .card-body {
                padding: 15px !important;
            }
            
            .row.g-3 > * {
                margin-bottom: 10px;
            }
        }
        
        /* Loading states */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Enhanced hover effects */
        .enhanced-card {
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .enhanced-card:hover {
            transform: translateY(-4px) scale(1.01);
        }
        
        /* Improved focus states */
        .btn:focus,
        .form-control:focus,
        .nav-link:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* Print styles */
        @media print {
            .sidebar,
            .top-header,
            .quick-action-btn {
                display: none !important;
            }
            
            .main-content {
                margin: 0;
                padding: 0;
            }
            
            body {
                padding-left: 0;
                background: white !important;
            }
        }
    </style>
</body>
</html>