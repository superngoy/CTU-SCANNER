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

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="CTU_Scanner_Report_' . $startDate . '_to_' . $endDate . '.xls"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');

    echo '<html>';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<style>';
    echo 'table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; }';
    echo 'th { background-color: #972529; color: white; padding: 10px; text-align: left; border: 1px solid #7a1d21; }';
    echo 'td { padding: 8px 10px; border: 1px solid #dee2e6; }';
    echo 'tr:nth-child(even) { background-color: #f8f9fa; }';
    echo '.header { background-color: #972529; color: white; padding: 20px; text-align: center; }';
    echo '.section-title { background-color: #972529; color: white; padding: 10px; font-weight: bold; }';
    echo '</style>';
    
    echo '<table>';
    echo '<tr>';
    echo '<td colspan="8" class="header"><h2>CTU Scanner System Report</h2>';
    echo '<p>Period: ' . date('F d, Y', strtotime($startDate)) . ' to ' . date('F d, Y', strtotime($endDate)) . '</p>';
    echo '<p>Generated on: ' . date('F d, Y \\a\\t H:i:s', time()) . '</p></td>';
    echo '</tr>';
    echo '</table>';

    // Get statistics
    $stats = $scanner->getDailyStats($startDate);
    
    // Get analytics data - peak hours
    $peakHours = $scanner->getPeakHours($startDate);
    
    // Get department statistics
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
    
    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="8" class="section-title">Summary Statistics</td></tr>';
    echo '<tr><th>Metric</th><th>Count</th></tr>';
    echo '<tr><td>Total Entries</td><td>' . ($stats['total_entries'] ?? 0) . '</td></tr>';
    echo '<tr><td>Total Exits</td><td>' . ($stats['total_exits'] ?? 0) . '</td></tr>';
    echo '<tr><td>Student Entries</td><td>' . ($stats['student_entries'] ?? 0) . '</td></tr>';
    echo '<tr><td>Faculty Entries</td><td>' . ($stats['faculty_entries'] ?? 0) . '</td></tr>';
    echo '<tr><td>Total Unique Visitors</td><td>' . ($stats['unique_visitors'] ?? 0) . '</td></tr>';
    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="8" class="section-title">Analytics - Peak Hours</td></tr>';
    echo '<tr><th>Hour</th><th>Entry Count</th></tr>';
    
    if (!empty($peakHours) && is_array($peakHours)) {
        foreach ($peakHours as $hourData) {
            echo '<tr>';
            echo '<td>' . str_pad($hourData['hour'], 2, '0', STR_PAD_LEFT) . ':00 - ' . str_pad($hourData['hour'], 2, '0', STR_PAD_LEFT) . ':59</td>';
            echo '<td>' . $hourData['count'] . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2" style="text-align: center; color: #999;">No peak hour data available</td></tr>';
    }
    echo '</table>';

    // Department Statistics
    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="2" class="section-title">Analytics - Department Statistics</td></tr>';
    echo '<tr><th>Department</th><th>Entry Count</th></tr>';
    
    if (!empty($departmentStats)) {
        foreach ($departmentStats as $dept) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($dept['Department'] ?? 'N/A') . '</td>';
            echo '<td>' . $dept['entry_count'] . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2" style="text-align: center; color: #999;">No department data available</td></tr>';
    }
    echo '</table>';
    
    // Scanner Location Statistics
    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="2" class="section-title">Analytics - Scanner Location Statistics</td></tr>';
    echo '<tr><th>Location</th><th>Entry Count</th></tr>';
    
    if (!empty($locationStats)) {
        foreach ($locationStats as $location) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($location['Location'] ?? 'N/A') . '</td>';
            echo '<td>' . $location['entry_count'] . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2" style="text-align: center; color: #999;">No location data available</td></tr>';
    }
    echo '</table>';

    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="8" class="section-title">Entry Logs</td></tr>';
    echo '<tr>';
    echo '<th>Entry ID</th>';
    echo '<th>Person ID</th>';
    echo '<th>Name</th>';
    echo '<th>Type</th>';
    echo '<th>Department</th>';
    echo '<th>Date</th>';
    echo '<th>Time</th>';
    echo '<th>Location</th>';
    echo '</tr>';

    try {
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

        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $fname = $entry['StudentFName'] ?? $entry['FacultyFName'] ?? '';
                $mname = $entry['StudentMName'] ?? $entry['FacultyMName'] ?? '';
                $lname = $entry['StudentLName'] ?? $entry['FacultyLName'] ?? '';
                $name = trim($fname . ' ' . $mname . ' ' . $lname);
                $time = date('H:i:s', strtotime($entry['Timestamp']));
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($entry['EntryID'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($entry['PersonID'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($name) . '</td>';
                echo '<td>' . ucfirst($entry['PersonType']) . '</td>';
                echo '<td>' . htmlspecialchars($entry['Department'] ?? 'N/A') . '</td>';
                echo '<td>' . $entry['Date'] . '</td>';
                echo '<td>' . $time . '</td>';
                echo '<td>' . htmlspecialchars($entry['Location'] ?? 'N/A') . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8" style="text-align: center; color: #999;">No entry records found</td></tr>';
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="8" style="text-align: center; color: red;">Error loading entry data</td></tr>';
    }

    echo '</table>';

    // Exit Logs
    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="8" class="section-title">Exit Logs</td></tr>';
    echo '<tr>';
    echo '<th>Exit ID</th>';
    echo '<th>Person ID</th>';
    echo '<th>Name</th>';
    echo '<th>Type</th>';
    echo '<th>Department</th>';
    echo '<th>Date</th>';
    echo '<th>Time</th>';
    echo '<th>Location</th>';
    echo '</tr>';

    try {
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

        if (!empty($exits)) {
            foreach ($exits as $exit) {
                $fname = $exit['StudentFName'] ?? $exit['FacultyFName'] ?? '';
                $mname = $exit['StudentMName'] ?? $exit['FacultyMName'] ?? '';
                $lname = $exit['StudentLName'] ?? $exit['FacultyLName'] ?? '';
                $name = trim($fname . ' ' . $mname . ' ' . $lname);
                $time = date('H:i:s', strtotime($exit['Timestamp']));
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($exit['ExitID'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($exit['PersonID'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($name) . '</td>';
                echo '<td>' . ucfirst($exit['PersonType']) . '</td>';
                echo '<td>' . htmlspecialchars($exit['Department'] ?? 'N/A') . '</td>';
                echo '<td>' . $exit['Date'] . '</td>';
                echo '<td>' . $time . '</td>';
                echo '<td>' . htmlspecialchars($exit['Location'] ?? 'N/A') . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8" style="text-align: center; color: #999;">No exit records found</td></tr>';
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="8" style="text-align: center; color: red;">Error loading exit data</td></tr>';
    }

    echo '</table>';
    
    echo '<br/>';
    echo '<table>';
    echo '<tr><td colspan="8" style="text-align: center; font-size: 11px; color: #666; border: none;">';
    echo '<p>CTU Scanner System | Access Control Report</p>';
    echo '<p>Generated on ' . date('Y-m-d H:i:s') . '</p>';
    echo '</td></tr>';
    echo '</table>';

    echo '</body>';
    echo '</html>';

} catch (Exception $e) {
    http_response_code(500);
    die('Error generating Excel: ' . htmlspecialchars($e->getMessage()));
}
?>