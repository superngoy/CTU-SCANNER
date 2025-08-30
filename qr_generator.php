<?php
// qr_generator.php - Utility to generate QR codes for students/faculty
require_once 'config/database.php';

class QRCodeGenerator {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function generateQRCodeURL($data, $size = 200) {
        // Using Google Charts API for QR code generation
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($data);
    }
    
    public function generateStudentQRCodes() {
        $stmt = $this->conn->prepare("SELECT StudentID, StudentFName, StudentLName FROM students WHERE isActive = 1");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Student QR Codes</h2>";
        foreach ($students as $student) {
            $qrUrl = $this->generateQRCodeURL($student['StudentID']);
            echo "<div style='margin: 20px; padding: 20px; border: 1px solid #ccc; display: inline-block; text-align: center;'>";
            echo "<h4>{$student['StudentFName']} {$student['StudentLName']}</h4>";
            echo "<p>ID: {$student['StudentID']}</p>";
            echo "<img src='{$qrUrl}' alt='QR Code' style='border: 1px solid #ddd;'>";
            echo "</div>";
        }
    }
    
    public function generateFacultyQRCodes() {
        $stmt = $this->conn->prepare("SELECT FacultyID, FacultyFName, FacultyLName FROM faculty WHERE isActive = 1");
        $stmt->execute();
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Faculty QR Codes</h2>";
        foreach ($faculty as $fac) {
            $qrUrl = $this->generateQRCodeURL($fac['FacultyID']);
            echo "<div style='margin: 20px; padding: 20px; border: 1px solid #ccc; display: inline-block; text-align: center;'>";
            echo "<h4>{$fac['FacultyFName']} {$fac['FacultyLName']}</h4>";
            echo "<p>ID: {$fac['FacultyID']}</p>";
            echo "<img src='{$qrUrl}' alt='QR Code' style='border: 1px solid #ddd;'>";
            echo "</div>";
        }
    }
}

// Usage
if (isset($_GET['generate'])) {
    $generator = new QRCodeGenerator();
    echo "<!DOCTYPE html><html><head><title>QR Code Generator</title></head><body>";
    $generator->generateStudentQRCodes();
    $generator->generateFacultyQRCodes();
    echo "</body></html>";
}
?>