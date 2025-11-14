<?php
require 'config/database.php';

$db = new Database();
$pdo = $db->connect();

// Check entry logs
$stmt = $pdo->query('SELECT COUNT(*) as count FROM entrylogs');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Entry logs count: " . $result['count'] . "\n";

// Check recent entries with full query
echo "\n--- Recent Entries Query Test ---\n";
$limit = 10;
$query = "
    SELECT 
        e.PersonID,
        CASE 
            WHEN e.PersonType = 'student' THEN CONCAT(s.StudentFName, ' ', s.StudentLName)
            WHEN e.PersonType = 'faculty' THEN CONCAT(f.FacultyFName, ' ', f.FacultyLName)
            ELSE 'Unknown'
        END as FullName,
        e.PersonType,
        e.Timestamp
    FROM entrylogs e
    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
    ORDER BY e.Timestamp DESC
    LIMIT " . intval($limit);
echo "Query: " . $query . "\n";
$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent entries found: " . count($results) . "\n";
if (count($results) > 0) {
    echo "Sample: " . json_encode($results[0]) . "\n";
} else {
    echo "No results found\n";
}

// Check exit logs
echo "\n--- Exit logs count ---\n";
$stmt = $pdo->query('SELECT COUNT(*) as count FROM exitlogs');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Exit logs count: " . $result['count'] . "\n";

// Check recent exits with full query
echo "\n--- Recent Exits Query Test ---\n";
$limit = 10;
$query = "
    SELECT 
        ex.PersonID,
        CASE 
            WHEN ex.PersonType = 'student' THEN CONCAT(s.StudentFName, ' ', s.StudentLName)
            WHEN ex.PersonType = 'faculty' THEN CONCAT(f.FacultyFName, ' ', f.FacultyLName)
            ELSE 'Unknown'
        END as FullName,
        ex.PersonType,
        ex.Timestamp
    FROM exitlogs ex
    LEFT JOIN students s ON ex.PersonID = s.StudentID AND ex.PersonType = 'student'
    LEFT JOIN faculty f ON ex.PersonID = f.FacultyID AND ex.PersonType = 'faculty'
    ORDER BY ex.Timestamp DESC
    LIMIT " . intval($limit);
echo "Query: " . $query . "\n";
$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent exits found: " . count($results) . "\n";
if (count($results) > 0) {
    echo "Sample: " . json_encode($results[0]) . "\n";
} else {
    echo "No results found\n";
}
?>
