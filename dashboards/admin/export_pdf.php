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
    
    // Generate basic HTML that can be converted to PDF
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Campus Activity Report</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            .header {
                text-align: center;
                border-bottom: 3px solid #972529;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .header h1 {
                margin: 0;
                color: #972529;
            }
            .header p {
                margin: 5px 0;
                color: #666;
            }
            .summary {
                background: #f5f5f5;
                padding: 15px;
                border-left: 4px solid #972529;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .summary-row {
                display: flex;
                justify-content: space-between;
                margin: 5px 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th {
                background: #972529;
                color: white;
                padding: 12px;
                text-align: left;
                font-weight: bold;
            }
            td {
                padding: 10px 12px;
                border-bottom: 1px solid #ddd;
            }
            tr:nth-child(even) {
                background: #f9f9f9;
            }
            tr:hover {
                background: #f0f0f0;
            }
            .entry {
                color: #28a745;
            }
            .exit {
                color: #dc3545;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #ddd;
                padding-top: 15px;
            }
            .stats {
                display: flex;
                gap: 20px;
                margin: 20px 0;
                flex-wrap: wrap;
            }
            .stat-box {
                flex: 1;
                min-width: 200px;
                background: white;
                border: 2px solid #E5C573;
                padding: 15px;
                border-radius: 4px;
                text-align: center;
            }
            .stat-box h3 {
                margin: 0;
                color: #972529;
                font-size: 32px;
            }
            .stat-box p {
                margin: 5px 0 0 0;
                color: #666;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Campus Activity Report</h1>
            <p>CTU Scanner System</p>
            <p>Report Period: ' . date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate)) . '</p>
            <p>Generated: ' . date('F d, Y H:i:s A') . '</p>
        </div>
        
        <div class="stats">';
    
    // Add statistics
    $byType = array_count_values(array_column($allLogs, 'PersonType'));
    $html .= '<div class="stat-box">
                <h3>' . count($entries) . '</h3>
                <p>Total Entries</p>
            </div>
            <div class="stat-box">
                <h3>' . count($exits) . '</h3>
                <p>Total Exits</p>
            </div>
            <div class="stat-box">
                <h3>' . count($allLogs) . '</h3>
                <p>Total Records</p>
            </div>';
    
    $html .= '</div>
        
        <div class="summary">
            <h3 style="margin-top: 0; color: #972529;">Summary Statistics</h3>';
    
    $html .= '<div class="summary-row">
                <span><strong>Total Entries:</strong></span>
                <span>' . count($entries) . '</span>
            </div>
            <div class="summary-row">
                <span><strong>Total Exits:</strong></span>
                <span>' . count($exits) . '</span>
            </div>
            <div class="summary-row">
                <span><strong>Total Records:</strong></span>
                <span>' . count($allLogs) . '</span>
            </div>';
    
    foreach ($byType as $type => $count) {
        $html .= '<div class="summary-row">
                    <span><strong>' . ucfirst($type) . '(s):</strong></span>
                    <span>' . $count . '</span>
                </div>';
    }
    
    $html .= '</div>
        
        <h3 style="color: #972529; border-bottom: 2px solid #E5C573; padding-bottom: 10px;">Activity Logs</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Scanner</th>
                    <th>Log Type</th>
                </tr>
            </thead>
            <tbody>';
    
    $count = 0;
    foreach ($allLogs as $log) {
        $count++;
        if ($count > 500) { // Limit to 500 records for PDF to avoid too large files
            break;
        }
        
        $logTypeClass = strtolower($log['LogType']);
        $html .= '<tr>
                    <td>' . htmlspecialchars($log['PersonID']) . '</td>
                    <td>' . htmlspecialchars($log['FullName']) . '</td>
                    <td>' . ucfirst(htmlspecialchars($log['PersonType'])) . '</td>
                    <td>' . htmlspecialchars($log['Department']) . '</td>
                    <td>' . htmlspecialchars($log['Date']) . '</td>
                    <td>' . date('H:i:s', strtotime($log['Timestamp'])) . '</td>
                    <td>' . htmlspecialchars($log['ScannerID'] ?? 'N/A') . '</td>
                    <td><span class="' . $logTypeClass . '">' . htmlspecialchars($log['LogType']) . '</span></td>
                </tr>';
    }
    
    if (count($allLogs) > 500) {
        $html .= '<tr>
                    <td colspan="8" style="text-align: center; color: #999; font-style: italic;">
                        ... and ' . (count($allLogs) - 500) . ' more records (showing first 500 for PDF)
                    </td>
                </tr>';
    }
    
    $html .= '  </tbody>
        </table>
        
        <div class="footer">
            <p>This is an official Campus Activity Report generated by the CTU Scanner System.</p>
            <p>For more information or to request a complete report, contact the administration.</p>
            <p style="margin-top: 20px; color: #999;">Report ID: ' . md5($startDate . $endDate . time()) . '</p>
        </div>
    </body>
    </html>';
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Campus_Activity_Report_' . $startDate . '_to_' . $endDate . '.html"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output HTML (which browsers can print as PDF using Print to PDF)
    echo $html;
    exit();
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>
