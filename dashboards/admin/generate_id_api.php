<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../includes/functions.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $scanner = new CTUScanner();

    if ($action === 'get_person') {
        // Fetch person details by ID and type
        $personId = $_POST['person_id'] ?? '';
        $personType = $_POST['person_type'] ?? '';

        header('Content-Type: application/json');

        if (empty($personId) || empty($personType)) {
            echo json_encode(['error' => 'Invalid parameters']);
            exit();
        }

        try {
            $person = null;

            if ($personType === 'student') {
                $stmt = $scanner->conn->prepare("SELECT * FROM students WHERE StudentID = ?");
                $stmt->execute([$personId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $person = [
                        'id' => $result['StudentID'],
                        'firstName' => $result['StudentFName'],
                        'middleName' => $result['StudentMName'] ?? '',
                        'lastName' => $result['StudentLName'],
                        'course' => $result['Course'],
                        'yearLevel' => $result['YearLvl'],
                        'section' => $result['Section'],
                        'department' => $result['Department'],
                        'birthDate' => $result['BirthDate'],
                        'gender' => $result['Gender'],
                        'image' => $result['image'] ?? null,
                        'type' => 'student'
                    ];
                }
            } elseif ($personType === 'faculty') {
                $stmt = $scanner->conn->prepare("SELECT * FROM faculty WHERE FacultyID = ?");
                $stmt->execute([$personId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $person = [
                        'id' => $result['FacultyID'],
                        'firstName' => $result['FacultyFName'],
                        'middleName' => $result['FacultyMName'] ?? '',
                        'lastName' => $result['FacultyLName'],
                        'position' => $result['Position'] ?? '',
                        'department' => $result['Department'],
                        'birthDate' => $result['BirthDate'] ?? '',
                        'gender' => $result['Gender'] ?? '',
                        'image' => $result['image'] ?? null,
                        'type' => 'faculty'
                    ];
                }
            } elseif ($personType === 'staff') {
                $stmt = $scanner->conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
                $stmt->execute([$personId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $person = [
                        'id' => $result['StaffID'],
                        'firstName' => $result['StaffFName'],
                        'middleName' => $result['StaffMName'] ?? '',
                        'lastName' => $result['StaffLName'],
                        'position' => $result['Position'] ?? '',
                        'department' => $result['Department'],
                        'birthDate' => $result['BirthDate'] ?? '',
                        'gender' => $result['Gender'] ?? '',
                        'image' => $result['image'] ?? null,
                        'type' => 'staff'
                    ];
                }
            }

            if ($person) {
                echo json_encode(['success' => true, 'data' => $person]);
            } else {
                echo json_encode(['error' => 'Person not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }
}

// Handle GET requests for HTML rendering
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['person_id']) && isset($_GET['person_type'])) {
    $personId = $_GET['person_id'];
    $personType = $_GET['person_type'];
    $scanner = new CTUScanner();

    try {
        $person = null;

        if ($personType === 'student') {
            $stmt = $scanner->conn->prepare("SELECT * FROM students WHERE StudentID = ?");
            $stmt->execute([$personId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $person = [
                    'id' => $result['StudentID'],
                    'firstName' => $result['StudentFName'],
                    'middleName' => $result['StudentMName'] ?? '',
                    'lastName' => $result['StudentLName'],
                    'course' => $result['Course'],
                    'yearLevel' => $result['YearLvl'],
                    'section' => $result['Section'],
                    'department' => $result['Department'],
                    'birthDate' => $result['BirthDate'],
                    'gender' => $result['Gender'],
                    'image' => $result['image'] ?? null,
                    'type' => 'student'
                ];
            }
        } elseif ($personType === 'faculty') {
            $stmt = $scanner->conn->prepare("SELECT * FROM faculty WHERE FacultyID = ?");
            $stmt->execute([$personId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $person = [
                    'id' => $result['FacultyID'],
                    'firstName' => $result['FacultyFName'],
                    'middleName' => $result['FacultyMName'] ?? '',
                    'lastName' => $result['FacultyLName'],
                    'position' => $result['Position'] ?? '',
                    'department' => $result['Department'],
                    'birthDate' => $result['BirthDate'] ?? '',
                    'gender' => $result['Gender'] ?? '',
                    'image' => $result['image'] ?? null,
                    'type' => 'faculty'
                ];
            }
        } elseif ($personType === 'staff') {
            $stmt = $scanner->conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
            $stmt->execute([$personId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $person = [
                    'id' => $result['StaffID'],
                    'firstName' => $result['StaffFName'],
                    'middleName' => $result['StaffMName'] ?? '',
                    'lastName' => $result['StaffLName'],
                    'position' => $result['Position'] ?? '',
                    'department' => $result['Department'],
                    'birthDate' => $result['BirthDate'] ?? '',
                    'gender' => $result['Gender'] ?? '',
                    'image' => $result['image'] ?? null,
                    'type' => 'staff'
                ];
            }
        }

        if (!$person) {
            die('Person not found');
        }

    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate ID Card</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
        }

        /* ID Card Styles */
        .id-card {
            width: 320px;
            height: 500px;
            background: linear-gradient(to bottom, #fff 0%, #fff 40%, #972529 85%, #972529 100%);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            position: relative;
            page-break-inside: avoid;
            border: none !important;
            padding: 0 !important;
        }

        .id-card-back {
            background: linear-gradient(to bottom, #fff 0%, #fff 40%, #972529 85%, #972529 100%);
        }

        .header {
            background: white;
            padding: 15px;
            text-align: center;
            border-bottom: 3px solid #972529;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #972529;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
            overflow: hidden;
            border: 2px solid #E5C573;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .asean-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #972529;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fbbf24;
            font-size: 24px;
            overflow: hidden;
            border: 2px solid #E5C573;
        }

        .asean-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .university-name {
            color: #972529;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .university-title {
            color: #972529;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .campus-info {
            color: #972529;
            font-size: 11px;
            margin-top: 3px;
        }

        .photo-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            padding: 0 10px;
            position: relative;
        }

        .photo-container {
            width: 150px;
            height: 180px;
            background: #e2e8f0;
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 14px;
            overflow: hidden;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .barcode-vertical {
            position: absolute;
            right: -8px;
            top: 0;
            width: 18px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        .barcode-vertical svg {
            width: 100% !important;
            height: auto !important;
        }

        .barcode-container {
            position: absolute;
            right: 0;
            top: 70%;
            transform: translateY(-50%) rotate(90deg);
            transform-origin: right center;
            width: auto;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            z-index: 1;
        }

        .barcode-container svg {
            width: auto !important;
            height: auto !important;
        }

        .name {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin: 8px 0 5px 0;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.7);
            min-height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            word-break: break-word;
        }

        .course {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin: 3px auto;
            border-bottom: 2px solid #000;
            padding: 3px;
            width: 200px;
            background: rgba(255, 255, 255, 0.7);
            word-break: break-word;
        }

        .course-label {
            font-size: 8px;
            color: #000;
            text-align: center;
            margin-top: -2px;
            background: rgba(255, 255, 255, 0.7);
            padding: 1px;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }

        .id-number {
            text-align: center;
            font-size: 8px;
            color: #E5C573;
            margin: 1px 0;
            background: #972529;
            padding: 5px 3px;
            font-weight: 700;
            position: relative;
            z-index: 10;
            border-radius: 5px;
        }

        .id-value {
            font-size: 18px;
            font-weight: 900;
            color: #fff;
            display: block;
            margin-top: 2px;
        }
            color: #000;
        }

        /* Back Card Styles */
        .back-content {
            padding: 30px 20px;
        }

        .back-info {
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #972529;
            padding-bottom: 10px;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            font-weight: bold;
            color: #972529;
            font-size: 12px;
            width: 100px;
        }

        .info-value {
            color: #1e293b;
            font-size: 12px;
            flex: 1;
            word-break: break-word;
        }

        .emergency-contact {
            background: rgba(255,255,255,0.9);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #972529;
        }

        .emergency-title {
            font-weight: bold;
            color: #972529;
            font-size: 11px;
            margin-bottom: 8px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .container {
                gap: 60px;
            }

            .id-card {
                box-shadow: none;
            }

            .no-print {
                display: none;
            }
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
            width: 100%;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-print {
            background: #972529;
            color: white;
        }

        .btn-print:hover {
            background: #7a1d20;
            box-shadow: 0 4px 12px rgba(151,37,41,0.3);
        }

        .btn-download-png {
            background: #E5C573;
            color: #333;
        }

        .btn-download-png:hover {
            background: #d4b85a;
            box-shadow: 0 4px 12px rgba(229,197,115,0.3);
        }

        .btn-download-pdf {
            background: #972529;
            color: white;
        }

        .btn-download-pdf:hover {
            background: #7a1d20;
            box-shadow: 0 4px 12px rgba(151,37,41,0.3);
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
            box-shadow: 0 4px 12px rgba(107,114,128,0.3);
        }
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .btn-back:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Front of ID -->
        <div class="id-card">
            <div class="header">
                <div class="logo-container">
                    <div class="logo">
                        <img src="../../assets/images/logo.png" alt="CTU Logo">
                    </div>
                    <div class="asean-logo">
                        <img src="../../assets/images/asean.png" alt="ASEAN Logo">
                    </div>
                </div>
                <div class="university-name">Republic of the Philippines</div>
                <div class="university-title">CEBU TECHNOLOGICAL<br>UNIVERSITY</div>
                <div class="campus-info">Daanbantayan Campus:<br>Agujo, Daanbantayan, Cebu</div>
            </div>

            <div class="photo-section">
                <div class="photo-container">
                    <?php if (isset($person['image']) && !empty($person['image']) && file_exists('../../' . $person['image'])): ?>
                        <img src="../../<?php echo htmlspecialchars($person['image']); ?>" alt="Photo">
                    <?php else: ?>
                        [PHOTO]
                    <?php endif; ?>
                </div>
            </div>

            <div class="barcode-container">
                <svg id="barcode"></svg>
            </div>

            <div class="name">
                <?php 
                if (isset($person)) {
                    $name = trim($person['firstName'] . ' ' . ($person['middleName'] ?? '') . ' ' . $person['lastName']);
                    echo htmlspecialchars($name);
                } else {
                    echo '[NAME]';
                }
                ?>
            </div>

            <?php if (isset($person) && isset($person['course'])): ?>
                <div class="course"><?php echo htmlspecialchars($person['course']); ?></div>
                <div class="course-label">COURSE:</div>
            <?php else: ?>
                <div class="course"><?php echo isset($person['position']) ? htmlspecialchars($person['position']) : '[POSITION]'; ?></div>
                <div class="course-label"><?php echo isset($person['course']) ? 'COURSE:' : 'POSITION:'; ?></div>
            <?php endif; ?>

            <div class="id-number">
                ID No. <span class="id-value"><?php echo isset($person) ? htmlspecialchars($person['id']) : '[ID]'; ?></span>
            </div>
        </div>

        <!-- Back of ID -->
        <div class="id-card id-card-back">
            <div class="back-content">
                <div class="back-info">
                    <?php if (isset($person)): ?>
                        <div class="info-row">
                            <div class="info-label">Blood Type:</div>
                            <div class="info-value">[BLOOD TYPE]</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Birthdate:</div>
                            <div class="info-value"><?php echo !empty($person['birthDate']) ? htmlspecialchars($person['birthDate']) : '[N/A]'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Guardian:</div>
                            <div class="info-value">[GUARDIAN NAME]</div>
                        </div>
                        <div class="info-row" style="border-bottom: none;">
                            <div class="info-label">Contact No.:</div>
                            <div class="info-value">[CONTACT NUMBER]</div>
                        </div>
                    <?php else: ?>
                        <div class="info-row">
                            <div class="info-label">Blood Type:</div>
                            <div class="info-value">[BLOOD TYPE]</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Birthdate:</div>
                            <div class="info-value">[BIRTHDATE]</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Guardian:</div>
                            <div class="info-value">[GUARDIAN NAME]</div>
                        </div>
                        <div class="info-row" style="border-bottom: none;">
                            <div class="info-label">Contact No.:</div>
                            <div class="info-value">[CONTACT NUMBER]</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="emergency-contact">
                    <div class="emergency-title">IN CASE OF EMERGENCY</div>
                    <div class="info-row">
                        <div class="info-label">Contact:</div>
                        <div class="info-value">[GUARDIAN CONTACT]</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons no-print">
            <button class="btn btn-download-png" onclick="downloadAsImage()"><i class="fas fa-image"></i> Download as PNG</button>
            <button class="btn btn-download-pdf" onclick="downloadAsPDF()"><i class="fas fa-file-pdf"></i> Download as PDF</button>
            <button class="btn btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print ID</button>
            <button class="btn btn-back" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Generate barcode from ID
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($person)): ?>
                JsBarcode("#barcode", "<?php echo htmlspecialchars($person['id']); ?>", {
                    format: "code39",
                    width: 1,
                    height: 80,
                    displayValue: false,
                    margin: 0,
                    lineColor: "#000000"
                });
            <?php endif; ?>
        });

        // Download ID card as PNG image
        function downloadAsImage() {
            const idCards = document.querySelectorAll('.id-card');
            let cardIndex = 0;

            idCards.forEach((card, index) => {
                setTimeout(() => {
                    html2canvas(card, {
                        scale: 2,
                        backgroundColor: null,
                        useCORS: true
                    }).then(canvas => {
                        const link = document.createElement('a');
                        link.href = canvas.toDataURL('image/png');
                        link.download = `CTU_ID_Card_<?php echo isset($person) ? htmlspecialchars($person['id']) : 'Sample'; ?>_${index === 0 ? 'Front' : 'Back'}.png`;
                        link.click();
                    });
                }, index * 500);
            });
        }

        // Download ID card as PDF
        function downloadAsPDF() {
            const { jsPDF } = window.jspdf;
            const idCards = document.querySelectorAll('.id-card');
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            let pageCount = 0;

            idCards.forEach((card, index) => {
                html2canvas(card, {
                    scale: 2,
                    backgroundColor: null,
                    useCORS: true
                }).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const imgWidth = 80;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    const xPos = (210 - imgWidth) / 2;
                    const yPos = 20 + (index * (imgHeight + 10));

                    if (yPos + imgHeight > 277) {
                        pdf.addPage();
                    }

                    pdf.addImage(imgData, 'PNG', xPos, yPos, imgWidth, imgHeight);
                    pageCount++;

                    if (pageCount === idCards.length) {
                        pdf.save(`CTU_ID_Card_<?php echo isset($person) ? htmlspecialchars($person['id']) : 'Sample'; ?>.pdf`);
                    }
                });
            });
        }
    </script>
</body>
</html>
