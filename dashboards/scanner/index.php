<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#FF9B00">
    <title>CTU Scanner - QR Code Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #FF9B00;
            --secondary-yellow: #FFE100;
            --tertiary-gold: #FFC900;
            --light-beige: #EBE389;
            --gradient-bg: linear-gradient(135deg, #6a5acd 0%, #4169e1 50%, #1e90ff 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-bg);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .scanner-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .scanner-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .scanner-header h1 {
            color: white;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .scanner-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        .scanner-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            flex: 1;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 200px);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--tertiary-gold));
            padding: 20px;
            border-radius: 20px 20px 0 0;
            color: white;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .scanner-controls select {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 10px;
            padding: 8px 12px;
        }

        .scanner-controls select option {
            background: #333;
            color: white;
        }

        .card-body {
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .scanning-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 20;
            color: var(--secondary-yellow);
            font-size: 2rem;
            animation: pulse 2s infinite;
            pointer-events: none;
        }

        .qr-reader {
            width: 100%;
            max-width: 400px;
            height: 300px;
            border: 3px solid var(--primary-orange);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            background: #f8f9fa;
            touch-action: manipulation;
        }

        .qr-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
        }

        .scanner-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 2px solid var(--secondary-yellow);
            border-radius: 10px;
            z-index: 10;
            pointer-events: none;
        }

        .scanner-frame::before,
        .scanner-frame::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid var(--secondary-yellow);
        }

        .scanner-frame::before {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }

        .scanner-frame::after {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }

        .scanner-status {
            margin-top: 20px;
            text-align: center;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
        }

        .spinner-border {
            color: var(--primary-orange) !important;
        }

        /* Floating Recent Scans Window */
        .recent-scans-float {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 320px;
            max-height: 400px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            z-index: 1000;
            transition: all 0.3s ease;
            transform: translateX(100%);
        }

        .recent-scans-float.open {
            transform: translateX(0);
        }

        .float-header {
            background: linear-gradient(135deg, var(--tertiary-gold), var(--secondary-yellow));
            padding: 15px 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
        }

        .float-header h6 {
            margin: 0;
            font-weight: 600;
        }

        .close-btn, .toggle-btn {
            background: none;
            border: none;
            color: #333;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .close-btn:hover, .toggle-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .toggle-btn {
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--primary-orange);
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .float-body {
            padding: 15px 20px;
            max-height: 300px;
            overflow-y: auto;
        }

        .scan-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-orange);
        }

        .scan-item:last-child {
            margin-bottom: 0;
        }

        .scan-time {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .scan-name {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .scan-role {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .scan-data {
            font-weight: 500;
            color: var(--primary-orange);
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .scan-location {
            font-size: 0.8rem;
            color: #28a745;
            margin-top: 3px;
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .scanner-container {
                padding: 10px;
                min-height: 100vh;
                min-height: 100dvh; /* Dynamic viewport height for mobile */
            }

            .scanner-header {
                margin-bottom: 15px;
            }

            .scanner-header h1 {
                font-size: 1.4rem;
            }

            .scanner-header p {
                font-size: 0.9rem;
            }

            .card-body {
                padding: 15px;
            }

            .qr-reader {
                max-width: 100%;
                height: 280px;
                border-width: 2px;
            }

            .scanner-frame {
                width: 180px;
                height: 180px;
            }

            .recent-scans-float {
                width: calc(100vw - 20px);
                right: 10px;
                left: 10px;
                max-height: 50vh;
            }

            .toggle-btn {
                right: 15px;
                width: 45px;
                height: 45px;
                top: 70px;
            }

            .scanner-controls {
                width: 100%;
            }

            .scanner-controls select {
                width: 100%;
                margin-top: 5px;
            }

            /* Touch-friendly scan result */
            .alert {
                font-size: 0.9rem;
                padding: 12px;
            }

            /* Improve touch targets */
            .close-btn, .toggle-btn {
                min-width: 44px;
                min-height: 44px;
            }
        }

        @media (max-width: 480px) {
            .scanner-container {
                padding: 8px;
            }

            .card-header {
                padding: 12px;
            }

            .card-header h4 {
                font-size: 1rem;
            }

            .card-body {
                padding: 12px;
            }

            .qr-reader {
                height: 250px;
            }

            .scanner-frame {
                width: 150px;
                height: 150px;
            }

            .scanning-indicator {
                font-size: 1.5rem;
            }
        }

        /* Landscape orientation on mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            .scanner-container {
                padding: 5px;
            }
            
            .scanner-header {
                margin-bottom: 10px;
            }
            
            .scanner-header h1 {
                font-size: 1.2rem;
                margin-bottom: 2px;
            }
            
            .scanner-header p {
                font-size: 0.8rem;
            }
            
            .card-body {
                padding: 10px;
            }
            
            .qr-reader {
                height: 200px;
            }
            
            .scanner-frame {
                width: 120px;
                height: 120px;
            }
        }

        /* PWA and mobile browser enhancements */
        @media (display-mode: standalone) {
            .scanner-container {
                padding-top: 30px; /* Account for status bar */
            }
        }

        /* Pulse animation for scanning indicator */
        @keyframes pulse {
            0% { opacity: 0.4; transform: translate(-50%, -50%) scale(0.8); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
            100% { opacity: 0.4; transform: translate(-50%, -50%) scale(0.8); }
        }

        /* Animation for scan success */
        @keyframes scanSuccess {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .scan-success {
            animation: scanSuccess 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <div class="scanner-header">
            <div style="width: 60px; height: 60px; background: var(--primary-orange); border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-graduation-cap text-white" style="font-size: 24px;"></i>
            </div>
            <h1>CTU Access Control System</h1>
            <p>Scan your QR Code to Enter/Exit</p>
        </div>
        
        <div class="scanner-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4><i class="fas fa-qrcode me-2"></i>QR Code Scanner</h4>
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
                <div class="qr-reader" id="reader">
                    <div class="scanner-frame"></div>
                </div>
                
                <div class="scanner-status">
                    <div id="scanResult" class="alert" style="display: none;"></div>
                    <div id="scannerStatus" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading scanner...</span>
                        </div>
                        <p class="mt-2 text-muted">Initializing camera...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Button for Recent Scans -->
    <button class="toggle-btn" id="toggleRecentScans" title="Recent Scans">
        <i class="fas fa-history"></i>
    </button>

    <!-- Floating Recent Scans Window -->
    <div class="recent-scans-float" id="recentScansFloat">
        <div class="float-header">
            <h6><i class="fas fa-history me-2"></i>Recent Scans</h6>
            <button class="close-btn" id="closeRecentScans">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="float-body" id="recentScans">
            <!-- Recent scans will be loaded here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Scanner functionality
        let html5QrcodeScanner;
        let isScanning = false;
        let recentScansData = [];
        let lastScanTime = 0;
        let scanCooldown = 3000; // 3 seconds between scans

        // Storage keys
        const STORAGE_KEY = 'ctu_recent_scans';
        const DATE_KEY = 'ctu_scans_date';

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadRecentScans();
            initializeScanner();
            setupRecentScansToggle();
        });

        function showScanResult(data, type) {
            const resultDiv = document.getElementById('scanResult');
            resultDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            
            if (type === 'success' && data.person) {
                resultDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <strong>${data.message}</strong><br>
                            <small>${data.person.name} (${data.person.id}) - ${data.person.type}</small>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>${data.message || 'Scan failed'}</strong>
                        </div>
                    </div>
                `;
            }
            
            resultDiv.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, 5000);

            // Add pulse animation
            resultDiv.classList.add('scan-success');
            setTimeout(() => {
                resultDiv.classList.remove('scan-success');
            }, 1000);
        }

        function addToRecentScansFromBackend(person) {
            // This function handles data from your backend
            const location = document.getElementById('scannerSelect').selectedOptions[0].text;
            const now = new Date();
            const newScan = {
                timestamp: now.getTime(),
                time: formatTime(now),
                name: person.name,
                role: person.type,
                id: person.id,
                location: location,
                action: person.action
            };

            recentScansData.unshift(newScan);
            if (recentScansData.length > 20) {
                recentScansData.pop();
            }

            saveRecentScans();
            updateRecentScansDisplay();
        }

        function initializeScanner() {
            // Check if we're on mobile
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            // Check if HTTPS is required but not available
            const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            
            if (!isSecureContext && isMobile) {
                showHTTPSError();
                return;
            }
            
            // Check for camera support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia) {
                    // Fallback for older browsers
                    initializeLegacyScanner();
                    return;
                } else {
                    showError("Camera not supported on this browser. Please use Chrome, Firefox, or Safari.");
                    return;
                }
            }
            
            const config = {
                fps: 10,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    // Square QR box, responsive to screen size
                    let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdge * 0.7);
                    return {
                        width: qrboxSize,
                        height: qrboxSize
                    };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                rememberLastUsedCamera: true,
                showZoomSliderIfSupported: true,
                defaultZoomValueIfSupported: 2
            };

            html5QrcodeScanner = new Html5Qrcode("reader");
            
            // First check camera permissions
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: isMobile ? "environment" : "user",
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            })
            .then(() => {
                // Permission granted, now get cameras
                return Html5Qrcode.getCameras();
            })
            .then(devices => {
                if (devices && devices.length) {
                    // Prefer back camera on mobile devices
                    let selectedCamera = devices[0];
                    if (isMobile && devices.length > 1) {
                        // Look for back camera
                        const backCamera = devices.find(camera => 
                            camera.label.toLowerCase().includes('back') ||
                            camera.label.toLowerCase().includes('rear') ||
                            camera.label.toLowerCase().includes('environment')
                        );
                        if (backCamera) {
                            selectedCamera = backCamera;
                        }
                    }
                    
                    startScanning(selectedCamera.id, config);
                    
                    // Add camera selector if multiple cameras available
                    if (devices.length > 1) {
                        addCameraSelector(devices);
                    }
                } else {
                    showError("No cameras found on this device");
                }
            })
            .catch(err => {
                console.error("Camera access error:", err);
                if (err.name === 'NotAllowedError') {
                    showPermissionError();
                } else if (err.name === 'NotFoundError') {
                    showError("No camera found on this device");
                } else if (err.name === 'NotSupportedError') {
                    showError("Camera not supported on this device");
                } else if (err.name === 'NotReadableError') {
                    showError("Camera is being used by another application");
                } else if (err.name === 'OverconstrainedError') {
                    // Try with less restrictive constraints
                    initializeFallbackScanner();
                } else {
                    showError("Camera initialization failed: " + err.message);
                }
            });
        }

        function initializeFallbackScanner() {
            // Try with basic constraints
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            navigator.mediaDevices.getUserMedia({ video: true })
            .then(() => {
                return Html5Qrcode.getCameras();
            })
            .then(devices => {
                if (devices && devices.length) {
                    startScanning(devices[0].id, config);
                } else {
                    showError("No cameras available");
                }
            })
            .catch(err => {
                showPermissionError();
            });
        }

        function initializeLegacyScanner() {
            // Fallback for older browsers
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            };

            html5QrcodeScanner = new Html5Qrcode("reader");
            
            // Try legacy getUserMedia
            const getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            
            getUserMedia.call(navigator, { video: true }, 
                function(stream) {
                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length) {
                            startScanning(devices[0].id, config);
                        }
                    });
                },
                function(err) {
                    showPermissionError();
                }
            );
        }

        function showHTTPSError() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-lock me-2"></i>
                    <strong>HTTPS Required for Mobile</strong><br>
                    <small>Camera access requires HTTPS on mobile devices.</small><br>
                    <div class="mt-3">
                        <strong>Solutions:</strong><br>
                        <div class="mt-2">
                            <button class="btn btn-sm me-2" style="background: var(--primary-orange); color: white;" onclick="tryLocalhost()">
                                <i class="fas fa-desktop me-1"></i> Use Desktop
                            </button>
                            <button class="btn btn-sm" style="background: var(--tertiary-gold); color: #333;" onclick="showHTTPSInstructions()">
                                <i class="fas fa-info-circle me-1"></i> Setup HTTPS
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Quick Fix:</strong> Access via desktop browser or setup HTTPS/SSL certificate.
                        </small>
                    </div>
                </div>
            `;
        }

        // Global functions for buttons
        window.tryLocalhost = function() {
            if (location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                const newUrl = location.protocol + '//localhost' + location.pathname + location.search;
                window.location.href = newUrl;
            } else {
                initializeScanner();
            }
        }

        window.showHTTPSInstructions = function() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>How to Enable HTTPS</strong><br>
                    <div class="mt-2" style="text-align: left; font-size: 0.85rem;">
                        <strong>Option 1 - Use Desktop:</strong><br>
                        • Access scanner from desktop/laptop browser<br><br>
                        
                        <strong>Option 2 - Setup HTTPS:</strong><br>
                        • Generate SSL certificate<br>
                        • Configure web server for HTTPS<br>
                        • Access via https://your-domain<br><br>
                        
                        <strong>Option 3 - Development:</strong><br>
                        • Use ngrok or similar tunneling service<br>
                        • Access via HTTPS tunnel URL
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm" style="background: var(--primary-orange); color: white;" onclick="initializeScanner()">
                            <i class="fas fa-refresh me-1"></i> Try Again
                        </button>
                    </div>
                </div>
            `;
        }

        function startScanning(cameraId, config) {
            html5QrcodeScanner.start(
                cameraId,
                config,
                onScanSuccess,
                onScanFailure
            ).then(() => {
                document.getElementById('scannerStatus').style.display = 'none';
                isScanning = true;
                
                // Add scanning indicator
                addScanningIndicator();
            }).catch(err => {
                console.error("Failed to start scanning:", err);
                showError("Failed to start camera: " + err.message);
            });
        }

        function showPermissionError() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Camera Permission Required</strong><br>
                    <small>Please allow camera access to use the QR scanner.</small><br>
                    <div class="mt-3">
                        <button class="btn btn-sm" style="background: var(--primary-orange); color: white;" onclick="requestCameraPermission()">
                            <i class="fas fa-camera me-1"></i> Allow Camera Access
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <strong>Mobile Instructions:</strong><br>
                            1. Tap "Allow Camera Access" button<br>
                            2. Choose "Allow" when browser asks for permission<br>
                            3. If blocked, tap the camera icon in address bar
                        </small>
                    </div>
                </div>
            `;
        }

        function addCameraSelector(cameras) {
            const headerControls = document.querySelector('.scanner-controls');
            const cameraSelector = document.createElement('select');
            cameraSelector.id = 'cameraSelect';
            cameraSelector.className = 'form-select form-select-sm mt-2';
            cameraSelector.innerHTML = cameras.map((camera, index) => 
                `<option value="${camera.id}">${camera.label || `Camera ${index + 1}`}</option>`
            ).join('');
            
            cameraSelector.addEventListener('change', function() {
                if (isScanning) {
                    html5QrcodeScanner.stop().then(() => {
                        const config = {
                            fps: 10,
                            qrbox: function(viewfinderWidth, viewfinderHeight) {
                                let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                let qrboxSize = Math.floor(minEdge * 0.7);
                                return { width: qrboxSize, height: qrboxSize };
                            },
                            aspectRatio: 1.0,
                            showTorchButtonIfSupported: true
                        };
                        startScanning(this.value, config);
                    });
                }
            });
            
            headerControls.appendChild(cameraSelector);
        }

        function addScanningIndicator() {
            const reader = document.getElementById('reader');
            const indicator = document.createElement('div');
            indicator.className = 'scanning-indicator';
            indicator.innerHTML = '<i class="fas fa-crosshairs"></i>';
            reader.appendChild(indicator);
        }

        // Global function for permission request button
        window.requestCameraPermission = function() {
            initializeScanner();
        }

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = Date.now();
            if (currentTime - lastScanTime < scanCooldown) {
                return; // Prevent rapid scanning
            }
            
            lastScanTime = currentTime;

            // Stop scanning temporarily
            html5QrcodeScanner.pause(true);

            // Add scan success animation
            document.getElementById('reader').classList.add('scan-success');
            setTimeout(() => {
                document.getElementById('reader').classList.remove('scan-success');
            }, 300);

            const scannerSelect = document.getElementById('scannerSelect');
            const scannerId = scannerSelect.value;
            
            // Send scan data to backend (using your existing API)
            fetch('scan_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=scan&qr_data=${encodeURIComponent(decodedText)}&scanner_id=${scannerId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showScanResult(data, 'success');
                    addToRecentScansFromBackend(data.person);
                    // Refresh recent scans list
                    loadRecentScans();
                    
                    // Add haptic feedback for successful scan (mobile)
                    if (window.vibrate) {
                        window.vibrate([100, 50, 100]); // Success pattern
                    }
                } else {
                    showScanResult(data, 'error');
                    
                    // Add haptic feedback for failed scan (mobile)
                    if (window.vibrate) {
                        window.vibrate(200); // Error vibration
                    }
                }
                
                // Resume scanning after showing result
                setTimeout(() => {
                    html5QrcodeScanner.resume();
                }, 3000);
            })
            .catch(error => {
                console.error('Scan error:', error);
                showScanResult({ message: 'Network error occurred' }, 'error');
                
                // Resume scanning even on error
                setTimeout(() => {
                    html5QrcodeScanner.resume();
                }, 3000);
            });
        }

        function onScanFailure(error) {
            // Handle scan failure silently for most cases
            // Only log if it's not a common scanning error
            if (!error.includes('NotFoundException') && 
                !error.includes('No MultiFormat Readers') &&
                !error.includes('No QR code found')) {
                console.warn('Scan failure:', error);
            }
        }

        function showError(message) {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Scanner Error</strong><br>
                    <small>${message}</small>
                    <div class="mt-2">
                        <button class="btn btn-sm" style="background: var(--primary-orange); color: white;" onclick="location.reload()">
                            <i class="fas fa-refresh me-1"></i> Retry
                        </button>
                    </div>
                </div>
            `;
        }

        function addToRecentScans(userData) {
            // This is kept for fallback compatibility
            const location = document.getElementById('scannerSelect').selectedOptions[0].text;
            const now = new Date();
            const newScan = {
                timestamp: now.getTime(),
                time: formatTime(now),
                name: userData.name || 'Unknown User',
                role: userData.role || 'Unknown',
                id: userData.id || 'N/A',
                location: location
            };

            recentScansData.unshift(newScan);
            if (recentScansData.length > 20) {
                recentScansData.pop();
            }

            saveRecentScans();
            updateRecentScansDisplay();
        }

        function formatTime(date) {
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) {
                return 'Just now';
            } else if (diffInMinutes < 60) {
                return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
            } else {
                const diffInHours = Math.floor(diffInMinutes / 60);
                if (diffInHours < 24) {
                    return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
                } else {
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
            }
        }

        function loadRecentScans() {
            // Load recent scans from your backend API
            fetch('get_recent_scans.php')
            .then(response => response.json())
            .then(data => {
                if (data.scans) {
                    // Convert backend data to our format and merge with local storage
                    const backendScans = data.scans.map(scan => ({
                        timestamp: new Date().getTime() - Math.random() * 3600000, // Approximate timestamp
                        time: scan.time,
                        name: scan.name,
                        role: scan.type,
                        id: scan.id,
                        location: `Scanner Location`,
                        action: scan.action
                    }));
                    
                    // Load local storage data
                    const today = new Date().toDateString();
                    const savedDate = window.localStorage?.getItem(DATE_KEY);
                    
                    if (savedDate !== today) {
                        // New day - use only backend data
                        recentScansData = backendScans;
                        if (window.localStorage) {
                            window.localStorage.setItem(DATE_KEY, today);
                            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(recentScansData));
                        }
                    } else {
                        // Same day - merge local and backend data
                        const savedData = window.localStorage?.getItem(STORAGE_KEY);
                        if (savedData) {
                            try {
                                const localScans = JSON.parse(savedData);
                                // Combine and deduplicate
                                const combined = [...localScans, ...backendScans];
                                recentScansData = combined.slice(0, 20);
                            } catch (e) {
                                recentScansData = backendScans;
                            }
                        } else {
                            recentScansData = backendScans;
                        }
                    }
                    
                    updateRecentScansDisplay();
                }
            })
            .catch(error => {
                console.error('Failed to load recent scans:', error);
                // Fallback to local storage only
                const today = new Date().toDateString();
                const savedDate = window.localStorage?.getItem(DATE_KEY);
                
                if (savedDate !== today) {
                    recentScansData = [];
                    if (window.localStorage) {
                        window.localStorage.setItem(DATE_KEY, today);
                        window.localStorage.removeItem(STORAGE_KEY);
                    }
                } else {
                    if (window.localStorage) {
                        const savedData = window.localStorage.getItem(STORAGE_KEY);
                        if (savedData) {
                            try {
                                recentScansData = JSON.parse(savedData);
                            } catch (e) {
                                recentScansData = [];
                            }
                        }
                    }
                }
                
                updateRecentScansDisplay();
            });
        }

        function saveRecentScans() {
            if (window.localStorage) {
                try {
                    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(recentScansData));
                } catch (e) {
                    console.warn('Unable to save recent scans to localStorage');
                }
            }
        }

        function updateRecentScansDisplay() {
            const recentScansContainer = document.getElementById('recentScans');
            
            if (recentScansData.length === 0) {
                recentScansContainer.innerHTML = '<div class="text-center text-muted py-3">No recent scans today</div>';
                return;
            }

            recentScansContainer.innerHTML = '';

            recentScansData.forEach(scan => {
                const scanItem = document.createElement('div');
                scanItem.className = 'scan-item';
                scanItem.innerHTML = `
                    <div class="scan-time">${scan.time}</div>
                    <div class="scan-name">${scan.name}</div>
                    <div class="scan-role">${scan.role}</div>
                    <div class="scan-data">ID: ${scan.id}</div>
                    <div class="scan-location">${scan.action ? scan.action + ' - ' : ''}${scan.location}</div>
                `;
                recentScansContainer.appendChild(scanItem);
            });
        }

        function setupRecentScansToggle() {
            const toggleBtn = document.getElementById('toggleRecentScans');
            const floatWindow = document.getElementById('recentScansFloat');
            const closeBtn = document.getElementById('closeRecentScans');

            toggleBtn.addEventListener('click', function() {
                floatWindow.classList.add('open');
            });

            closeBtn.addEventListener('click', function() {
                floatWindow.classList.remove('open');
            });

            // Close when clicking outside
            document.addEventListener('click', function(event) {
                if (!floatWindow.contains(event.target) && 
                    !toggleBtn.contains(event.target) && 
                    floatWindow.classList.contains('open')) {
                    floatWindow.classList.remove('open');
                }
            });

            // Initial display of recent scans
            updateRecentScansDisplay();
        }

        // Update time display every minute
        setInterval(() => {
            updateRecentScansDisplay();
        }, 60000);

        // Handle scanner selection change
        document.getElementById('scannerSelect').addEventListener('change', function() {
            // You can add logic here to handle different scanner locations
            console.log('Scanner location changed to:', this.value);
        });
    </script>
</body>
</html>