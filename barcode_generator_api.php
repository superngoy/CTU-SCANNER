<?php
// barcode_generator_api.php - Code 39 Barcode Generator API for Admin Dashboard
require_once 'config/database.php';

class BarcodeGenerator {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function generateBarcodeURL($data, $width = 250, $height = 100) {
        // Using Barcode Server API for Code 39 barcodes
        // Dimensions are in pixels, API will convert to appropriate size
        return "https://barcode.tec-it.com/barcode.ashx?data=" . urlencode($data) . "&code=Code39&dpi=150&print=true&width=" . $width . "&height=" . $height;
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
    
    public function generateStudentBarcodes() {
        $stmt = $this->conn->prepare("SELECT StudentID, StudentFName, StudentMName, StudentLName, Course, YearLvl, Section, Department FROM students WHERE isActive = 1 ORDER BY StudentID LIMIT 50");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h4><i class='fas fa-user-graduate me-2'></i>Student Code 39 Barcodes</h4>";
        echo "<button class='btn btn-primary' onclick='printBarcodes()'><i class='fas fa-print me-1'></i>Print All</button>";
        echo "</div>";
        echo "<div class='barcode-grid'>";
        
        foreach ($students as $student) {
            $barcodeUrl = $this->generateBarcodeURL($student['StudentID'], 200, 80);
            echo "<div class='barcode-card'>";
            echo "<div class='barcode-header'>";
            echo "<h5>{$student['StudentFName']} " . ($student['StudentMName'] ? $student['StudentMName'][0] . '. ' : '') . "{$student['StudentLName']}</h5>";
            echo "<p class='id-number'>{$student['StudentID']}</p>";
            echo "</div>";
            echo "<div class='barcode-code'>";
            echo "<img src='{$barcodeUrl}' alt='Barcode for {$student['StudentID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='barcode-details'>";
            echo "<small class='text-primary'><strong>{$student['Course']}</strong></small><br>";
            echo "<small>Year {$student['YearLvl']} - Section {$student['Section']}</small><br>";
            echo "<small class='text-muted'>{$student['Department']}</small>";
            echo "</div>";
            echo "<div class='mt-2'>";
            echo "<button class='btn btn-sm btn-primary me-1' onclick='downloadBarcode(\"{$student['StudentID']}\", \"{$student['StudentFName']} {$student['StudentLName']}\")'>Download</button>";
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
    
    public function generateFacultyBarcodes() {
        $stmt = $this->conn->prepare("SELECT FacultyID, FacultyFName, FacultyMName, FacultyLName, Department FROM faculty WHERE isActive = 1 ORDER BY FacultyID LIMIT 50");
        $stmt->execute();
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h4><i class='fas fa-chalkboard-teacher me-2'></i>Faculty Code 39 Barcodes</h4>";
        echo "<button class='btn btn-primary' onclick='printBarcodes()'><i class='fas fa-print me-1'></i>Print All</button>";
        echo "</div>";
        echo "<div class='barcode-grid'>";
        
        foreach ($faculty as $fac) {
            $barcodeUrl = $this->generateBarcodeURL($fac['FacultyID'], 200, 80);
            echo "<div class='barcode-card'>";
            echo "<div class='barcode-header'>";
            echo "<h5>{$fac['FacultyFName']} " . ($fac['FacultyMName'] ? $fac['FacultyMName'][0] . '. ' : '') . "{$fac['FacultyLName']}</h5>";
            echo "<p class='id-number'>{$fac['FacultyID']}</p>";
            echo "</div>";
            echo "<div class='barcode-code'>";
            echo "<img src='{$barcodeUrl}' alt='Barcode for {$fac['FacultyID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='barcode-details'>";
            echo "<small class='text-info'><strong>Faculty Member</strong></small><br>";
            echo "<small class='text-muted'>Department: {$fac['Department']}</small>";
            echo "</div>";
            echo "<div class='mt-2'>";
            echo "<button class='btn btn-sm btn-primary me-1' onclick='downloadBarcode(\"{$fac['FacultyID']}\", \"{$fac['FacultyFName']} {$fac['FacultyLName']}\")'>Download</button>";
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
    
    public function generateStaffBarcodes() {
        $stmt = $this->conn->prepare("SELECT StaffID, StaffFName, StaffMName, StaffLName, Department FROM staff WHERE isActive = 1 ORDER BY StaffID LIMIT 50");
        $stmt->execute();
        $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h4><i class='fas fa-user-tie me-2'></i>Staff Code 39 Barcodes</h4>";
        echo "<button class='btn btn-primary' onclick='printBarcodes()'><i class='fas fa-print me-1'></i>Print All</button>";
        echo "</div>";
        echo "<div class='barcode-grid'>";
        
        foreach ($staff as $stf) {
            $barcodeUrl = $this->generateBarcodeURL($stf['StaffID'], 200, 80);
            echo "<div class='barcode-card'>";
            echo "<div class='barcode-header'>";
            echo "<h5>{$stf['StaffFName']} " . ($stf['StaffMName'] ? $stf['StaffMName'][0] . '. ' : '') . "{$stf['StaffLName']}</h5>";
            echo "<p class='id-number'>{$stf['StaffID']}</p>";
            echo "</div>";
            echo "<div class='barcode-code'>";
            echo "<img src='{$barcodeUrl}' alt='Barcode for {$stf['StaffID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='barcode-details'>";
            echo "<small class='text-warning'><strong>Staff Member</strong></small><br>";
            echo "<small class='text-muted'>Department: {$stf['Department']}</small>";
            echo "</div>";
            echo "<div class='mt-2'>";
            echo "<button class='btn btn-sm btn-primary me-1' onclick='downloadBarcode(\"{$stf['StaffID']}\", \"{$stf['StaffFName']} {$stf['StaffLName']}\")'>Download</button>";
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
$generator = new BarcodeGenerator();

if ($action === 'generate_by_id' && isset($_GET['id'])) {
    // Generate barcode for specific ID
    $id = $_GET['id'];
    $person = $generator->getPersonInfo($id);
    
    header('Content-Type: application/json');
    if ($person) {
        $barcodeUrl = $generator->generateBarcodeURL($id);
        echo json_encode([
            'success' => true,
            'person' => $person,
            'barcode_url' => $barcodeUrl
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
    $generator->generateStudentBarcodes();
    exit;
}

if ($action === 'faculty') {
    $generator->generateFacultyBarcodes();
    exit;
}

if ($action === 'staff') {
    $generator->generateStaffBarcodes();
    exit;
}

// If no specific action, return error
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Invalid action specified'
]);
?>
