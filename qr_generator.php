<?php
// qr_generator.php - Enhanced QR Code Generator for CTU Scanner (FIXED VERSION)
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
        
        return $result;
    }
    
    public function generateStudentQRCodes() {
        $stmt = $this->conn->prepare("SELECT StudentID, StudentFName, StudentMName, StudentLName, Course, YearLvl, Section, Department FROM students WHERE isActive = 1 ORDER BY StudentID");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<h2><i class='fas fa-user-graduate'></i> Student QR Codes</h2>";
        echo "<div class='qr-grid'>";
        foreach ($students as $student) {
            $qrUrl = $this->generateQRCodeURL($student['StudentID'], 150);
            echo "<div class='qr-card'>";
            echo "<div class='qr-header'>";
            echo "<h4>{$student['StudentFName']} " . ($student['StudentMName'] ? $student['StudentMName'][0] . '. ' : '') . "{$student['StudentLName']}</h4>";
            echo "<p class='id-number'>{$student['StudentID']}</p>";
            echo "</div>";
            echo "<div class='qr-code'>";
            echo "<img src='{$qrUrl}' alt='QR Code for {$student['StudentID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='qr-details'>";
            echo "<small>{$student['Course']}</small><br>";
            echo "<small>Year {$student['YearLvl']} - Section {$student['Section']}</small><br>";
            echo "<small>{$student['Department']}</small>";
            echo "</div>";
            echo "<button class='btn btn-sm btn-outline-primary' onclick='downloadQR(\"{$student['StudentID']}\", \"{$student['StudentFName']} {$student['StudentLName']}\")'>Download</button>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }
    
    public function generateFacultyQRCodes() {
        $stmt = $this->conn->prepare("SELECT FacultyID, FacultyFName, FacultyMName, FacultyLName, Department FROM faculty WHERE isActive = 1 ORDER BY FacultyID");
        $stmt->execute();
        $faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='section'>";
        echo "<h2><i class='fas fa-chalkboard-teacher'></i> Faculty QR Codes</h2>";
        echo "<div class='qr-grid'>";
        foreach ($faculty as $fac) {
            $qrUrl = $this->generateQRCodeURL($fac['FacultyID'], 150);
            echo "<div class='qr-card'>";
            echo "<div class='qr-header'>";
            echo "<h4>{$fac['FacultyFName']} " . ($fac['FacultyMName'] ? $fac['FacultyMName'][0] . '. ' : '') . "{$fac['FacultyLName']}</h4>";
            echo "<p class='id-number'>{$fac['FacultyID']}</p>";
            echo "</div>";
            echo "<div class='qr-code'>";
            echo "<img src='{$qrUrl}' alt='QR Code for {$fac['FacultyID']}' loading='lazy'>";
            echo "</div>";
            echo "<div class='qr-details'>";
            echo "<small>Department: {$fac['Department']}</small>";
            echo "</div>";
            echo "<button class='btn btn-sm btn-outline-primary' onclick='downloadQR(\"{$fac['FacultyID']}\", \"{$fac['FacultyFName']} {$fac['FacultyLName']}\")'>Download</button>";
            echo "</div>";
        }
        echo "</div>";
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

// Default: Show the generator interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU QR Code Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 0;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .generator-form {
            padding: 30px;
            background: #f8f9fa;
        }
        .section {
            margin: 30px;
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .qr-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s, border-color 0.2s;
        }
        .qr-card:hover {
            transform: translateY(-5px);
            border-color: #3498db;
        }
        .qr-header h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        .id-number {
            background: #3498db;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .qr-code img {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin: 10px 0;
            max-width: 150px;
            height: auto;
        }
        .qr-details {
            margin: 10px 0;
            color: #6c757d;
        }
        .single-qr-result {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background: linear-gradient(45deg, #3498db, #2c3e50);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: transform 0.2s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            color: white;
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
            <h1><i class="fas fa-qrcode me-3"></i>CTU QR Code Generator</h1>
            <p class="mb-0">Generate QR codes for testing the scanner system</p>
            <small><span class="status-indicator status-online"></span>QR Server Online</small>
        </div>
        
        <!-- Quick ID Generator Form -->
        <div class="generator-form">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-magic me-2"></i>Generate QR Code by ID</h4>
                        </div>
                        <div class="card-body">
                            <form id="quickGenerateForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="idInput" placeholder="Enter ID" required>
                                            <label for="idInput">Enter Student ID or Faculty ID</label>
                                        </div>
                                        <small class="text-muted">
                                            Examples: 2024-001, FAC-001, or create your own test ID
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-custom w-100 h-100">
                                            <i class="fas fa-qrcode me-2"></i>Generate QR
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
                <div class="col-md-6">
                    <button class="btn btn-success w-100 py-3" onclick="generateAll('students')">
                        <i class="fas fa-user-graduate me-2"></i>Generate All Student QR Codes
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-info w-100 py-3" onclick="generateAll('faculty')">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Generate All Faculty QR Codes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Results Area -->
        <div id="resultsArea">
            <!-- Generated QR codes will appear here -->
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
            
            generateSingleQR(id);
        });
        
        // Generate single QR code
        function generateSingleQR(id) {
            const resultDiv = document.getElementById('quickResult');
            resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p>Generating QR code...</p></div>';
            resultDiv.style.display = 'block';
            
            fetch(`qr_generator.php?action=generate_by_id&id=${encodeURIComponent(id)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const person = data.person;
                        resultDiv.innerHTML = `
                            <div class="single-qr-result">
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
                                            <button class="btn btn-outline-primary" onclick="downloadQR('${person.ID}', '${person.FName} ${person.LName}')">
                                                <i class="fas fa-download me-1"></i>Download
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 250px; border: 2px solid #e9ecef; border-radius: 10px;">
                                        <p class="mt-2 text-muted">Scan this QR code with the scanner</p>
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
                                <p><strong>Testing Mode:</strong> You can still generate a QR code for testing purposes.</p>
                                <button class="btn btn-warning" onclick="generateTestQR('${id}')">
                                    <i class="fas fa-vial me-1"></i>Generate Test QR Code
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error generating QR code: ${error.message}
                        </div>
                    `;
                });
        }
        
        // Generate test QR code (for IDs not in database)
        function generateTestQR(id) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(id)}`;
            const resultDiv = document.getElementById('quickResult');
            
            resultDiv.innerHTML = `
                <div class="single-qr-result">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4>Test QR Code</h4>
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
                                <button class="btn btn-outline-primary" onclick="downloadQR('${id}', 'Test ID')">
                                    <i class="fas fa-download me-1"></i>Download
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="${qrUrl}" alt="QR Code" class="img-fluid" style="max-width: 250px; border: 2px solid #e9ecef; border-radius: 10px;">
                            <p class="mt-2 text-muted">Test QR code for scanning</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Generate all codes
        function generateAll(type) {
            const resultsArea = document.getElementById('resultsArea');
            resultsArea.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div><p>Loading all ' + type + ' QR codes...</p></div>';
            
            // Load the appropriate generator
            if (type === 'students') {
                loadStudentCodes();
            } else {
                loadFacultyCodes();
            }
        }
        
        function loadStudentCodes() {
            fetch('qr_generator.php?action=students')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('resultsArea').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('resultsArea').innerHTML = '<div class="alert alert-danger">Error loading student codes</div>';
                });
        }
        
        function loadFacultyCodes() {
            fetch('qr_generator.php?action=faculty')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('resultsArea').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('resultsArea').innerHTML = '<div class="alert alert-danger">Error loading faculty codes</div>';
                });
        }
        
        // Test with scanner
        function testScanner(id) {
            const scannerUrl = `dashboards/scanner/index.php?test_id=${encodeURIComponent(id)}`;
            window.open(scannerUrl, '_blank');
        }
        
        // Download QR code
        function downloadQR(id, name) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(id)}`;
            
            // Create a temporary link and trigger download
            fetch(qrUrl)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `QR_${id}_${name.replace(/\s+/g, '_')}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Download failed:', error);
                    alert('Download failed. You can right-click the QR code image and save it manually.');
                });
        }
        
        // Print function
        function printQRCodes() {
            window.print();
        }
    </script>
</body>
</html>

<?php
// Handle AJAX requests for batch generation
if ($action === 'students') {
    $generator->generateStudentQRCodes();
    exit;
}

if ($action === 'faculty') {
    $generator->generateFacultyQRCodes();  
    exit;
}
?>