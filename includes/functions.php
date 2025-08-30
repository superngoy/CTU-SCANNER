<?php
require_once '../config/database.php';

class CTUScanner {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Verify QR Code and get person info
    public function verifyQRCode($qr_data) {
        try {
            // Check if it's a student
            $stmt = $this->conn->prepare("SELECT *, 'student' as type FROM students WHERE StudentID = ? AND isActive = 1");
            $stmt->execute([$qr_data]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Check if it's faculty
                $stmt = $this->conn->prepare("SELECT *, 'faculty' as type FROM faculty WHERE FacultyID = ? AND isActive = 1");
                $stmt->execute([$qr_data]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Log Entry
    public function logEntry($person_id, $person_type, $scanner_id) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO entrylogs (PersonID, PersonType, Date, ScannerID) VALUES (?, ?, CURDATE(), ?)");
            return $stmt->execute([$person_id, $person_type, $scanner_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Log Exit
    public function logExit($person_id, $person_type, $scanner_id) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO exitlogs (PersonID, PersonType, Date, ScannerID) VALUES (?, ?, CURDATE(), ?)");
            return $stmt->execute([$person_id, $person_type, $scanner_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get Recent Entries
    public function getRecentEntries($limit = 50) {
        try {
            $sql = "SELECT e.*, s.StudentFName, s.StudentMName, s.StudentLName, 'Student' as PersonCategory
                   FROM entrylogs e 
                   JOIN students s ON e.PersonID = s.StudentID 
                   WHERE e.PersonType = 'student'
                   UNION ALL
                   SELECT e.*, f.FacultyFName, f.FacultyMName, f.FacultyLName, 'Faculty' as PersonCategory
                   FROM entrylogs e 
                   JOIN faculty f ON e.PersonID = f.FacultyID 
                   WHERE e.PersonType = 'faculty'
                   ORDER BY Timestamp DESC LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Get Recent Exits
    public function getRecentExits($limit = 50) {
        try {
            $sql = "SELECT e.*, s.StudentFName, s.StudentMName, s.StudentLName, 'Student' as PersonCategory
                   FROM exitlogs e 
                   JOIN students s ON e.PersonID = s.StudentID 
                   WHERE e.PersonType = 'student'
                   UNION ALL
                   SELECT e.*, f.FacultyFName, f.FacultyMName, f.FacultyLName, 'Faculty' as PersonCategory
                   FROM exitlogs e 
                   JOIN faculty f ON e.PersonID = f.FacultyID 
                   WHERE e.PersonType = 'faculty'
                   ORDER BY Timestamp DESC LIMIT ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Analytics Functions
    public function getDailyStats($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        try {
            $stats = [];
            
            // Total entries today
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = ?");
            $stmt->execute([$date]);
            $stats['total_entries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total exits today
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM exitlogs WHERE Date = ?");
            $stmt->execute([$date]);
            $stats['total_exits'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Students entries
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = ? AND PersonType = 'student'");
            $stmt->execute([$date]);
            $stats['student_entries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Faculty entries
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = ? AND PersonType = 'faculty'");
            $stmt->execute([$date]);
            $stats['faculty_entries'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return $stats;
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Get Peak Hours
    public function getPeakHours($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        try {
            $stmt = $this->conn->prepare("
                SELECT HOUR(Timestamp) as hour, COUNT(*) as count 
                FROM entrylogs 
                WHERE Date = ? 
                GROUP BY HOUR(Timestamp) 
                ORDER BY count DESC
            ");
            $stmt->execute([$date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>