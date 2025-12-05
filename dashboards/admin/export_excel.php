<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Unauthorized access';
    exit();
}

require_once '../../includes/functions.php';

// Get date range from query parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate dates
if (empty($startDate) || empty($endDate)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Date range is required';
    exit();
}

try {
    $scanner = new CTUScanner();
    
    // Fetch entry logs
    $stmt = $scanner->conn->prepare("
        SELECT 
            e.PersonID,
            e.PersonType,
            CASE 
                WHEN e.PersonType = 'student' THEN CONCAT(s.StudentFName, ' ', COALESCE(s.StudentMName, ''), ' ', s.StudentLName)
                WHEN e.PersonType = 'faculty' THEN CONCAT(f.FacultyFName, ' ', COALESCE(f.FacultyMName, ''), ' ', f.FacultyLName)
                WHEN e.PersonType = 'staff' THEN CONCAT(st.StaffFName, ' ', COALESCE(st.StaffMName, ''), ' ', st.StaffLName)
                ELSE 'Unknown'
            END as FullName,
            CASE 
                WHEN e.PersonType = 'student' THEN s.Department
                WHEN e.PersonType = 'faculty' THEN f.Department
                WHEN e.PersonType = 'staff' THEN st.Department
                ELSE 'N/A'
            END as Department,
            e.Date,
            e.Timestamp,
            e.ScannerID,
            'Entry' as LogType
        FROM entrylogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
        WHERE e.Date BETWEEN ? AND ?
        ORDER BY e.Timestamp DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch exit logs
    $stmt = $scanner->conn->prepare("
        SELECT 
            ex.PersonID,
            ex.PersonType,
            CASE 
                WHEN ex.PersonType = 'student' THEN CONCAT(s.StudentFName, ' ', COALESCE(s.StudentMName, ''), ' ', s.StudentLName)
                WHEN ex.PersonType = 'faculty' THEN CONCAT(f.FacultyFName, ' ', COALESCE(f.FacultyMName, ''), ' ', f.FacultyLName)
                WHEN ex.PersonType = 'staff' THEN CONCAT(st.StaffFName, ' ', COALESCE(st.StaffMName, ''), ' ', st.StaffLName)
                ELSE 'Unknown'
            END as FullName,
            CASE 
                WHEN ex.PersonType = 'student' THEN s.Department
                WHEN ex.PersonType = 'faculty' THEN f.Department
                WHEN ex.PersonType = 'staff' THEN st.Department
                ELSE 'N/A'
            END as Department,
            ex.Date,
            ex.Timestamp,
            ex.ScannerID,
            'Exit' as LogType
        FROM exitlogs ex
        LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
        LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
        LEFT JOIN staff st ON ex.PersonID = st.StaffID AND ex.PersonType = 'staff'
        WHERE ex.Date BETWEEN ? AND ?
        ORDER BY ex.Timestamp DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    $exits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine and sort all logs
    $allLogs = array_merge($entries, $exits);
    usort($allLogs, function($a, $b) {
        return strtotime($b['Timestamp']) - strtotime($a['Timestamp']);
    });
    
    // Generate HTML Table for Excel with styling
    $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
    body { font-family: Arial, Helvetica, sans-serif; }
    table { border-collapse: collapse; width: 100%; }
    th { 
        background-color: #972529; 
        color: white; 
        padding: 12px; 
        text-align: left; 
        border: 1px solid #666;
        font-weight: bold;
    }
    td { 
        padding: 8px 12px; 
        border: 1px solid #ddd;
    }
    .title { 
        background-color: #972529; 
        color: white; 
        font-size: 14px; 
        font-weight: bold; 
        padding: 12px;
        border: 1px solid #666;
    }
    .entry { background-color: #E8F5E9; color: #157A3B; }
    .exit { background-color: #FFEBEE; color: #B71C1C; }
    .summary-label { 
        background-color: #E5C573; 
        font-weight: bold; 
        border: 1px solid #972529;
        padding: 8px 12px;
    }
    .summary-value { 
        background-color: #972529; 
        color: white; 
        font-weight: bold;
        border: 1px solid #972529;
        padding: 8px 12px;
        text-align: center;
    }
    .spacer { height: 20px; }
</style>
</head>
<body>
<table>
    <tr><td colspan="8" class="title">Campus Activity Report</td></tr>
    <tr><td colspan="8" class="title">Period: ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate)) . '</td></tr>
    <tr class="spacer"><td colspan="8"></td></tr>
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Type</th>
        <th>Department</th>
        <th>Date</th>
        <th>Time</th>
        <th>Scanner</th>
        <th>Log Type</th>
    </tr>';
    
    foreach ($allLogs as $log) {
        $rowClass = ($log['LogType'] === 'Entry') ? 'entry' : 'exit';
        $html .= '<tr class="' . $rowClass . '">
            <td>' . htmlspecialchars($log['PersonID']) . '</td>
            <td>' . htmlspecialchars($log['FullName']) . '</td>
            <td>' . ucfirst(htmlspecialchars($log['PersonType'])) . '</td>
            <td>' . htmlspecialchars($log['Department']) . '</td>
            <td>' . htmlspecialchars($log['Date']) . '</td>
            <td>' . date('H:i:s', strtotime($log['Timestamp'])) . '</td>
            <td>' . htmlspecialchars($log['ScannerID'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($log['LogType']) . '</td>
        </tr>';
    }
    
    $html .= '<tr class="spacer"><td colspan="8"></td></tr>
    <tr><td colspan="2" class="title">SUMMARY STATISTICS</td></tr>
    <tr>
        <td class="summary-label">Total Entries</td>
        <td class="summary-value">' . count($entries) . '</td>
    </tr>
    <tr>
        <td class="summary-label">Total Exits</td>
        <td class="summary-value">' . count($exits) . '</td>
    </tr>
    <tr>
        <td class="summary-label">Total Records</td>
        <td class="summary-value">' . count($allLogs) . '</td>
    </tr>';
    
    $byType = array_count_values(array_column($allLogs, 'PersonType'));
    foreach ($byType as $type => $count) {
        $html .= '<tr>
            <td class="summary-label">' . ucfirst($type) . '(s)</td>
            <td class="summary-value">' . $count . '</td>
        </tr>';
    }
    
    $html .= '</table>
</body>
</html>';
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="Campus_Activity_Report_' . $startDate . '_to_' . $endDate . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    
    // Output HTML
    echo $html;
    exit();
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>
