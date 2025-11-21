<?php
require_once __DIR__ . '/../config/database.php';

class CTUScanner {
    public $conn; // Make this public so analytics.php can access it
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        
        if (!$this->conn) {
            throw new Exception('Database connection failed');
        }
    }
    
    // Verify Barcode/QR Code and get person info
    public function verifyQRCode($barcode_data) {
        try {
            // Check if it's a student
            $stmt = $this->conn->prepare("SELECT *, 'student' as type FROM students WHERE StudentID = ? AND isActive = 1");
            $stmt->execute([$barcode_data]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // Check if it's faculty
                $stmt = $this->conn->prepare("SELECT *, 'faculty' as type FROM faculty WHERE FacultyID = ? AND isActive = 1");
                $stmt->execute([$barcode_data]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$result) {
                // Check if it's staff
                $stmt = $this->conn->prepare("SELECT *, 'staff' as type FROM staff WHERE StaffID = ? AND isActive = 1");
                $stmt->execute([$barcode_data]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("verifyQRCode error: " . $e->getMessage());
            return false;
        }
    }
    
    // Log Entry
    public function logEntry($person_id, $person_type, $scanner_id) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO entrylogs (PersonID, PersonType, Date, ScannerID) VALUES (?, ?, DATE(NOW()), ?)");
            return $stmt->execute([$person_id, $person_type, $scanner_id]);
        } catch(PDOException $e) {
            error_log("logEntry error: " . $e->getMessage());
            return false;
        }
    }
    
    // Log Exit
    public function logExit($person_id, $person_type, $scanner_id) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO exitlogs (PersonID, PersonType, Date, ScannerID) VALUES (?, ?, DATE(NOW()), ?)");
            return $stmt->execute([$person_id, $person_type, $scanner_id]);
        } catch(PDOException $e) {
            error_log("logExit error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Recent Entries - FIXED FOR LIMIT ISSUE
    public function getRecentEntries($limit = 50) {
        try {
            // Convert limit to integer to prevent SQL injection and syntax errors
            $limit = (int)$limit;
            
            $sql = "SELECT 
                        e.PersonID,
                        e.PersonType as PersonCategory,
                        CASE 
                            WHEN e.PersonType = 'student' THEN s.image
                            WHEN e.PersonType = 'faculty' THEN f.image
                            ELSE NULL
                        END as image,
                        e.Timestamp,
                        CASE 
                            WHEN e.PersonType = 'student' THEN s.StudentFName
                            ELSE f.FacultyFName
                        END as StudentFName,
                        CASE 
                            WHEN e.PersonType = 'student' THEN s.StudentLName
                            ELSE f.FacultyLName
                        END as StudentLName,
                        f.FacultyFName,
                        f.FacultyLName
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    ORDER BY e.Timestamp DESC 
                    LIMIT " . $limit;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("getRecentEntries found " . count($results) . " entries");
            return $results;
        } catch(PDOException $e) {
            error_log("getRecentEntries error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Recent Exits - FIXED FOR LIMIT ISSUE
    public function getRecentExits($limit = 50) {
        try {
            // Convert limit to integer to prevent SQL injection and syntax errors
            $limit = (int)$limit;
            
            $sql = "SELECT 
                        CASE 
                            WHEN e.PersonType = 'student' THEN s.image
                            WHEN e.PersonType = 'faculty' THEN f.image
                            ELSE NULL
                        END as image, 
                        e.PersonID,
                        e.PersonType as PersonCategory,
                        e.Timestamp,
                        CASE 
                            WHEN e.PersonType = 'student' THEN s.StudentFName
                            ELSE f.FacultyFName
                        END as StudentFName,
                        CASE 
                            WHEN e.PersonType = 'student' THEN s.StudentLName
                            ELSE f.FacultyLName
                        END as StudentLName,
                        f.FacultyFName,
                        f.FacultyLName
                    FROM exitlogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    ORDER BY e.Timestamp DESC 
                    LIMIT " . $limit;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("getRecentExits found " . count($results) . " exits");
            return $results;
        } catch(PDOException $e) {
            error_log("getRecentExits error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Daily Stats - FIXED
    public function getDailyStats($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        try {
            $stats = [];
            
            // Total entries today
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = ?");
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_entries'] = (int)$result['total'];
            
            // Total exits today
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM exitlogs WHERE Date = ?");
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_exits'] = (int)$result['total'];
            
            // Students entries
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = ? AND PersonType = 'student'");
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['student_entries'] = (int)$result['total'];
            
            // Faculty entries
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = ? AND PersonType = 'faculty'");
            $stmt->execute([$date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['faculty_entries'] = (int)$result['total'];
            
            error_log("getDailyStats for $date: " . json_encode($stats));
            return $stats;
        } catch(PDOException $e) {
            error_log("getDailyStats error: " . $e->getMessage());
            return [
                'total_entries' => 0,
                'total_exits' => 0,
                'student_entries' => 0,
                'faculty_entries' => 0
            ];
        }
    }
    
    // Get Peak Hours - Fixed to return proper format
    public function getPeakHours($date = null) {
        if (!$date) $date = date('Y-m-d');
        
        try {
            $stmt = $this->conn->prepare("
                SELECT HOUR(Timestamp) as hour, COUNT(*) as count 
                FROM entrylogs 
                WHERE Date = ? 
                GROUP BY HOUR(Timestamp) 
                ORDER BY hour ASC
            ");
            $stmt->execute([$date]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to proper integer format
            $formattedResults = [];
            foreach ($results as $row) {
                $formattedResults[] = [
                    'hour' => (int)$row['hour'],
                    'count' => (int)$row['count']
                ];
            }
            
            return $formattedResults;
        } catch(PDOException $e) {
            error_log("getPeakHours error: " . $e->getMessage());
            return [];
        }
    }
}

// Auth class for security login
class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        
        if (!$this->conn) {
            throw new Exception('Database connection failed');
        }
    }
    
    public function isLoggedIn($type) {
        return isset($_SESSION[$type . '_id']);
    }
    
    public function loginAdmin($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM admin WHERE email = ? AND isActive = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['AdminID'];
                $_SESSION['admin_name'] = $admin['AdminFName'] . ' ' . $admin['AdminLName'];
                $_SESSION['user_type'] = 'admin';
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("loginAdmin error: " . $e->getMessage());
            return false;
        }
    }
    
    public function loginSecurity($securityId, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM security WHERE SecurityID = ? AND isActive = 1");
            $stmt->execute([$securityId]);
            $security = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($security && password_verify($password, $security['password'])) {
                $_SESSION['security_id'] = $security['SecurityID'];
                $_SESSION['security_name'] = $security['SecurityFName'] . ' ' . $security['SecurityLName'];
                $_SESSION['user_type'] = 'security';
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("loginSecurity error: " . $e->getMessage());
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function requireAuth($userType = null) {
        if (!$this->isLoggedIn($userType)) {
            header('Location: login.php');
            exit();
        }
    }
}
?>