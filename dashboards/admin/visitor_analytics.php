<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once '../../config/database.php';

$database = new Database();
if (method_exists($database, 'connect')) {
    $conn = $database->connect();
} elseif (method_exists($database, 'getConnection')) {
    $conn = $database->getConnection();
} else {
    $conn = $database->connection();
}

// Get date range from request - safely validate dates
$startDate = getSafeGET('start_date', date('Y-m-d', strtotime('-30 days')), 'date');
$endDate = getSafeGET('end_date', date('Y-m-d'), 'date');

// Get visitor statistics
try {
    // Total visitors registered
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM visitors 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $totalVisitors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total check-ins
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM visitor_logs 
        WHERE DATE(check_in_time) BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $totalCheckIns = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Average dwell time
    $stmt = $conn->prepare("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, check_in_time, check_out_time)) as avg_dwell
        FROM visitor_logs 
        WHERE DATE(check_in_time) BETWEEN ? AND ? AND check_out_time IS NOT NULL
    ");
    $stmt->execute([$startDate, $endDate]);
    $avgDwell = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_dwell'] ?? 0, 1);

    // Visitors by purpose
    $stmt = $conn->prepare("
        SELECT purpose, COUNT(*) as count 
        FROM visitors 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY purpose 
        ORDER BY count DESC 
        LIMIT 10
    ");
    $stmt->execute([$startDate, $endDate]);
    $visitorsByPurpose = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Daily visitor trend
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM visitors 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ");
    $stmt->execute([$startDate, $endDate]);
    $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total check-outs
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM visitor_logs 
        WHERE DATE(check_out_time) BETWEEN ? AND ? AND check_out_time IS NOT NULL
    ");
    $stmt->execute([$startDate, $endDate]);
    $totalCheckOuts = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Fetch recent visitors
    $stmt = $conn->prepare("
        SELECT id, visitor_code, first_name, middle_name, last_name, company, purpose, contact_number, created_at
        FROM visitors
        WHERE DATE(created_at) BETWEEN ? AND ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$startDate, $endDate]);
    $recentVisitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch check-in/out logs
    $stmt = $conn->prepare("
        SELECT 
            v.visitor_code,
            v.first_name,
            v.last_name,
            vl.check_in_time,
            vl.check_out_time,
            CASE 
                WHEN vl.check_out_time IS NOT NULL 
                THEN CONCAT(TIMESTAMPDIFF(HOUR, vl.check_in_time, vl.check_out_time), 'h ', MOD(TIMESTAMPDIFF(MINUTE, vl.check_in_time, vl.check_out_time), 60), 'm')
                ELSE '--'
            END as dwell_time
        FROM visitor_logs vl
        LEFT JOIN visitors v ON vl.visitor_id = v.id
        WHERE DATE(vl.check_in_time) BETWEEN ? AND ?
        ORDER BY vl.check_in_time DESC
        LIMIT 50
    ");
    $stmt->execute([$startDate, $endDate]);
    $checkInOutLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Visitor analytics error: " . $e->getMessage());
    $recentVisitors = [];
    $checkInOutLogs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Analytics - CTU Scanner Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        :root {
            --primary-color: #972529;
            --secondary-color: #E5C573;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 70px;
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
        }

        /* Sidebar Styles */
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

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            margin-bottom: 10px;
            border: 2px solid #E5C573;
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
        }

        .nav-link:hover {
            background: #E5C573;
            color: #333;
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }

        .nav-link.active {
            background: #E5C573;
            color: #333;
        }

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: rgba(255,255,255,0.95);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            z-index: 1040;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: left 0.2s ease;
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

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px 12px 0 0;
            padding: 20px;
        }

        .card-header h5 {
            color: #333;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        thead {
            background: #E5C573;
            color: #333;
        }

        .btn-primary {
            background: #972529;
            border-color: #972529;
        }

        .btn-primary:hover {
            background: #7a1d21;
            border-color: #7a1d21;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.3);
                opacity: 0.7;
            }
        }

        /* Mobile Toggle Button */
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
            color: var(--primary-color);
        }

        /* Mobile Overlay */
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
            transition: all 0.15s ease;
        }

        .mobile-close-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }
            
            .sidebar {
                display: none;
                position: fixed;
                left: 0;
                top: 70px;
                width: 280px;
                height: calc(100vh - 70px);
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.mobile-show {
                display: block;
                transform: translateX(0);
            }
            
            .top-header {
                left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
            
            .mobile-close-btn {
                display: flex;
            }
        }

        /* Card-based Row Styling */
        .card-body > div > div {
            transition: all 0.2s ease !important;
        }

        .card-body > div > div:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            transform: translateY(-2px) !important;
        }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="hideMobileSidebarMenu()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="mobile-close-btn" onclick="hideMobileSidebarMenu()">
            <i class="fas fa-times"></i>
        </button>
        <div class="sidebar-header">
            <img src="../../assets/images/logo.png" alt="CTU Logo" class="sidebar-logo">
            <h3 class="sidebar-title">CTU Admin</h3>
            <p class="sidebar-subtitle">System Dashboard</p>
        </div>
        
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="index.php#qr-generator" class="nav-link">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Generator</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="index.php#analytics" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="visitor_analytics.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Visitor Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="index.php#reports" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="index.php#users" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="index.php#settings" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>
        
        <div style="position: absolute; bottom: 20px; left: 8px; right: 8px;">
            <div class="nav-item">
                <a href="logout.php" class="nav-link" style="background: #972529; color: #fff;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Top Header -->
    <header class="top-header">
        <div class="d-flex align-items-center">
            <button class="mobile-toggle me-3" onclick="showMobileSidebarMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">
                <i class="fas fa-users me-2"></i>Visitor Analytics
            </h1>
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

    <main class="main-content">
        <div class="container-fluid">
        <!-- Date Range Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo escapeOutput($startDate, 'attr'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo escapeOutput($endDate, 'attr'); ?>">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="visitor_analytics.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3><?php echo $totalVisitors; ?></h3>
                    <p><i class="fas fa-id-card me-1"></i>Total Visitors Registered</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3><?php echo $totalCheckIns; ?></h3>
                    <p><i class="fas fa-sign-in-alt me-1"></i>Total Check-Ins</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3><?php echo $totalCheckOuts; ?></h3>
                    <p><i class="fas fa-sign-out-alt me-1"></i>Total Check-Outs</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <h3><?php echo $avgDwell; ?> min</h3>
                    <p><i class="fas fa-hourglass-half me-1"></i>Avg Dwell Time</p>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Daily Visitor Registrations</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Top Visit Purposes</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="purposeChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Visitors Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Recent Visitors</h5>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px;">
                            <table class="table table-hover mb-0" style="border-collapse: collapse; width: 100%; table-layout: fixed; background-color: white;">
                                <colgroup>
                                    <col style="width: 15%;">
                                    <col style="width: 25%;">
                                    <col style="width: 20%;">
                                    <col style="width: 20%;">
                                    <col style="width: 15%;">
                                    <col style="width: 5%;">
                                </colgroup>
                                <thead style="position: sticky; top: 0; z-index: 10; background: var(--secondary-color); color: #333;">
                                    <tr style="border-bottom: 3px solid #972529;">
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Code</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Name</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Company</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Purpose</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Contact</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700;">Registered</th>
                                    </tr>
                                </thead>
                                <tbody id="recentVisitorsBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5" style="padding: 24px !important;">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading recent visitors...
                                        </td>
                                    </tr>
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Check-In & Check-Out Logs</h5>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px;">
                            <table class="table table-hover mb-0" style="border-collapse: collapse; width: 100%; table-layout: fixed; background-color: white;">
                                <colgroup>
                                    <col style="width: 12%;">
                                    <col style="width: 18%;">
                                    <col style="width: 22%;">
                                    <col style="width: 22%;">
                                    <col style="width: 12%;">
                                    <col style="width: 14%;">
                                </colgroup>
                                <thead style="position: sticky; top: 0; z-index: 10; background: var(--secondary-color); color: #333;">
                                    <tr style="border-bottom: 3px solid #972529;">
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Visitor Code</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Visitor Name</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Check-In Time</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Check-Out Time</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700; border-right: 1px solid #ddd;">Dwell Time</th>
                                        <th style="padding: 16px; text-align: left; font-weight: 700;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="checkInOutBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5" style="padding: 24px !important;">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading check-in/out logs...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                                        echo "<tr><td colspan='6' class='text-danger'>Error loading logs: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Daily Trend Chart
        const trendData = <?php echo json_encode($dailyTrend); ?>;
        const trendDates = trendData.map(d => d.date);
        const trendCounts = trendData.map(d => d.count);

        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendDates,
                datasets: [{
                    label: 'Visitors Registered',
                    data: trendCounts,
                    borderColor: '#972529',
                    backgroundColor: 'rgba(151, 37, 41, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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

        // Purpose Chart
        const purposeData = <?php echo json_encode($visitorsByPurpose); ?>;
        const purposeLabels = purposeData.map(d => d.purpose.substring(0, 20));
        const purposeCounts = purposeData.map(d => d.count);

        const purposeCtx = document.getElementById('purposeChart').getContext('2d');
        new Chart(purposeCtx, {
            type: 'doughnut',
            data: {
                labels: purposeLabels,
                datasets: [{
                    data: purposeCounts,
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

        // Mobile Sidebar Functions
        function showMobileSidebarMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (sidebar && overlay) {
                sidebar.classList.add('mobile-show');
                overlay.classList.add('mobile-show');
                document.body.style.overflow = 'hidden';
            }
        }

        function hideMobileSidebarMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (sidebar && overlay) {
                sidebar.classList.remove('mobile-show');
                overlay.classList.remove('mobile-show');
                document.body.style.overflow = '';
            }
        }

        // Close sidebar when clicking on a nav link
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Check if this is an internal anchor link
                    const href = this.getAttribute('href');
                    if (href && href.includes('#')) {
                        // For anchor links, close the sidebar
                        setTimeout(hideMobileSidebarMenu, 100);
                    }
                });
            });

            // Close sidebar on overlay click (already set in HTML onclick)
            // Handle swipe to close
            const sidebar = document.getElementById('sidebar');
            let touchStartX = 0;
            let touchEndX = 0;

            if (sidebar) {
                sidebar.addEventListener('touchstart', e => {
                    touchStartX = e.touches[0].clientX;
                });

                sidebar.addEventListener('touchmove', e => {
                    touchEndX = e.touches[0].clientX;
                    const swipeDistance = touchStartX - touchEndX;
                    
                    if (swipeDistance > 50) { // Threshold for swipe
                        hideMobileSidebarMenu();
                    }
                });
            }
        });
    </script>
    </main>
    </div>

<script>
// Embed data in the page
const recentVisitorsData = <?php echo json_encode($recentVisitors ?? []); ?>;
const checkInOutLogsData = <?php echo json_encode($checkInOutLogs ?? []); ?>;

function loadRecentVisitors() {
    console.log('loadRecentVisitors called with embedded data:', recentVisitorsData);
    displayRecentVisitors(recentVisitorsData);
}

function displayRecentVisitors(visitors) {
    console.log('displayRecentVisitors called with:', visitors);
    const tbody = document.getElementById('recentVisitorsBody');
    if (!tbody) {
        console.error('recentVisitorsBody element not found!');
        return;
    }

    if (!visitors || visitors.length === 0) {
        console.log('No visitors to display');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-5" style="padding: 24px !important;">
                    <i class="fas fa-inbox me-2"></i>No recent visitors found
                </td>
            </tr>
        `;
        return;
    }

    console.log('Processing', visitors.length, 'visitors');
    tbody.innerHTML = '';

    visitors.forEach((visitor) => {
        const fullName = (visitor.first_name + ' ' + (visitor.middle_name || '') + ' ' + visitor.last_name).trim();
        const company = visitor.company || 'N/A';
        const purpose = visitor.purpose.substring(0, 30);
        const registered = new Date(visitor.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

        const tr = document.createElement('tr');
        tr.style.borderBottom = '2px solid #dee2e6';
        tr.style.height = '65px';
        tr.style.backgroundColor = 'white';
        tr.style.transition = 'all 0.2s ease';
        tr.onmouseover = function() {
            this.style.backgroundColor = '#f0f7ff';
        };
        tr.onmouseout = function() {
            this.style.backgroundColor = 'white';
        };

        // Build HTML for all cells
        let cellsHTML = '';
        
        // Code
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; vertical-align: middle; text-align: center; width: 15%; overflow: hidden; text-overflow: ellipsis;"><span class="badge bg-info">${visitor.visitor_code}</span></td>`;
        
        // Name
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; font-weight: 600; color: #2c3e50; vertical-align: middle; width: 25%; overflow: hidden; text-overflow: ellipsis;">${fullName}</td>`;
        
        // Company
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #666; vertical-align: middle; width: 20%; overflow: hidden; text-overflow: ellipsis;">${company}</td>`;
        
        // Purpose
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #666; vertical-align: middle; width: 20%; overflow: hidden; text-overflow: ellipsis;">${purpose}</td>`;
        
        // Contact
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #666; vertical-align: middle; width: 15%; overflow: hidden; text-overflow: ellipsis;">${visitor.contact_number}</td>`;
        
        // Registered
        cellsHTML += `<td style="padding: 12px 16px; color: #666; vertical-align: middle; font-size: 0.9rem; width: 5%; overflow: hidden; text-overflow: ellipsis;">${registered}</td>`;

        tr.innerHTML = cellsHTML;
        tbody.appendChild(tr);
    });
}

function loadCheckInOutLogs() {
    console.log('loadCheckInOutLogs called with embedded data:', checkInOutLogsData);
    displayCheckInOutLogs(checkInOutLogsData);
}

function displayCheckInOutLogs(logs) {
    const tbody = document.getElementById('checkInOutBody');
    if (!tbody) return;

    if (!logs || logs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-5" style="padding: 24px !important;">
                    <i class="fas fa-inbox me-2"></i>No check-in/out logs found
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = '';

    logs.forEach((log) => {
        const visitorName = (log.first_name || '') + ' ' + (log.last_name || '');
        const checkInTime = new Date(log.check_in_time).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const checkOutTime = log.check_out_time ? new Date(log.check_out_time).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }) : '--';
        const isCheckedOut = log.check_out_time !== null;

        const tr = document.createElement('tr');
        tr.style.borderBottom = '2px solid #dee2e6';
        tr.style.height = '65px';
        tr.style.backgroundColor = 'white';
        tr.style.transition = 'all 0.2s ease';
        tr.onmouseover = function() {
            this.style.backgroundColor = '#fff0f0';
        };
        tr.onmouseout = function() {
            this.style.backgroundColor = 'white';
        };

        // Build HTML for all cells
        let cellsHTML = '';
        
        // Visitor Code
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; font-weight: 600; vertical-align: middle; text-align: center; width: 12%; overflow: hidden; text-overflow: ellipsis;">${log.visitor_code}</td>`;
        
        // Visitor Name
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #333; font-weight: 500; vertical-align: middle; width: 18%; overflow: hidden; text-overflow: ellipsis;">${visitorName}</td>`;
        
        // Check-In Time
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #666; font-size: 0.9rem; vertical-align: middle; width: 22%; overflow: hidden; text-overflow: ellipsis;">${checkInTime}</td>`;
        
        // Check-Out Time
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #666; font-size: 0.9rem; vertical-align: middle; width: 22%; overflow: hidden; text-overflow: ellipsis;">${checkOutTime}</td>`;
        
        // Dwell Time
        cellsHTML += `<td style="padding: 12px 16px; border-right: 1px solid #ecf0f1; color: #666; vertical-align: middle; text-align: center; width: 12%; overflow: hidden; text-overflow: ellipsis;">${log.dwell_time}</td>`;
        
        // Status
        const statusBadge = isCheckedOut ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Checked Out</span>' : '<span class="badge bg-warning"><i class="fas fa-hourglass-start me-1"></i>Checked In</span>';
        cellsHTML += `<td style="padding: 12px 16px; vertical-align: middle; text-align: center; width: 14%; overflow: hidden;">${statusBadge}</td>`;

        tr.innerHTML = cellsHTML;
        tbody.appendChild(tr);
    });
}

// Load data on page load
console.log('Script loaded, document.readyState:', document.readyState);
if (document.readyState === 'loading') {
    console.log('DOM still loading, setting up DOMContentLoaded listener');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded fired, calling load functions');
        loadRecentVisitors();
        loadCheckInOutLogs();
    });
} else {
    // DOM is already loaded
    console.log('DOM already loaded, calling load functions immediately');
    loadRecentVisitors();
    loadCheckInOutLogs();
}
</script>
</body>
</html>
