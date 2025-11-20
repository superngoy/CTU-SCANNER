<?php
require_once '../../includes/functions.php';

try {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        exit('Unauthorized access');
    }

    $scanner = new CTUScanner();
    $startDate = $_GET['start_date'] ?? date('Y-m-d');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception('Invalid date format');
    }

    // Get statistics
    $stats = $scanner->getDailyStats($startDate);
    
    // Get peak hours for the date range
    $peakHours = $scanner->getPeakHours($startDate);
    
    // Get department statistics for the date range
    try {
        $stmt = $scanner->conn->prepare("
            SELECT s.Department, COUNT(*) as entry_count
            FROM entrylogs e
            LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
            WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'student' AND s.Department IS NOT NULL
            GROUP BY s.Department
            UNION ALL
            SELECT f.Department, COUNT(*) as entry_count
            FROM entrylogs e
            LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
            WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'faculty' AND f.Department IS NOT NULL
            GROUP BY f.Department
        ");
        $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
        $departmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $departmentStats = [];
    }
    
    // Get scanner location statistics
    try {
        $stmt = $scanner->conn->prepare("
            SELECT sc.Location, COUNT(*) as entry_count
            FROM entrylogs e
            LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
            WHERE e.Date BETWEEN ? AND ?
            GROUP BY sc.Location
        ");
        $stmt->execute([$startDate, $endDate]);
        $locationStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $locationStats = [];
    }
    
    // Fetch entry logs
    $stmt = $scanner->conn->prepare("
        SELECT e.EntryID, e.PersonID, s.StudentFName, s.StudentMName, s.StudentLName,
               s.Department, e.Date, e.Timestamp, sc.Location, e.PersonType
        FROM entrylogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'student'
        UNION ALL
        SELECT e.EntryID, e.PersonID, f.FacultyFName, f.FacultyMName, f.FacultyLName,
               f.Department, e.Date, e.Timestamp, sc.Location, e.PersonType
        FROM entrylogs e
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'faculty'
        ORDER BY Date DESC, Timestamp DESC
    ");
    $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch exit logs
    $stmt = $scanner->conn->prepare("
        SELECT e.ExitID, e.PersonID, s.StudentFName, s.StudentMName, s.StudentLName,
               s.Department, e.Date, e.Timestamp, sc.Location, e.PersonType
        FROM exitlogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'student'
        UNION ALL
        SELECT e.ExitID, e.PersonID, f.FacultyFName, f.FacultyMName, f.FacultyLName,
               f.Department, e.Date, e.Timestamp, sc.Location, e.PersonType
        FROM exitlogs e
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'faculty'
        ORDER BY Date DESC, Timestamp DESC
    ");
    $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
    $exits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set header to output as HTML for browser printing to PDF
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline;filename="CTU_Scanner_Report_' . $startDate . '_to_' . $endDate . '.html"');
    
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #972529;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #972529;
            font-size: 26px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #555;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .report-period {
            background-color: #ecf0f1;
            padding: 12px;
            border-left: 4px solid #972529;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #972529;
            color: white;
            padding: 12px 15px;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #972529;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #7a1d21;
        }
        
        td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .summary-table th {
            background-color: #972529;
        }
        
        .stat-value {
            font-weight: bold;
            color: #972529;
        }
        
        .no-data {
            text-align: center;
            color: #999;
            padding: 20px;
            font-style: italic;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            font-size: 12px;
            color: #7f8c8d;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }
            .container {
                max-width: 100%;
            }
            .section {
                page-break-inside: avoid;
            }
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CTU Scanner System Report</h1>
            <p>Comprehensive Access Control Report</p>
            <div class="report-period">
                Period: <?php echo date('F d, Y', strtotime($startDate)); ?> to <?php echo date('F d, Y', strtotime($endDate)); ?>
            </div>
            <p>Generated on: <?php echo date('F d, Y \\a\\t H:i:s', time()); ?></p>
        </div>

        <!-- Summary Statistics -->
        <div class="section">
            <div class="section-title">Summary Statistics</div>
            <table class="summary-table">
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
                <tr>
                    <td>Total Entries</td>
                    <td class="stat-value"><?php echo $stats['total_entries'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Total Exits</td>
                    <td class="stat-value"><?php echo $stats['total_exits'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Student Entries</td>
                    <td class="stat-value"><?php echo $stats['student_entries'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Faculty Entries</td>
                    <td class="stat-value"><?php echo $stats['faculty_entries'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>Total Unique Visitors</td>
                    <td class="stat-value"><?php echo $stats['unique_visitors'] ?? 0; ?></td>
                </tr>
            </table>
        </div>

        <!-- Peak Hours Analytics -->
        <div class="section">
            <div class="section-title">Peak Hours Analytics</div>
            <table>
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th>Entry Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($peakHours)) {
                        foreach ($peakHours as $hourData) {
                    ?>
                    <tr>
                        <td><?php echo str_pad($hourData['hour'], 2, '0', STR_PAD_LEFT); ?>:00 - <?php echo str_pad($hourData['hour'], 2, '0', STR_PAD_LEFT); ?>:59</td>
                        <td><?php echo $hourData['count']; ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="2" class="no-data">No peak hour data available</td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Department Analytics -->
        <div class="section">
            <div class="section-title">Department Analytics</div>
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Entry Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($departmentStats)) {
                        foreach ($departmentStats as $dept) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dept['Department'] ?? 'N/A'); ?></td>
                        <td><?php echo $dept['entry_count']; ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="2" class="no-data">No department data available</td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Scanner Location Analytics -->
        <div class="section">
            <div class="section-title">Scanner Location Analytics</div>
            <table>
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Entry Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($locationStats)) {
                        foreach ($locationStats as $location) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($location['Location'] ?? 'N/A'); ?></td>
                        <td><?php echo $location['entry_count']; ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="2" class="no-data">No location data available</td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Entry Logs -->
        <div class="section">
            <div class="section-title">Entry Logs</div>
            <table>
                <thead>
                    <tr>
                        <th>Entry ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($entries)) {
                        foreach ($entries as $entry) {
                            $fname = $entry['StudentFName'] ?? $entry['FacultyFName'] ?? '';
                            $mname = $entry['StudentMName'] ?? $entry['FacultyMName'] ?? '';
                            $lname = $entry['StudentLName'] ?? $entry['FacultyLName'] ?? '';
                            $name = trim($fname . ' ' . $mname . ' ' . $lname);
                            $time = date('H:i:s', strtotime($entry['Timestamp']));
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['EntryID'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($name); ?></td>
                        <td><?php echo ucfirst($entry['PersonType']); ?></td>
                        <td><?php echo htmlspecialchars($entry['Department'] ?? 'N/A'); ?></td>
                        <td><?php echo $entry['Date']; ?></td>
                        <td><?php echo $time; ?></td>
                        <td><?php echo htmlspecialchars($entry['Location'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="7" class="no-data">No entry records found</td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Exit Logs -->
        <div class="section">
            <div class="section-title">Exit Logs</div>
            <table>
                <thead>
                    <tr>
                        <th>Exit ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($exits)) {
                        foreach ($exits as $exit) {
                            $fname = $exit['StudentFName'] ?? $exit['FacultyFName'] ?? '';
                            $mname = $exit['StudentMName'] ?? $exit['FacultyMName'] ?? '';
                            $lname = $exit['StudentLName'] ?? $exit['FacultyLName'] ?? '';
                            $name = trim($fname . ' ' . $mname . ' ' . $lname);
                            $time = date('H:i:s', strtotime($exit['Timestamp']));
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exit['ExitID'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($name); ?></td>
                        <td><?php echo ucfirst($exit['PersonType']); ?></td>
                        <td><?php echo htmlspecialchars($exit['Department'] ?? 'N/A'); ?></td>
                        <td><?php echo $exit['Date']; ?></td>
                        <td><?php echo $time; ?></td>
                        <td><?php echo htmlspecialchars($exit['Location'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php 
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="7" class="no-data">No exit records found</td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p><strong>CTU Scanner System</strong> | Access Control Report</p>
            <p>Report generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <div style="text-align: center; padding: 20px; background-color: #f0f0f0; margin-top: 20px; border-top: 2px solid #ddd; print:hidden;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #972529; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin: 5px;">
            🖨️ Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #666; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin: 5px;">
            ✕ Close
        </button>
    </div>
    
    <script>
        // Check if auto-print parameter is set
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            // Auto-print after a short delay to ensure content is fully loaded
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
    <style media="print">
        .no-print {
            display: none !important;
        }
        button {
            display: none !important;
        }
    </style>
</body>
</html>
<?php

} catch (Exception $e) {
    http_response_code(500);
    die('Error generating PDF: ' . htmlspecialchars($e->getMessage()));
}
?>