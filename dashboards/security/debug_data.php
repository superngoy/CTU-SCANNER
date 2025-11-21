<?php
session_start();

// Add authentication check for security
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    header('Location: login.php');
    exit();
}

// Save this as dashboards/security/debug_data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Security Dashboard Data Debug</h1>";
echo "<style>body{font-family:Arial,sans-serif;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

try {
    require_once '../../includes/functions.php';
    
    echo "<h2>1. Database Connection Test</h2>";
    $scanner = new CTUScanner();
    echo "<span style='color:green;'>✓ CTUScanner class loaded successfully</span><br>";
    
    echo "<h2>2. Raw Database Check</h2>";
    
    // Check entrylogs table
    echo "<h3>Entry Logs Table:</h3>";
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date = CURDATE()");
    $stmt->execute();
    $todayEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Today's entries in database: <strong>$todayEntries</strong><br>";
    
    $stmt = $conn->prepare("SELECT * FROM entrylogs ORDER BY Timestamp DESC LIMIT 5");
    $stmt->execute();
    $rawEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($rawEntries)) {
        echo "<table><tr><th>ID</th><th>PersonID</th><th>PersonType</th><th>Date</th><th>Timestamp</th></tr>";
        foreach ($rawEntries as $entry) {
            echo "<tr><td>{$entry['EntryID']}</td><td>{$entry['PersonID']}</td><td>{$entry['PersonType']}</td><td>{$entry['Date']}</td><td>{$entry['Timestamp']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>No entries found in entrylogs table</p>";
    }
    
    // Check exitlogs table
    echo "<h3>Exit Logs Table:</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE Date = CURDATE()");
    $stmt->execute();
    $todayExits = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Today's exits in database: <strong>$todayExits</strong><br>";
    
    $stmt = $conn->prepare("SELECT * FROM exitlogs ORDER BY Timestamp DESC LIMIT 5");
    $stmt->execute();
    $rawExits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($rawExits)) {
        echo "<table><tr><th>ID</th><th>PersonID</th><th>PersonType</th><th>Date</th><th>Timestamp</th></tr>";
        foreach ($rawExits as $exit) {
            echo "<tr><td>{$exit['ExitID']}</td><td>{$exit['PersonID']}</td><td>{$exit['PersonType']}</td><td>{$exit['Date']}</td><td>{$exit['Timestamp']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>No exits found in exitlogs table</p>";
    }
    
    echo "<h2>3. CTUScanner Methods Test</h2>";
    
    // Test getDailyStats
    echo "<h3>getDailyStats() Result:</h3>";
    $stats = $scanner->getDailyStats();
    echo "<pre>" . print_r($stats, true) . "</pre>";
    
    // Test getRecentEntries
    echo "<h3>getRecentEntries(5) Result:</h3>";
    $entries = $scanner->getRecentEntries(5);
    echo "Number of entries returned: " . count($entries) . "<br>";
    if (!empty($entries)) {
        echo "<table><tr><th>PersonID</th><th>PersonCategory</th><th>Name</th><th>Timestamp</th></tr>";
        foreach ($entries as $entry) {
            $firstName = $entry['StudentFName'] ?? $entry['FacultyFName'] ?? 'Unknown';
            $lastName = $entry['StudentLName'] ?? $entry['FacultyLName'] ?? '';
            $name = trim($firstName . ' ' . $lastName);
            echo "<tr><td>{$entry['PersonID']}</td><td>{$entry['PersonCategory']}</td><td>$name</td><td>{$entry['Timestamp']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>No entries returned by getRecentEntries()</p>";
    }
    
    // Test getRecentExits
    echo "<h3>getRecentExits(5) Result:</h3>";
    $exits = $scanner->getRecentExits(5);
    echo "Number of exits returned: " . count($exits) . "<br>";
    if (!empty($exits)) {
        echo "<table><tr><th>PersonID</th><th>PersonCategory</th><th>Name</th><th>Timestamp</th></tr>";
        foreach ($exits as $exit) {
            $firstName = $exit['StudentFName'] ?? $exit['FacultyFName'] ?? 'Unknown';
            $lastName = $exit['StudentLName'] ?? $exit['FacultyLName'] ?? '';
            $name = trim($firstName . ' ' . $lastName);
            echo "<tr><td>{$exit['PersonID']}</td><td>{$exit['PersonCategory']}</td><td>$name</td><td>{$exit['Timestamp']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>No exits returned by getRecentExits()</p>";
    }
    
    echo "<h2>4. Test realtime_data.php Endpoints</h2>";
    
    // Simulate realtime_data.php calls
    $endpoints = ['stats', 'entries', 'exits'];
    
    foreach ($endpoints as $endpoint) {
        echo "<h3>Testing $endpoint endpoint:</h3>";
        
        switch ($endpoint) {
            case 'stats':
                $result = $scanner->getDailyStats();
                break;
            case 'entries':
                $result = ['entries' => $scanner->getRecentEntries(20)];
                break;
            case 'exits':
                $result = ['exits' => $scanner->getRecentExits(20)];
                break;
        }
        
        echo "<strong>JSON Response:</strong><br>";
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<span style='color:red;'>JSON Error: " . json_last_error_msg() . "</span><br>";
        }
    }
    
    echo "<h2>5. Students and Faculty Data Check</h2>";
    
    // Check if student/faculty data exists
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE isActive = 1");
    $stmt->execute();
    $activeStudents = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Active students: <strong>$activeStudents</strong><br>";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM faculty WHERE isActive = 1");
    $stmt->execute();
    $activeFaculty = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Active faculty: <strong>$activeFaculty</strong><br>";
    
    // Show recent scans with person details
    echo "<h3>Recent Entry/Exit with Person Details:</h3>";
    $stmt = $conn->prepare("
        SELECT 
            e.PersonID,
            e.PersonType,
            e.Timestamp,
            'entry' as action,
            COALESCE(s.StudentFName, f.FacultyFName) as first_name,
            COALESCE(s.StudentLName, f.FacultyLName) as last_name
        FROM entrylogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        
        UNION ALL
        
        SELECT 
            e.PersonID,
            e.PersonType,
            e.Timestamp,
            'exit' as action,
            COALESCE(s.StudentFName, f.FacultyFName) as first_name,
            COALESCE(s.StudentLName, f.FacultyLName) as last_name
        FROM exitlogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        
        ORDER BY Timestamp DESC LIMIT 10
    ");
    $stmt->execute();
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($recentActivity)) {
        echo "<table><tr><th>PersonID</th><th>Type</th><th>Name</th><th>Action</th><th>Timestamp</th></tr>";
        foreach ($recentActivity as $activity) {
            $name = trim(($activity['first_name'] ?? 'Unknown') . ' ' . ($activity['last_name'] ?? ''));
            echo "<tr><td>{$activity['PersonID']}</td><td>{$activity['PersonType']}</td><td>$name</td><td>{$activity['action']}</td><td>{$activity['Timestamp']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>No recent activity found</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color:red;'>";
    echo "<h2>Error Occurred:</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If you see data in the 'Raw Database Check' but not in the 'CTUScanner Methods Test', there's an issue with the SQL queries in CTUScanner class.</li>";
echo "<li>If you see data in CTUScanner methods but not in the dashboard, there's likely a JavaScript/AJAX issue.</li>";
echo "<li>Check your browser's Developer Tools (F12) Console for JavaScript errors when viewing the security dashboard.</li>";
echo "<li>Check your web server error logs for PHP errors.</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to Security Dashboard</a></p>";
?>