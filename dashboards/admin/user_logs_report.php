<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once '../../includes/functions.php';

$scanner = new CTUScanner();
$logs = [];
$personDetails = null;
$searchPerformed = false;
$selectedDate = date('Y-m-d');
$personId = '';
$personType = 'student';

// Handle AJAX search request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'search_user') {
        $searchId = trim($_POST['person_id'] ?? '');
        $searchType = $_POST['person_type'] ?? 'student';
        $searchDate = $_POST['date'] ?? date('Y-m-d');
        
        if (empty($searchId)) {
            echo json_encode(['error' => 'Please enter a user ID']);
            exit();
        }
        
        try {
            // Get person details based on type
            $person = null;
            
            if ($searchType === 'student') {
                $stmt = $scanner->conn->prepare("SELECT * FROM students WHERE StudentID = ?");
                $stmt->execute([$searchId]);
                $person = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($person) {
                    $person['Type'] = 'Student';
                    $person['DisplayName'] = $person['StudentFName'] . ' ' . ($person['StudentMName'] ?? '') . ' ' . $person['StudentLName'];
                    $person['Department'] = $person['Department'] ?? 'N/A';
                }
            } elseif ($searchType === 'faculty') {
                $stmt = $scanner->conn->prepare("SELECT * FROM faculty WHERE FacultyID = ?");
                $stmt->execute([$searchId]);
                $person = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($person) {
                    $person['Type'] = 'Faculty';
                    $person['DisplayName'] = $person['FacultyFName'] . ' ' . ($person['FacultyMName'] ?? '') . ' ' . $person['FacultyLName'];
                    $person['Department'] = $person['Department'] ?? 'N/A';
                }
            } elseif ($searchType === 'staff') {
                $stmt = $scanner->conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
                $stmt->execute([$searchId]);
                $person = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($person) {
                    $person['Type'] = 'Staff';
                    $person['DisplayName'] = $person['StaffFName'] . ' ' . ($person['StaffMName'] ?? '') . ' ' . $person['StaffLName'];
                    $person['Department'] = $person['Department'] ?? 'N/A';
                }
            }
            
            if (!$person) {
                echo json_encode(['error' => 'User not found']);
                exit();
            }
            
            // Get logs for the selected date
            $stmt = $scanner->conn->prepare("SELECT * FROM entrylogs WHERE PersonID = ? AND PersonType = ? AND Date = ? ORDER BY Timestamp DESC");
            $stmt->execute([$searchId, $searchType, $searchDate]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get exit logs if available
            $exitLogs = [];
            $stmt = $scanner->conn->prepare("SELECT * FROM exitlogs WHERE PersonID = ? AND PersonType = ? AND Date = ? ORDER BY Timestamp DESC");
            if ($stmt->execute([$searchId, $searchType, $searchDate])) {
                $exitLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Merge and sort logs
            $allLogs = [];
            foreach ($logs as $log) {
                $log['Type'] = 'Entry';
                $allLogs[] = $log;
            }
            foreach ($exitLogs as $log) {
                $log['Type'] = 'Exit';
                $allLogs[] = $log;
            }
            
            // Sort by timestamp
            usort($allLogs, function($a, $b) {
                return strtotime($b['Timestamp']) - strtotime($a['Timestamp']);
            });
            
            echo json_encode([
                'success' => true,
                'person' => $person,
                'logs' => $allLogs,
                'log_count' => count($allLogs)
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Logs Report - CTU Scanner Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js"></script>
    <style>
        :root {
            --primary-color: #972529;
            --secondary-color: #E5C573;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-bottom: 3px solid var(--primary-color);
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            color: var(--primary-color);
            margin: 0;
            font-weight: 700;
        }

        .search-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: flex-end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(151,37,41,0.1);
            outline: none;
        }

        .btn-search {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-search:hover {
            background: #7a1d20;
            box-shadow: 0 4px 12px rgba(151,37,41,0.3);
            transform: translateY(-2px);
        }

        .user-info {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: none;
        }

        .user-info.active {
            display: block;
            border-left: 5px solid var(--secondary-color);
        }

        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .user-info-item {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .user-info-item:last-child {
            border-bottom: none;
        }

        .user-info-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .user-info-value {
            font-size: 16px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .logs-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: none;
        }

        .logs-section.active {
            display: block;
        }

        .logs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .logs-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logs-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-export {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-export-pdf {
            background: var(--primary-color);
            color: white;
        }

        .btn-export-pdf:hover {
            background: #7a1d20;
            box-shadow: 0 4px 12px rgba(151,37,41,0.3);
        }

        .btn-export-excel {
            background: var(--secondary-color);
            color: #333;
        }

        .btn-export-excel:hover {
            background: #d4b85a;
            box-shadow: 0 4px 12px rgba(229,197,115,0.3);
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .logs-table thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .logs-table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 13px;
            text-transform: uppercase;
        }

        .logs-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .logs-table tbody tr:hover {
            background: #f9fafb;
        }

        .log-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .log-type.entry {
            background: #d1fae5;
            color: #065f46;
        }

        .log-type.exit {
            background: #fee2e2;
            color: #991b1b;
        }

        .no-logs {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .no-logs i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }

        .error-message {
            background: #fee2e2;
            border: 2px solid #fecaca;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media print {
            body {
                background: white;
            }

            .page-header,
            .search-section,
            .logs-actions {
                display: none;
            }

            .user-info,
            .logs-section {
                box-shadow: none;
                border: none;
            }
        }

        @media (max-width: 1200px) {
            .search-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .logs-table {
                font-size: 12px;
            }

            .logs-table th,
            .logs-table td {
                padding: 8px;
            }

            .logs-actions {
                justify-content: flex-start;
            }

            .btn-export {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1><i class="fas fa-file-alt"></i> User Logs Report</h1>
    </div>

    <div class="container-fluid" style="padding: 0 30px 30px 30px;">
        <!-- Search Section -->
        <div class="search-section">
            <h5 style="margin-bottom: 20px; color: var(--primary-color); font-weight: 700;">Search User Logs</h5>
            <div class="search-form">
                <div class="form-group">
                    <label for="personType">User Type</label>
                    <select id="personType" class="form-select">
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="personId">User ID</label>
                    <input type="text" id="personId" class="form-control" placeholder="Enter user ID">
                </div>
                <div class="form-group">
                    <label for="searchDate">Date</label>
                    <input type="date" id="searchDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <button type="button" class="btn-search" onclick="searchUser()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div class="error-message" id="errorMessage"></div>

        <!-- Loading Indicator -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Searching for user logs...</p>
        </div>

        <!-- User Info Section -->
        <div class="user-info" id="userInfo">
            <div class="user-info-grid" id="userInfoGrid"></div>
        </div>

        <!-- Logs Section -->
        <div class="logs-section" id="logsSection">
            <div class="logs-header">
                <div class="logs-title">
                    <i class="fas fa-history"></i> Activity Logs - <span id="logsDateDisplay"></span>
                </div>
                <div class="logs-actions">
                    <button class="btn-export btn-export-excel" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                    <button class="btn-export btn-export-pdf" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                </div>
            </div>
            <div id="logsContainer"></div>
        </div>
    </div>

    <script>
        let currentLogs = [];
        let currentPerson = null;
        let currentDate = new Date().toISOString().split('T')[0];

        function searchUser() {
            const personType = document.getElementById('personType').value;
            const personId = document.getElementById('personId').value.trim();
            const searchDate = document.getElementById('searchDate').value;

            if (!personId) {
                showError('Please enter a user ID');
                return;
            }

            currentDate = searchDate;
            showLoading(true);
            hideError();

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=search_user&person_type=${personType}&person_id=${encodeURIComponent(personId)}&date=${searchDate}`
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);

                if (data.error) {
                    showError(data.error);
                    hideUserInfo();
                    hideLogs();
                } else {
                    currentPerson = data.person;
                    currentLogs = data.logs;

                    displayUserInfo(data.person);
                    displayLogs(data.logs, searchDate);
                    showUserInfo();
                    showLogs();
                }
            })
            .catch(error => {
                showLoading(false);
                showError('Error searching user: ' + error.message);
            });
        }

        function displayUserInfo(person) {
            const userInfoGrid = document.getElementById('userInfoGrid');
            const userTypeColor = person.Type === 'Student' ? '#3b82f6' : (person.Type === 'Faculty' ? '#8b5cf6' : '#10b981');

            userInfoGrid.innerHTML = `
                <div class="user-info-item">
                    <div class="user-info-label">User ID</div>
                    <div class="user-info-value">${person.StudentID || person.FacultyID || person.StaffID}</div>
                </div>
                <div class="user-info-item">
                    <div class="user-info-label">Full Name</div>
                    <div class="user-info-value">${person.DisplayName}</div>
                </div>
                <div class="user-info-item">
                    <div class="user-info-label">Type</div>
                    <div class="user-info-value" style="color: ${userTypeColor};">${person.Type}</div>
                </div>
                <div class="user-info-item">
                    <div class="user-info-label">Department</div>
                    <div class="user-info-value">${person.Department}</div>
                </div>
            `;
        }

        function displayLogs(logs, date) {
            const logsContainer = document.getElementById('logsContainer');
            const logsDateDisplay = document.getElementById('logsDateDisplay');

            const dateObj = new Date(date);
            logsDateDisplay.textContent = dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            if (logs.length === 0) {
                logsContainer.innerHTML = `
                    <div class="no-logs">
                        <i class="fas fa-inbox"></i>
                        <p>No logs found for this date</p>
                    </div>
                `;
                return;
            }

            let tableHtml = `
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Scanner Location</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            logs.forEach(log => {
                const time = new Date(log.Timestamp).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                const typeClass = log.Type.toLowerCase();
                const scannerLocation = log.ScannerID || 'N/A';

                tableHtml += `
                    <tr>
                        <td>${time}</td>
                        <td><span class="log-type ${typeClass}">${log.Type}</span></td>
                        <td>${scannerLocation}</td>
                    </tr>
                `;
            });

            tableHtml += `
                    </tbody>
                </table>
            `;

            logsContainer.innerHTML = tableHtml;
        }

        function exportToExcel() {
            if (!currentPerson || currentLogs.length === 0) {
                alert('No data to export');
                return;
            }

            const workbook = XLSX.utils.book_new();
            
            // User Info Sheet
            const userInfoData = [
                ['User Information'],
                ['Field', 'Value'],
                ['User ID', currentPerson.StudentID || currentPerson.FacultyID || currentPerson.StaffID],
                ['Full Name', currentPerson.DisplayName],
                ['Type', currentPerson.Type],
                ['Department', currentPerson.Department],
                ['Search Date', currentDate]
            ];
            
            const userSheet = XLSX.utils.aoa_to_sheet(userInfoData);
            XLSX.utils.book_append_sheet(workbook, userSheet, 'User Info');

            // Logs Sheet
            const logsData = [
                ['Activity Logs'],
                ['Date', currentDate],
                ['Time', 'Type', 'Scanner Location']
            ];

            currentLogs.forEach(log => {
                const time = new Date(log.Timestamp).toLocaleTimeString();
                logsData.push([time, log.Type, log.ScannerID || 'N/A']);
            });

            const logsSheet = XLSX.utils.aoa_to_sheet(logsData);
            XLSX.utils.book_append_sheet(workbook, logsSheet, 'Activity Logs');

            // Generate filename
            const userId = currentPerson.StudentID || currentPerson.FacultyID || currentPerson.StaffID;
            const filename = `User_Logs_${userId}_${currentDate}.xlsx`;

            XLSX.writeFile(workbook, filename);
        }

        function exportToPDF() {
            if (!currentPerson || currentLogs.length === 0) {
                alert('No data to export');
                return;
            }

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();
            
            const userId = currentPerson.StudentID || currentPerson.FacultyID || currentPerson.StaffID;
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            let yPosition = 15;

            // Header
            pdf.setFillColor(151, 37, 41);
            pdf.rect(0, 0, pageWidth, 30, 'F');
            pdf.setTextColor(255, 255, 255);
            pdf.setFontSize(18);
            pdf.text('User Activity Report', 15, 20);

            // User Info
            yPosition = 45;
            pdf.setTextColor(0, 0, 0);
            pdf.setFontSize(12);
            pdf.setFont(undefined, 'bold');
            pdf.text('User Information', 15, yPosition);
            
            yPosition += 10;
            pdf.setFontSize(10);
            pdf.setFont(undefined, 'normal');
            
            const userInfo = [
                `User ID: ${userId}`,
                `Name: ${currentPerson.DisplayName}`,
                `Type: ${currentPerson.Type}`,
                `Department: ${currentPerson.Department}`,
                `Date: ${currentDate}`
            ];

            userInfo.forEach(info => {
                pdf.text(info, 15, yPosition);
                yPosition += 8;
            });

            // Logs Table
            yPosition += 10;
            pdf.setFont(undefined, 'bold');
            pdf.text('Activity Logs', 15, yPosition);

            yPosition += 10;
            pdf.setFontSize(9);
            pdf.setFont(undefined, 'normal');

            // Table headers
            const colWidth = pageWidth / 3.5;
            pdf.setFillColor(229, 197, 115);
            pdf.rect(15, yPosition - 5, colWidth - 2, 7, 'F');
            pdf.rect(15 + colWidth - 2, yPosition - 5, colWidth - 2, 7, 'F');
            pdf.rect(15 + (colWidth - 2) * 2, yPosition - 5, colWidth - 2, 7, 'F');

            pdf.setTextColor(0, 0, 0);
            pdf.text('Time', 17, yPosition);
            pdf.text('Type', 15 + colWidth, yPosition);
            pdf.text('Scanner', 15 + (colWidth - 2) * 2 + 2, yPosition);

            yPosition += 8;

            // Table data
            currentLogs.forEach((log, index) => {
                if (yPosition > pageHeight - 20) {
                    pdf.addPage();
                    yPosition = 15;
                }

                const time = new Date(log.Timestamp).toLocaleTimeString();
                pdf.text(time, 17, yPosition);
                pdf.text(log.Type, 15 + colWidth, yPosition);
                pdf.text(log.ScannerID || 'N/A', 15 + (colWidth - 2) * 2 + 2, yPosition);

                yPosition += 7;
            });

            // Footer
            const totalPages = pdf.internal.pages.length - 1;
            for (let i = 1; i <= totalPages; i++) {
                pdf.setPage(i);
                pdf.setFontSize(8);
                pdf.setTextColor(128, 128, 128);
                pdf.text(`Page ${i} of ${totalPages}`, pageWidth - 20, pageHeight - 10);
            }

            pdf.save(`User_Logs_${userId}_${currentDate}.pdf`);
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
        }

        function hideError() {
            document.getElementById('errorMessage').classList.remove('show');
        }

        function showLoading(show) {
            document.getElementById('loading').classList.toggle('show', show);
        }

        function showUserInfo() {
            document.getElementById('userInfo').classList.add('active');
        }

        function hideUserInfo() {
            document.getElementById('userInfo').classList.remove('active');
        }

        function showLogs() {
            document.getElementById('logsSection').classList.add('active');
        }

        function hideLogs() {
            document.getElementById('logsSection').classList.remove('active');
        }

        // Allow search on Enter key
        document.getElementById('personId').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchUser();
            }
        });

        // Update search date when input changes
        document.getElementById('searchDate').addEventListener('change', function() {
            if (currentPerson) {
                searchUser();
            }
        });
    </script>
</body>
</html>
