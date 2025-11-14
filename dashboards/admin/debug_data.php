<?php
session_start();
require_once '../../config/database.php';

try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=ctuscanner",
        "root",
        ""
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count entries
    $stmt = $conn->query("SELECT COUNT(*) as count FROM entrylogs");
    $entries_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count exits
    $stmt = $conn->query("SELECT COUNT(*) as count FROM exitlogs");
    $exits_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get sample entries
    $stmt = $conn->query("
        SELECT 
            e.PersonID,
            e.PersonType,
            e.Timestamp,
            s.StudentFName,
            s.StudentLName,
            f.FacultyFName,
            f.FacultyLName
        FROM entrylogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        ORDER BY e.Timestamp DESC
        LIMIT 5
    ");
    $sample_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Entries count: " . $entries_count . "\n";
    echo "Exits count: " . $exits_count . "\n";
    echo "\nSample entries:\n";
    echo json_encode($sample_entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
