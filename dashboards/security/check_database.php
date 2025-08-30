<?php
// Save this as dashboards/security/check_database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Class Methods Check</h1>";

require_once '../../config/database.php';

$database = new Database();

echo "<h2>Available Methods in Database class:</h2>";
$methods = get_class_methods($database);
echo "<ul>";
foreach ($methods as $method) {
    echo "<li><strong>$method()</strong></li>";
}
echo "</ul>";

echo "<h2>Database Class Properties:</h2>";
$reflection = new ReflectionClass($database);
$properties = $reflection->getProperties();
echo "<ul>";
foreach ($properties as $property) {
    echo "<li>" . $property->getName() . "</li>";
}
echo "</ul>";

// Try common method names
echo "<h2>Testing Common Method Names:</h2>";
$commonMethods = ['connect', 'getConnection', 'connection', 'getConn', 'conn'];

foreach ($commonMethods as $method) {
    if (method_exists($database, $method)) {
        echo "<span style='color:green;'>✓</span> $method() exists - trying to call it...<br>";
        try {
            $conn = $database->$method();
            if ($conn) {
                echo "<span style='color:green;'>✓✓</span> $method() works and returns a connection!<br>";
                echo "Connection type: " . get_class($conn) . "<br>";
                
                // Test a simple query
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM students");
                $stmt->execute();
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<span style='color:green;'>✓✓✓</span> Query successful - Found $count students<br>";
                break;
            }
        } catch (Exception $e) {
            echo "<span style='color:red;'>✗</span> $method() failed: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "<span style='color:orange;'>-</span> $method() does not exist<br>";
    }
}
?>