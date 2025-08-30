<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

$scanner = new CTUScanner();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'department':
        try {
            $stmt = $scanner->conn->prepare("
                SELECT s.Department, COUNT(e.EntryID) as count
                FROM entrylogs e
                JOIN students s ON e.PersonID = s.StudentID
                WHERE e.PersonType = 'student' AND e.Date = CURDATE()
                GROUP BY s.Department
                UNION ALL
                SELECT f.Department, COUNT(e.EntryID) as count
                FROM entrylogs e
                JOIN faculty f ON e.PersonID = f.FacultyID
                WHERE e.PersonType = 'faculty' AND e.Date = CURDATE()
                GROUP BY f.Department
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = ['COTE' => 0, 'COED' => 0];
            foreach ($results as $row) {
                if (isset($data[$row['Department']])) {
                    $data[$row['Department']] += $row['count'];
                }
            }
            
            echo json_encode($data);
        } catch (PDOException $e) {
            echo json_encode(['COTE' => 0, 'COED' => 0]);
        }
        break;
        
    case 'weekly':
        try {
            $stmt = $scanner->conn->prepare("
                SELECT DAYOFWEEK(Date) as day, COUNT(*) as count
                FROM entrylogs
                WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DAYOFWEEK(Date)
                ORDER BY day
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        break;
        
    case 'custom_report':
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        
        try {
            // Total entries
            $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $totalEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Total exits
            $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE Date BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate]);
            $totalExits = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Student entries
            $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'student'");
            $stmt->execute([$startDate, $endDate]);
            $studentEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Faculty entries
            $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'faculty'");
            $stmt->execute([$startDate, $endDate]);
            $facultyEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'total_entries' => $totalEntries,
                'total_exits' => $totalExits,
                'student_entries' => $studentEntries,
                'faculty_entries' => $facultyEntries
            ]);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>