<?php
// Save this as dashboards/security/test_connection.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Connection Test</h1>";

// Test 1: Check if files exist
echo "<h2>1. File Existence Check</h2>";
$files = [
    '../../config/database.php',
    '../../includes/functions.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<span style='color:green'>✓</span> $file exists<br>";
    } else {
        echo "<span style='color:red'>✗</span> $file NOT FOUND<br>";
    }
}

// Test 2: Include files
echo "<h2>2. Include Files Test</h2>";
try {
    require_once '../../config/database.php';
    echo "<span style='color:green'>✓</span> database.php included successfully<br>";
} catch (Exception $e) {
    echo "<span style='color:red'>✗</span> Error including database.php: " . $e->getMessage() . "<br>";
}

try {
    require_once '../../includes/functions.php';
    echo "<span style='color:green'>✓</span> functions.php included successfully<br>";
} catch (Exception $e) {
    echo "<span style='color:red'>✗</span> Error including functions.php: " . $e->getMessage() . "<br>";
}

// Test 3: Database connection
echo "<h2>3. Database Connection Test</h2>";
try {
    $database = new Database();
    echo "<span style='color:green'>✓</span> Database class created<br>";
    
    $conn = $database->getConnection();
    echo "<span style='color:green'>✓</span> Database connection successful<br>";
    
    // Test a simple query
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<span style='color:green'>✓</span> Query successful - Found $count entries<br>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>✗</span> Database error: " . $e->getMessage() . "<br>";
}

// Test 4: CTUScanner class
echo "<h2>4. CTUScanner Class Test</h2>";
try {
    $scanner = new CTUScanner();
    echo "<span style='color:green'>✓</span> CTUScanner class created<br>";
    
    $stats = $scanner->getDailyStats();
    echo "<span style='color:green'>✓</span> getDailyStats() works<br>";
    echo "Stats: <pre>" . print_r($stats, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>✗</span> CTUScanner error: " . $e->getMessage() . "<br>";
}

// Test 5: Simulate the exact realtime_data.php call
echo "<h2>5. Simulate realtime_data.php</h2>";
echo "<h3>Stats endpoint:</h3>";
try {
    $_GET['action'] = 'stats';
    ob_start();
    include 'realtime_data.php';
    $output = ob_get_clean();
    echo "Output: <pre>$output</pre>";
} catch (Exception $e) {
    echo "<span style='color:red'>✗</span> Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>If everything shows green checkmarks above, then the issue might be with file paths or permissions.</strong></p>";
echo "<p><a href='realtime_data.php?action=stats'>Test realtime_data.php?action=stats directly</a></p>";
echo "<p><a href='index.php'>Back to Security Dashboard</a></p>";
?>