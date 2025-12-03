<?php
// barcode_generator.php - Enhanced Code 39 Barcode Generator for CTU Scanner
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
        $stmt = $this->conn->prepare("SELECT StudentID, StudentFName, StudentMName, StudentLName, Course, YearLvl, Section, Department FROM students WHERE isActive = 1 ORDER BY StudentID");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<h2><i class='fas fa-user-graduate'></i> Student Code 39 Barcodes</h2>";
        echo "<div class='barcode-grid'>";
        foreach ($students as $student) {
            $barcodeUrl = $this->generateBarcodeURL($student['StudentID']);
            echo "<div class='barcode-card'>";
            echo "<div class='barcode-header'>";
            echo "<h4>{$student['StudentFName']} " . ($student['StudentMName'] ? $student['StudentMName'][0] . '. ' : '') . "{$student['StudentLName']}</h4>";
            echo "<p class='id-number'>{$student['StudentID']}</p>";
            echo "</div>";
            echo "<div class='barcode-code'>";
            echo "<img src='{$barcodeUrl}' alt='Barcode for {$student['StudentID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='barcode-details'>";
            echo "<small>{$student['Course']}</small><br>";
            echo "<small>Year {$student['YearLvl']} - Section {$student['Section']}</small><br>";
            echo "<small>{$student['Department']}</small>";
            echo "</div>";
            echo "<button class='btn btn-sm btn-outline-primary' onclick='downloadBarcode(\"{$student['StudentID']}\", \"{$student['StudentFName']} {$student['StudentLName']}\")'>Download</button>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }
    
    public function generateFacultyBarcodes() {
        $stmt = $this->conn->prepare("SELECT FacultyID, FacultyFName, FacultyMName, FacultyLName, Department FROM faculty WHERE isActive = 1 ORDER BY FacultyID");
        $stmt->execute();
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<h2><i class='fas fa-chalkboard-teacher'></i> Faculty Code 39 Barcodes</h2>";
        echo "<div class='barcode-grid'>";
        foreach ($faculty as $fac) {
            $barcodeUrl = $this->generateBarcodeURL($fac['FacultyID']);
            echo "<div class='barcode-card'>";
            echo "<div class='barcode-header'>";
            echo "<h4>{$fac['FacultyFName']} " . ($fac['FacultyMName'] ? $fac['FacultyMName'][0] . '. ' : '') . "{$fac['FacultyLName']}</h4>";
            echo "<p class='id-number'>{$fac['FacultyID']}</p>";
            echo "</div>";
            echo "<div class='barcode-code'>";
            echo "<img src='{$barcodeUrl}' alt='Barcode for {$fac['FacultyID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='barcode-details'>";
            echo "<small>Department: {$fac['Department']}</small>";
            echo "</div>";
            echo "<button class='btn btn-sm btn-outline-primary' onclick='downloadBarcode(\"{$fac['FacultyID']}\", \"{$fac['FacultyFName']} {$fac['FacultyLName']}\")'>Download</button>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }
    
    public function generateStaffBarcodes() {
        $stmt = $this->conn->prepare("SELECT StaffID, StaffFName, StaffMName, StaffLName, Department FROM staff WHERE isActive = 1 ORDER BY StaffID");
        $stmt->execute();
        $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<h2><i class='fas fa-user-tie'></i> Staff Code 39 Barcodes</h2>";
        echo "<div class='barcode-grid'>";
        foreach ($staff as $stf) {
            $barcodeUrl = $this->generateBarcodeURL($stf['StaffID']);
            echo "<div class='barcode-card'>";
            echo "<div class='barcode-header'>";
            echo "<h4>{$stf['StaffFName']} " . ($stf['StaffMName'] ? $stf['StaffMName'][0] . '. ' : '') . "{$stf['StaffLName']}</h4>";
            echo "<p class='id-number'>{$stf['StaffID']}</p>";
            echo "</div>";
            echo "<div class='barcode-code'>";
            echo "<img src='{$barcodeUrl}' alt='Barcode for {$stf['StaffID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='barcode-details'>";
            echo "<small>Department: {$stf['Department']}</small>";
            echo "</div>";
            echo "<button class='btn btn-sm btn-outline-primary' onclick='downloadBarcode(\"{$stf['StaffID']}\", \"{$stf['StaffFName']} {$stf['StaffLName']}\")'>Download</button>";
            echo "</div>";
        }
        echo "</div>";
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

// Default: Show the generator interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Code 39 Barcode Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #972529;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: #FEFEFE;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 0;
            overflow: hidden;
        }
        .header {
            background: #972529;
            color: #FEFEFE;
            padding: 30px;
            text-align: center;
        }
        .generator-form {
            padding: 30px;
            background: #FEFEFE;
        }
        .section {
            margin: 30px;
        }
        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .barcode-card {
            background: #FEFEFE;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s, border-color 0.2s;
        }
        .barcode-card:hover {
            transform: translateY(-5px);
            border-color: #972529;
        }
        .barcode-header h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        .id-number {
            background: #972529;
            color: #FEFEFE;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .barcode-code {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        .barcode-code img {
            border: 3px solid #2c3e50;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            margin: 10px 0;
            max-width: 100%;
            height: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .barcode-details {
            margin: 10px 0;
            color: #6c757d;
        }
        .single-barcode-result {
            background: #FEFEFE;
            border-radius: 15px;
            padding: 30px;
            margin: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background: #972529;
            border: none;
            color: #FEFEFE;
            padding: 10px 20px;
            border-radius: 25px;
            transition: transform 0.2s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            color: #FEFEFE;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #dc3545; }
        
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
            .generator-form, .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-barcode me-3"></i>CTU Code 39 Barcode Generator</h1>
            <p class="mb-0">Generate Code 39 barcodes for the scanner system</p>
            <small><span class="status-indicator status-online"></span>Barcode Service Online</small>
        </div>
        
        <!-- Quick ID Generator Form -->
        <div class="generator-form">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-magic me-2"></i>Generate Code 39 Barcode by ID</h4>
                        </div>
                        <div class="card-body">
                            <form id="quickGenerateForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="idInput" placeholder="Enter ID" required>
                                            <label for="idInput">Enter Student, Faculty, or Staff ID</label>
                                        </div>
                                        <small class="text-muted">
                                            Examples: 2024-001, FAC-001, STF-001, or create your own test ID
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-custom w-100 h-100">
                                            <i class="fas fa-barcode me-2"></i>Generate Barcode
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Quick Generate Result -->
                            <div id="quickResult" style="display: none;" class="mt-4">
                                <!-- Result will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Batch Generation Options -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <button class="btn btn-success w-100 py-3" onclick="generateAll('students')">
                        <i class="fas fa-user-graduate me-2"></i>Generate All Student Barcodes
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-info w-100 py-3" onclick="generateAll('faculty')">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Generate All Faculty Barcodes
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-warning w-100 py-3" onclick="generateAll('staff')">
                        <i class="fas fa-user-tie me-2"></i>Generate All Staff Barcodes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Results Area -->
        <div id="resultsArea">
            <!-- Generated barcodes will appear here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quick generate form handler
        document.getElementById('quickGenerateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('idInput').value.trim();
            
            if (!id) {
                alert('Please enter an ID');
                return;
            }
            
            generateSingleBarcode(id);
        });
        
        // Generate single barcode
        function generateSingleBarcode(id) {
            const resultDiv = document.getElementById('quickResult');
            resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p>Generating barcode...</p></div>';
            resultDiv.style.display = 'block';
            
            fetch(`barcode_generator.php?action=generate_by_id&id=${encodeURIComponent(id)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const person = data.person;
                        resultDiv.innerHTML = `
                            <div class="single-barcode-result">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h4>${person.FName} ${person.MName ? person.MName + ' ' : ''}${person.LName}</h4>
                                        <p><strong>ID:</strong> <span class="badge bg-primary">${person.ID}</span></p>
                                        <p><strong>Type:</strong> ${person.Type}</p>
                                        ${person.Course ? `<p><strong>Course:</strong> ${person.Course}</p>` : ''}
                                        ${person.YearLvl ? `<p><strong>Year:</strong> ${person.YearLvl} - Section ${person.Section}</p>` : ''}
                                        <p><strong>Department:</strong> ${person.Department}</p>
                                        
                                        <div class="mt-3">
                                            <button class="btn btn-success me-2" onclick="testScanner('${person.ID}')">
                                                <i class="fas fa-camera me-1"></i>Test with Scanner
                                            </button>
                                            <button class="btn btn-outline-primary" onclick="downloadBarcode('${person.ID}', '${person.FName} ${person.LName}')">
                                                <i class="fas fa-download me-1"></i>Download
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <img src="${data.barcode_url}" alt="Barcode" class="img-fluid" style="border: 3px solid #2c3e50; border-radius: 8px; padding: 8px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                                        <p class="mt-2 text-muted"><strong>Code 39 Format</strong> - Scan this barcode with the scanner</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${data.message}
                                <hr>
                                <p><strong>Testing Mode:</strong> You can still generate a barcode for testing purposes.</p>
                                <button class="btn btn-warning" onclick="generateTestBarcode('${id}')">
                                    <i class="fas fa-vial me-1"></i>Generate Test Barcode
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error generating barcode: ${error.message}
                        </div>
                    `;
                });
        }
        
        // Generate test barcode (for IDs not in database)
        function generateTestBarcode(id) {
            const barcodeUrl = `https://barcode.tec-it.com/barcode.ashx?data=${encodeURIComponent(id)}&code=Code39&dpi=150&print=true&width=350&height=120`;
            const resultDiv = document.getElementById('quickResult');
            
            resultDiv.innerHTML = `
                <div class="single-barcode-result">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4>Test Barcode</h4>
                            <p><strong>ID:</strong> <span class="badge bg-warning">${id}</span></p>
                            <p><strong>Type:</strong> Test/Unknown</p>
                            <p class="text-warning">
                                <i class="fas fa-info-circle me-1"></i>
                                This ID is not in the database but can be used for scanner testing.
                            </p>
                            
                            <div class="mt-3">
                                <button class="btn btn-success me-2" onclick="testScanner('${id}')">
                                    <i class="fas fa-camera me-1"></i>Test with Scanner
                                </button>
                                <button class="btn btn-outline-primary" onclick="downloadBarcode('${id}', 'Test ID')">
                                    <i class="fas fa-download me-1"></i>Download
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="${barcodeUrl}" alt="Barcode" class="img-fluid" style="border: 3px solid #2c3e50; border-radius: 8px; padding: 8px; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                            <p class="mt-2 text-muted"><strong>Code 39 Format</strong> - Test barcode for scanning</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Generate all codes
        function generateAll(type) {
            const resultsArea = document.getElementById('resultsArea');
            resultsArea.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div><p>Loading all ' + type + ' barcodes...</p></div>';
            
            // Load the appropriate generator
            if (type === 'students') {
                loadStudentCodes();
            } else if (type === 'faculty') {
                loadFacultyCodes();
            } else if (type === 'staff') {
                loadStaffCodes();
            }
        }
        
        function loadStudentCodes() {
            fetch('barcode_generator.php?action=students')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('resultsArea').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('resultsArea').innerHTML = '<div class="alert alert-danger">Error loading student barcodes</div>';
                });
        }
        
        function loadFacultyCodes() {
            fetch('barcode_generator.php?action=faculty')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('resultsArea').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('resultsArea').innerHTML = '<div class="alert alert-danger">Error loading faculty barcodes</div>';
                });
        }
        
        function loadStaffCodes() {
            fetch('barcode_generator.php?action=staff')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('resultsArea').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('resultsArea').innerHTML = '<div class="alert alert-danger">Error loading staff barcodes</div>';
                });
        }
        
        // Test with scanner
        function testScanner(id) {
            const scannerUrl = `dashboards/scanner/index.php?test_id=${encodeURIComponent(id)}`;
            window.open(scannerUrl, '_blank');
        }
        
        // Download barcode
        function downloadBarcode(id, name) {
            // Sanitize filename
            const safeName = name.replace(/[^a-zA-Z0-9_-]/g, '_');
            
            // Use the download API endpoint
            const downloadUrl = `api/download_barcode.php?data=${encodeURIComponent(id)}&name=${encodeURIComponent(safeName)}`;
            
            // Create an anchor element and trigger download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `Barcode_${safeName}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Print function
        function printBarcodes() {
            window.print();
        }
    </script>
</body>
</html>
