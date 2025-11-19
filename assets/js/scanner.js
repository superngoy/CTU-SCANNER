class QRScanner {
    constructor() {
        this.html5QrCode = null;
        this.isScanning = false;
        this.lastScanTime = 0;
        this.scanCooldown = 3000; // 3 seconds between scans
        this.init();
    }

    init() {
        this.initializeScanner();
        this.bindEvents();
        this.loadRecentScans();
    }

    initializeScanner() {
        const config = {
            fps: 10,
            qrbox: { width: 300, height: 300 },
            aspectRatio: 1.0,
            showTorchButtonIfSupported: true,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
        };

        this.html5QrCode = new Html5Qrcode("reader");
        
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                const cameraId = devices[0].id;
                this.startScanning(cameraId, config);
            } else {
                this.showError("No cameras found");
            }
        }).catch(err => {
            this.showError("Camera initialization failed: " + err);
        });
    }

    startScanning(cameraId, config) {
        this.html5QrCode.start(
            cameraId,
            config,
            (decodedText, decodedResult) => {
                this.handleScanSuccess(decodedText, decodedResult);
            },
            (errorMessage) => {
                // Handle scan failure - usually too frequent, so we ignore
            }
        ).then(() => {
            document.getElementById('scannerStatus').style.display = 'none';
            this.isScanning = true;
        }).catch(err => {
            this.showError("Failed to start scanning: " + err);
        });
    }

    handleScanSuccess(decodedText, decodedResult) {
        const currentTime = Date.now();
        if (currentTime - this.lastScanTime < this.scanCooldown) {
            return; // Prevent rapid scanning
        }
        
        this.lastScanTime = currentTime;
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
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.showScanResult(data, 'success');
                this.addToRecentScans(data.person);
            } else {
                // Display error message with reason if available
                const displayData = {
                    message: data.message || 'Scan failed',
                    reason: data.reason
                };
                this.showScanResult(displayData, 'error');
            }
        })
        .catch(error => {
            console.error('Scan error:', error);
            this.showScanResult({ message: 'Network error occurred: ' + error.message }, 'error');
        });
    }

    showScanResult(data, type) {
        const resultDiv = document.getElementById('scanResult');
        resultDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} scan-${type}`;
        
        // Build the message with reason if available
        let reasonText = '';
        if (data.reason) {
            const reasonMap = {
                'invalid_qr': 'Invalid QR Code',
                'not_enrolled': 'Student Not Enrolled',
                'inactive': 'Account Inactive',
                'log_failed': 'Failed to Log'
            };
            reasonText = reasonMap[data.reason] || data.reason;
            reasonText = ` - ${reasonText}`;
        }
        
        resultDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                <div>
                    <strong>${data.message}${reasonText}</strong>
                    ${data.person ? `<br><small>${data.person.name} (${data.person.id}) - ${data.person.type}</small>` : ''}
                </div>
            </div>
        `;
        resultDiv.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            resultDiv.style.display = 'none';
        }, 5000);

        // Add pulse animation
        resultDiv.classList.add('pulse');
        setTimeout(() => {
            resultDiv.classList.remove('pulse');
        }, 1000);
    }

    addToRecentScans(person) {
        const recentScansDiv = document.getElementById('recentScans');
        const scanItem = document.createElement('div');
        scanItem.className = 'list-group-item scan-item fade-in';
        scanItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${person.name}</strong>
                    <br>
                    <small class="text-muted">${person.id} - ${person.type} - ${person.action}</small>
                </div>
                <small class="text-muted">${new Date().toLocaleTimeString()}</small>
            </div>
        `;
        
        recentScansDiv.insertBefore(scanItem, recentScansDiv.firstChild);
        
        // Keep only last 10 scans
        const items = recentScansDiv.getElementsByClassName('scan-item');
        if (items.length > 10) {
            recentScansDiv.removeChild(items[items.length - 1]);
        }
    }

    showError(message) {
        const statusDiv = document.getElementById('scannerStatus');
        statusDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    bindEvents() {
        // Scanner selection change
        document.getElementById('scannerSelect').addEventListener('change', () => {
            this.loadRecentScans();
        });

        // Handle page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isScanning) {
                // Pause scanning when page is hidden
                this.html5QrCode.pause();
            } else if (!document.hidden && this.isScanning) {
                // Resume scanning when page is visible
                this.html5QrCode.resume();
            }
        });
    }

    loadRecentScans() {
        // Load recent scans from backend
        fetch('get_recent_scans.php')
        .then(response => response.json())
        .then(data => {
            const recentScansDiv = document.getElementById('recentScans');
            recentScansDiv.innerHTML = '';
            
            data.scans.forEach(scan => {
                const scanItem = document.createElement('div');
                scanItem.className = 'list-group-item scan-item';
                scanItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${scan.name}</strong>
                            <br>
                            <small class="text-muted">${scan.id} - ${scan.type} - ${scan.action}</small>
                        </div>
                        <small class="text-muted">${scan.time}</small>
                    </div>
                `;
                recentScansDiv.appendChild(scanItem);
            });
        })
        .catch(error => {
            console.error('Failed to load recent scans:', error);
        });
    }

    destroy() {
        if (this.html5QrCode && this.isScanning) {
            this.html5QrCode.stop().then(() => {
                this.html5QrCode.clear();
            }).catch(err => {
                console.error("Failed to stop scanning:", err);
            });
        }
    }
}

// Initialize scanner when page loads
document.addEventListener('DOMContentLoaded', () => {
    const scanner = new QRScanner();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        scanner.destroy();
    });
});