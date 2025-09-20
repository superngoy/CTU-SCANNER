<?php
session_start();
require_once '../../includes/functions.php';
$scanner = new CTUScanner();
$stats = $scanner->getDailyStats();
$peakHours = $scanner->getPeakHours();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../../assets/images/logo.png" alt="CTU Logo">
                <div>
                    <div>CTU Admin Dashboard</div>
                    <small style="font-size: 0.75rem; opacity: 0.9;">System Administration Panel</small>
                </div>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-shield me-2"></i>Administrator
                </span>
                <a class="nav-link" href="logout.php" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card fade-in-up">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-bar me-3"></i>Today's Campus Overview
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-primary">
                                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['total_entries']; ?></h3>
                                        <p>Total Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-success">
                                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['student_entries']; ?></h3>
                                        <p>Student Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-info">
                                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['faculty_entries']; ?></h3>
                                        <p>Faculty Entries</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card bg-warning">
                                    <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    <div class="stat-info">
                                        <h3><?php echo $stats['total_exits']; ?></h3>
                                        <p>Total Exits</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="analytics-grid">
            <div class="card fade-in-up" style="animation-delay: 0.1s;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Peak Hours Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="peakHoursChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card fade-in-up" style="animation-delay: 0.2s;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Data Export Center
                    </h5>
                </div>
                <div class="card-body">
                    <div class="export-section">
                        <div class="d-grid gap-3 mb-4">
                            <button class="btn btn-success" onclick="exportExcel()">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                                <span class="loading-spinner d-none" id="excelLoading"></span>
                            </button>
                            <button class="btn btn-danger" onclick="exportPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                                <span class="loading-spinner d-none" id="pdfLoading"></span>
                            </button>
                        </div>
                        
                        <div class="date-input-group">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-2"></i>Date Range:
                            </label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="date" id="startDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="date" id="endDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 mt-3" onclick="generateReport()">
                                <i class="fas fa-chart-bar me-2"></i>Generate Custom Report
                                <span class="loading-spinner d-none" id="reportLoading"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Generator Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card fade-in-up" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-qrcode me-2"></i>QR Code Generator & Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Quick QR Generator -->
                        <div class="row mb-4">
                            <div class="col-lg-8 mx-auto">
                                <div class="card border-2" style="border-color: var(--primary-color) !important;">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-magic me-2"></i>Quick QR Generator
                                        </h6>
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
                                                    <button type="submit" class="btn btn-primary w-100 h-100">
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
                        <div class="row mb-4">
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
                        
                        <!-- QR Results Area -->
                        <div id="qrResultsArea">
                            <!-- Generated QR codes will appear here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card fade-in-up" style="animation-delay: 0.4s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-pie-chart me-2"></i>Department Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card fade-in-up" style="animation-delay: 0.5s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-week me-2"></i>Weekly Activity Trends
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="weeklyTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Section -->
        <div class="row">
            <div class="col-12">
                <div class="card fade-in-up" style="animation-delay: 0.6s;">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>User Management System
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="management-card" onclick="manageUsers('students')">
                                    <i class="fas fa-user-graduate"></i>
                                    <h6>Manage Students</h6>
                                    <p>Add, edit, or manage student accounts and information</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="management-card" onclick="manageUsers('faculty')">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <h6>Manage Faculty</h6>
                                    <p>Add, edit, or manage faculty accounts and information</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="management-card" onclick="manageUsers('security')">
                                    <i class="fas fa-shield-alt"></i>
                                    <h6>Manage Security</h6>
                                    <p>Add, edit, or manage security personnel accounts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/admin.js"></script>
    
    <script>
        // Initialize charts with PHP data
        const peakHoursData = <?php echo json_encode($peakHours); ?>;
        
        // QR Generator Functions
        document.getElementById('quickGenerateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('idInput').value.trim();
            
            if (!id) {
                alert('Please enter an ID');
                return;
            }
            
            generateSingleQR(id);
        });
        
        function generateSingleQR(id) {
            const resultDiv = document.getElementById('quickResult');
            resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p>Generating QR code...</p></div>';
            resultDiv.style.display = 'block';
            
            fetch(`../../qr_generator_api.php?action=generate_by_id&id=${encodeURIComponent(id)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const person = data.person;
                        resultDiv.innerHTML = `
                            <div class="card border-success">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5>${person.FName} ${person.MName ? person.MName + ' ' : ''}${person.LName}</h5>
                                            <p><strong>ID:</strong> <span class="badge bg-primary">${person.ID}</span></p>
                                            <p><strong>Type:</strong> <span class="badge bg-info">${person.Type}</span></p>
                                            ${person.Course ? `<p><strong>Course:</strong> ${person.Course}</p>` : ''}
                                            ${person.YearLvl ? `<p><strong>Year:</strong> ${person.YearLvl} - Section ${person.Section}</p>` : ''}
                                            <p><strong>Department:</strong> ${person.Department}</p>
                                            
                                            <div class="mt-3">
                                                <button class="btn btn-success me-2" onclick="testScanner('${person.ID}')">
                                                    <i class="fas fa-camera me-1"></i>Test Scanner
                                                </button>
                                                <button class="btn btn-primary" onclick="downloadQR('${person.ID}', '${person.FName} ${person.LName}')">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 200px; border: 2px solid var(--primary-color); border-radius: 10px;">
                                            <p class="mt-2 text-muted">Scan with CTU Scanner</p>
                                        </div>
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
                                <p><strong>Testing Mode:</strong> Generate QR for testing purposes.</p>
                                <button class="btn btn-warning" onclick="generateTestQR('${id}')">
                                    <i class="fas fa-vial me-1"></i>Generate Test QR
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
        
        function generateTestQR(id) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(id)}`;
            const resultDiv = document.getElementById('quickResult');
            
            resultDiv.innerHTML = `
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5>Test QR Code</h5>
                                <p><strong>ID:</strong> <span class="badge bg-warning">${id}</span></p>
                                <p><strong>Type:</strong> <span class="badge bg-secondary">Test/Unknown</span></p>
                                <p class="text-warning">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This ID is not in the database but can be used for testing.
                                </p>
                                
                                <div class="mt-3">
                                    <button class="btn btn-success me-2" onclick="testScanner('${id}')">
                                        <i class="fas fa-camera me-1"></i>Test Scanner
                                    </button>
                                    <button class="btn btn-primary" onclick="downloadQR('${id}', 'Test ID')">
                                        <i class="fas fa-download me-1"></i>Download
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <img src="${qrUrl}" alt="QR Code" class="img-fluid" style="max-width: 200px; border: 2px solid var(--warning-color); border-radius: 10px;">
                                <p class="mt-2 text-muted">Test QR Code</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateAll(type) {
            const resultsArea = document.getElementById('qrResultsArea');
            resultsArea.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div><p>Loading all ' + type + ' QR codes...</p></div>';
            
            fetch(`../../qr_generator_api.php?action=${type}`)
                .then(response => response.text())
                .then(html => {
                    resultsArea.innerHTML = html;
                })
                .catch(error => {
                    resultsArea.innerHTML = '<div class="alert alert-danger">Error loading ' + type + ' codes</div>';
                });
        }
        
        function testScanner(id) {
            window.open(`../scanner/index.php?test_id=${encodeURIComponent(id)}`, '_blank');
        }
        
        function downloadQR(id, name) {
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(id)}`;
            
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
                    alert('Download failed. Right-click the QR code to save manually.');
                });
        }
        
        // Enhanced management functions
        function manageUsers(type) {
            const managementCards = document.querySelectorAll('.management-card');
            const clickedCard = event.currentTarget;
            
            clickedCard.style.transform = 'scale(0.95)';
            setTimeout(() => {
                window.location.href = `manage_users.php?type=${type}`;
            }, 200);
        }
        
        // Enhanced export functions with loading states
        function exportExcel() {
            const loading = document.getElementById('excelLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            
            setTimeout(() => {
                window.open(`export_excel.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
            }, 1000);
        }
        
        function exportPDF() {
            const loading = document.getElementById('pdfLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            loading.classList.remove('d-none');
            
            setTimeout(() => {
                window.open(`export_pdf.php?start_date=${startDate}&end_date=${endDate}`);
                loading.classList.add('d-none');
            }, 1000);
        }
        
        function generateReport() {
            const loading = document.getElementById('reportLoading');
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }
            
            loading.classList.remove('d-none');
            
            setTimeout(() => {
                alert(`Custom report generated for ${startDate} to ${endDate}`);
                loading.classList.add('d-none');
            }, 2000);
        }
        
        // Initialize page animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.fade-in-up');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            if (typeof initializeCharts === 'function') {
                initializeCharts();
            }
        });
    </script>
</body>
</html>