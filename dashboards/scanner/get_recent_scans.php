<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    $recentEntries = $scanner->getRecentEntries(10);
    $recentExits = $scanner->getRecentExits(10);
    
    $scans = [];
    
    // Process entries
    foreach ($recentEntries as $entry) {
        $firstName = $entry['StudentFName'] ?? $entry['FacultyFName'] ?? 'Unknown';
        $lastName = $entry['StudentLName'] ?? $entry['FacultyLName'] ?? '';
        
        $scans[] = [
            'name' => trim($firstName . ' ' . $lastName),
            'id' => $entry['PersonID'],
            'type' => ucfirst($entry['PersonCategory']),
            'action' => 'Entry',
            'time' => date('h:i A', strtotime($entry['Timestamp'])),
            'timestamp' => $entry['Timestamp']
        ];
    }
    
    // Process exits
    foreach ($recentExits as $exit) {
        $firstName = $exit['StudentFName'] ?? $exit['FacultyFName'] ?? 'Unknown';
        $lastName = $exit['StudentLName'] ?? $exit['FacultyLName'] ?? '';
        
        $scans[] = [
            'name' => trim($firstName . ' ' . $lastName),
            'id' => $exit['PersonID'],
            'type' => ucfirst($exit['PersonCategory']),
            'action' => 'Exit',
            'time' => date('h:i A', strtotime($exit['Timestamp'])),
            'timestamp' => $exit['Timestamp']
        ];
    }
    
    // Sort by timestamp (most recent first)
    usort($scans, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    echo json_encode(['scans' => array_slice($scans, 0, 10)]);
} catch (Exception $e) {
    error_log("get_recent_scans error: " . $e->getMessage());
    echo json_encode(['scans' => [], 'error' => $e->getMessage()]);
}
?>