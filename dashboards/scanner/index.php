<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner - QR Code Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="scanner-bg">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="scanner-header text-center py-4">
                    <img src="../../assets/images/logo.png" alt="CTU Logo" class="logo mb-3">
                    <h1 class="text-white mb-2">CTU Access Control System</h1>
                    <p class="text-white-50">Scan your QR Code to Enter/Exit</p>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="scanner-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Code Scanner</h4>
                            <div class="scanner-controls">
                                <select id="scannerSelect" class="form-select form-select-sm">
                                    <option value="SC001">Main Entrance</option>
                                    <option value="SC002">Main Exit</option>
                                    <option value="SC003">Library Entrance</option>
                                    <option value="SC004">Library Exit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div id="reader" class="qr-reader"></div>
                        
                        <div class="scanner-status mt-4">
                            <div id="scanResult" class="alert" style="display: none;"></div>
                            <div id="scannerStatus" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading scanner...</span>
                                </div>
                                <p class="mt-2 text-muted">Initializing camera...</p>
                            </div>
                        </div>
                        
                        <!-- Recent Scans -->
                        <div class="recent-scans mt-4">
                            <h5><i class="fas fa-history me-2"></i>Recent Scans</h5>
                            <div id="recentScans" class="list-group">
                                <!-- Recent scans will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/html5-qrcode.min.js"></script>
    <script src="../../assets/js/scanner.js"></script>
</body>
</html>