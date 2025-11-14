<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    
    $debug = [];
    
    // Check today's entries
    $stmt = $scanner->conn->prepare("
        SELECT EntryID, PersonID, PersonType, Date, Timestamp
        FROM entrylogs
        WHERE Date = CURDATE()
        ORDER BY Timestamp
    ");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['today_entries'] = $entries;
    
    // Check today's exits
    $stmt = $scanner->conn->prepare("
        SELECT ExitID, PersonID, PersonType, Date, Timestamp
        FROM exitlogs
        WHERE Date = CURDATE()
        ORDER BY Timestamp
    ");
    $stmt->execute();
    $exits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['today_exits'] = $exits;
    
    // Check entry/exit pairs (for dwell time calculation)
    $stmt = $scanner->conn->prepare("
        SELECT 
            e.PersonID,
            e.Timestamp as entry_time,
            ex.Timestamp as exit_time,
            TIMESTAMPDIFF(MINUTE, e.Timestamp, ex.Timestamp) as dwell_minutes
        FROM entrylogs e
        JOIN exitlogs ex ON e.PersonID = ex.PersonID AND DATE(e.Date) = DATE(ex.Date)
        WHERE e.Date = CURDATE()
        ORDER BY e.Timestamp
    ");
    $stmt->execute();
    $pairs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['entry_exit_pairs'] = $pairs;
    
    // Calculate average dwell time
    if (count($pairs) > 0) {
        $totalMinutes = 0;
        foreach ($pairs as $pair) {
            $totalMinutes += $pair['dwell_minutes'];
        }
        $avgMinutes = $totalMinutes / count($pairs);
        $avgHours = round($avgMinutes / 60, 1);
        $debug['avg_dwell_time'] = $avgHours . 'h';
    } else {
        $debug['avg_dwell_time'] = '0h (no pairs)';
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
