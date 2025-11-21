<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Prevent caching - critical for real-time stats
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');
require_once '../../includes/functions.php';

// Helper function to get date range based on filter
function getDateRange($dateRange) {
    $today = date('Y-m-d');
    $now = time();
    
    switch ($dateRange) {
        case 'today':
            // Include yesterday, today, and tomorrow to account for timezone differences
            $yesterday = date('Y-m-d', $now - 86400);
            $tomorrow = date('Y-m-d', $now + 86400);
            return [$yesterday, $tomorrow];
        case 'week':
            $startOfWeek = date('Y-m-d', strtotime('monday this week'));
            $tomorrow = date('Y-m-d', $now + 86400);
            return [$startOfWeek, $tomorrow];
        case 'month':
            $startOfMonth = date('Y-m-01');
            $tomorrow = date('Y-m-d', $now + 86400);
            return [$startOfMonth, $tomorrow];
        case 'year':
            $startOfYear = date('Y-01-01');
            $tomorrow = date('Y-m-d', $now + 86400);
            return [$startOfYear, $tomorrow];
        default:
            // For 'today', include timezone buffer
            $yesterday = date('Y-m-d', $now - 86400);
            $tomorrow = date('Y-m-d', $now + 86400);
            return [$yesterday, $tomorrow];
    }
}

// Helper function to build WHERE clause for userType filter
function getUserTypeWhereClause($userType, $tableAlias = 'e') {
    if ($userType === 'all' || empty($userType)) {
        return '';
    }
    if ($userType === 'student' || $userType === 'faculty' || $userType === 'staff') {
        return " AND {$tableAlias}.PersonType = '{$userType}'";
    }
    return '';
}

// Helper function to build WHERE clause for department filter
function getDepartmentWhereClause($department, $tableAlias = 'e') {
    if ($department === 'all' || empty($department)) {
        return '';
    }
    if ($department === 'COTE' || $department === 'COED') {
        return " AND (s.Department = '{$department}' OR f.Department = '{$department}' OR st.Department = '{$department}')";
    }
    return '';
}

try {
    $scanner = new CTUScanner();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'department':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                // Build user type filter
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $departmentFilter = getDepartmentWhereClause($department);
                
                // Fixed query to properly aggregate department data
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        COALESCE(s.Department, f.Department, st.Department) as Department,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY COALESCE(s.Department, f.Department, st.Department)
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = [
                    'daily' => ['COTE' => 0, 'COED' => 0],
                    'hourly' => [],
                    'comparison' => [
                        'entries' => ['COTE' => 0, 'COED' => 0],
                        'exits' => ['COTE' => 0, 'COED' => 0]
                    ]
                ];

                // Daily department counts
                foreach ($results as $row) {
                    if (isset($data['daily'][$row['Department']])) {
                        $data['daily'][$row['Department']] = (int)$row['count'];
                    }
                }

                // Hourly distribution by department
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        COALESCE(s.Department, f.Department, st.Department) as Department,
                        HOUR(e.Timestamp) as Hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY COALESCE(s.Department, f.Department, st.Department), HOUR(e.Timestamp)
                    ORDER BY Hour
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $hourlyResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($hourlyResults as $row) {
                    if (!isset($data['hourly'][$row['Hour']])) {
                        $data['hourly'][$row['Hour']] = ['COTE' => 0, 'COED' => 0];
                    }
                    if (isset($data['hourly'][$row['Hour']][$row['Department']])) {
                        $data['hourly'][$row['Hour']][$row['Department']] = (int)$row['count'];
                    }
                }

                // Entry/Exit comparison
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                $departmentFilterExit = getDepartmentWhereClause($department, 'ex');
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        'entries' as type,
                        COALESCE(s.Department, f.Department, st.Department) as Department,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY COALESCE(s.Department, f.Department, st.Department)
                    UNION ALL
                    SELECT 
                        'exits' as type,
                        COALESCE(s.Department, f.Department, st.Department) as Department,
                        COUNT(*) as count
                    FROM exitlogs ex
                    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
                    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
                    LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff'
                    WHERE (DATE(ex.Timestamp) BETWEEN ? AND ? OR ex.Date BETWEEN ? AND ?) {$userTypeFilterExit}
                    {$departmentFilterExit}
                    GROUP BY COALESCE(s.Department, f.Department, st.Department)
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
                $comparisonResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($comparisonResults as $row) {
                    if (isset($data['comparison'][$row['type']][$row['Department']])) {
                        $data['comparison'][$row['type']][$row['Department']] = (int)$row['count'];
                    }
                }

                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Department query error: " . $e->getMessage());
                echo json_encode(['COTE' => 0, 'COED' => 0]);
            }
            break;
            
        case 'weekly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'week';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $departmentFilter = getDepartmentWhereClause($department);
                
                $stmt = $scanner->conn->prepare("
                    SELECT DAYOFWEEK(DATE(e.Timestamp)) as day, COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY DAYOFWEEK(DATE(e.Timestamp))
                    ORDER BY day
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert to proper format
                $weeklyData = [];
                foreach ($results as $row) {
                    $weeklyData[] = [
                        'day' => (int)$row['day'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($weeklyData);
            } catch (PDOException $e) {
                error_log("Weekly query error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;
            
        case 'custom_report':
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            
            if (empty($startDate) || empty($endDate)) {
                echo json_encode(['error' => 'Start date and end date are required']);
                break;
            }
            
            try {
                // Total entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) BETWEEN ? AND ? OR Date BETWEEN ? AND ?)");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $totalEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Total exits
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE (DATE(Timestamp) BETWEEN ? AND ? OR Date BETWEEN ? AND ?)");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $totalExits = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Student entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) BETWEEN ? AND ? OR Date BETWEEN ? AND ?) AND PersonType = 'student'");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $studentEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Faculty entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) BETWEEN ? AND ? OR Date BETWEEN ? AND ?) AND PersonType = 'faculty'");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $facultyEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Staff entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) BETWEEN ? AND ? OR Date BETWEEN ? AND ?) AND PersonType = 'staff'");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $staffEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo json_encode([
                    'total_entries' => $totalEntries,
                    'total_exits' => $totalExits,
                    'student_entries' => $studentEntries,
                    'faculty_entries' => $facultyEntries,
                    'staff_entries' => $staffEntries
                ]);
            } catch (PDOException $e) {
                error_log("Custom report error: " . $e->getMessage());
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'entry_exit_comparison':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                $departmentFilter = getDepartmentWhereClause($department);
                $departmentFilterExit = getDepartmentWhereClause($department, 'ex');
                
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        (SELECT COUNT(*) FROM entrylogs e LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student' LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty' LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff' WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter} {$departmentFilter}) as total_entries,
                        (SELECT COUNT(*) FROM exitlogs ex LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student' LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty' LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff' WHERE (DATE(ex.Timestamp) BETWEEN ? AND ? OR ex.Date BETWEEN ? AND ?) {$userTypeFilterExit} {$departmentFilterExit}) as total_exits
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode($result);
            } catch (PDOException $e) {
                error_log("Entry/Exit comparison error: " . $e->getMessage());
                echo json_encode(['error' => 'Database error']);
            }
            break;

        case 'user_type_distribution':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $departmentFilter = getDepartmentWhereClause($department);
                
                $query = "
                    SELECT 
                        PersonType,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?)
                    {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY PersonType
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = ['student_entries' => 0, 'faculty_entries' => 0, 'staff_entries' => 0];
                foreach ($results as $row) {
                    $type = $row['PersonType'] === 'student' ? 'student_entries' : 
                            ($row['PersonType'] === 'faculty' ? 'faculty_entries' : 
                            'staff_entries');
                    if ($row['PersonType'] === 'student' || $row['PersonType'] === 'faculty' || $row['PersonType'] === 'staff') {
                        $data[$type] = (int)$row['count'];
                    }
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("User type distribution error: " . $e->getMessage());
                echo json_encode(['error' => 'Database error']);
            }
            break;

        case 'scanner_activity':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $departmentFilter = getDepartmentWhereClause($department);
                
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        s.ScannerID,
                        s.Location,
                        COUNT(*) as scan_count
                    FROM entrylogs e
                    LEFT JOIN students st ON e.PersonID = st.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff staff ON e.PersonID = staff.StaffID AND e.PersonType = 'staff'
                    JOIN scanner s ON e.ScannerID = s.ScannerID
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) AND s.isActive = 1 {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY s.ScannerID, s.Location
                    ORDER BY scan_count DESC
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['scanners' => $results]);
            } catch (PDOException $e) {
                error_log("Scanner activity error: " . $e->getMessage());
                echo json_encode(['error' => 'Database error']);
            }
            break;

        case 'dashboard_stats':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                $departmentFilter = getDepartmentWhereClause($department);
                $departmentFilterExit = getDepartmentWhereClause($department, 'ex');
                
                // Total entries - timezone safe
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs e LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student' LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty' LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff' WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter} {$departmentFilter}");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $totalEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Total exits
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM exitlogs ex LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student' LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty' LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff' WHERE (DATE(ex.Timestamp) BETWEEN ? AND ? OR ex.Date BETWEEN ? AND ?) {$userTypeFilterExit} {$departmentFilterExit}");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $totalExits = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Unique visitors
                $stmt = $scanner->conn->prepare("SELECT COUNT(DISTINCT e.PersonID) as count FROM entrylogs e LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student' LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty' LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff' WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter} {$departmentFilter}");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $uniqueVisitors = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Peak hour
                $stmt = $scanner->conn->prepare("
                    SELECT HOUR(e.Timestamp) as hour, COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter} {$departmentFilter}
                    GROUP BY HOUR(e.Timestamp)
                    ORDER BY count DESC
                    LIMIT 1
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $peakHourResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $peakHour = $peakHourResult ? $peakHourResult['hour'] . ':00' : 'N/A';
                
                // Busiest day
                $stmt = $scanner->conn->prepare("
                    SELECT DAYNAME(DATE(e.Timestamp)) as day, COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) {$userTypeFilter} {$departmentFilter}
                    GROUP BY DATE(e.Timestamp)
                    ORDER BY count DESC
                    LIMIT 1
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $busiestDayResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $busiestDay = $busiestDayResult ? $busiestDayResult['day'] : 'N/A';
                
                // Average dwell time (simplified - time between entry and exit for same person)
                $stmt = $scanner->conn->prepare("
                    SELECT AVG(TIMESTAMPDIFF(MINUTE, e.Timestamp, ex.Timestamp)) as avg_minutes
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    JOIN exitlogs ex ON e.PersonID = ex.PersonID AND DATE(e.Timestamp) = DATE(ex.Timestamp)
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?) AND TIMESTAMPDIFF(MINUTE, e.Timestamp, ex.Timestamp) > 0 {$userTypeFilter} {$departmentFilter}
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $dwellResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $avgDwellTime = $dwellResult && $dwellResult['avg_minutes'] ? round($dwellResult['avg_minutes'] / 60, 1) . 'h' : '0h';
                
                echo json_encode([
                    'total_entries' => $totalEntries,
                    'total_exits' => $totalExits,
                    'unique_visitors' => $uniqueVisitors,
                    'peak_hour' => $peakHour,
                    'busiest_day' => $busiestDay,
                    'avg_dwell_time' => $avgDwellTime
                ]);
            } catch (PDOException $e) {
                error_log("Dashboard stats error: " . $e->getMessage());
                echo json_encode(['error' => 'Database error']);
            }
            break;
            
        case 'peak_hours_by_day':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $departmentFilter = getDepartmentWhereClause($department);
                
                $query = "
                    SELECT 
                        DATE(e.Timestamp) as date,
                        HOUR(e.Timestamp) as hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?)
                    {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY DATE(e.Timestamp), HOUR(e.Timestamp)
                    ORDER BY DATE(e.Timestamp), HOUR(e.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Format data grouped by date
                $data = [];
                foreach ($results as $row) {
                    $date = $row['date'];
                    if (!isset($data[$date])) {
                        $data[$date] = [];
                    }
                    $data[$date][$row['hour']] = (int)$row['count'];
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Peak hours error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'attempts_summary':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);

                $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
                $typeFilter = '';
                if ($userType !== 'all' && !empty($userType)) {
                    $typeFilter = " AND person_type = ? ";
                    $params[] = $userType;
                }
                
                $departmentFilter = '';
                if ($department !== 'all') {
                    $departmentFilter = " AND person_department = ? ";
                    $params[] = $department;
                }

                $stmt = $scanner->conn->prepare("SELECT status, COUNT(*) as cnt FROM scan_attempts WHERE scanned_at BETWEEN ? AND ? {$typeFilter}{$departmentFilter} GROUP BY status");
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $data = ['success' => 0, 'failed' => 0, 'total' => 0];
                foreach ($rows as $r) {
                    $s = strtolower($r['status']);
                    if ($s === 'success') $data['success'] = (int)$r['cnt'];
                    else $data['failed'] += (int)$r['cnt'];
                    $data['total'] += (int)$r['cnt'];
                }

                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Attempts summary error: " . $e->getMessage());
                echo json_encode(['success' => 0, 'failed' => 0, 'total' => 0]);
            }
            break;

        case 'attempts_by_reason':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);

                $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
                $typeFilter = '';
                if ($userType !== 'all' && !empty($userType)) {
                    $typeFilter = " AND person_type = ? ";
                    $params[] = $userType;
                }
                
                $departmentFilter = '';
                if ($department !== 'all') {
                    $departmentFilter = " AND person_department = ? ";
                    $params[] = $department;
                }

                // Get failed attempts by reason (excluding NULL reasons which are successful scans)
                $stmt = $scanner->conn->prepare("SELECT reason, COUNT(*) as cnt FROM scan_attempts WHERE status = 'failed' AND reason IS NOT NULL AND scanned_at BETWEEN ? AND ? {$typeFilter}{$departmentFilter} GROUP BY reason ORDER BY cnt DESC");
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($rows);
            } catch (PDOException $e) {
                error_log("Attempts by reason error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'recent_failed_attempts':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
                list($startDate, $endDate) = getDateRange($dateRange);

                $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
                $typeFilter = '';
                if ($userType !== 'all' && !empty($userType)) {
                    $typeFilter = " AND person_type = ? ";
                    $params[] = $userType;
                }

                $stmt = $scanner->conn->prepare("SELECT id, scanned_at, qr_data, person_id, person_type, scanner_id, location, reason, meta, ip_address FROM scan_attempts WHERE scanned_at BETWEEN ? AND ? AND status = 'failed' {$typeFilter} ORDER BY scanned_at DESC LIMIT ?");
                $params[] = $limit;
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($rows);
            } catch (PDOException $e) {
                error_log("Recent failed attempts error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'entry_logs_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $departmentFilter = getDepartmentWhereClause($department);
                
                $query = "
                    SELECT 
                        HOUR(e.Timestamp) as hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?)
                    {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY HOUR(e.Timestamp)
                    ORDER BY HOUR(e.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert to array of objects
                $data = [];
                foreach ($results as $row) {
                    $data[] = [
                        'hour' => (int)$row['hour'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Entry logs hourly error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'exit_logs_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'ex');
                $departmentFilter = getDepartmentWhereClause($department);
                
                $query = "
                    SELECT 
                        HOUR(ex.Timestamp) as hour,
                        COUNT(*) as count
                    FROM exitlogs ex
                    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
                    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
                    LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff'
                    WHERE (DATE(ex.Timestamp) BETWEEN ? AND ? OR ex.Date BETWEEN ? AND ?)
                    {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY HOUR(ex.Timestamp)
                    ORDER BY HOUR(ex.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert to array of objects
                $data = [];
                foreach ($results as $row) {
                    $data[] = [
                        'hour' => (int)$row['hour'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Exit logs hourly error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'entry_exit_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                $departmentFilter = getDepartmentWhereClause($department);
                $departmentFilterExit = getDepartmentWhereClause($department, 'ex');
                
                // Get entry data
                $query = "
                    SELECT 
                        HOUR(e.Timestamp) as hour,
                        'entry' as type,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?)
                    {$userTypeFilter}
                    {$departmentFilter}
                    GROUP BY HOUR(e.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $entryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get exit data
                $query = "
                    SELECT 
                        HOUR(ex.Timestamp) as hour,
                        'exit' as type,
                        COUNT(*) as count
                    FROM exitlogs ex
                    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
                    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
                    LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff'
                    WHERE (DATE(ex.Timestamp) BETWEEN ? AND ? OR ex.Date BETWEEN ? AND ?)
                    {$userTypeFilterExit}
                    {$departmentFilterExit}
                    GROUP BY HOUR(ex.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
                $exitResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Merge results
                $data = array_merge($entryResults, $exitResults);
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Entry/Exit hourly error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'recent_entries':
            try {
                $limit = $_GET['limit'] ?? 10;
                $limit = (int)$limit;
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                $dateRange = $_GET['dateRange'] ?? 'today';
                
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $query = "
                    SELECT 
                        e.PersonID,
                        e.PersonType,
                        e.Timestamp,
                        CASE 
                            WHEN e.PersonType = 'student' THEN CONCAT(COALESCE(s.StudentFName, ''), ' ', COALESCE(s.StudentLName, ''))
                            WHEN e.PersonType = 'faculty' THEN CONCAT(COALESCE(f.FacultyFName, ''), ' ', COALESCE(f.FacultyLName, ''))
                            WHEN e.PersonType = 'staff' THEN CONCAT(COALESCE(st.StaffFName, ''), ' ', COALESCE(st.StaffLName, ''))
                            ELSE 'Unknown'
                        END as FullName,
                        e.PersonType
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                    WHERE (DATE(e.Timestamp) BETWEEN ? AND ? OR e.Date BETWEEN ? AND ?)
                ";
                
                $params = [$startDate, $endDate, $startDate, $endDate];
                
                if ($userType !== 'all' && !empty($userType)) {
                    $query .= " AND e.PersonType = ?";
                    $params[] = $userType;
                }
                if ($department !== 'all' && !empty($department)) {
                    $query .= " AND (s.Department = ? OR f.Department = ? OR st.Department = ?)";
                    $params[] = $department;
                    $params[] = $department;
                    $params[] = $department;
                }
                
                $query .= " ORDER BY e.Timestamp DESC LIMIT ?";
                $params[] = $limit;
                    
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute($params);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Recent entries query returned " . count($results) . " results");
                echo json_encode($results);
            } catch (PDOException $e) {
                error_log("Recent entries error: " . $e->getMessage());
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        case 'recent_exits':
            try {
                $limit = $_GET['limit'] ?? 10;
                $limit = (int)$limit;
                $userType = $_GET['userType'] ?? 'all';
                $department = $_GET['department'] ?? 'all';
                $dateRange = $_GET['dateRange'] ?? 'today';
                
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $query = "
                    SELECT 
                        ex.PersonID,
                        ex.PersonType,
                        ex.Timestamp,
                        CASE 
                            WHEN ex.PersonType = 'student' THEN CONCAT(COALESCE(s.StudentFName, ''), ' ', COALESCE(s.StudentLName, ''))
                            WHEN ex.PersonType = 'faculty' THEN CONCAT(COALESCE(f.FacultyFName, ''), ' ', COALESCE(f.FacultyLName, ''))
                            WHEN ex.PersonType = 'staff' THEN CONCAT(COALESCE(st.StaffFName, ''), ' ', COALESCE(st.StaffLName, ''))
                            ELSE 'Unknown'
                        END as FullName,
                        ex.PersonType
                    FROM exitlogs ex
                    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
                    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
                    LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff'
                    WHERE (DATE(ex.Timestamp) BETWEEN ? AND ? OR ex.Date BETWEEN ? AND ?)
                ";
                
                $params = [$startDate, $endDate, $startDate, $endDate];
                
                if ($userType !== 'all' && !empty($userType)) {
                    $query .= " AND ex.PersonType = ?";
                    $params[] = $userType;
                }
                if ($department !== 'all' && !empty($department)) {
                    $query .= " AND (s.Department = ? OR f.Department = ? OR st.Department = ?)";
                    $params[] = $department;
                    $params[] = $department;
                    $params[] = $department;
                }
                
                $query .= " ORDER BY ex.Timestamp DESC LIMIT ?";
                $params[] = $limit;
                    
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute($params);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Recent exits query returned " . count($results) . " results");
                echo json_encode($results);
            } catch (PDOException $e) {
                error_log("Recent exits error: " . $e->getMessage());
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Analytics error: " . $e->getMessage());
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
?>