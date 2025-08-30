<?php
// Debug script for security dashboard
// Save this as dashboards/security/debug.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>CTU Scanner Security Dashboard Debug</h2>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once '../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        echo "<span style='color: green;'>✓ Database connection successful</span><br>";
        echo "Connection object type: " . get_class($conn) . "<br>";
    } else {
        echo "<span style='color: red;'>✗ Database connection failed</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</span><br>";
}

// Test CTUScanner class
echo "<h3>2. CTUScanner Class Test</h3>";
try {
    require_once '../../includes/functions.php';
    $scanner = new CTUScanner();
    echo "<span style='color: green;'>✓ CTUScanner class loaded successfully</span><br>";
    
    // Test each method
    echo "<h4>Testing getDailyStats():</h4>";
    $stats = $scanner->getDailyStats();
    echo "<pre>" . print_r($stats, true) . "</pre>";
    
    echo "<h4>Testing getRecentEntries():</h4>";
    $entries = $scanner->getRecentEntries(5);
    echo "<pre>" . print_r($entries, true) . "</pre>";
    
    echo "<h4>Testing getRecentExits():</h4>";
    $exits = $scanner->getRecentExits(5);
    echo "<pre>" . print_r($exits, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ CTUScanner error: " . $e->getMessage() . "</span><br>";
    echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
}

// Check database tables
echo "<h3>3. Database Tables Check</h3>";
try {
    $tables = ['student_info', 'faculty_info', 'entry_logs', 'exit_logs', 'scan_logs'];
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "$table: {$result['count']} records<br>";
        
        // Show sample data
        if ($result['count'] > 0) {
            $stmt = $conn->prepare("SELECT * FROM $table LIMIT 1");
            $stmt->execute();
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<details><summary>Sample record</summary><pre>" . print_r($sample, true) . "</pre></details>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Table check error: " . $e->getMessage() . "</span><br>";
}

// Check recent scan activity
echo "<h3>4. Recent Scan Activity</h3>";
try {
    // Check for recent scans (last 24 hours)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count, 
               MAX(created_at) as latest_scan,
               MIN(created_at) as oldest_scan
        FROM scan_logs 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $scanActivity = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Today's scans: {$scanActivity['count']}<br>";
    echo "Latest scan: " . ($scanActivity['latest_scan'] ?? 'None') . "<br>";
    echo "Oldest scan today: " . ($scanActivity['oldest_scan'] ?? 'None') . "<br>";
    
    // Show recent scan logs
    $stmt = $conn->prepare("
        SELECT sl.*, 
               COALESCE(si.StudentFName, fi.FacultyFName) as first_name,
               COALESCE(si.StudentLName, fi.FacultyLName) as last_name,
               sl.scan_type
        FROM scan_logs sl
        LEFT JOIN student_info si ON sl.person_id = si.StudentID
        LEFT JOIN faculty_info fi ON sl.person_id = fi.FacultyID
        ORDER BY sl.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recentScans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Last 10 scans:</h4>";
    if (!empty($recentScans)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Time</th><th>Person ID</th><th>Name</th><th>Type</th><th>Action</th></tr>";
        foreach ($recentScans as $scan) {
            $name = ($scan['first_name'] ?? 'Unknown') . ' ' . ($scan['last_name'] ?? '');
            echo "<tr>";
            echo "<td>{$scan['created_at']}</td>";
            echo "<td>{$scan['person_id']}</td>";
            echo "<td>{$name}</td>";
            echo "<td>{$scan['person_type']}</td>";
            echo "<td>{$scan['scan_type']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No recent scans found.</p>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Scan activity check error: " . $e->getMessage() . "</span><br>";
}

// Test realtime_data.php endpoints
echo "<h3>5. Realtime Data Endpoints Test</h3>";
$endpoints = ['stats', 'entries', 'exits'];

foreach ($endpoints as $endpoint) {
    echo "<h4>Testing $endpoint endpoint:</h4>";
    
    // Simulate the realtime_data.php request
    $_GET['action'] = $endpoint;
    
    ob_start();
    try {
        // Include the realtime data logic
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
        
        echo "<pre>JSON Response:\n" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<span style='color: red;'>JSON Error: " . json_last_error_msg() . "</span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span style='color: red;'>Endpoint error: " . $e->getMessage() . "</span><br>";
    }
    $output = ob_get_clean();
    echo $output;
}

echo "<h3>6. File Permissions Check</h3>";
$files = [
    '../../config/database.php',
    '../../includes/functions.php',
    'realtime_data.php',
    '../../assets/js/security.js'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? '✓' : '✗';
        echo "$file: $readable readable (perms: " . substr(sprintf('%o', $perms), -4) . ")<br>";
    } else {
        echo "<span style='color: red;'>$file: ✗ File not found</span><br>";
    }
}

// Check session status
echo "<h3>7. Session Status</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Security ID in session: " . ($_SESSION['security_id'] ?? 'Not set') . "<br>";
echo "User type in session: " . ($_SESSION['user_type'] ?? 'Not set') . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>8. Browser Console Instructions</h3>";
echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo "<p>To debug the JavaScript side:</p>";
echo "<ol>";
echo "<li>Open your browser's Developer Tools (F12)</li>";
echo "<li>Go to the Console tab</li>";
echo "<li>Load the security dashboard</li>";
echo "<li>Look for any error messages in red</li>";
echo "<li>Check the Network tab for failed HTTP requests</li>";
echo "</ol>";
echo "</div>";

echo "<h3>Debug Complete</h3>";
echo "<p><a href='index.php'>← Back to Security Dashboard</a></p>";
?>