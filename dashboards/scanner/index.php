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
            min-height: 100dvh;
            overflow-x: hidden;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .scanner-container {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        .scanner-header {
            text-align: center;
            margin-bottom: 15px;
            flex-shrink: 0;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: var(--primary-orange);
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scanner-header h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .scanner-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .scanner-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-orange), var(--tertiary-gold));
            padding: 15px 20px;
            border-radius: 20px 20px 0 0;
            color: white;
            flex-shrink: 0;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .scanner-controls select {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.9rem;
            width: 100%;
            margin-top: 8px;
        }

        .scanner-controls select option {
            background: #333;
            color: white;
        }

        .card-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 0;
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
            text-shadow: 0 0 10px rgba(255, 225, 0, 0.5);
        }

        .qr-reader {
            width: 100%;
            max-width: 350px;
            height: 280px;
            border: 3px solid var(--primary-orange);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            background: #000;
            touch-action: manipulation;
            flex-shrink: 0;
        }

        .qr-reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            background: #000;
        }

        .scanner-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 180px;
            border: 2px solid var(--secondary-yellow);
            border-radius: 10px;
            z-index: 10;
            pointer-events: none;
            box-shadow: 0 0 20px rgba(255, 225, 0, 0.3);
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
            margin-top: 15px;
            text-align: center;
            flex-shrink: 0;
            width: 100%;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            padding: 12px 15px;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
        }

        .alert-warning {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: #333;
        }

        .alert-info {
            background: linear-gradient(135deg, #17a2b8, #007bff);
            color: white;
        }

        .spinner-border {
            color: var(--primary-orange) !important;
            width: 2rem;
            height: 2rem;
        }

        /* Camera permission button styles */
        .permission-btn {
            background: var(--primary-orange);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 155, 0, 0.3);
            margin: 10px 5px;
        }

        .permission-btn:hover {
            background: #e6890a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 155, 0, 0.4);
        }

        .permission-btn:active {
            transform: translateY(0);
        }

        /* Recent Scans Float Window */
        .recent-scans-float {
            position: fixed;
            top: 15px;
            right: 15px;
            width: 300px;
            max-height: 50vh;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            z-index: 1000;
            transition: all 0.3s ease;
            transform: translateX(100%);
            overflow: hidden;
        }

        .recent-scans-float.open {
            transform: translateX(0);
        }

        .float-header {
            background: linear-gradient(135deg, var(--tertiary-gold), var(--secondary-yellow));
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
        }

        .float-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .close-btn, .toggle-btn {
            background: none;
            border: none;
            color: #333;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            min-width: 36px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover, .toggle-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .toggle-btn {
            position: fixed;
            top: 70px;
            right: 15px;
            background: var(--primary-orange);
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            z-index: 999;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .float-body {
            padding: 12px 15px;
            max-height: calc(50vh - 60px);
            overflow-y: auto;
        }

        .scan-item {
            background: #f8f9fa;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            border-left: 3px solid var(--primary-orange);
            font-size: 0.85rem;
        }

        .scan-item:last-child {
            margin-bottom: 0;
        }

        .scan-time {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .scan-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .scan-role {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .scan-data {
            font-weight: 500;
            color: var(--primary-orange);
            font-size: 0.8rem;
            margin-bottom: 4px;
        }

        .scan-location {
            font-size: 0.75rem;
            color: #28a745;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .scanner-container {
                padding: 8px;
            }

            .scanner-header h1 {
                font-size: 1.3rem;
            }

            .scanner-header p {
                font-size: 0.9rem;
            }

            .logo {
                width: 45px;
                height: 45px;
            }

            .qr-reader {
                max-width: 100%;
                height: 260px;
            }

            .scanner-frame {
                width: 160px;
                height: 160px;
            }

            .recent-scans-float {
                width: calc(100vw - 20px);
                right: 10px;
                left: 10px;
                max-height: 40vh;
            }

            .toggle-btn {
                width: 45px;
                height: 45px;
                right: 12px;
                top: 65px;
            }

            .permission-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .card-header {
                padding: 12px 15px;
            }

            .card-body {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .scanner-container {
                padding: 5px;
            }

            .qr-reader {
                height: 240px;
                border-width: 2px;
            }

            .scanner-frame {
                width: 140px;
                height: 140px;
            }

            .scanning-indicator {
                font-size: 1.5rem;
            }

            .card-header h4 {
                font-size: 1rem;
            }
        }

        /* Landscape mode optimizations */
        @media (max-height: 600px) and (orientation: landscape) {
            .scanner-container {
                flex-direction: row;
                padding: 8px;
            }

            .scanner-header {
                flex: 0 0 200px;
                margin-right: 15px;
                margin-bottom: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .scanner-header h1 {
                font-size: 1.1rem;
            }

            .scanner-header p {
                font-size: 0.8rem;
            }

            .logo {
                width: 40px;
                height: 40px;
                margin-bottom: 8px;
            }

            .scanner-card {
                flex: 1;
                max-height: calc(100vh - 16px);
            }

            .qr-reader {
                height: 200px;
                max-width: 280px;
            }

            .scanner-frame {
                width: 120px;
                height: 120px;
            }
        }

        /* Animations */
        @keyframes pulse {
            0% { opacity: 0.4; transform: translate(-50%, -50%) scale(0.8); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
            100% { opacity: 0.4; transform: translate(-50%, -50%) scale(0.8); }
        }

        @keyframes scanSuccess {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 30px rgba(255, 155, 0, 0.5); }
            100% { transform: scale(1); }
        }

        .scan-success {
            animation: scanSuccess 0.3s ease;
        }

        /* PWA enhancements */
        @media (display-mode: standalone) {
            .scanner-container {
                padding-top: env(safe-area-inset-top, 20px);
                padding-bottom: env(safe-area-inset-bottom, 10px);
            }
        }

        /* Prevent zoom on double tap */
        * {
            touch-action: manipulation;
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <div class="scanner-header">
            <div class="logo">
                <i class="fas fa-graduation-cap text-white" style="font-size: 20px;"></i>
            </div>
            <h1>CTU Access Control</h1>
            <p>Scan QR Code to Enter/Exit</p>
        </div>
        
        <div class="scanner-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <h4><i class="fas fa-qrcode me-2"></i>QR Scanner</h4>
                    <div class="scanner-controls">
                        <select id="scannerSelect" class="form-select form-select-sm">
                            <option value="SC001">Main Entrance</option>
                            <option value="SC002">Main Exit</option>
                            <option value="SC004">Vehicular Exit</option>
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
                            <span class="visually-hidden">Loading...</span>
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
            <div class="text-center text-muted py-3">Loading recent scans...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Enhanced mobile QR scanner with improved camera handling
        let html5QrcodeScanner;
        let isScanning = false;
        let recentScansData = [];
        let lastScanTime = 0;
        let scanCooldown = 2000;
        let cameraStream = null;

        // Storage keys
        const STORAGE_KEY = 'ctu_recent_scans';
        const DATE_KEY = 'ctu_scans_date';

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent zoom on iOS
            document.addEventListener('touchstart', function(e) {
                if (e.touches.length > 1) {
                    e.preventDefault();
                }
            }, { passive: false });

            // Prevent double-tap zoom
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(e) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, false);

            loadRecentScans();
            setupRecentScansToggle();
            
            // Small delay to ensure DOM is fully ready
            setTimeout(() => {
                initializeScanner();
            }, 100);
        });

        function initializeScanner() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            
            console.log('Initializing scanner - Mobile:', isMobile, 'Secure:', isSecureContext);

            // Check for secure context on mobile
            if (isMobile && !isSecureContext) {
                showHTTPSError();
                return;
            }

            // Check for camera support
            if (!navigator.mediaDevices?.getUserMedia) {
                showError("Camera not supported in this browser. Please use Chrome, Firefox, or Safari.");
                return;
            }

            // Request camera permission immediately with improved constraints
            requestCameraAccess();
        }

        async function requestCameraAccess() {
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            try {
                // Enhanced camera constraints for better mobile support
                const constraints = {
                    video: {
                        facingMode: isMobile ? { ideal: 'environment' } : 'user',
                        width: { ideal: 1280, min: 640 },
                        height: { ideal: 720, min: 480 },
                        aspectRatio: { ideal: 16/9 },
                        frameRate: { ideal: 30, max: 60 }
                    }
                };

                console.log('Requesting camera access with constraints:', constraints);
                
                // Request permission
                cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                console.log('Camera permission granted');
                
                // Stop the stream immediately - we just needed permission
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;

                // Now initialize the QR scanner
                initializeQRScanner();
                
            } catch (error) {
                console.error('Camera access error:', error);
                handleCameraError(error);
            }
        }

        async function initializeQRScanner() {
            try {
                const devices = await Html5Qrcode.getCameras();
                console.log('Available cameras:', devices);
                
                if (!devices || devices.length === 0) {
                    throw new Error('No cameras found');
                }

                // Select appropriate camera
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                let selectedCamera = devices[0];
                
                if (isMobile && devices.length > 1) {
                    // Look for back/environment camera
                    const backCamera = devices.find(camera => {
                        const label = camera.label.toLowerCase();
                        return label.includes('back') || label.includes('rear') || label.includes('environment');
                    });
                    
                    if (backCamera) {
                        selectedCamera = backCamera;
                        console.log('Selected back camera:', selectedCamera.label);
                    }
                }

                // Enhanced config for better mobile performance
                const config = {
                    fps: isMobile ? 15 : 20,
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                        const qrboxSize = Math.floor(minEdge * (isMobile ? 0.75 : 0.7));
                        console.log('QR box size:', qrboxSize, 'Viewfinder:', viewfinderWidth, 'x', viewfinderHeight);
                        return {
                            width: qrboxSize,
                            height: qrboxSize
                        };
                    },
                    aspectRatio: 1.777778, // 16:9
                    showTorchButtonIfSupported: true,
                    showZoomSliderIfSupported: true,
                    defaultZoomValueIfSupported: 1,
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                    rememberLastUsedCamera: true,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                };

                html5QrcodeScanner = new Html5Qrcode("reader");
                await startScanning(selectedCamera.id, config);
                
                // Add camera selector if multiple cameras
                if (devices.length > 1) {
                    addCameraSelector(devices);
                }
                
            } catch (error) {
                console.error('QR Scanner initialization error:', error);
                showError(`Scanner initialization failed: ${error.message}`);
            }
        }

        async function startScanning(cameraId, config) {
            try {
                console.log('Starting scanner with camera:', cameraId);
                
                await html5QrcodeScanner.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanFailure
                );
                
                console.log('Scanner started successfully');
                document.getElementById('scannerStatus').style.display = 'none';
                isScanning = true;
                
                // Add scanning indicator
                addScanningIndicator();
                
                // Add haptic feedback if available
                if (window.navigator.vibrate) {
                    window.navigator.vibrate(50); // Short vibration to indicate scanner is ready
                }
                
            } catch (error) {
                console.error('Failed to start scanning:', error);
                throw error;
            }
        }

        function handleCameraError(error) {
            console.error('Camera error details:', error);
            
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                showPermissionError();
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                showError("No camera found on this device. Please ensure your device has a camera.");
            } else if (error.name === 'NotSupportedError') {
                showError("Camera not supported on this device or browser.");
            } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                showError("Camera is being used by another application. Please close other camera apps and try again.");
            } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
                console.log('Trying with fallback constraints...');
                tryFallbackCamera();
            } else {
                showError(`Camera error: ${error.message || 'Unknown error occurred'}`);
            }
        }

        async function tryFallbackCamera() {
            try {
                console.log('Attempting fallback camera access...');
                
                // Try with minimal constraints
                const fallbackConstraints = {
                    video: {
                        facingMode: 'environment'
                    }
                };
                
                cameraStream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
                
                // Try simpler QR config
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    const simpleConfig = {
                        fps: 10,
                        qrbox: { width: 200, height: 200 },
                        aspectRatio: 1.0
                    };
                    
                    html5QrcodeScanner = new Html5Qrcode("reader");
                    await startScanning(devices[0].id, simpleConfig);
                }
                
            } catch (fallbackError) {
                console.error('Fallback camera also failed:', fallbackError);
                showPermissionError();
            }
        }

        function showPermissionError() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-camera-retro me-2"></i>
                    <strong>Camera Permission Required</strong><br>
                    <small>Please allow camera access to scan QR codes.</small><br>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="retryCamera()">
                            <i class="fas fa-camera me-1"></i> Enable Camera
                        </button>
                    </div>
                    <div class="mt-3">
                        <small style="text-align: left; display: block; line-height: 1.4;">
                            <strong>Solutions:</strong><br>
                            • Access via HTTPS (https://your-domain)<br>
                            • Use desktop/laptop browser<br>
                            • Setup SSL certificate for mobile access
                        </small>
                    </div>
                </div>
            `;
        }

        function showError(message) {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Scanner Error</strong><br>
                    <small>${message}</small>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="retryCamera()">
                            <i class="fas fa-refresh me-1"></i> Try Again
                        </button>
                    </div>
                </div>
            `;
        }

        // Global functions for buttons
        window.retryCamera = function() {
            console.log('Retrying camera initialization...');
            document.getElementById('scannerStatus').innerHTML = `
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Requesting camera access...</p>
            `;
            
            setTimeout(() => {
                initializeScanner();
            }, 500);
        }

        window.tryHTTPS = function() {
            const currentUrl = window.location.href;
            if (currentUrl.startsWith('http://')) {
                const httpsUrl = currentUrl.replace('http://', 'https://');
                window.location.href = httpsUrl;
            } else {
                retryCamera();
            }
        }

        window.tryDesktop = function() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Desktop Instructions</strong><br>
                    <small>Open this page on a desktop/laptop computer for easier camera access without HTTPS requirements.</small>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="retryCamera()">
                            <i class="fas fa-mobile-alt me-1"></i> Try Mobile Anyway
                        </button>
                    </div>
                </div>
            `;
        }

        function addScanningIndicator() {
            const reader = document.getElementById('reader');
            if (!reader.querySelector('.scanning-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'scanning-indicator';
                indicator.innerHTML = '<i class="fas fa-crosshairs"></i>';
                reader.appendChild(indicator);
            }
        }

        function addCameraSelector(cameras) {
            const headerControls = document.querySelector('.scanner-controls');
            
            // Check if camera selector already exists
            if (document.getElementById('cameraSelect')) {
                return;
            }
            
            const cameraSelector = document.createElement('select');
            cameraSelector.id = 'cameraSelect';
            cameraSelector.className = 'form-select form-select-sm mt-1';
            cameraSelector.innerHTML = cameras.map((camera, index) => {
                const label = camera.label || `Camera ${index + 1}`;
                const isBack = label.toLowerCase().includes('back') || label.toLowerCase().includes('rear') || label.toLowerCase().includes('environment');
                return `<option value="${camera.id}">${isBack ? '📷 ' : '🤳 '}${label}</option>`;
            }).join('');
            
            cameraSelector.addEventListener('change', async function() {
                if (isScanning && html5QrcodeScanner) {
                    try {
                        console.log('Switching to camera:', this.value);
                        await html5QrcodeScanner.stop();
                        
                        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                        const config = {
                            fps: isMobile ? 15 : 20,
                            qrbox: function(viewfinderWidth, viewfinderHeight) {
                                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                const qrboxSize = Math.floor(minEdge * 0.7);
                                return { width: qrboxSize, height: qrboxSize };
                            },
                            aspectRatio: 1.777778,
                            showTorchButtonIfSupported: true,
                            showZoomSliderIfSupported: true
                        };
                        
                        await startScanning(this.value, config);
                    } catch (error) {
                        console.error('Camera switch error:', error);
                        showError('Failed to switch camera: ' + error.message);
                    }
                }
            });
            
            headerControls.appendChild(cameraSelector);
        }

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = Date.now();
            if (currentTime - lastScanTime < scanCooldown) {
                return;
            }
            
            lastScanTime = currentTime;
            console.log('QR Code scanned:', decodedText);

            // Pause scanning temporarily
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause(true);
            }

            // Add scan success animation
            const reader = document.getElementById('reader');
            reader.classList.add('scan-success');
            setTimeout(() => {
                reader.classList.remove('scan-success');
            }, 300);

            // Haptic feedback for successful scan
            if (window.navigator.vibrate) {
                window.navigator.vibrate([100, 50, 100]);
            }

            const scannerSelect = document.getElementById('scannerSelect');
            const scannerId = scannerSelect.value;
            
            // Send scan data to backend
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
                    loadRecentScans();
                } else {
                    showScanResult(data, 'error');
                    if (window.navigator.vibrate) {
                        window.navigator.vibrate(300);
                    }
                }
                
                // Resume scanning after delay
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 3000);
            })
            .catch(error => {
                console.error('Scan processing error:', error);
                showScanResult({ message: 'Network error occurred' }, 'error');
                
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 3000);
            });
        }

        function onScanFailure(error) {
            // Only log non-routine scanning errors
            if (!error.includes('NotFoundException') && 
                !error.includes('No MultiFormat Readers') &&
                !error.includes('No QR code found') &&
                !error.includes('QR code parse error')) {
                console.warn('Scan failure:', error);
            }
        }

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
            resultDiv.classList.add('scan-success');
            
            // Auto-hide after 4 seconds
            setTimeout(() => {
                resultDiv.style.display = 'none';
                resultDiv.classList.remove('scan-success');
            }, 4000);
        }

        function addToRecentScansFromBackend(person) {
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
            if (recentScansData.length > 15) {
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
                return `${diffInMinutes}m ago`;
            } else {
                const diffInHours = Math.floor(diffInMinutes / 60);
                if (diffInHours < 24) {
                    return `${diffInHours}h ago`;
                } else {
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
            }
        }

        function loadRecentScans() {
            fetch('get_recent_scans.php')
            .then(response => response.json())
            .then(data => {
                if (data.scans) {
                    const backendScans = data.scans.map(scan => ({
                        timestamp: new Date(scan.timestamp || Date.now()).getTime(),
                        time: scan.time || formatTime(new Date()),
                        name: scan.name,
                        role: scan.type,
                        id: scan.id,
                        location: scan.location || 'Scanner Location',
                        action: scan.action
                    }));
                    
                    const today = new Date().toDateString();
                    const savedDate = localStorage?.getItem(DATE_KEY);
                    
                    if (savedDate !== today) {
                        recentScansData = backendScans;
                        if (localStorage) {
                            localStorage.setItem(DATE_KEY, today);
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(recentScansData));
                        }
                    } else {
                        const savedData = localStorage?.getItem(STORAGE_KEY);
                        if (savedData) {
                            try {
                                const localScans = JSON.parse(savedData);
                                const combined = [...localScans, ...backendScans];
                                recentScansData = combined.slice(0, 15);
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
                
                const today = new Date().toDateString();
                const savedDate = localStorage?.getItem(DATE_KEY);
                
                if (savedDate !== today) {
                    recentScansData = [];
                    if (localStorage) {
                        localStorage.setItem(DATE_KEY, today);
                        localStorage.removeItem(STORAGE_KEY);
                    }
                } else {
                    if (localStorage) {
                        const savedData = localStorage.getItem(STORAGE_KEY);
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
            if (localStorage) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(recentScansData));
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

            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                floatWindow.classList.add('open');
            });

            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
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

            updateRecentScansDisplay();
        }

        // Update time display periodically
        setInterval(() => {
            if (recentScansData.length > 0) {
                updateRecentScansDisplay();
            }
        }, 60000);

        // Handle scanner selection change
        document.getElementById('scannerSelect').addEventListener('change', function() {
            console.log('Scanner location changed to:', this.value);
        });

        // Handle page visibility changes (pause/resume scanner)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden, pause scanner to save battery
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.pause(true);
                }
            } else {
                // Page is visible again, resume scanner
                setTimeout(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                }, 100);
            }
        });

        // Handle orientation changes
        window.addEventListener('orientationchange', function() {
            setTimeout(() => {
                // Restart scanner after orientation change for better performance
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.pause(true);
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 300);
                }
            }, 100);
        });
    </script>
</body>
</html> line-height: 1.4;">
                            <strong>Mobile Steps:</strong><br>
                            1. Tap "Enable Camera" button<br>
                            2. Select "Allow" when prompted<br>
                            3. If blocked, tap the camera icon in address bar<br>
                            4. Make sure you're using HTTPS (secure connection)
                        </small>
                    </div>
                </div>
            `;
        }

        function showHTTPSError() {
            const statusDiv = document.getElementById('scannerStatus');
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Secure Connection Required</strong><br>
                    <small>Camera access requires HTTPS on mobile devices.</small><br>
                    <div class="mt-3">
                        <button class="permission-btn" onclick="tryHTTPS()">
                            <i class="fas fa-lock me-1"></i> Switch to HTTPS
                        </button>
                        <button class="permission-btn" onclick="tryDesktop()" style="background: var(--tertiary-gold); color: #333;">
                            <i class="fas fa-desktop me-1"></i> Use Desktop
                        </button>
                    </div>
                    <div class="mt-3">
                        <small style="text-align: left; display: block;