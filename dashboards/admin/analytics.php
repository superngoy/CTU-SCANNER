<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

// Helper function to get date range based on filter
function getDateRange($dateRange) {
    $today = date('Y-m-d');
    switch ($dateRange) {
        case 'today':
            return [$today, $today];
        case 'week':
            $startOfWeek = date('Y-m-d', strtotime('monday this week'));
            return [$startOfWeek, $today];
        case 'month':
            $startOfMonth = date('Y-m-01');
            return [$startOfMonth, $today];
        case 'year':
            $startOfYear = date('Y-01-01');
            return [$startOfYear, $today];
        default:
            return [$today, $today];
    }
}

// Helper function to build WHERE clause for userType filter
function getUserTypeWhereClause($userType, $tableAlias = 'e') {
    if ($userType === 'all' || empty($userType)) {
        return '';
    }
    if ($userType === 'student' || $userType === 'faculty') {
        return " AND {$tableAlias}.PersonType = '{$userType}'";
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                // Fixed query to properly aggregate department data
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        COALESCE(s.Department, f.Department) as Department,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    WHERE e.Date BETWEEN ? AND ?
                    GROUP BY COALESCE(s.Department, f.Department)
                ");
                $stmt->execute([$startDate, $endDate]);
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
                        COALESCE(s.Department, f.Department) as Department,
                        HOUR(e.Timestamp) as Hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    WHERE e.Date BETWEEN ? AND ?
                    GROUP BY COALESCE(s.Department, f.Department), HOUR(e.Timestamp)
                    ORDER BY Hour
                ");
                $stmt->execute([$startDate, $endDate]);
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
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        'entries' as type,
                        COALESCE(s.Department, f.Department) as Department,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    WHERE e.Date BETWEEN ? AND ?
                    GROUP BY COALESCE(s.Department, f.Department)
                    UNION ALL
                    SELECT 
                        'exits' as type,
                        COALESCE(s.Department, f.Department) as Department,
                        COUNT(*) as count
                    FROM exitlogs ex
                    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
                    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
                    WHERE ex.Date BETWEEN ? AND ?
                    GROUP BY COALESCE(s.Department, f.Department)
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $stmt = $scanner->conn->prepare("
                    SELECT DAYOFWEEK(Date) as day, COUNT(*) as count
                    FROM entrylogs
                    WHERE Date BETWEEN ? AND ?
                    GROUP BY DAYOFWEEK(Date)
                    ORDER BY day
                ");
                $stmt->execute([$startDate, $endDate]);
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
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ?");
                $stmt->execute([$startDate, $endDate]);
                $totalEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Total exits
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE Date BETWEEN ? AND ?");
                $stmt->execute([$startDate, $endDate]);
                $totalExits = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Student entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'student'");
                $stmt->execute([$startDate, $endDate]);
                $studentEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Faculty entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'faculty'");
                $stmt->execute([$startDate, $endDate]);
                $facultyEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo json_encode([
                    'total_entries' => $totalEntries,
                    'total_exits' => $totalExits,
                    'student_entries' => $studentEntries,
                    'faculty_entries' => $facultyEntries
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        (SELECT COUNT(*) FROM entrylogs e WHERE e.Date BETWEEN ? AND ? {$userTypeFilter}) as total_entries,
                        (SELECT COUNT(*) FROM exitlogs ex WHERE ex.Date BETWEEN ? AND ? {$userTypeFilterExit}) as total_exits
                ");
                $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                
                $query = "
                    SELECT 
                        PersonType,
                        COUNT(*) as count
                    FROM entrylogs e
                    WHERE e.Date BETWEEN ? AND ?
                    {$userTypeFilter}
                    GROUP BY PersonType
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = ['student_entries' => 0, 'faculty_entries' => 0, 'security_entries' => 0];
                foreach ($results as $row) {
                    $type = $row['PersonType'] === 'student' ? 'student_entries' : 
                            ($row['PersonType'] === 'faculty' ? 'faculty_entries' : 'security_entries');
                    $data[$type] = (int)$row['count'];
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        s.ScannerID,
                        s.Location,
                        COUNT(*) as scan_count
                    FROM entrylogs e
                    JOIN scanner s ON e.ScannerID = s.ScannerID
                    WHERE e.Date BETWEEN ? AND ? AND s.isActive = 1 {$userTypeFilter}
                    GROUP BY s.ScannerID, s.Location
                    ORDER BY scan_count DESC
                ");
                $stmt->execute([$startDate, $endDate]);
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                
                // Total entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs e WHERE e.Date BETWEEN ? AND ? {$userTypeFilter}");
                $stmt->execute([$startDate, $endDate]);
                $totalEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Total exits
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM exitlogs ex WHERE ex.Date BETWEEN ? AND ? {$userTypeFilterExit}");
                $stmt->execute([$startDate, $endDate]);
                $totalExits = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Unique visitors
                $stmt = $scanner->conn->prepare("SELECT COUNT(DISTINCT e.PersonID) as count FROM entrylogs e WHERE e.Date BETWEEN ? AND ? {$userTypeFilter}");
                $stmt->execute([$startDate, $endDate]);
                $uniqueVisitors = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Peak hour
                $stmt = $scanner->conn->prepare("
                    SELECT HOUR(e.Timestamp) as hour, COUNT(*) as count
                    FROM entrylogs e
                    WHERE e.Date BETWEEN ? AND ? {$userTypeFilter}
                    GROUP BY HOUR(e.Timestamp)
                    ORDER BY count DESC
                    LIMIT 1
                ");
                $stmt->execute([$startDate, $endDate]);
                $peakHourResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $peakHour = $peakHourResult ? $peakHourResult['hour'] . ':00' : 'N/A';
                
                // Busiest day
                $stmt = $scanner->conn->prepare("
                    SELECT DAYNAME(e.Date) as day, COUNT(*) as count
                    FROM entrylogs e
                    WHERE e.Date BETWEEN ? AND ? {$userTypeFilter}
                    GROUP BY DATE(e.Date)
                    ORDER BY count DESC
                    LIMIT 1
                ");
                $stmt->execute([$startDate, $endDate]);
                $busiestDayResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $busiestDay = $busiestDayResult ? $busiestDayResult['day'] : 'N/A';
                
                // Average dwell time (simplified - time between entry and exit for same person)
                $stmt = $scanner->conn->prepare("
                    SELECT AVG(TIMESTAMPDIFF(MINUTE, e.Timestamp, ex.Timestamp)) as avg_minutes
                    FROM entrylogs e
                    JOIN exitlogs ex ON e.PersonID = ex.PersonID AND DATE(e.Date) = DATE(ex.Date)
                    WHERE e.Date BETWEEN ? AND ? AND TIMESTAMPDIFF(MINUTE, e.Timestamp, ex.Timestamp) > 0 {$userTypeFilter}
                ");
                $stmt->execute([$startDate, $endDate]);
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                
                $query = "
                    SELECT 
                        DATE(e.Date) as date,
                        HOUR(e.Timestamp) as hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    WHERE e.Date BETWEEN ? AND ?
                    {$userTypeFilter}
                    GROUP BY DATE(e.Date), HOUR(e.Timestamp)
                    ORDER BY DATE(e.Date), HOUR(e.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate]);
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

        case 'entry_logs_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                $userType = $_GET['userType'] ?? 'all';
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                
                $query = "
                    SELECT 
                        HOUR(e.Timestamp) as hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    WHERE e.Date BETWEEN ? AND ?
                    {$userTypeFilter}
                    GROUP BY HOUR(e.Timestamp)
                    ORDER BY HOUR(e.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate]);
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'ex');
                
                $query = "
                    SELECT 
                        HOUR(ex.Timestamp) as hour,
                        COUNT(*) as count
                    FROM exitlogs ex
                    WHERE ex.Date BETWEEN ? AND ?
                    {$userTypeFilter}
                    GROUP BY HOUR(ex.Timestamp)
                    ORDER BY HOUR(ex.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate]);
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
                list($startDate, $endDate) = getDateRange($dateRange);
                
                $userTypeFilter = getUserTypeWhereClause($userType, 'e');
                $userTypeFilterExit = getUserTypeWhereClause($userType, 'ex');
                
                // Get entry data
                $query = "
                    SELECT 
                        HOUR(e.Timestamp) as hour,
                        'entry' as type,
                        COUNT(*) as count
                    FROM entrylogs e
                    WHERE e.Date BETWEEN ? AND ?
                    {$userTypeFilter}
                    GROUP BY HOUR(e.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate]);
                $entryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get exit data
                $query = "
                    SELECT 
                        HOUR(ex.Timestamp) as hour,
                        'exit' as type,
                        COUNT(*) as count
                    FROM exitlogs ex
                    WHERE ex.Date BETWEEN ? AND ?
                    {$userTypeFilterExit}
                    GROUP BY HOUR(ex.Timestamp)
                ";
                
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute([$startDate, $endDate]);
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
                
                // LIMIT cannot use parameter placeholders in MySQL, must use string concatenation
                $whereClause = '';
                if ($userType !== 'all') {
                    $whereClause = " WHERE e.PersonType = '" . $scanner->conn->quote($userType) . "'";
                }
                
                $query = "
                    SELECT 
                        e.PersonID,
                        e.PersonType,
                        e.Timestamp,
                        CASE 
                            WHEN e.PersonType = 'student' THEN CONCAT(s.StudentFName, ' ', s.StudentLName)
                            WHEN e.PersonType = 'faculty' THEN CONCAT(f.FacultyFName, ' ', f.FacultyLName)
                            ELSE 'Unknown'
                        END as FullName,
                        e.PersonType
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    " . $whereClause . "
                    ORDER BY e.Timestamp DESC
                    LIMIT " . $limit;
                    
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute();
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
                
                // LIMIT cannot use parameter placeholders in MySQL, must use string concatenation
                $whereClause = '';
                if ($userType !== 'all') {
                    $whereClause = " WHERE ex.PersonType = '" . $scanner->conn->quote($userType) . "'";
                }
                
                $query = "
                    SELECT 
                        ex.PersonID,
                        ex.PersonType,
                        ex.Timestamp,
                        CASE 
                            WHEN ex.PersonType = 'student' THEN CONCAT(s.StudentFName, ' ', s.StudentLName)
                            WHEN ex.PersonType = 'faculty' THEN CONCAT(f.FacultyFName, ' ', f.FacultyLName)
                            ELSE 'Unknown'
                        END as FullName,
                        ex.PersonType
                    FROM exitlogs ex
                    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
                    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
                    " . $whereClause . "
                    ORDER BY ex.Timestamp DESC
                    LIMIT " . $limit;
                    
                $stmt = $scanner->conn->prepare($query);
                $stmt->execute();
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