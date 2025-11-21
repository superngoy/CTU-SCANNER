<?php
// qr_generator_api.php - QR Code Generator API for Admin Dashboard
require_once 'config/database.php';

class QRCodeGenerator {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function generateQRCodeURL($data, $size = 200) {
        // Using QR Server API (reliable alternative to Google Charts)
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
    }
    
    public function getPersonInfo($id) {
        // Check if it's a student
        $stmt = $this->conn->prepare("SELECT StudentID as ID, StudentFName as FName, StudentMName as MName, StudentLName as LName, 'Student' as Type, Course, YearLvl, Section, Department FROM students WHERE StudentID = ? AND isActive = 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            // Check if it's faculty
            $stmt = $this->conn->prepare("SELECT FacultyID as ID, FacultyFName as FName, FacultyMName as MName, FacultyLName as LName, 'Faculty' as Type, Department FROM faculty WHERE FacultyID = ? AND isActive = 1");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$result) {
            // Check if it's staff
            $stmt = $this->conn->prepare("SELECT StaffID as ID, StaffFName as FName, StaffMName as MName, StaffLName as LName, 'Staff' as Type, Department FROM staff WHERE StaffID = ? AND isActive = 1");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return $result;
    }
    
    public function generateStudentQRCodes() {
        $stmt = $this->conn->prepare("SELECT StudentID, StudentFName, StudentMName, StudentLName, Course, YearLvl, Section, Department FROM students WHERE isActive = 1 ORDER BY StudentID LIMIT 50");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h4><i class='fas fa-user-graduate me-2'></i>Student QR Codes</h4>";
        echo "<button class='btn btn-primary' onclick='printQRCodes()'><i class='fas fa-print me-1'></i>Print All</button>";
        echo "</div>";
        echo "<div class='qr-grid'>";
        
        foreach ($students as $student) {
            $qrUrl = $this->generateQRCodeURL($student['StudentID'], 150);
            echo "<div class='qr-card'>";
            echo "<div class='qr-header'>";
            echo "<h5>{$student['StudentFName']} " . ($student['StudentMName'] ? $student['StudentMName'][0] . '. ' : '') . "{$student['StudentLName']}</h5>";
            echo "<p class='id-number'>{$student['StudentID']}</p>";
            echo "</div>";
            echo "<div class='qr-code'>";
            echo "<img src='{$qrUrl}' alt='QR Code for {$student['StudentID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='qr-details'>";
            echo "<small class='text-primary'><strong>{$student['Course']}</strong></small><br>";
            echo "<small>Year {$student['YearLvl']} - Section {$student['Section']}</small><br>";
            echo "<small class='text-muted'>{$student['Department']}</small>";
            echo "</div>";
            echo "<div class='mt-2'>";
            echo "<button class='btn btn-sm btn-primary me-1' onclick='downloadQR(\"{$student['StudentID']}\", \"{$student['StudentFName']} {$student['StudentLName']}\")'>Download</button>";
            echo "<button class='btn btn-sm btn-success' onclick='testScanner(\"{$student['StudentID']}\")'>Test</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
        
        if (count($students) >= 50) {
            echo "<div class='alert alert-info mt-3'>";
            echo "<i class='fas fa-info-circle me-2'></i>";
            echo "Showing first 50 students. Use search or filters for more specific results.";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    public function generateFacultyQRCodes() {
        $stmt = $this->conn->prepare("SELECT FacultyID, FacultyFName, FacultyMName, FacultyLName, Department FROM faculty WHERE isActive = 1 ORDER BY FacultyID LIMIT 50");
        $stmt->execute();
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h4><i class='fas fa-chalkboard-teacher me-2'></i>Faculty QR Codes</h4>";
        echo "<button class='btn btn-primary' onclick='printQRCodes()'><i class='fas fa-print me-1'></i>Print All</button>";
        echo "</div>";
        echo "<div class='qr-grid'>";
        
        foreach ($faculty as $fac) {
            $qrUrl = $this->generateQRCodeURL($fac['FacultyID'], 150);
            echo "<div class='qr-card'>";
            echo "<div class='qr-header'>";
            echo "<h5>{$fac['FacultyFName']} " . ($fac['FacultyMName'] ? $fac['FacultyMName'][0] . '. ' : '') . "{$fac['FacultyLName']}</h5>";
            echo "<p class='id-number'>{$fac['FacultyID']}</p>";
            echo "</div>";
            echo "<div class='qr-code'>";
            echo "<img src='{$qrUrl}' alt='QR Code for {$fac['FacultyID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='qr-details'>";
            echo "<small class='text-info'><strong>Faculty Member</strong></small><br>";
            echo "<small class='text-muted'>Department: {$fac['Department']}</small>";
            echo "</div>";
            echo "<div class='mt-2'>";
            echo "<button class='btn btn-sm btn-primary me-1' onclick='downloadQR(\"{$fac['FacultyID']}\", \"{$fac['FacultyFName']} {$fac['FacultyLName']}\")'>Download</button>";
            echo "<button class='btn btn-sm btn-success' onclick='testScanner(\"{$fac['FacultyID']}\")'>Test</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
        
        if (count($faculty) >= 50) {
            echo "<div class='alert alert-info mt-3'>";
            echo "<i class='fas fa-info-circle me-2'></i>";
            echo "Showing first 50 faculty members. Use search or filters for more specific results.";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    public function generateStaffQRCodes() {
        $stmt = $this->conn->prepare("SELECT StaffID, StaffFName, StaffMName, StaffLName, Department FROM staff WHERE isActive = 1 ORDER BY StaffID LIMIT 50");
        $stmt->execute();
        $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h4><i class='fas fa-user-tie me-2'></i>Staff QR Codes</h4>";
        echo "<button class='btn btn-primary' onclick='printQRCodes()'><i class='fas fa-print me-1'></i>Print All</button>";
        echo "</div>";
        echo "<div class='qr-grid'>";
        
        foreach ($staff as $stf) {
            $qrUrl = $this->generateQRCodeURL($stf['StaffID'], 150);
            echo "<div class='qr-card'>";
            echo "<div class='qr-header'>";
            echo "<h5>{$stf['StaffFName']} " . ($stf['StaffMName'] ? $stf['StaffMName'][0] . '. ' : '') . "{$stf['StaffLName']}</h5>";
            echo "<p class='id-number'>{$stf['StaffID']}</p>";
            echo "</div>";
            echo "<div class='qr-code'>";
            echo "<img src='{$qrUrl}' alt='QR Code for {$stf['StaffID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='qr-details'>";
            echo "<small class='text-warning'><strong>Staff Member</strong></small><br>";
            echo "<small class='text-muted'>Department: {$stf['Department']}</small>";
            echo "</div>";
            echo "<div class='mt-2'>";
            echo "<button class='btn btn-sm btn-primary me-1' onclick='downloadQR(\"{$stf['StaffID']}\", \"{$stf['StaffFName']} {$stf['StaffLName']}\")'>Download</button>";
            echo "<button class='btn btn-sm btn-success' onclick='testScanner(\"{$stf['StaffID']}\")'>Test</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
        
        if (count($staff) >= 50) {
            echo "<div class='alert alert-info mt-3'>";
            echo "<i class='fas fa-info-circle me-2'></i>";
            echo "Showing first 50 staff members. Use search or filters for more specific results.";
            echo "</div>";
        }
        
        echo "</div>";
    }
}

// Handle different actions
$action = $_GET['action'] ?? '';
$generator = new QRCodeGenerator();

if ($action === 'generate_by_id' && isset($_GET['id'])) {
    // Generate QR code for specific ID
    $id = $_GET['id'];
    $person = $generator->getPersonInfo($id);
    
    header('Content-Type: application/json');
    if ($person) {
        $qrUrl = $generator->generateQRCodeURL($id, 200);
        echo json_encode([
            'success' => true,
            'person' => $person,
            'qr_url' => $qrUrl
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ID not found in database'
        ]);
    }
    exit;
}

if ($action === 'students') {
    $generator->generateStudentQRCodes();
    exit;
}

if ($action === 'faculty') {
    $generator->generateFacultyQRCodes();
    exit;
}

if ($action === 'staff') {
    $generator->generateStaffQRCodes();
    exit;
}

// If no specific action, return error
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Invalid action specified'
]);
?>