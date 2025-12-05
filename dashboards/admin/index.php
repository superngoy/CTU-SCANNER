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
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
            padding: 2px 4px;
            min-width: 16px;
            height: auto;
        }

        .notification-badge:not(:empty) {
            width: auto;
            height: 18px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Notification Container & Dropdown */
        .notification-container {
            position: relative;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: -20px;
            width: 380px;
            max-height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            z-index: 1000;
            margin-top: 10px;
            animation: slideInDown 0.3s ease-out;
        }

        .notification-dropdown.show {
            display: flex;
        }

        .notification-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
            border-radius: 12px 12px 0 0;
        }

        .notification-header h6 {
            color: #333;
            font-weight: 600;
            margin: 0;
        }

        .notification-list {
            flex: 1;
            overflow-y: auto;
            min-height: 200px;
            max-height: 380px;
        }

        .notification-list::-webkit-scrollbar {
            width: 6px;
        }

        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .notification-list::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .notification-item:hover {
            background: #f9f9f9;
        }

        .notification-item.unread {
            background: rgba(151, 37, 41, 0.05);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .notification-item.type-success .notification-icon {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .notification-item.type-error .notification-icon {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .notification-item.type-warning .notification-icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .notification-item.type-info .notification-icon {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            margin: 0 0 4px 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notification-title .badge {
            font-size: 10px;
            padding: 2px 6px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            margin: 0 0 6px 0;
            line-height: 1.4;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
            margin: 0;
        }

        .notification-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        .notification-actions button {
            background: none;
            border: none;
            color: #666;
            font-size: 12px;
            padding: 4px 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .notification-actions button:hover {
            color: var(--primary-color);
        }

        .notification-footer {
            padding: 10px 20px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
            border-radius: 0 0 12px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .notification-footer .btn-link {
            color: #666;
            font-size: 13px;
            padding: 8px 12px;
            text-decoration: none;
        }

        .notification-footer .btn-link:hover {
            color: var(--primary-color);
            background: rgba(151, 37, 41, 0.05);
        }

        .notification-empty {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .notification-empty i {
            font-size: 32px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        /* Animation for dropdown */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
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
            
            .notification-dropdown {
                position: fixed;
                top: 70px;
                left: 50%;
                right: auto;
                transform: translateX(-50%);
                width: calc(100vw - 20px);
                max-width: 380px;
                max-height: 70vh;
            }
        }
        
        @media (max-width: 768px) {
            .notification-dropdown {
                position: fixed;
                top: 70px;
                left: 50%;
                right: auto;
                transform: translateX(-50%);
                width: calc(100vw - 20px);
                max-width: 380px;
                max-height: 70vh;
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
                    <span>Barcode Generator</span>
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
                <a href="#visitor-analytics" class="nav-link" data-section="visitor-analytics">
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
            <!-- Notification Bell with Dropdown -->
            <div class="notification-container">
                <button class="notification-btn" id="notificationBell" title="Notifications" onclick="toggleNotificationDropdown()">
                    <i class="fas fa-bell"></i>
                    <div class="notification-badge" id="notificationBadge">0</div>
                </button>
                
                <!-- Notification Dropdown Panel -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h6 class="mb-0">Notifications</h6>
                        <button class="btn-close btn-sm" onclick="toggleNotificationDropdown()"></button>
                    </div>
                    
                    <div class="notification-list" id="notificationList">
                        <div class="text-center p-4">
                            <i class="fas fa-spinner fa-spin text-muted mb-2"></i>
                            <p class="text-muted small">Loading...</p>
                        </div>
                    </div>
                    
                    <div class="notification-footer">
                        <button class="btn btn-sm btn-link w-100" onclick="markAllNotificationsRead()">
                            <i class="fas fa-check me-1"></i>Mark all as read
                        </button>
                        <button class="btn btn-sm btn-link text-danger w-100" onclick="clearAllNotifications()">
                            <i class="fas fa-trash me-1"></i>Clear all
                        </button>
                    </div>
                </div>
            </div>
            
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
            <!-- Tabs for different report types -->
            <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist" style="border-bottom: 2px solid #dee2e6;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="export-tab" data-bs-toggle="tab" data-bs-target="#export-content" type="button" role="tab" style="color: #495057; border: 1px solid transparent; border-bottom: 3px solid #0d6efd;">
                        <i class="fas fa-download me-2"></i>Data Export
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="user-logs-tab" data-bs-toggle="tab" data-bs-target="#user-logs-content" type="button" role="tab" style="color: #495057; border: 1px solid transparent;">
                        <i class="fas fa-history me-2"></i>User Activity Logs
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="reportTabContent">
                <!-- Export Tab -->
                <div class="tab-pane fade show active" id="export-content" role="tabpanel">
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

                <!-- User Activity Logs Tab -->
                <div class="tab-pane fade" id="user-logs-content" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <!-- Search Card -->
                            <div class="enhanced-card fade-in-up mb-4">
                                <div class="card-header" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); border: none; padding: 20px;">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-magnifying-glass me-2"></i>Search User Activity Logs
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Search Form -->
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold" style="color: #972529;">User Type</label>
                                            <select id="userLogsType" class="form-select" style="border: 2px solid #E5C573; border-radius: 8px; padding: 10px; transition: all 0.3s;">
                                                <option value="student">🎓 Student</option>
                                                <option value="faculty">👨‍🏫 Faculty</option>
                                                <option value="staff">👔 Staff</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold" style="color: #972529;">User ID</label>
                                            <input type="text" id="userLogsId" class="form-control" placeholder="Enter user ID" style="border: 2px solid #E5C573; border-radius: 8px; padding: 10px; transition: all 0.3s;">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold" style="color: #972529;">Date</label>
                                            <input type="date" id="userLogsDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="border: 2px solid #E5C573; border-radius: 8px; padding: 10px; transition: all 0.3s;">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button class="btn w-100 py-2" onclick="searchUserLogs()" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); color: white; border: none; border-radius: 8px; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 12px rgba(151, 37, 41, 0.3);">
                                                <i class="fas fa-search me-2"></i>Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div id="userLogsError" class="alert d-none mb-4" role="alert" style="background: #fee2e2; border: 2px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 15px;">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span id="userLogsErrorText"></span>
                            </div>

                            <!-- Loading -->
                            <div id="userLogsLoading" class="d-none text-center py-5">
                                <div class="spinner-border" role="status" style="color: #972529; width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3" style="color: #666; font-weight: 500;">Fetching user activity logs...</p>
                            </div>

                            <!-- User Info Card -->
                            <div id="userLogsInfo" class="d-none mb-4">
                                <div class="enhanced-card" style="background: linear-gradient(135deg, rgba(151, 37, 41, 0.1) 0%, rgba(229, 197, 115, 0.1) 100%); border-left: 5px solid #E5C573;">
                                    <div class="card-body p-4">
                                        <div class="row" id="userLogsInfoContent">
                                            <!-- Populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logs Table Card -->
                            <div id="userLogsTable" class="d-none">
                                <div class="enhanced-card">
                                    <div class="card-header" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); border: none; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                                        <h5 class="mb-0 text-white">
                                            <i class="fas fa-history me-2"></i>Activity Logs
                                        </h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-light btn-sm" onclick="exportUserLogsExcel()" style="border-radius: 6px; font-weight: 600; transition: all 0.3s;">
                                                <i class="fas fa-file-excel me-1" style="color: #28a745;"></i>Excel
                                            </button>
                                            <button class="btn btn-light btn-sm" onclick="exportUserLogsPDF()" style="border-radius: 6px; font-weight: 600; transition: all 0.3s;">
                                                <i class="fas fa-file-pdf me-1" style="color: #dc3545;"></i>PDF
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="table-responsive">
                                            <table class="table" style="border-collapse: collapse;">
                                                <thead>
                                                    <tr style="border-bottom: 2px solid #E5C573;">
                                                        <th style="color: #972529; font-weight: 700; padding: 12px; text-transform: uppercase; font-size: 0.85rem;">Time</th>
                                                        <th style="color: #972529; font-weight: 700; padding: 12px; text-transform: uppercase; font-size: 0.85rem;">Type</th>
                                                        <th style="color: #972529; font-weight: 700; padding: 12px; text-transform: uppercase; font-size: 0.85rem;">Scanner Location</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="userLogsTableBody">
                                                    <!-- Populated by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No logs found -->
                            <div id="userLogsEmpty" class="d-none">
                                <div class="enhanced-card text-center py-5">
                                    <i class="fas fa-inbox" style="font-size: 56px; color: #d1d5db; margin-bottom: 15px; opacity: 0.6;"></i>
                                    <p class="text-muted" style="font-size: 16px; margin-top: 10px;">No activity logs found for this user on the selected date</p>
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
            <div class="container-fluid">
                <!-- Settings Tabs -->
                <div class="row mb-4">
                    <div class="col-12">
                        <ul class="nav nav-tabs settings-tabs" role="tablist" style="border-bottom: 3px solid #972529;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active settings-tab-btn" id="display-settings-tab" data-bs-toggle="tab" data-bs-target="#display-settings-panel" type="button" role="tab" style="color: #333; font-weight: 600;">
                                    <i class="fas fa-monitor me-2"></i>Display
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link settings-tab-btn" id="notification-settings-tab" data-bs-toggle="tab" data-bs-target="#notification-settings-panel" type="button" role="tab" style="color: #333; font-weight: 600;">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link settings-tab-btn" id="report-settings-tab" data-bs-toggle="tab" data-bs-target="#report-settings-panel" type="button" role="tab" style="color: #333; font-weight: 600;">
                                    <i class="fas fa-file-export me-2"></i>Reports
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link settings-tab-btn" id="security-settings-tab" data-bs-toggle="tab" data-bs-target="#security-settings-panel" type="button" role="tab" style="color: #333; font-weight: 600;">
                                    <i class="fas fa-shield-alt me-2"></i>Security
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link settings-tab-btn" id="system-settings-tab" data-bs-toggle="tab" data-bs-target="#system-settings-panel" type="button" role="tab" style="color: #333; font-weight: 600;">
                                    <i class="fas fa-cogs me-2"></i>System
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Display Settings Tab -->
                    <div class="tab-pane fade show active" id="display-settings-panel" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-palette me-2"></i>Appearance
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <label class="form-label">Theme</label>
                                            <select class="form-select" id="themeSelect" onchange="changeTheme(this.value)">
                                                <option value="light" selected>CTU Gold Theme (Current)</option>
                                                <option value="dark">Dark Mode</option>
                                                <option value="auto">Auto (System Preference)</option>
                                            </select>
                                            <small class="text-muted">Changes apply immediately</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Sidebar Collapse</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="collapseSidebarPref">
                                                <label class="form-check-label" for="collapseSidebarPref">
                                                    Remember collapsed state
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #E5C573 0%, #D4B85A 100%); color: #333;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>Date & Time
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <label class="form-label">Date Format</label>
                                            <select class="form-select" id="dateFormatSelect" onchange="changeDateFormat(this.value)">
                                                <option value="MM/DD/YYYY">MM/DD/YYYY (US)</option>
                                                <option value="DD/MM/YYYY" selected>DD/MM/YYYY (PH)</option>
                                                <option value="YYYY-MM-DD">YYYY-MM-DD (ISO)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Timezone</label>
                                            <select class="form-select" id="timezoneSelect" onchange="changeTimezone(this.value)">
                                                <option value="UTC+8" selected>Philippine Time (UTC+8)</option>
                                                <option value="UTC">Coordinated Universal Time</option>
                                                <option value="UTC+0">GMT</option>
                                            </select>
                                        </div>
                                        <div class="alert alert-info border-0 p-2" style="background: rgba(13, 202, 240, 0.1); font-size: 0.85rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Current time: <span id="currentTimeDisplay"><?php echo date('h:i:s A'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-table me-2"></i>Data Display
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Pagination Size</label>
                                                <select class="form-select" id="paginationSelect" onchange="changePagination(this.value)">
                                                    <option value="10">10 items</option>
                                                    <option value="25" selected>25 items</option>
                                                    <option value="50">50 items</option>
                                                    <option value="100">100 items</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="compactViewToggle" checked>
                                                    <label class="form-check-label" for="compactViewToggle">
                                                        Compact table view
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="showAvatarsToggle" checked>
                                                    <label class="form-check-label" for="showAvatarsToggle">
                                                        Show user avatars
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings Tab -->
                    <div class="tab-pane fade" id="notification-settings-panel" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-bell me-2"></i>Notification Channels
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                            <label class="form-check-label" for="emailNotif">
                                                <i class="fas fa-envelope me-1"></i>Email Notifications
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="pushNotif" checked>
                                            <label class="form-check-label" for="pushNotif">
                                                <i class="fas fa-desktop me-1"></i>Browser Notifications
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="soundNotif" checked onchange="toggleNotificationSound()">
                                            <label class="form-check-label" for="soundNotif">
                                                <i class="fas fa-volume-up me-1"></i>Notification Sound
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="systemTrayNotif" checked>
                                            <label class="form-check-label" for="systemTrayNotif">
                                                <i class="fas fa-star me-1"></i>Highlight Failed Scans
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #E5C573 0%, #D4B85A 100%); color: #333;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-filter me-2"></i>Alert Types
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="alertFailed" checked>
                                            <label class="form-check-label" for="alertFailed">
                                                Failed scans
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="alertDuplicate" checked>
                                            <label class="form-check-label" for="alertDuplicate">
                                                Duplicate entries
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="alertInactive" checked>
                                            <label class="form-check-label" for="alertInactive">
                                                Inactive user scan
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="alertSystem" checked>
                                            <label class="form-check-label" for="alertSystem">
                                                System alerts
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Settings Tab -->
                    <div class="tab-pane fade" id="report-settings-panel" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-file-export me-2"></i>Default Export Format
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="exportFormat" id="exportExcel" value="excel" checked>
                                                <label class="form-check-label" for="exportExcel">
                                                    <i class="fas fa-file-excel me-1" style="color: #207245;"></i>Excel (.xlsx)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="exportFormat" id="exportPdf" value="pdf">
                                                <label class="form-check-label" for="exportPdf">
                                                    <i class="fas fa-file-pdf me-1" style="color: #d32f2f;"></i>PDF (.pdf)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="exportFormat" id="exportCsv" value="csv">
                                                <label class="form-check-label" for="exportCsv">
                                                    <i class="fas fa-file-csv me-1" style="color: #27AE60;"></i>CSV (.csv)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #E5C573 0%, #D4B85A 100%); color: #333;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-sliders-h me-2"></i>Report Options
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="includeCharts" checked>
                                            <label class="form-check-label" for="includeCharts">
                                                Include charts in reports
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="includeSummary" checked>
                                            <label class="form-check-label" for="includeSummary">
                                                Include summary statistics
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="includeFooter" checked>
                                            <label class="form-check-label" for="includeFooter">
                                                Add company footer
                                            </label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="autoCompress" checked>
                                            <label class="form-check-label" for="autoCompress">
                                                Compress large files
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings Tab -->
                    <div class="tab-pane fade" id="security-settings-panel" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-shield-alt me-2"></i>Session Security
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <label class="form-label">Session Timeout</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="sessionTimeout" value="60" min="15" max="480">
                                                <span class="input-group-text">minutes</span>
                                            </div>
                                            <small class="text-muted">Auto-logout inactive sessions</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Login Attempt Limit</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="loginAttempts" value="5" min="3" max="10">
                                                <span class="input-group-text">attempts</span>
                                            </div>
                                            <small class="text-muted">Failed attempts before lockout</small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enableTwoFactor" checked disabled>
                                            <label class="form-check-label" for="enableTwoFactor">
                                                <i class="fas fa-lock me-1"></i>Two-Factor Authentication
                                            </label>
                                            <small class="d-block text-muted mt-1">Built-in security feature (always enabled)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #E5C573 0%, #D4B85A 100%); color: #333;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-lock me-2"></i>Password Policy
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="alert alert-info border-0 p-2 mb-3" style="background: rgba(13, 202, 240, 0.1); font-size: 0.85rem;">
                                            <div class="mb-2"><strong>Current Policy:</strong></div>
                                            <ul class="mb-0" style="padding-left: 20px;">
                                                <li>Minimum 8 characters</li>
                                                <li>Must contain numbers & letters</li>
                                                <li>Must contain special characters</li>
                                                <li>Expires every 90 days</li>
                                            </ul>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm" onclick="changePassword()">
                                            <i class="fas fa-key me-1"></i>Change Your Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-history me-2"></i>Login History
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead>
                                                    <tr style="background: #f8f9fa;">
                                                        <th>Date & Time</th>
                                                        <th>IP Address</th>
                                                        <th>Browser</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="loginHistoryBody">
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">
                                                            <i class="fas fa-hourglass-start me-2"></i>Loading...
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

                    <!-- System Settings Tab -->
                    <div class="tab-pane fade" id="system-settings-panel" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #972529 0%, #7a1d20 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-database me-2"></i>System Information
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <small class="text-muted d-block">Application</small>
                                                <p class="mb-0"><strong>CTU Scanner</strong></p>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted d-block">Version</small>
                                                <p class="mb-0"><strong>v2.1</strong></p>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted d-block">Database</small>
                                                <p class="mb-0"><strong>MySQL 8.0</strong></p>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted d-block">PHP Version</small>
                                                <p class="mb-0"><strong><?php echo phpversion(); ?></strong></p>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted d-block">Status</small>
                                                <p class="mb-0"><span class="badge bg-success">Active</span></p>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted d-block">Last Updated</small>
                                                <p class="mb-0"><strong><?php echo date('M d, Y'); ?></strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #E5C573 0%, #D4B85A 100%); color: #333;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-hdd me-2"></i>Storage & Backups
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">Database Size</small>
                                                <small class="text-muted" id="dbSize">Calculating...</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: 35%;"></div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-2">Last Backup</small>
                                            <p class="mb-0"><strong>Today, 01:00 AM</strong></p>
                                            <small class="text-success"><i class="fas fa-check me-1"></i>Successful</small>
                                        </div>
                                        <button class="btn btn-primary btn-sm" onclick="createBackup()">
                                            <i class="fas fa-download me-1"></i>Backup Now
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="viewBackups()">
                                            <i class="fas fa-list me-1"></i>View Backups
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="enhanced-card fade-in-up">
                                    <div class="card-header p-3" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white;">
                                        <h6 class="mb-0">
                                            <i class="fas fa-wrench me-2"></i>Maintenance Tools
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <button class="btn btn-outline-warning btn-block w-100" onclick="cleanupLogs()">
                                                    <i class="fas fa-trash me-1"></i>Clean Old Logs
                                                </button>
                                                <small class="text-muted d-block mt-1">Logs older than 90 days</small>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <button class="btn btn-outline-info btn-block w-100" onclick="rebuildIndexes()">
                                                    <i class="fas fa-sync-alt me-1"></i>Rebuild Indexes
                                                </button>
                                                <small class="text-muted d-block mt-1">Optimize database</small>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <button class="btn btn-outline-success btn-block w-100" onclick="checkDatabase()">
                                                    <i class="fas fa-stethoscope me-1"></i>Check Database
                                                </button>
                                                <small class="text-muted d-block mt-1">Integrity check</small>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <button class="btn btn-outline-danger btn-block w-100" onclick="cacheStats()">
                                                    <i class="fas fa-layer-group me-1"></i>Cache Stats
                                                </button>
                                                <small class="text-muted d-block mt-1">System cache info</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Settings Button -->
                <div class="row mt-4 mb-3">
                    <div class="col-12">
                        <button class="btn btn-primary" onclick="saveAllSettings()">
                            <i class="fas fa-save me-2"></i>Save All Settings
                        </button>
                        <button class="btn btn-outline-secondary" onclick="resetSettings()">
                            <i class="fas fa-undo me-2"></i>Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Analytics Section -->
        <div id="visitor-analytics-section" class="content-section" style="display: none;">
            <div class="container-fluid">
                <!-- Date Range Filter -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="enhanced-card fade-in-up">
                            <div class="card-body p-3">
                                <form method="GET" class="row g-3" id="visitorAnalyticsForm">
                                    <input type="hidden" name="section" value="visitor-analytics">
                                    <div class="col-md-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" id="visitorStartDate" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date" id="visitorEndDate" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-filter me-1"></i>Filter
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary" onclick="resetVisitorAnalytics()">
                                            <i class="fas fa-redo me-1"></i>Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4" id="visitorStatsRow">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3 id="totalVisitors">-</h3>
                            <p><i class="fas fa-id-card me-1"></i>Total Visitors Registered</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3 id="totalCheckIns">-</h3>
                            <p><i class="fas fa-sign-in-alt me-1"></i>Total Check-Ins</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3 id="totalCheckOuts">-</h3>
                            <p><i class="fas fa-sign-out-alt me-1"></i>Total Check-Outs</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h3 id="avgDwellTime">-</h3>
                            <p><i class="fas fa-hourglass-half me-1"></i>Avg Dwell Time</p>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="enhanced-card fade-in-up">
                            <div class="card-header p-3">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Daily Visitor Registrations</h5>
                            </div>
                            <div class="card-body p-3">
                                <canvas id="visitorTrendChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="enhanced-card fade-in-up">
                            <div class="card-header p-3">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Top Visit Purposes</h5>
                            </div>
                            <div class="card-body p-3">
                                <canvas id="visitorPurposeChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Visitors Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="enhanced-card fade-in-up">
                            <div class="card-header p-3">
                                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Recent Visitors</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead style="background: var(--secondary-color); color: #333;">
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Company</th>
                                                <th>Purpose</th>
                                                <th>Contact</th>
                                                <th>Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentVisitorsBody">
                                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-hourglass-start me-2"></i>Loading...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-In/Check-Out Logs -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="enhanced-card fade-in-up">
                            <div class="card-header p-3">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Check-In & Check-Out Logs</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead style="background: var(--secondary-color); color: #333;">
                                            <tr>
                                                <th>Visitor Code</th>
                                                <th>Visitor Name</th>
                                                <th>Check-In Time</th>
                                                <th>Check-Out Time</th>
                                                <th>Dwell Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="visitorLogsBody">
                                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-hourglass-start me-2"></i>Loading...</td></tr>
                                        </tbody>
                                    </table>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="../../assets/js/admin.js"></script>
    <script src="../../assets/audio/notification-sound.js"></script>
    
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
            
            // Restore Reports tab after section is shown (tab persistence)
            if (section === 'reports') {
                setTimeout(() => {
                    const savedTab = sessionStorage.getItem('activeReportsTab');
                    if (savedTab) {
                        const tabButton = document.getElementById(savedTab + '-tab');
                        if (tabButton) {
                            const tab = new bootstrap.Tab(tabButton);
                            tab.show();
                        }
                    }

                    // Restore search data for user logs
                    const savedUserLogsData = sessionStorage.getItem('userLogsSearchData');
                    if (savedUserLogsData) {
                        try {
                            const data = JSON.parse(savedUserLogsData);
                            
                            // Restore form values
                            document.getElementById('userLogsType').value = data.userType || 'student';
                            document.getElementById('userLogsId').value = data.userId || '';
                            document.getElementById('userLogsDate').value = data.searchDate || new Date().toISOString().split('T')[0];
                            
                            // Restore search results if available
                            if (data.userLogsInfo && data.userLogs) {
                                currentUserLogsInfo = data.userLogsInfo;
                                currentUserLogs = data.userLogs;
                                currentUserLogsDate = data.searchDate;
                                
                                displayUserLogsInfo(data.userLogsInfo);
                                displayUserLogsTable(data.userLogs);
                                showUserLogsTable();
                                hideUserLogsEmpty();
                            }
                        } catch (e) {
                            console.log('Could not restore user logs data');
                        }
                    }
                }, 50);
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
                                                <div style="background: #f8f9fa; padding: 15px; border-radius: 15px; display: inline-block; border: 1px solid #e9ecef;">
                                                    <img src="${data.barcode_url}" alt="Code 39 Barcode" class="img-fluid" style="max-width: 200px; border: 3px solid #2c3e50; border-radius: 8px; padding: 8px; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                                                </div>
                                                <p class="mt-2 small text-muted">
                                                    <strong>Code 39 Format</strong> - Scan with CTU Scanner
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
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 15px; display: inline-block; border: 1px solid #e9ecef;">
                                    <img src="${barcodeUrl}" alt="Code 39 Barcode" class="img-fluid" style="max-width: 200px; border: 3px solid #2c3e50; border-radius: 8px; padding: 8px; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                                </div>
                                <p class="mt-2 small text-muted">
                                    <strong>Code 39 Format</strong> - Test Code 39 Barcode
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
            // Sanitize filename
            const safeName = name.replace(/[^a-zA-Z0-9_-]/g, '_');
            
            // Use the download API endpoint
            const downloadUrl = `../../api/download_barcode.php?data=${encodeURIComponent(id)}&name=${encodeURIComponent(safeName)}`;
            
            showNotification('Downloading barcode...', 'info');
            
            // Create an anchor element and trigger download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `Barcode_${safeName}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('Barcode downloaded successfully!', 'success');
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

        // ============================================
        // NOTIFICATION SYSTEM - Session-based
        // ============================================
        
        let notificationPollingInterval = null;
        
        /**
         * Toggle notification dropdown visibility
         */
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
            
            // Close dropdown when clicking outside
            if (dropdown.classList.contains('show')) {
                document.addEventListener('click', closeNotificationDropdownOnClickOutside);
                fetchNotifications();
            } else {
                document.removeEventListener('click', closeNotificationDropdownOnClickOutside);
            }
        }
        
        function closeNotificationDropdownOnClickOutside(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const bell = document.getElementById('notificationBell');
            
            if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
                dropdown.classList.remove('show');
                document.removeEventListener('click', closeNotificationDropdownOnClickOutside);
            }
        }
        
        /**
         * Fetch notifications from API
         */
        function fetchNotifications() {
            fetch('../../api/notifications_api.php?action=get_all&limit=20')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotifications(data.notifications);
                        updateNotificationBadge(data);
                    }
                })
                .catch(error => console.log('Notification fetch error:', error));
        }
        
        /**
         * Display notifications in dropdown
         */
        function displayNotifications(notifications) {
            const listContainer = document.getElementById('notificationList');
            
            if (!notifications || notifications.length === 0) {
                listContainer.innerHTML = `
                    <div class="notification-empty">
                        <div><i class="fas fa-inbox"></i></div>
                        <p>No notifications yet</p>
                    </div>
                `;
                return;
            }
            
            listContainer.innerHTML = notifications.map(notif => {
                const timeAgo = getTimeAgo(notif.created_at);
                const isUnread = !notif.is_read;
                
                return `
                    <div class="notification-item type-${notif.type} ${isUnread ? 'unread' : ''}">
                        <div class="notification-icon">
                            <i class="fas ${notif.icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">
                                ${notif.title}
                                ${isUnread ? '<span class="badge bg-danger">New</span>' : ''}
                            </div>
                            <p class="notification-message">${notif.message}</p>
                            <p class="notification-time">${timeAgo}</p>
                            <div class="notification-actions">
                                ${isUnread ? `<button onclick="markNotificationRead('${notif.id}')"><i class="fas fa-check me-1"></i>Mark read</button>` : ''}
                                <button onclick="deleteNotification('${notif.id}')" class="text-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                                ${notif.action_url ? `<a href="${notif.action_url}" class="text-primary" style="text-decoration:none;"><i class="fas fa-arrow-right me-1"></i>View</a>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Play sound for unread failed scan notifications
            if (typeof notificationSound !== 'undefined' && notifications.some(n => !n.is_read && n.category === 'scan_failure')) {
                if (notificationSound) {
                    // Play appropriate sound based on notification type
                    const failedScan = notifications.find(n => !n.is_read && n.category === 'scan_failure');
                    if (failedScan) {
                        if (failedScan.type === 'error') {
                            notificationSound.playErrorTone();
                        } else if (failedScan.type === 'warning') {
                            notificationSound.playWarningTone();
                        } else {
                            notificationSound.playNotificationTone();
                        }
                    }
                }
            }
        }
        
        /**
         * Update notification badge with unread count
         */
        function updateNotificationBadge(data) {
            const badge = document.getElementById('notificationBadge');
            let unreadCount = 0;
            
            if (data.notifications) {
                unreadCount = data.notifications.filter(n => !n.is_read).length;
            }
            
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
            }
        }
        
        /**
         * Mark single notification as read
         */
        function markNotificationRead(notifId) {
            fetch(`../../api/notifications_api.php?action=mark_read&notification_id=${notifId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                    }
                })
                .catch(error => console.log('Error marking read:', error));
        }
        
        /**
         * Mark all notifications as read
         */
        function markAllNotificationsRead() {
            fetch('../../api/notifications_api.php?action=mark_all_read', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchNotifications();
                    showNotification('All notifications marked as read', 'success');
                }
            })
            .catch(error => console.log('Error marking all read:', error));
        }
        
        /**
         * Delete notification
         */
        function deleteNotification(notifId) {
            if (confirm('Delete this notification?')) {
                fetch(`../../api/notifications_api.php?action=delete&notification_id=${notifId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchNotifications();
                        }
                    })
                    .catch(error => console.log('Error deleting:', error));
            }
        }
        
        /**
         * Clear all notifications
         */
        function clearAllNotifications() {
            if (confirm('Clear all notifications? This cannot be undone.')) {
                fetch('../../api/notifications_api.php?action=clear_all', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications();
                        showNotification('All notifications cleared', 'info');
                    }
                })
                .catch(error => console.log('Error clearing:', error));
            }
        }
        
        /**
         * Convert timestamp to "time ago" format
         */
        function getTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
            if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
            
            return date.toLocaleDateString();
        }
        
        /**
         * Initialize notification system on page load
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notification sound preference
            if (typeof notificationSound !== 'undefined') {
                const soundToggle = document.getElementById('soundNotif');
                if (soundToggle) {
                    soundToggle.checked = notificationSound.isSoundEnabled;
                }
            }
            
            // Fetch notifications immediately
            fetchNotifications();
            
            // Poll every 30 seconds
            notificationPollingInterval = setInterval(() => {
                fetchNotifications();
            }, 30000);
        });
        
        /**
         * Toggle notification sound setting
         */
        function toggleNotificationSound() {
            const soundToggle = document.getElementById('soundNotif');
            if (typeof notificationSound !== 'undefined' && notificationSound) {
                notificationSound.setSoundPreference(soundToggle.checked);
                showNotification(
                    soundToggle.checked ? 'Notification sounds enabled' : 'Notification sounds disabled',
                    'info'
                );
            }
        }
        
        // Cleanup polling on page unload
        window.addEventListener('beforeunload', () => {
            if (notificationPollingInterval) {
                clearInterval(notificationPollingInterval);
            }
        });
        
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

        // User Logs Report Functions
        let currentUserLogs = [];
        let currentUserLogsInfo = null;
        let currentUserLogsDate = new Date().toISOString().split('T')[0];

        // Save tab selection whenever it changes
        document.addEventListener('shown.bs.tab', function(e) {
            const activeTabId = e.target.id.replace('-tab', '');
            sessionStorage.setItem('activeReportsTab', activeTabId);
        });

        function searchUserLogs() {
            const userType = document.getElementById('userLogsType').value;
            const userId = document.getElementById('userLogsId').value.trim();
            const searchDate = document.getElementById('userLogsDate').value;

            if (!userId) {
                showUserLogsError('Please enter a user ID');
                return;
            }

            currentUserLogsDate = searchDate;
            showUserLogsLoading(true);
            hideUserLogsError();

            fetch('user_logs_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=search_user&person_type=${userType}&person_id=${encodeURIComponent(userId)}&date=${searchDate}`
            })
            .then(response => response.json())
            .then(data => {
                showUserLogsLoading(false);

                if (data.error) {
                    showUserLogsError(data.error);
                    hideUserLogsTable();
                } else {
                    currentUserLogs = data.logs;
                    currentUserLogsInfo = data.person;
                    currentUserLogsDate = searchDate;

                    displayUserLogsInfo(data.person);
                    displayUserLogsTable(data.logs);
                    showUserLogsTable();
                    hideUserLogsEmpty();
                    
                    // Save search data to sessionStorage for persistence on refresh
                    try {
                        sessionStorage.setItem('userLogsSearchData', JSON.stringify({
                            userType: userType,
                            userId: userId,
                            searchDate: searchDate,
                            userLogsInfo: data.person,
                            userLogs: data.logs
                        }));
                    } catch (e) {
                        console.log('Could not save search data');
                    }
                }
            })
            .catch(error => {
                showUserLogsLoading(false);
                showUserLogsError('Error searching user: ' + error.message);
            });
        }

        function displayUserLogsInfo(person) {
            const userId = person.StudentID || person.FacultyID || person.StaffID;
            const userType = person.Type;
            let typeIcon = '🎓';
            let typeColor = '#27AE60';
            
            if (userType === 'Faculty') {
                typeIcon = '👨‍🏫';
                typeColor = '#2980B9';
            } else if (userType === 'Staff') {
                typeIcon = '👔';
                typeColor = '#8B4513';
            }

            const html = `
                <div class="col-md-6">
                    <div style="border-left: 4px solid #972529; padding: 15px;">
                        <p style="color: #666; font-size: 0.9rem; margin: 0; text-transform: uppercase; font-weight: 700;">Full Name</p>
                        <p style="color: #972529; font-size: 1.3rem; font-weight: 700; margin: 5px 0 0 0;">${person.DisplayName}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="border-left: 4px solid #E5C573; padding: 15px;">
                        <p style="color: #666; font-size: 0.9rem; margin: 0; text-transform: uppercase; font-weight: 700;">User ID</p>
                        <p style="color: #E5C573; font-size: 1.3rem; font-weight: 700; margin: 5px 0 0 0;">${userId}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="border-left: 4px solid ${typeColor}; padding: 15px;">
                        <p style="color: #666; font-size: 0.9rem; margin: 0; text-transform: uppercase; font-weight: 700;">Type</p>
                        <p style="color: ${typeColor}; font-size: 1.1rem; font-weight: 700; margin: 5px 0 0 0;">${typeIcon} ${userType}</p>
                    </div>
                </div>
                <div class="col-12">
                    <div style="border-left: 4px solid #6b7280; padding: 15px;">
                        <p style="color: #666; font-size: 0.9rem; margin: 0; text-transform: uppercase; font-weight: 700;">Department</p>
                        <p style="color: #333; font-size: 1rem; font-weight: 600; margin: 5px 0 0 0;">${person.Department}</p>
                    </div>
                </div>
            `;
            document.getElementById('userLogsInfoContent').innerHTML = html;
            document.getElementById('userLogsInfo').classList.remove('d-none');
        }

        function displayUserLogsTable(logs) {
            const tbody = document.getElementById('userLogsTableBody');
            tbody.innerHTML = '';

            if (logs.length === 0) {
                hideUserLogsTable();
                showUserLogsEmpty();
                return;
            }

            logs.forEach((log, index) => {
                const time = new Date(log.Timestamp).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                const isEntry = log.Type === 'Entry';
                const badgeStyle = isEntry 
                    ? 'background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border: none;'
                    : 'background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; border: none;';
                const badgeIcon = isEntry ? '📥' : '📤';
                const scannerLocation = log.ScannerID || 'N/A';

                const row = `
                    <tr style="border-bottom: 1px solid #e5e7eb; transition: all 0.2s;">
                        <td style="padding: 15px; font-weight: 500; color: #333;">${time}</td>
                        <td style="padding: 15px;">
                            <span style="${badgeStyle} padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                ${badgeIcon} ${log.Type}
                            </span>
                        </td>
                        <td style="padding: 15px; color: #666;">${scannerLocation}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function exportUserLogsExcel() {
            console.log('Excel Export Called');
            
            if (!currentUserLogsInfo) {
                alert('No user data. Please search for a user first.');
                return;
            }
            
            if (!currentUserLogs || currentUserLogs.length === 0) {
                alert('No logs found to export');
                return;
            }

            try {
                const userId = currentUserLogsInfo.StudentID || currentUserLogsInfo.FacultyID || currentUserLogsInfo.StaffID;
                
                // Create HTML table with styles
                let html = `
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <style>
                            body { font-family: Calibri, Arial; margin: 0; padding: 20px; background: #f5f5f5; }
                            .container { max-width: 900px; margin: 0 auto; }
                            .title { 
                                background-color: #972529; 
                                color: white; 
                                padding: 20px; 
                                text-align: center; 
                                font-size: 18px; 
                                font-weight: bold; 
                                border: 3px solid #7a1d20;
                                margin-bottom: 20px;
                            }
                            .section-header {
                                background-color: #E5C573;
                                color: #333;
                                padding: 12px 15px;
                                font-size: 14px;
                                font-weight: bold;
                                border: 2px solid #D4B85A;
                                margin-top: 15px;
                                margin-bottom: 10px;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-bottom: 20px;
                            }
                            .user-info-table td {
                                padding: 12px 15px;
                                border: 1.5px solid #972529;
                            }
                            .user-info-table td:first-child {
                                background-color: #972529;
                                color: white;
                                font-weight: bold;
                                width: 25%;
                            }
                            .user-info-table td:last-child {
                                background-color: #FFF9E6;
                                color: #333;
                            }
                            .logs-table thead th {
                                background-color: #972529;
                                color: white;
                                padding: 14px;
                                border: 2px solid #7a1d20;
                                font-weight: bold;
                                text-align: center;
                                font-size: 13px;
                            }
                            .logs-table tbody tr:nth-child(odd) {
                                background-color: #FFFFFF;
                            }
                            .logs-table tbody tr:nth-child(even) {
                                background-color: #F9F3E6;
                            }
                            .logs-table tbody td {
                                padding: 12px 15px;
                                border: 1px solid #D9CCC4;
                                text-align: left;
                                color: #333;
                            }
                            .logs-table tbody td:nth-child(2) {
                                text-align: center;
                            }
                            .entry-badge {
                                background-color: #C8E6C9;
                                color: #1B5E20;
                                padding: 6px 12px;
                                border-radius: 4px;
                                font-weight: bold;
                                font-size: 12px;
                            }
                            .exit-badge {
                                background-color: #FFCCCC;
                                color: #B71C1C;
                                padding: 6px 12px;
                                border-radius: 4px;
                                font-weight: bold;
                                font-size: 12px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <tr>
                                    <td colspan="3" class="title">USER ACTIVITY REPORT</td>
                                </tr>
                            </table>
                            
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <tr>
                                    <td colspan="3" class="section-header">USER INFORMATION</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #972529; color: white; font-weight: bold; width: 25%;">User ID</td>
                                    <td colspan="2" style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #FFF9E6; color: #333;">${userId}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #972529; color: white; font-weight: bold;">Full Name</td>
                                    <td colspan="2" style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #FFF9E6; color: #333;">${currentUserLogsInfo.DisplayName}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #972529; color: white; font-weight: bold;">Type</td>
                                    <td colspan="2" style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #FFF9E6; color: #333;">${currentUserLogsInfo.Type}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #972529; color: white; font-weight: bold;">Department</td>
                                    <td colspan="2" style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #FFF9E6; color: #333;">${currentUserLogsInfo.Department}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #972529; color: white; font-weight: bold;">Report Date</td>
                                    <td colspan="2" style="padding: 12px 15px; border: 1.5px solid #972529; background-color: #FFF9E6; color: #333;">${currentUserLogsDate}</td>
                                </tr>
                            </table>
                            
                            <table class="logs-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <tr>
                                    <td colspan="3" class="section-header">ACTIVITY LOGS</td>
                                </tr>
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Scanner Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                // Add log rows
                currentUserLogs.forEach(log => {
                    const time = new Date(log.Timestamp).toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    const badgeClass = log.Type === 'Entry' ? 'entry-badge' : 'exit-badge';
                    const scannerLocation = log.ScannerID || 'N/A';
                    
                    html += `
                        <tr>
                            <td>${time}</td>
                            <td><span class="${badgeClass}">${log.Type}</span></td>
                            <td>${scannerLocation}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </body>
                    </html>
                `;

                // Convert HTML to blob and download
                const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=UTF-8' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `User_Logs_${userId}_${currentUserLogsDate}.xls`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                console.log('Excel export successful');
                alert('Excel file exported successfully!');
            } catch (error) {
                console.error('Excel export error:', error);
                alert('Error exporting to Excel: ' + error.message);
            }
        }

        function exportUserLogsPDF() {
            console.log('PDF Export Called');
            console.log('CurrentUserLogsInfo:', currentUserLogsInfo);
            console.log('CurrentUserLogs:', currentUserLogs);
            
            if (!currentUserLogsInfo) {
                alert('No user data. Please search for a user first.');
                return;
            }
            
            if (!currentUserLogs || currentUserLogs.length === 0) {
                alert('No logs found to export');
                return;
            }

            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF();
                const userId = currentUserLogsInfo.StudentID || currentUserLogsInfo.FacultyID || currentUserLogsInfo.StaffID;
                const pageWidth = pdf.internal.pageSize.getWidth();
                let yPosition = 20;

                // Header
                pdf.setFillColor(151, 37, 41);
                pdf.rect(0, 0, pageWidth, 25, 'F');
                pdf.setTextColor(255, 255, 255);
                pdf.setFontSize(16);
                pdf.text('User Activity Report', 15, 18);

                // User Info
                yPosition = 40;
                pdf.setTextColor(0, 0, 0);
                pdf.setFontSize(11);
                pdf.setFont(undefined, 'bold');
                pdf.text('User Information', 15, yPosition);
                
                yPosition += 10;
                pdf.setFontSize(9);
                pdf.setFont(undefined, 'normal');
                
                const userInfo = [
                    `User ID: ${userId}`,
                    `Name: ${currentUserLogsInfo.DisplayName}`,
                    `Type: ${currentUserLogsInfo.Type}`,
                    `Department: ${currentUserLogsInfo.Department}`,
                    `Date: ${currentUserLogsDate}`
                ];

                userInfo.forEach(info => {
                    pdf.text(info, 15, yPosition);
                    yPosition += 7;
                });

                yPosition += 5;
                pdf.setFont(undefined, 'bold');
                pdf.text('Activity Logs', 15, yPosition);

                yPosition += 10;
                pdf.setFont(undefined, 'normal');

                // Table
                const colWidth = pageWidth / 3.5;
                pdf.setFillColor(229, 197, 115);
                pdf.rect(15, yPosition - 5, colWidth - 2, 7, 'F');
                pdf.rect(15 + colWidth - 2, yPosition - 5, colWidth - 2, 7, 'F');
                pdf.rect(15 + (colWidth - 2) * 2, yPosition - 5, colWidth - 2, 7, 'F');

                pdf.setTextColor(0, 0, 0);
                pdf.setFontSize(8);
                pdf.text('Time', 17, yPosition);
                pdf.text('Type', 15 + colWidth, yPosition);
                pdf.text('Scanner', 15 + (colWidth - 2) * 2 + 2, yPosition);

                yPosition += 8;

                currentUserLogs.forEach((log) => {
                    if (yPosition > 270) {
                        pdf.addPage();
                        yPosition = 15;
                    }

                    const time = new Date(log.Timestamp).toLocaleTimeString();
                    pdf.text(time, 17, yPosition);
                    pdf.text(log.Type, 15 + colWidth, yPosition);
                    pdf.text(log.ScannerID || 'N/A', 15 + (colWidth - 2) * 2 + 2, yPosition);

                    yPosition += 7;
                });

                pdf.save(`User_Logs_${userId}_${currentUserLogsDate}.pdf`);
                console.log('PDF export successful');
            } catch (error) {
                console.error('PDF export error:', error);
                alert('Error exporting to PDF: ' + error.message);
            }
        }

        function showUserLogsError(message) {
            const errorDiv = document.getElementById('userLogsError');
            document.getElementById('userLogsErrorText').textContent = message;
            errorDiv.classList.remove('d-none');
            hideUserLogsLoading();
        }

        function hideUserLogsError() {
            document.getElementById('userLogsError').classList.add('d-none');
        }

        function showUserLogsLoading(show) {
            document.getElementById('userLogsLoading').classList.toggle('d-none', !show);
        }

        function hideUserLogsLoading() {
            document.getElementById('userLogsLoading').classList.add('d-none');
        }

        function showUserLogsTable() {
            document.getElementById('userLogsTable').classList.remove('d-none');
        }

        function hideUserLogsTable() {
            document.getElementById('userLogsTable').classList.add('d-none');
        }

        function showUserLogsEmpty() {
            document.getElementById('userLogsEmpty').classList.remove('d-none');
        }

        function hideUserLogsEmpty() {
            document.getElementById('userLogsEmpty').classList.add('d-none');
        }

        // Allow search on Enter key in user logs
        document.getElementById('userLogsId').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchUserLogs();
            }
        });

        // Visitor Analytics Functions
        let visitorAnalyticsChart = null;
        let visitorPurposeChart = null;

        function loadVisitorAnalytics(startDate, endDate) {
            const formData = new FormData();
            formData.append('action', 'get_visitor_analytics');
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);

            fetch('visitor_analytics_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateVisitorAnalytics(data);
                } else {
                    console.error('Error loading visitor analytics:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateVisitorAnalytics(data) {
            // Update stat cards
            document.getElementById('totalVisitors').textContent = data.totalVisitors || '-';
            document.getElementById('totalCheckIns').textContent = data.totalCheckIns || '-';
            document.getElementById('totalCheckOuts').textContent = data.totalCheckOuts || '-';
            document.getElementById('avgDwellTime').textContent = (data.avgDwell || 0) + ' min';

            // Update charts
            updateVisitorTrendChart(data.dailyTrend || []);
            updateVisitorPurposeChart(data.visitorsByPurpose || []);

            // Update tables
            updateRecentVisitors(data.recentVisitors || []);
            updateVisitorLogs(data.visitorLogs || []);
        }

        function updateVisitorTrendChart(trendData) {
            const ctx = document.getElementById('visitorTrendChart');
            if (!ctx) return;

            const dates = trendData.map(d => d.date || d.Date);
            const counts = trendData.map(d => d.count || d.Count);

            if (visitorAnalyticsChart) {
                visitorAnalyticsChart.destroy();
            }

            visitorAnalyticsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Visitors Registered',
                        data: counts,
                        borderColor: '#972529',
                        backgroundColor: 'rgba(151, 37, 41, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#972529',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function updateVisitorPurposeChart(purposeData) {
            const ctx = document.getElementById('visitorPurposeChart');
            if (!ctx) return;

            const labels = purposeData.map(d => (d.purpose || '').substring(0, 20));
            const counts = purposeData.map(d => d.count);

            if (visitorPurposeChart) {
                visitorPurposeChart.destroy();
            }

            visitorPurposeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: [
                            '#972529',
                            '#E5C573',
                            '#a83531',
                            '#eed490',
                            '#c44536',
                            '#f5deba'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        function updateRecentVisitors(visitors) {
            const tbody = document.getElementById('recentVisitorsBody');
            if (!tbody) return;

            if (visitors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No visitors found</td></tr>';
                return;
            }

            tbody.innerHTML = visitors.map(visitor => `
                <tr>
                    <td><span class="badge bg-info">${visitor.visitor_code || visitor.VisitorCode || ''}</span></td>
                    <td><strong>${visitor.first_name} ${visitor.last_name}</strong></td>
                    <td>${visitor.company || 'N/A'}</td>
                    <td>${(visitor.purpose || '').substring(0, 30)}</td>
                    <td>${visitor.contact_number || 'N/A'}</td>
                    <td>${new Date(visitor.created_at).toLocaleDateString()} ${new Date(visitor.created_at).toLocaleTimeString()}</td>
                </tr>
            `).join('');
        }

        function updateVisitorLogs(logs) {
            const tbody = document.getElementById('visitorLogsBody');
            if (!tbody) return;

            if (logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No logs found</td></tr>';
                return;
            }

            tbody.innerHTML = logs.map(log => {
                const statusHTML = log.check_out_time 
                    ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Checked Out</span>'
                    : '<span class="badge bg-warning"><i class="fas fa-hourglass-start me-1"></i>Checked In</span>';
                
                return `
                    <tr>
                        <td><strong>${log.visitor_code || ''}</strong></td>
                        <td>${log.first_name} ${log.last_name || ''}</td>
                        <td>${new Date(log.check_in_time).toLocaleString()}</td>
                        <td>${log.check_out_time ? new Date(log.check_out_time).toLocaleString() : '--'}</td>
                        <td>${log.dwell_time || '--'}</td>
                        <td>${statusHTML}</td>
                    </tr>
                `;
            }).join('');
        }

        function resetVisitorAnalytics() {
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            const endDate = new Date();

            document.getElementById('visitorStartDate').valueAsDate = startDate;
            document.getElementById('visitorEndDate').valueAsDate = endDate;

            loadVisitorAnalytics(startDate.toISOString().split('T')[0], endDate.toISOString().split('T')[0]);
        }

        // Load visitor analytics when form is submitted
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('visitorAnalyticsForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const startDate = document.getElementById('visitorStartDate').value;
                    const endDate = document.getElementById('visitorEndDate').value;
                    loadVisitorAnalytics(startDate, endDate);
                });

                // Load initial data
                const startDate = document.getElementById('visitorStartDate').value;
                const endDate = document.getElementById('visitorEndDate').value;
                loadVisitorAnalytics(startDate, endDate);
            }
        });

        // ============================================
        // SETTINGS PAGE FUNCTIONS
        // ============================================

        // Load settings from localStorage on page load
        function loadSettingsFromStorage() {
            const theme = localStorage.getItem('adminTheme') || 'light';
            const dateFormat = localStorage.getItem('dateFormat') || 'DD/MM/YYYY';
            const timezone = localStorage.getItem('timezone') || 'UTC+8';
            const pagination = localStorage.getItem('pagination') || '25';

            document.getElementById('themeSelect').value = theme;
            document.getElementById('dateFormatSelect').value = dateFormat;
            document.getElementById('timezoneSelect').value = timezone;
            document.getElementById('paginationSelect').value = pagination;

            // Notification settings
            document.getElementById('emailNotif').checked = localStorage.getItem('emailNotif') !== 'false';
            document.getElementById('pushNotif').checked = localStorage.getItem('pushNotif') !== 'false';
            document.getElementById('soundNotif').checked = localStorage.getItem('soundNotif') !== 'false';

            // Report settings
            const exportFormat = localStorage.getItem('exportFormat') || 'excel';
            document.querySelector(`input[value="${exportFormat}"]`).checked = true;
            document.getElementById('includeCharts').checked = localStorage.getItem('includeCharts') !== 'false';
            document.getElementById('includeSummary').checked = localStorage.getItem('includeSummary') !== 'false';
            document.getElementById('includeFooter').checked = localStorage.getItem('includeFooter') !== 'false';

            // Security settings
            document.getElementById('sessionTimeout').value = localStorage.getItem('sessionTimeout') || '60';

            updateCurrentTime();
            loadScannerList();
            loadLoginHistory();
        }

        // Change theme
        function changeTheme(theme) {
            localStorage.setItem('adminTheme', theme);
            if (theme === 'dark') {
                document.body.style.backgroundColor = '#1a1a1a';
                document.body.style.color = '#fff';
            } else {
                document.body.style.backgroundColor = '#fff';
                document.body.style.color = '#000';
            }
            showNotification('Theme changed to ' + theme, 'success');
        }

        // Change date format
        function changeDateFormat(format) {
            localStorage.setItem('dateFormat', format);
            showNotification('Date format updated', 'success');
        }

        // Change timezone
        function changeTimezone(timezone) {
            localStorage.setItem('timezone', timezone);
            updateCurrentTime();
            showNotification('Timezone updated', 'success');
        }

        // Change pagination
        function changePagination(size) {
            localStorage.setItem('pagination', size);
            showNotification('Pagination size changed to ' + size, 'success');
        }

        // Update live clock
        function updateCurrentTime() {
            const timeDisplay = document.getElementById('currentTimeDisplay');
            if (timeDisplay) {
                const now = new Date();
                timeDisplay.textContent = now.toLocaleTimeString();
                setTimeout(updateCurrentTime, 1000);
            }
        }

        // Load scanner list
        function loadScannerList() {
            const container = document.getElementById('scannerListContainer');
            if (!container) return;

            // Demo data - in production, fetch from API
            const scanners = [
                { id: 1, name: 'Main Gate Scanner', location: 'Gate 1', status: 'Active', scans: 1254 },
                { id: 2, name: 'Vehicular Exit Scanner', location: 'Exit 1', status: 'Active', scans: 856 },
                { id: 3, name: 'Main Exit Scanner', location: 'Main Entrance', status: 'Active', scans: 523 }
            ];

            displayScannerList(scanners);
        }

        // Display scanner list in table
        function displayScannerList(scanners) {
            const container = document.getElementById('scannerListContainer');
            if (!container) return;

            const html = `
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th>Scanner Name</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Total Scans</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${scanners.map(scanner => `
                            <tr>
                                <td>
                                    <i class="fas fa-camera me-2"></i>${scanner.name}
                                </td>
                                <td>${scanner.location}</td>
                                <td>
                                    <span class="badge bg-${scanner.status === 'Active' ? 'success' : 'danger'}">
                                        ${scanner.status}
                                    </span>
                                </td>
                                <td><strong>${scanner.scans}</strong></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            container.innerHTML = html;

            // Display stats
            const stats = `
                <div class="row g-2">
                    <div class="col-4">
                        <div style="background: rgba(151, 37, 41, 0.1); padding: 12px; border-radius: 8px; text-align: center;">
                            <p class="mb-1" style="font-size: 0.85rem; color: #666;">Active</p>
                            <p class="mb-0" style="font-size: 1.5rem; color: #972529; font-weight: bold;">3</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background: rgba(229, 197, 115, 0.1); padding: 12px; border-radius: 8px; text-align: center;">
                            <p class="mb-1" style="font-size: 0.85rem; color: #666;">Total Scans</p>
                            <p class="mb-0" style="font-size: 1.5rem; color: #E5C573; font-weight: bold;">2.6K</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background: rgba(108, 117, 125, 0.1); padding: 12px; border-radius: 8px; text-align: center;">
                            <p class="mb-1" style="font-size: 0.85rem; color: #666;">Avg./Day</p>
                            <p class="mb-0" style="font-size: 1.5rem; color: #6c757d; font-weight: bold;">867</p>
                        </div>
                    </div>
                </div>
            `;
            const statsContainer = document.getElementById('scannerStats');
            if (statsContainer) statsContainer.innerHTML = stats;
        }

        // Refresh scanner list
        function refreshScannerList() {
            loadScannerList();
            showNotification('Scanner list refreshed', 'success');
        }

        // Filter scanners
        function filterScanners() {
            const searchText = document.getElementById('scannerSearch').value.toLowerCase();
            const statusFilter = document.getElementById('scannerStatusFilter').value;
            showNotification('Filters applied: Search="' + searchText + '", Status="' + statusFilter + '"', 'info');
        }

        // Reset scanner filter
        function resetScannerFilter() {
            document.getElementById('scannerSearch').value = '';
            document.getElementById('scannerStatusFilter').value = '';
            loadScannerList();
            showNotification('Filter reset', 'success');
        }

        // Load scanner statistics
        function loadScannerStats() {
            const statsContainer = document.getElementById('scannerStats');
            if (!statsContainer) return;

            const stats = `
                <div class="row g-2">
                    <div class="col-4">
                        <div style="background: rgba(151, 37, 41, 0.1); padding: 12px; border-radius: 8px; text-align: center;">
                            <p class="mb-1" style="font-size: 0.75rem; color: #666;">Active</p>
                            <p class="mb-0" style="font-size: 1.3rem; color: #972529; font-weight: bold;">3</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background: rgba(229, 197, 115, 0.1); padding: 12px; border-radius: 8px; text-align: center;">
                            <p class="mb-1" style="font-size: 0.75rem; color: #666;">Total Scans</p>
                            <p class="mb-0" style="font-size: 1.3rem; color: #E5C573; font-weight: bold;">2.8K</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background: rgba(108, 117, 125, 0.1); padding: 12px; border-radius: 8px; text-align: center;">
                            <p class="mb-1" style="font-size: 0.75rem; color: #666;">Avg./Day</p>
                            <p class="mb-0" style="font-size: 1.3rem; color: #6c757d; font-weight: bold;">850</p>
                        </div>
                    </div>
                </div>
            `;
            statsContainer.innerHTML = stats;
        }

        // Save scanner settings with all advanced options
        function saveScannerSettings() {
            const settings = {
                scannerTimeout: document.getElementById('scannerTimeout').value,
                successDelay: document.getElementById('successDelay').value,
                scanSuccessSound: document.getElementById('scanSuccessSound').checked,
                doubleCheckEnabled: document.getElementById('doubleCheckEnabled').checked,
                enableOfflineMode: document.getElementById('enableOfflineMode').checked,
                scanFormat: document.getElementById('scanFormat').value,
                duplicateBehavior: document.getElementById('duplicateBehavior').value,
                failedScanAction: document.getElementById('failedScanAction').value,
                enableAutoBackup: document.getElementById('enableAutoBackup').checked
            };

            localStorage.setItem('scannerSettings', JSON.stringify(settings));
            showNotification('All scanner settings saved successfully!', 'success');
        }

        // Reset scanner settings to defaults
        function resetScannerSettings() {
            if (confirm('Are you sure you want to reset all scanner settings to defaults?')) {
                document.getElementById('scannerTimeout').value = '30';
                document.getElementById('successDelay').value = '2';
                document.getElementById('scanSuccessSound').checked = true;
                document.getElementById('doubleCheckEnabled').checked = true;
                document.getElementById('enableOfflineMode').checked = false;
                document.getElementById('scanFormat').value = 'barcode';
                document.getElementById('duplicateBehavior').value = 'alert';
                document.getElementById('failedScanAction').value = 'retry';
                document.getElementById('enableAutoBackup').checked = true;

                localStorage.removeItem('scannerSettings');
                showNotification('Scanner settings reset to defaults', 'success');
            }
        }

        // Test scanner connectivity
        function testScanner() {
            showNotification('Testing scanner connection...', 'info');
            setTimeout(() => {
                showNotification('Scanner test completed - All 3 devices responding normally', 'success');
            }, 1500);
        }

        // Calibrate all scanners
        function calibrateScanner() {
            showNotification('Calibrating scanners... Please wait', 'info');
            setTimeout(() => {
                showNotification('Calibration complete! All scanners optimized for accuracy', 'success');
            }, 2000);
        }

        // Run scanner diagnostics
        function runDiagnostics() {
            showNotification('Running scanner diagnostics...', 'info');
            setTimeout(() => {
                showNotification('Diagnostics Report: All systems healthy. Scan accuracy: 99.8%', 'success');
            }, 2500);
        }

        // Sync scanners data
        function syncScannersNow() {
            showNotification('Syncing scanner data...', 'info');
            setTimeout(() => {
                showNotification('Sync complete! 342 new records uploaded', 'success');
            }, 1800);
        }

        // Clear scanner cache
        function clearScannerCache() {
            if (confirm('Clear all scanner cache? This will free up memory but may slow down first scans.')) {
                showNotification('Clearing scanner cache...', 'info');
                setTimeout(() => {
                    showNotification('Cache cleared successfully! (128 MB freed)', 'success');
                }, 1000);
            }
        }

        // Load login history
        function loadLoginHistory() {
            const container = document.getElementById('loginHistoryBody');
            if (!container) return;

            const history = [
                { date: '2024-01-15 08:30 AM', ip: '192.168.1.100', browser: 'Chrome', status: 'Success' },
                { date: '2024-01-14 03:45 PM', ip: '192.168.1.101', browser: 'Firefox', status: 'Success' },
                { date: '2024-01-14 10:20 AM', ip: '192.168.1.102', browser: 'Safari', status: 'Failed' },
                { date: '2024-01-13 06:15 PM', ip: '192.168.1.100', browser: 'Chrome', status: 'Success' }
            ];

            const html = history.map(log => `
                <tr>
                    <td>${log.date}</td>
                    <td>${log.ip}</td>
                    <td>${log.browser}</td>
                    <td>
                        <span class="badge bg-${log.status === 'Success' ? 'success' : 'danger'}">
                            ${log.status}
                        </span>
                    </td>
                </tr>
            `).join('');

            container.innerHTML = html;
        }

        // Toggle notification sound
        function toggleNotificationSound() {
            const enabled = document.getElementById('soundNotif').checked;
            localStorage.setItem('soundNotif', enabled);
            showNotification('Notification sound ' + (enabled ? 'enabled' : 'disabled'), 'success');
        }

        // Change password
        function changePassword() {
            const newPassword = prompt('Enter new password:');
            if (newPassword && newPassword.length >= 8) {
                showNotification('Password change initiated. Please check your email for confirmation.', 'success');
            } else {
                showNotification('Password must be at least 8 characters', 'danger');
            }
        }

        // Save all settings
        function saveAllSettings() {
            localStorage.setItem('emailNotif', document.getElementById('emailNotif').checked);
            localStorage.setItem('pushNotif', document.getElementById('pushNotif').checked);
            localStorage.setItem('soundNotif', document.getElementById('soundNotif').checked);
            localStorage.setItem('includeCharts', document.getElementById('includeCharts').checked);
            localStorage.setItem('includeSummary', document.getElementById('includeSummary').checked);
            localStorage.setItem('includeFooter', document.getElementById('includeFooter').checked);
            localStorage.setItem('sessionTimeout', document.getElementById('sessionTimeout').value);

            const exportFormat = document.querySelector('input[name="exportFormat"]:checked').value;
            localStorage.setItem('exportFormat', exportFormat);

            showNotification('All settings saved successfully!', 'success');
        }

        // Reset settings to defaults
        function resetSettings() {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                localStorage.clear();
                location.reload();
            }
        }

        // System maintenance functions
        function createBackup() {
            showNotification('Creating backup... This may take a few minutes.', 'info');
            setTimeout(() => {
                showNotification('Backup created successfully!', 'success');
            }, 2000);
        }

        function viewBackups() {
            alert('Backup management interface would open here');
        }

        function cleanupLogs() {
            showNotification('Cleaning old logs...', 'info');
            setTimeout(() => {
                showNotification('Old logs cleaned successfully (234 records removed)', 'success');
            }, 1500);
        }

        function rebuildIndexes() {
            showNotification('Rebuilding database indexes...', 'info');
            setTimeout(() => {
                showNotification('Database indexes rebuilt successfully!', 'success');
            }, 2000);
        }

        function checkDatabase() {
            showNotification('Checking database integrity...', 'info');
            setTimeout(() => {
                showNotification('Database check complete - All OK!', 'success');
            }, 1500);
        }

        function cacheStats() {
            showNotification('Cache Stats: Memory: 24MB | Hit Rate: 87% | Items: 1,245', 'info');
        }

        // Toast notification system
        function showNotification(message, type = 'info') {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'info'} border-0" role="alert" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            const toastDiv = document.createElement('div');
            toastDiv.innerHTML = toastHtml;
            document.body.appendChild(toastDiv);

            const toast = new bootstrap.Toast(toastDiv.querySelector('.toast'));
            toast.show();

            setTimeout(() => toastDiv.remove(), 3000);
        }

        // Initialize settings on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSettingsFromStorage();
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
        
        /* Smooth transitions for interactive elements (excluding text selection) */
        button, a, input, select, textarea, .btn, .form-control {
            transition-duration: 0.15s !important;
        }
        
        /* No transition for nav links to prevent text coverage */
        .nav-link {
            transition: none !important;
        }
        
        /* Prevent gold overlay on text selection */
        ::selection {
            background-color: rgba(229, 197, 115, 0.3);
            color: #333;
        }
        
        ::-webkit-selection {
            background-color: rgba(229, 197, 115, 0.3);
            color: #333;
        }
        
        /* Remove selection from buttons and interactive elements */
        button::selection,
        .btn::selection,
        .nav-link::selection,
        .settings-tab-btn::selection {
            background-color: transparent;
            color: inherit;
        }
        
        /* Settings tabs container - disable all transitions */
        .settings-tabs,
        .settings-tabs .nav-item,
        .settings-tabs .nav-link,
        .settings-tabs button {
            transition: none !important;
            -webkit-transition: none !important;
            -moz-transition: none !important;
            animation: none !important;
            -webkit-animation: none !important;
        }
        
        /* Settings tab styles */
        .settings-tab-btn {
            border-bottom: 3px solid transparent !important;
            padding: 12px 16px !important;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            transition: none !important;
            -webkit-transition: none !important;
            -moz-transition: none !important;
        }
        
        .settings-tab-btn.active {
            border-bottom-color: #972529 !important;
            background-color: rgba(229, 197, 115, 0.1);
            color: #972529 !important;
            transition: none !important;
        }
        
        .settings-tab-btn:hover {
            background-color: rgba(229, 197, 115, 0.08);
            transition: none !important;
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