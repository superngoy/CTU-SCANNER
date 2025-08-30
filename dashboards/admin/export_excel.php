<?php
require_once '../../includes/functions.php';

$scanner = new CTUScanner();
$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="CTU_Scanner_Report_' . $startDate . '_to_' . $endDate . '.xls"');
header('Cache-Control: max-age=0');

echo '<html>';
echo '<head><meta charset="UTF-8"></head>';
echo '<body>';
echo '<table border="1">';
echo '<tr style="background-color: #4CAF50; color: white;">';
echo '<th colspan="8">CTU Scanner Report (' . $startDate . ' to ' . $endDate . ')</th>';
echo '</tr>';

// Entry Logs
echo '<tr style="background-color: #2196F3; color: white;">';
echo '<th colspan="8">ENTRY LOGS</th>';
echo '</tr>';

echo '<tr style="background-color: #f2f2f2;">';
echo '<th>Entry ID</th><th>Person ID</th><th>Name</th><th>Type</th><th>Date</th><th>Time</th><th>Scanner</th><th>Department</th>';
echo '</tr>';

try {
    $stmt = $scanner->conn->prepare("
        SELECT e.*, s.StudentFName, s.StudentMName, s.StudentLName, s.Department, sc.Location
        FROM entrylogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'student'
        UNION ALL
        SELECT e.*, f.FacultyFName, f.FacultyMName, f.FacultyLName, f.Department, sc.Location
        FROM entrylogs e
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'faculty'
        ORDER BY Date DESC, Timestamp DESC
    ");
    $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($entries as $entry) {
        $name = ($entry['StudentFName'] ?? $entry['FacultyFName']) . ' ' . 
               ($entry['StudentMName'] ?? $entry['FacultyMName']) . ' ' . 
               ($entry['StudentLName'] ?? $entry['FacultyLName']);
        
        echo '<tr>';
        echo '<td>' . $entry['EntryID'] . '</td>';
        echo '<td>' . $entry['PersonID'] . '</td>';
        echo '<td>' . $name . '</td>';
        echo '<td>' . ucfirst($entry['PersonType']) . '</td>';
        echo '<td>' . $entry['Date'] . '</td>';
        echo '<td>' . date('H:i:s', strtotime($entry['Timestamp'])) . '</td>';
        echo '<td>' . ($entry['Location'] ?? 'N/A') . '</td>';
        echo '<td>' . ($entry['Department'] ?? 'N/A') . '</td>';
        echo '</tr>';
    }
} catch (PDOException $e) {
    echo '<tr><td colspan="8">Error loading entry data</td></tr>';
}

// Exit Logs
echo '<tr><td colspan="8"></td></tr>';
echo '<tr style="background-color: #FF9800; color: white;">';
echo '<th colspan="8">EXIT LOGS</th>';
echo '</tr>';

echo '<tr style="background-color: #f2f2f2;">';
echo '<th>Exit ID</th><th>Person ID</th><th>Name</th><th>Type</th><th>Date</th><th>Time</th><th>Scanner</th><th>Department</th>';
echo '</tr>';

try {
    $stmt = $scanner->conn->prepare("
        SELECT e.*, s.StudentFName, s.StudentMName, s.StudentLName, s.Department, sc.Location
        FROM exitlogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'student'
        UNION ALL
        SELECT e.*, f.FacultyFName, f.FacultyMName, f.FacultyLName, f.Department, sc.Location
        FROM exitlogs e
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        LEFT JOIN scanner sc ON e.ScannerID = sc.ScannerID
        WHERE e.Date BETWEEN ? AND ? AND e.PersonType = 'faculty'
        ORDER BY Date DESC, Timestamp DESC
    ");
    $stmt->execute([$startDate, $endDate, $startDate, $endDate]);
    $exits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($exits as $exit) {
        $name = ($exit['StudentFName'] ?? $exit['FacultyFName']) . ' ' . 
               ($exit['StudentMName'] ?? $exit['FacultyMName']) . ' ' . 
               ($exit['StudentLName'] ?? $exit['FacultyLName']);
        
        echo '<tr>';
        echo '<td>' . $exit['ExitID'] . '</td>';
        echo '<td>' . $exit['PersonID'] . '</td>';
        echo '<td>' . $name . '</td>';
        echo '<td>' . ucfirst($exit['PersonType']) . '</td>';
        echo '<td>' . $exit['Date'] . '</td>';
        echo '<td>' . date('H:i:s', strtotime($exit['Timestamp'])) . '</td>';
        echo '<td>' . ($exit['Location'] ?? 'N/A') . '</td>';
        echo '<td>' . ($exit['Department'] ?? 'N/A') . '</td>';
        echo '</tr>';
    }
} catch (PDOException $e) {
    echo '<tr><td colspan="8">Error loading exit data</td></tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';
?>