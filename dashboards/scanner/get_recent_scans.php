<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

$scanner = new CTUScanner();

try {
    $recentEntries = $scanner->getRecentEntries(10);
    $recentExits = $scanner->getRecentExits(10);
    
    $scans = [];
    
    // Process entries
    foreach ($recentEntries as $entry) {
        $scans[] = [
            'name' => $entry['StudentFName'] ?? $entry['FacultyFName'] . ' ' . 
                     $entry['StudentLName'] ?? $entry['FacultyLName'],
            'id' => $entry['PersonID'],
            'type' => $entry['PersonCategory'],
            'action' => 'Entry',
            'time' => date('h:i A', strtotime($entry['Timestamp'])),
            'timestamp' => $entry['Timestamp']
        ];
    }
    
    // Process exits
    foreach ($recentExits as $exit) {
        $scans[] = [
            'name' => $exit['StudentFName'] ?? $exit['FacultyFName'] . ' ' . 
                     $exit['StudentLName'] ?? $exit['FacultyLName'],
            'id' => $exit['PersonID'],
            'type' => $exit['PersonCategory'],
            'action' => 'Exit',
            'time' => date('h:i A', strtotime($exit['Timestamp'])),
            'timestamp' => $exit['Timestamp']
        ];
    }
    
    // Sort by timestamp
    usort($scans, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    echo json_encode(['scans' => array_slice($scans, 0, 10)]);
} catch (Exception $e) {
    echo json_encode(['scans' => [], 'error' => $e->getMessage()]);
}
?>