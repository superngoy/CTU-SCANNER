<?php
// Test the analytics endpoints
require 'config/database.php';
require 'includes/functions.php';

$db = new Database();
$scanner = $db->connect();

// Set the scanner connection for the includes
$_GET['action'] = 'recent_entries';
$limit = 10;

try {
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
        ORDER BY e.Timestamp DESC
        LIMIT " . $limit;
    
    $stmt = $scanner->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent Entries Test:\n";
    echo "Total records: " . count($results) . "\n";
    echo "JSON Output:\n";
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Test format
    if (count($results) > 0) {
        $first = $results[0];
        $time = date('g:i:s A', strtotime($first['Timestamp']));
        $type = strtolower($first['PersonType']);
        echo "Example Display Format:\n";
        echo $first['FullName'] . " " . $first['PersonID'] . " - " . $type . " " . $time . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
