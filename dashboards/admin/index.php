<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once '../../includes/functions.php';

// Determine the base path for assets and API calls
// This works for both local and Infinity Free hosting
$base_path = '/dashboards/admin/';
$assets_path = '../../';

// Handle AJAX requests for dashboard stats refresh
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $scanner = new CTUScanner();
    $stats = $scanner->getDailyStats();
    header('Content-Type: application/json');
    echo json_encode($stats);
    exit;
}

// AJAX endpoint for custom range analytics
if (isset($_GET['ajax']) && $_GET['ajax'] == 2) {
    $scanner = new CTUScanner();
    $start = $_GET['start_date'] ?? date('Y-m-d');
    $end = $_GET['end_date'] ?? date('Y-m-d');

    header('Content-Type: application/json');
    try {
        $resp = [];

        // Stats for range
        $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date BETWEEN ? AND ?");
        $stmt->execute([$start, $end]);
        $resp['total_entries'] = (int)$stmt->fetchColumn();

        $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM exitlogs WHERE Date BETWEEN ? AND ?");
        $stmt->execute([$start, $end]);
        $resp['total_exits'] = (int)$stmt->fetchColumn();

        $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'student'");
        $stmt->execute([$start, $end]);
        $resp['student_entries'] = (int)$stmt->fetchColumn();

        $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'faculty'");
        $stmt->execute([$start, $end]);
        $resp['faculty_entries'] = (int)$stmt->fetchColumn();

        $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'staff'");
        $stmt->execute([$start, $end]);
        $resp['staff_entries'] = (int)$stmt->fetchColumn();

        // Unique visitors
        $stmt = $scanner->conn->prepare("SELECT COUNT(DISTINCT PersonID) FROM entrylogs WHERE Date BETWEEN ? AND ?");
        $stmt->execute([$start, $end]);
        $resp['unique_visitors'] = (int)$stmt->fetchColumn();

        // Peak hours
        $stmt = $scanner->conn->prepare("SELECT HOUR(Timestamp) as hour, COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? GROUP BY HOUR(Timestamp) ORDER BY hour ASC");
        $stmt->execute([$start, $end]);
        $resp['peak_hours'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Department stats (students + faculty + staff)
        $stmt = $scanner->conn->prepare("SELECT s.Department as Department, COUNT(*) as entry_count FROM entrylogs e LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student' WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'student' AND s.Department IS NOT NULL GROUP BY s.Department");
        $stmt->execute([$start, $end]);
        $studDeps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $scanner->conn->prepare("SELECT f.Department as Department, COUNT(*) as entry_count FROM entrylogs e LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty' WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'faculty' AND f.Department IS NOT NULL GROUP BY f.Department");
        $stmt->execute([$start, $end]);
        $facDeps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $scanner->conn->prepare("SELECT st.Department as Department, COUNT(*) as entry_count FROM entrylogs e LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff' WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'staff' AND st.Department IS NOT NULL GROUP BY st.Department");
        $stmt->execute([$start, $end]);
        $staffDeps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resp['department_stats'] = array_merge($studDeps, $facDeps, $staffDeps);

        // Location stats
        $stmt = $scanner->conn->prepare("SELECT sc.Location, COUNT(*) as entry_count FROM entrylogs e LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID WHERE e.Date BETWEEN ? AND ? GROUP BY sc.Location");
        $stmt->execute([$start, $end]);
        $resp['location_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($resp);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

$scanner = new CTUScanner();
$stats = $scanner->getDailyStats();
$peakHours = $scanner->getPeakHours();

// Get user management counts
$totalStudents = 0;
$totalFaculty = 0;
$totalSecurity = 0;
$totalStaff = 0;

try {
    // Count students
    $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM students WHERE isActive = 1");
    $stmt->execute();
    $totalStudents = $stmt->fetchColumn();
    
    // Count faculty
    $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM faculty WHERE isActive = 1");
    $stmt->execute();
    $totalFaculty = $stmt->fetchColumn();
    
    // Count security
    $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM security WHERE isActive = 1");
    $stmt->execute();
    $totalSecurity = $stmt->fetchColumn();
    
    // Count staff
    $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM staff WHERE isActive = 1");
    $stmt->execute();
    $totalStaff = $stmt->fetchColumn();
} catch (Exception $e) {
    error_log("Error fetching user counts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" data-assets-path="<?php echo $assets_path; ?>" data-base-path="<?php echo $base_path; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        /* Find and replace the :root CSS variables */
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 70px;
            
            /* Updated Color Palette */
            --primary-color: #972529;    /* Dark Red */
            --secondary-color: #E5C573;  /* Gold */
            --accent-color: #972529;     /* Dark Red for accents */
            --success-color: #28a745;    /* Keep standard success */
            --warning-color: #E5C573;    /* Gold for warnings */
            --danger-color: #972529;     /* Dark Red for danger */
            --info-color: #E5C573;       /* Gold for info */
            
            /* Updated Gradients */
            --primary-gradient: #972529;
            --secondary-gradient: #E5C573;
            --success-gradient: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
            --warning-gradient: #E5C573;
            --danger-gradient: #972529;
            --info-gradient: #E5C573;
            
            --entries-gradient: #972529;      /* Dark Red */
            --exits-gradient: #E5C573;        /* Gold */
            --student-gradient: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);      /* Green */
            --faculty-gradient: linear-gradient(135deg, #2980B9 0%, #3498DB 100%);      /* Blue */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
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
            background: #972529;
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
            border: 2px solid #E5C573;
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
            background: rgba(151, 37, 41, 0.1);
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
            background: #E5C573;
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
            background: #E5C573;
            color: #333;
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
            background: rgba(229, 197, 115, 0.1);
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: #972529;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(151, 37, 41, 0.1);
            color: #972529;
            text-decoration: none;
            transition: all 0.15s ease;
            font-size: 14px;
        }

        .user-profile:hover {
            background: rgba(151, 37, 41, 0.2);
            color: #972529;
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
            background: var(--secondary-gradient);
        }

        .enhanced-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .stat-card {
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }

        .stat-card p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 0.9rem;
        }

        .stat-card.entries {
            border-left-color: #972529;
        }

        .stat-card.exits {
            border-left-color: #E5C573;
        }

        .stat-card.peak {
            border-left-color: #28a745;
        }

        .stat-card.busiest {
            border-left-color: #E5C573;
        }

        .stat-card.dwell {
            border-left-color: #007bff;
        }

        /* 5-Column responsive layout for analytics stats */
        .col-lg-2-4 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        @media (max-width: 1400px) {
            .col-lg-2-4 {
                flex: 0 0 33.33%;
                max-width: 33.33%;
            }
        }

        @media (max-width: 1200px) {
            .col-lg-2-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 768px) {
            .col-lg-2-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        /* Stat card number styling */
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            color: var(--primary-color);
            margin: 0;
        }

        .stat-card p {
            font-size: 0.9rem;
            color: #666;
            margin: 5px 0 0 0;
            letter-spacing: 0.5px;
        }

        .stat-card .card-body {
            padding-left: 0;
            padding-right: 0;
            display: none;
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
            background: var(--secondary-gradient);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(229, 197, 115, 0.3);
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
            color: #333;
            transition: all 0.15s ease;
        }

        .btn-warning:hover {
            background: var(--primary-gradient);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(151, 37, 41, 0.3);
            color: #FEFEFE;
        }

        .btn-danger {
            background: var(--danger-gradient);
            border: none;
            transition: all 0.15s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(151, 37, 41, 0.3);
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
        }

        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }
            
            body.sidebar-collapsed {
                padding-left: 0;
            }
            
            .sidebar {
                display: none;
                position: fixed;
                left: 0;
                top: 60px;
                width: 280px;
                height: calc(100vh - 60px);
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.mobile-show {
                display: block;
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                width: 280px;
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
        }

        @media (max-width: 576px) {
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
            background: rgba(151, 37, 41, 0.1);
            color: var(--accent-color);
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
            pointer-events: none;
        }

        .mobile-overlay.mobile-show {
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
            box-shadow: 0 0 0 0.2rem rgba(151, 37, 41, 0.25);
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

        /* Enhanced Mobile Sidebar Functions */
        /* Add to your existing styles */
        .sidebar {
            transition: transform 0.3s ease-in-out !important;
        }

        .sidebar.mobile-active {
            transform: translateX(0) !important;
        }

        .sidebar.fade-out {
            transform: translateX(-100%) !important;
        }

        .mobile-overlay {
            transition: opacity 0.3s ease-in-out !important;
        }

        .mobile-overlay.fade-out {
            opacity: 0 !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1050;
            }
            
            .mobile-close-btn {
                display: flex !important;
                z-index: 1051;
            }
            
            .mobile-overlay.active {
                opacity: 1;
                visibility: visible;
            }
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
        <button class="mobile-close-btn" onclick="hideMobileSidebarMenu()" title="Close Menu">
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
                <a href="visitor_analytics.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Visitor Analytics</span>
                    <div class="nav-link-tooltip">Visitor Analytics</div>
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
                <a href="logout.php" class="nav-link" style="background: var(--danger-gradient); color: #fff;">
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
            <button class="mobile-toggle me-3" onclick="showMobileSidebarMenu()">
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
                                    <div class="stat-card entries">
                                        <h3 data-stat="entries"><?php echo $stats['total_entries']; ?></h3>
                                        <p><i class="fas fa-sign-in-alt me-1"></i>Total Entries</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card peak">
                                        <h3 data-stat="student"><?php echo $stats['student_entries']; ?></h3>
                                        <p><i class="fas fa-user-graduate me-1"></i>Student Entries</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card peak">
                                        <h3 data-stat="faculty"><?php echo $stats['faculty_entries']; ?></h3>
                                        <p><i class="fas fa-chalkboard-user me-1"></i>Faculty Entries</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="stat-card exits">
                                        <h3 data-stat="exits"><?php echo $stats['total_exits']; ?></h3>
                                        <p><i class="fas fa-sign-out-alt me-1"></i>Total Exits</p>
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
                                <i class="fas fa-barcode" style="font-size: 24px; color: var(--primary-color);"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Generate Barcode</h6>
                                <small class="text-muted">Create Code 39 barcodes</small>
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
                                <i class="fas fa-barcode me-2"></i>Code 39 Barcode Generator & Management
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <!-- Quick QR Generator -->
                            <div class="row mb-4">
                                <div class="col-lg-8 mx-auto">
                                    <div class="enhanced-card" style="border: 2px solid var(--primary-color) !important;">
                                        <div class="card-header bg-light p-3">
                                            <h6 class="mb-0">
                                                <i class="fas fa-magic me-2"></i>Quick Barcode Generator
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
                                                            <i class="fas fa-barcode me-2"></i>Generate Barcode
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
                                <div class="col-md-4 mb-3">
                                    <button class="btn btn-success w-100 py-3" onclick="generateAll('students')" style="border-radius: 12px;">
                                        <i class="fas fa-user-graduate me-2"></i>Generate All Student Barcodes
                                    </button>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <button class="btn btn-info w-100 py-3" onclick="generateAll('faculty')" style="border-radius: 12px;">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>Generate All Faculty Barcodes
                                    </button>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <button class="btn btn-warning w-100 py-3" onclick="generateAll('staff')" style="border-radius: 12px;">
                                        <i class="fas fa-user-tie me-2"></i>Generate All Staff Barcodes
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Barcode Results Area -->
                            <div id="qrResultsArea">
                                <!-- Generated barcodes will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div id="analytics-section" class="content-section" style="display: none;">
            <div class="container-fluid">
                <!-- Analytics Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card enhanced-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Filters & Controls</h5>
                                    <div class="d-flex gap-2 flex-wrap align-items-end">
                                        <div class="form-group mb-0">
                                            <label class="form-label small fw-semibold mb-1">Date Range:</label>
                                            <select id="dateRangeFilter" class="form-select form-select-sm" style="min-width: 140px;">
                                                <option value="today">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="month">This Month</option>
                                                <option value="year">This Year</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="form-label small fw-semibold mb-1">Department:</label>
                                            <select id="departmentFilter" class="form-select form-select-sm" style="min-width: 140px;">
                                                <option value="all">All Departments</option>
                                                <option value="COTE">COTE</option>
                                                <option value="COED">COED</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="form-label small fw-semibold mb-1">User Type:</label>
                                            <select id="userTypeFilter" class="form-select form-select-sm" style="min-width: 140px;">
                                                <option value="all">All Users</option>
                                                <option value="student">Students</option>
                                                <option value="faculty">Faculty</option>
                                                <option value="staff">Staff</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="refreshAnalytics()" title="Refresh analytics data">
                                            <i class="fas fa-sync-alt me-1"></i>Refresh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card entries">
                            <h3 id="totalEntries">0</h3>
                            <p><i class="fas fa-sign-in-alt me-1"></i>Total Entries</p>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="stat-card exits">
                            <h3 id="totalExits">0</h3>
                            <p><i class="fas fa-sign-out-alt me-1"></i>Total Exits</p>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="stat-card peak">
                            <h3 id="peakHour">N/A</h3>
                            <p><i class="fas fa-clock me-1"></i>Peak Hour</p>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="stat-card dwell">
                            <h3 id="avgDwellTime">0h</h3>
                            <p><i class="fas fa-hourglass-end me-1"></i>Avg Dwell Time</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Peak Hours Distribution</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="peakHoursChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Department Comparison</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-line-chart me-2"></i>Weekly Trends</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="weeklyTrendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Entry vs Exit</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="entryExitChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3 -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i>User Type Distribution</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="userTypeChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Scanner Activity</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="scannerChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Distribution Row -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card enhanced-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Students by Course Distribution</h5>
                            </div>
                            <div class="card-body" style="height: 450px;">
                                <div id="courseDistributionContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Entry/Exit Logs Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Entry Logs Timeline</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="entryLogsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Exit Logs Timeline</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="exitLogsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Entry/Exit Comparison by Hour -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card enhanced-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-arrows-alt-h me-2"></i>Entry & Exit by Hour</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="entryExitHourlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scan Attempts Analytics -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scan Attempts Summary</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="attemptsSummaryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card" style="height: 100%;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Failed Attempts by Reason</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center" style="height: 350px;">
                                <canvas id="attemptsReasonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Scans Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="fas fa-history me-2"></i>Recent Scans</h5>
                    </div>
                </div>

                <!-- Recent Entries Table -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <h5 class="mb-0"><i class="fas fa-sign-in-alt me-2 text-success"></i>Latest Entries</h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.loadRecentEntries(new URLSearchParams(adminDashboard.filters))" title="Refresh entries">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="card-body p-0" style="height: 400px; overflow-y: auto;">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr class="border-bottom-2">
                                            <th style="width: 35%; padding: 12px 15px;"><i class="fas fa-user me-1 text-primary"></i>Name</th>
                                            <th style="width: 25%; padding: 12px 15px;"><i class="fas fa-id-card me-1 text-info"></i>ID</th>
                                            <th style="width: 20%; padding: 12px 15px;"><i class="fas fa-tag me-1 text-secondary"></i>Role</th>
                                            <th style="width: 20%; padding: 12px 15px;"><i class="fas fa-clock me-1 text-warning"></i>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="entriesTableBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Loading entries...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Exits Table -->
                    <div class="col-lg-6 mb-3">
                        <div class="card enhanced-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                <h5 class="mb-0"><i class="fas fa-sign-out-alt me-2 text-danger"></i>Latest Exits</h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.loadRecentExits(new URLSearchParams(adminDashboard.filters))" title="Refresh exits">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="card-body p-0" style="height: 400px; overflow-y: auto;">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr class="border-bottom-2">
                                            <th style="width: 35%; padding: 12px 15px;"><i class="fas fa-user me-1 text-primary"></i>Name</th>
                                            <th style="width: 25%; padding: 12px 15px;"><i class="fas fa-id-card me-1 text-info"></i>ID</th>
                                            <th style="width: 20%; padding: 12px 15px;"><i class="fas fa-tag me-1 text-secondary"></i>Role</th>
                                            <th style="width: 20%; padding: 12px 15px;"><i class="fas fa-clock me-1 text-warning"></i>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="exitsTableBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Loading exits...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                        <button class="btn btn-info py-3" onclick="printPDF()" style="border-radius: 12px;">
                                            <i class="fas fa-print me-2"></i>Print Report
                                            <span class="loading-spinner d-none ms-2" id="printLoading"></span>
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
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <select id="presetSelect" class="form-select" aria-label="Load preset">
                                                        <option value="">Load Preset...</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <button class="btn btn-outline-secondary w-100" onclick="savePreset()" title="Save current date range as preset">Save Preset</button>
                                                </div>
                                            </div>
                                            <div style="height:8px"></div>
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
                            <!-- User Statistics -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stat-card" style="border-left-color: #007bff;">
                                        <h3><?php echo $totalStudents; ?></h3>
                                        <p><i class="fas fa-user-graduate me-1"></i>Active Students</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stat-card" style="border-left-color: #28a745;">
                                        <h3><?php echo $totalFaculty; ?></h3>
                                        <p><i class="fas fa-chalkboard-teacher me-1"></i>Active Faculty</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stat-card" style="border-left-color: #ffc107;">
                                        <h3><?php echo $totalSecurity; ?></h3>
                                        <p><i class="fas fa-shield-alt me-1"></i>Active Security</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stat-card" style="border-left-color: #17a2b8;">
                                        <h3><?php echo $totalStaff; ?></h3>
                                        <p><i class="fas fa-user-tie me-1"></i>Active Staff</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Management Cards -->
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
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="manageUsers('staff')" style="cursor: pointer;">
                                        <div style="font-size: 42px; color: #17a2b8; margin-bottom: 15px;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <h6>Manage Staff</h6>
                                        <p class="text-muted small">Add, edit, or manage staff accounts and information</p>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="management-card enhanced-card text-center p-4" onclick="window.location.href='archive.php'" style="cursor: pointer;">
                                        <div style="font-size: 42px; color: #6c757d; margin-bottom: 15px;">
                                            <i class="fas fa-archive"></i>
                                        </div>
                                        <h6>View Archives</h6>
                                        <p class="text-muted small">View, restore, or permanently delete archived users</p>
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
        // Get asset and base paths from HTML data attributes
        const assetsPath = document.documentElement.getAttribute('data-assets-path') || '../../';
        const basePath = document.documentElement.getAttribute('data-base-path') || '/dashboards/admin/';
        
        // Helper function to get correct asset paths
        function getAssetPath(relativePath) {
            return assetsPath + relativePath;
        }
        
        // Helper function to get API endpoint paths
        function getAPIPath(endpoint) {
            return assetsPath + endpoint;
        }
        
        // Initialize charts with PHP data
        const peakHoursData = <?php echo json_encode($peakHours); ?>;
        let sidebarCollapsed = false;
        let adminDashboard = null;
        
        // Refresh Analytics Function
        function refreshAnalytics() {
            if (adminDashboard && typeof adminDashboard.loadAnalytics === 'function') {
                adminDashboard.loadAnalytics();
            }
        }
        
        // Show Section using query parameter instead of hash (works better on Infinity Free)
        function showSection(sectionId) {
            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            const contentSections = document.querySelectorAll('.content-section');
            
            // Remove active class from all nav links
            navLinks.forEach(link => link.classList.remove('active'));
            
            // Find and activate the corresponding nav link
            const correspondingNavLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
            if (correspondingNavLink) {
                correspondingNavLink.classList.add('active');
            }
            
            // Hide all content sections
            contentSections.forEach(section => {
                section.style.display = 'none';
                section.classList.remove('fade-in-up');
            });
            
            // Show selected section with animation
            const targetSection = document.getElementById(sectionId + '-section');
            if (targetSection) {
                targetSection.style.display = 'block';
                setTimeout(() => {
                    targetSection.classList.add('fade-in-up');
                }, 50);
                
                // Initialize and load analytics if switching to analytics section
                if (sectionId === 'analytics' && adminDashboard) {
                    // Stop dashboard auto-refresh
                    if (adminDashboard.dashboardRefreshInterval) {
                        adminDashboard.stopDashboardAutoRefresh();
                    }
                    
                    // Initialize charts and load data immediately
                    setTimeout(() => {
                        adminDashboard.init();
                    }, 100);
                }
                
                // Start dashboard auto-refresh if switching to dashboard
                if (sectionId === 'dashboard' && adminDashboard) {
                    // Stop analytics auto-refresh
                    if (adminDashboard.autoRefreshInterval) {
                        adminDashboard.stopAutoRefresh();
                    }
                    
                    // Start dashboard auto-refresh
                    adminDashboard.startDashboardAutoRefresh();
                }
            } else {
                // Stop auto-refresh when leaving sections
                if (adminDashboard) {
                    if (sectionId !== 'analytics' && adminDashboard.autoRefreshInterval) {
                        adminDashboard.stopAutoRefresh();
                    }
                    if (sectionId !== 'dashboard' && adminDashboard.dashboardRefreshInterval) {
                        adminDashboard.stopDashboardAutoRefresh();
                    }
                }
            }
            
            // Update URL using query parameter (more reliable on Infinity Free than hash)
            const url = new URL(window.location);
            url.searchParams.set('section', sectionId);
            window.history.replaceState({}, '', url);
            
            // Save to localStorage for persistence across refreshes
            localStorage.setItem('lastVisitedSection', sectionId);
            
            // Close mobile sidebar if open
            if (window.innerWidth <= 768) {
                toggleMobileSidebar(false);
            }
        }
        
        // Enhanced Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-link[data-section], .quick-action-btn[data-section]');
            const contentSections = document.querySelectorAll('.content-section');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    showSection(this.dataset.section);
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
            
            // Initialize AdminDashboard from admin.js - don't call init yet
            if (typeof AdminDashboard === 'function') {
                // Create instance but don't initialize charts yet
                // Charts will be initialized when user navigates to analytics section
                adminDashboard = new AdminDashboard();
            }
            
            
            // Check URL query parameter first (most reliable on Infinity Free)
            const urlParams = new URLSearchParams(window.location.search);
            let section = urlParams.get('section');
            
            // If no query parameter, check localStorage
            if (!section) {
                section = localStorage.getItem('lastVisitedSection');
            }
            
            // Default to dashboard
            section = section || 'dashboard';
            
            console.log('Page load: showing section:', section);
            showSection(section);
            
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
        
        // MOBILE SIDEBAR FUNCTIONS - WITH CLEAN VARIABLE NAMES
        function toggleMobileSidebarMenu() {
            const mobileNavbar = document.getElementById('sidebar');
            const mobileMenuOverlay = document.getElementById('mobileOverlay');
            
            const isMenuOpen = mobileNavbar.classList.contains('mobile-show');
            
            if (isMenuOpen) {
                hideMobileSidebarMenu();
            } else {
                showMobileSidebarMenu();
            }
        }
        
        function showMobileSidebarMenu() {
            const mobileNavbar = document.getElementById('sidebar');
            const mobileMenuOverlay = document.getElementById('mobileOverlay');
            
            mobileNavbar.classList.add('mobile-show');
            mobileMenuOverlay.classList.add('mobile-show');
            document.body.style.overflow = 'hidden';
        }
        
        function hideMobileSidebarMenu() {
            const mobileNavbar = document.getElementById('sidebar');
            const mobileMenuOverlay = document.getElementById('mobileOverlay');
            
            mobileNavbar.classList.remove('mobile-show');
            mobileMenuOverlay.classList.remove('mobile-show');
            document.body.style.overflow = '';
        }
        
        // Add touch event handling for better mobile experience
        document.addEventListener('DOMContentLoaded', function() {
            const mobileNavbar = document.getElementById('sidebar');
            const mobileMenuOverlay = document.getElementById('mobileOverlay');
            const mobileCloseBtn = document.querySelector('.mobile-close-btn');
            let touchStartX = 0;
            let touchEndX = 0;
            
            // Handle swipe to close
            mobileNavbar.addEventListener('touchstart', e => {
                touchStartX = e.touches[0].clientX;
            });
            
            mobileNavbar.addEventListener('touchmove', e => {
                touchEndX = e.touches[0].clientX;
                const swipeDistance = touchStartX - touchEndX;
                
                if (swipeDistance > 50) { // Threshold for swipe
                    hideMobileSidebarMenu();
                }
            });
            
            // Ensure close button works
            if (mobileCloseBtn) {
                mobileCloseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    hideMobileSidebarMenu();
                });
            }
            
            // Ensure overlay click closes menu
            if (mobileMenuOverlay) {
                mobileMenuOverlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    hideMobileSidebarMenu();
                });
            }
            
            // Prevent clicks inside sidebar from closing it
            mobileNavbar.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Close menu when clicking nav links
            const navLinks = mobileNavbar.querySelectorAll('.nav-link[data-section]');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    hideMobileSidebarMenu();
                });
            });
        });
        
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
                // Mobile: ensure sidebar is not in collapsed state but don't auto-open
                const sidebar = document.getElementById('sidebar');
                const body = document.body;
                
                if (sidebarCollapsed) {
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    sidebarCollapsed = false;
                }
                
                // Don't automatically add mobile-active class here
                sidebar.classList.remove('mobile-active');
                document.getElementById('mobileOverlay').classList.remove('active');
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
                    <p class="mb-0">Generating barcode...</p>
                </div>
            `;
            resultDiv.style.display = 'block';
            resultDiv.classList.add('fade-in-up');
            
            // Simulate API call
            setTimeout(() => {
                fetch(getAPIPath('barcode_generator_api.php?action=generate_by_id&id=' + encodeURIComponent(id)))
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
                                                        <small class="text-muted">Code 39 Barcode Generated</small>
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
                                                    <img src="${data.barcode_url}" alt="Code 39 Barcode" class="img-fluid" style="max-width: 150px; border: 3px solid white; border-radius: 10px;">
                                                </div>
                                                <p class="mt-2 small text-muted">
                                                    <i class="fas fa-mobile-alt me-1"></i>Scan with CTU Scanner
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            showNotification('Code 39 Barcode generated successfully!', 'success');
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
                                                <i class="fas fa-vial me-1"></i>Generate Test Barcode
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
                                            <p class="mb-0 small">Error generating barcode: ${error.message}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        showNotification('Error generating barcode', 'error');
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
                                        <h6 class="mb-0">Test Code 39 Barcode</h6>
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
                                    <img src="${barcodeUrl}" alt="Code 39 Barcode" class="img-fluid" style="max-width: 150px; border: 3px solid white; border-radius: 10px;">
                                </div>
                                <p class="mt-2 small text-muted">
                                    <i class="fas fa-flask me-1"></i>Test Code 39 Barcode
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            showNotification('Test Code 39 Barcode generated!', 'warning');
        }
        
        function generateAll(type) {
            const resultsArea = document.getElementById('qrResultsArea');
            resultsArea.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                    <h6>Loading all ${type} barcodes...</h6>
                    <p class="text-muted small">This may take a moment</p>
                </div>
            `;
            
            setTimeout(() => {
                fetch(getAPIPath('barcode_generator_api.php?action=' + type))
                    .then(response => response.text())
                    .then(html => {
                        resultsArea.innerHTML = html;
                        resultsArea.classList.add('fade-in-up');
                        showNotification(`${type} barcodes loaded successfully!`, 'success');
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
            
            showNotification('Downloading barcode...', 'info');
            
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
                    showNotification('Barcode downloaded successfully!', 'success');
                })
                .catch(error => {
                    console.error('Download failed:', error);
                    showNotification('Download failed. Right-click the barcode to save manually.', 'error');
                });
        }
        
        function shareQR(id) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(id)}`;
            
            if (navigator.share) {
                navigator.share({
                    title: `Code 39 Barcode for ${id}`,
                    text: `Code 39 Barcode for ID: ${id}`,
                    url: qrUrl
                });
            } else {
                navigator.clipboard.writeText(qrUrl).then(() => {
                    showNotification('Barcode URL copied to clipboard!', 'success');
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
                window.location.href = `manage_users.php?type=${type}`;
            }, 300);
        }
        
        // Enhanced export functions with loading states
        function exportExcel() {
            const loading = document.getElementById('excelLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showNotification('Please select both start and end dates.', 'warning');
                return;
            }
            
            loading.classList.remove('d-none');
            showNotification('Preparing Excel export...', 'info');
            
            setTimeout(() => {
                window.location.href = `export_excel.php?start_date=${startDate}&end_date=${endDate}`;
                loading.classList.add('d-none');
                showNotification('Excel file downloading...', 'success');
            }, 500);
        }
        
        function exportPDF() {
            const loading = document.getElementById('pdfLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showNotification('Please select both start and end dates.', 'warning');
                return;
            }
            
            loading.classList.remove('d-none');
            showNotification('Preparing PDF export...', 'info');
            
            setTimeout(() => {
                window.location.href = `export_pdf.php?start_date=${startDate}&end_date=${endDate}`;
                loading.classList.add('d-none');
                showNotification('PDF file downloading...', 'success');
            }, 500);
        }
        
        function printPDF() {
            const loading = document.getElementById('pdfLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showNotification('Please select both start and end dates.', 'warning');
                return;
            }
            
            loading.classList.remove('d-none');
            showNotification('Opening PDF for printing...', 'info');
            
            setTimeout(() => {
                window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}&autoprint=1`, '_blank');
                loading.classList.add('d-none');
                showNotification('PDF opened for printing', 'success');
            }, 500);
        }
        
        let peakChart = null;
        let deptChart = null;

        function generateReport() {
            const loading = document.getElementById('reportLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showNotification('Please select both start and end dates.', 'warning');
                return;
            }

            // update export buttons are already wired to read date inputs
            loading.classList.remove('d-none');
            showNotification('Generating custom report...', 'info');

            fetch(`?ajax=2&start_date=${startDate}&end_date=${endDate}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        showNotification('Error generating report: ' + data.error, 'error');
                        return;
                    }

                    // Update stat cards
                    const map = {
                        'entries': data.total_entries ?? 0,
                        'student': data.student_entries ?? 0,
                        'faculty': data.faculty_entries ?? 0,
                        'exits': data.total_exits ?? 0
                    };
                    Object.keys(map).forEach(k => {
                        const el = document.querySelector(`[data-stat="${k}"]`);
                        if (el) el.textContent = map[k];
                    });

                    // Update Peak Hours Chart
                    const peakLabels = [];
                    const peakData = [];
                    if (Array.isArray(data.peak_hours)) {
                        data.peak_hours.forEach(h => {
                            peakLabels.push(String(h.hour).padStart(2,'0') + ':00');
                            peakData.push(parseInt(h.count));
                        });
                    }
                    if (peakChart) {
                        peakChart.data.labels = peakLabels;
                        peakChart.data.datasets = [{ label: 'Entries', data: peakData, borderColor: '#972529', backgroundColor: 'rgba(151,37,41,0.1)'}];
                        peakChart.update();
                    } else {
                        const peakCtx = document.getElementById('peakHoursChart');
                        if (peakCtx) {
                            peakChart = new Chart(peakCtx, {
                                type: 'bar',
                                data: { labels: peakLabels, datasets: [{ label: 'Entries', data: peakData, backgroundColor: '#972529' }] },
                                options: { responsive: true }
                            });
                        }
                    }

                    // Update Department Chart (aggregate duplicates)
                    const deptMap = {};
                    if (Array.isArray(data.department_stats)) {
                        data.department_stats.forEach(d => {
                            const name = d.Department || 'N/A';
                            deptMap[name] = (deptMap[name] || 0) + parseInt($d = d.entry_count || 0);
                        });
                    }
                    const deptLabels = Object.keys(deptMap);
                    const deptCounts = deptLabels.map(l => deptMap[l]);
                    if (deptChart) {
                        deptChart.data.labels = deptLabels;
                        deptChart.data.datasets = [{ data: deptCounts, backgroundColor: ['#972529','#E5C573','#a83531','#eed490','#6c757d'] }];
                        deptChart.update();
                    } else {
                        const deptCtx = document.getElementById('departmentChart');
                        if (deptCtx) {
                            deptChart = new Chart(deptCtx, {
                                type: 'doughnut',
                                data: { labels: deptLabels, datasets: [{ data: deptCounts, backgroundColor: ['#972529','#E5C573','#a83531','#eed490','#6c757d'] }] },
                                options: { responsive: true }
                            });
                        }
                    }

                    // Save last used range as recent preset option
                    addPresetOption(`${startDate} - ${endDate}`, startDate + '|' + endDate);

                    showNotification(`Custom report generated for ${startDate} to ${endDate}`, 'success');
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Failed to generate report', 'error');
                })
                .finally(() => {
                    loading.classList.add('d-none');
                });
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
            // Create empty charts if canvas exists; real data will be populated by generateReport
            const peakCtx = document.getElementById('peakHoursChart');
            if (peakCtx) {
                peakChart = new Chart(peakCtx, {
                    type: 'bar',
                    data: { labels: [], datasets: [{ label: 'Entries', data: [], backgroundColor: '#972529' }] },
                    options: { responsive: true }
                });
            }

            const deptCtx = document.getElementById('departmentChart');
            if (deptCtx) {
                deptChart = new Chart(deptCtx, {
                    type: 'doughnut',
                    data: { labels: [], datasets: [{ data: [], backgroundColor: ['#972529','#E5C573','#a83531','#eed490','#6c757d'] }] },
                    options: { responsive: true }
                });
            }
        }

        // Preset management
        function getPresets() {
            try {
                return JSON.parse(localStorage.getItem('report_presets') || '[]');
            } catch (e) { return []; }
        }

        function savePreset() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            if (!start || !end) { showNotification('Select both dates first', 'warning'); return; }
            const name = prompt('Preset name:', `${start} to ${end}`) || `${start} to ${end}`;
            const presets = getPresets();
            presets.push({ name: name, value: start + '|' + end });
            localStorage.setItem('report_presets', JSON.stringify(presets));
            populatePresets();
            showNotification('Preset saved', 'success');
        }

        function populatePresets() {
            const select = document.getElementById('presetSelect');
            if (!select) return;
            const presets = getPresets();
            // clear existing (keep first placeholder)
            select.innerHTML = '<option value="">Load Preset...</option>';
            presets.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.value;
                opt.textContent = p.name;
                select.appendChild(opt);
            });
        }

        function addPresetOption(name, value) {
            const presets = getPresets();
            // avoid duplicates
            if (!presets.find(p => p.value === value)) {
                presets.push({ name: name, value: value });
                localStorage.setItem('report_presets', JSON.stringify(presets));
                populatePresets();
            }
        }

        // hook preset select change
        document.addEventListener('DOMContentLoaded', function() {
            populatePresets();
            const sel = document.getElementById('presetSelect');
            if (sel) {
                sel.addEventListener('change', function() {
                    if (!this.value) return;
                    const parts = this.value.split('|');
                    if (parts.length === 2) {
                        document.getElementById('startDate').value = parts[0];
                        document.getElementById('endDate').value = parts[1];
                    }
                });
            }
        });
        
               
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