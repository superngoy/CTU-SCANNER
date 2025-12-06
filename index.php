<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner System - Code 39 Barcode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background-image: url('assets/images/logo.png');
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            position: relative;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .hero-section .container {
            position: relative;
            z-index: 1;
        }

        .logo-inline {
            width: 100px;
            height: 100px;
            margin-right: 20px;
            vertical-align: middle;
            object-fit: contain;
            border-radius: 15px;
        }

        .hero-title {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">
                <img src="assets/images/logo.png" alt="CTU Logo" class="logo-inline">
                <span>CTU Scanner System</span>
            </h1>
        </div>
    </div>
    
    <!-- Dashboard Cards Section -->
    <div class="container">
        <div class="row justify-content-center">
            <!-- Scanner Dashboard -->
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="dashboards/scanner/" class="dashboard-card scanner-card d-block text-center">
                    <div class="dashboard-icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <h3>Scanner Dashboard</h3>
                    <p class="text-muted">
                        Scan Code 39 barcodes for entry and exit tracking with real-time validation
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            <i class="fas fa-camera me-1"></i>
                            Active Scanner
                        </span>
                    </div>
                </a>
            </div>
            
            <!-- Security Dashboard -->
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="dashboards/security/login.php" class="dashboard-card security-card d-block text-center">
                    <div class="dashboard-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Security Dashboard</h3>
                    <p class="text-muted">
                        Real-time monitoring and security oversight of campus access
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            <i class="fas fa-eye me-1"></i>
                            Live Monitoring
                        </span>
                    </div>
                </a>
            </div>
            
            <!-- Admin Dashboard -->
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="dashboards/admin/login.php" class="dashboard-card admin-card d-block text-center">
                    <div class="dashboard-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Admin Dashboard</h3>
                    <p class="text-muted">
                        System administration, analytics, and comprehensive management tools
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                            <i class="fas fa-chart-bar me-1"></i>
                            Full Control
                        </span>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- System Status Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <h5 class="mb-3">
                            <i class="fas fa-server me-2"></i>
                            System Status
                        </h5>
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="status-indicator status-online"></span>
                                    <span>Database Online</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="status-indicator status-online"></span>
                                    <span>Scanner Active</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="status-indicator status-online"></span>
                                    <span>Security Portal</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="status-indicator status-online"></span>
                                    <span>Admin Panel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!-- Footer Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-center text-white">
                    <p class="mb-2">
                        <i class="fas fa-university me-2"></i>
                        Cebu Technological University - Access Control System
                    </p>
                    <small class="opacity-75">
                        Powered by advanced Code 39 barcode technology for seamless campus management
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Add loading animation to dashboard cards
        document.addEventListener('DOMContentLoaded', function() {
            const dashboardCards = document.querySelectorAll('.dashboard-card');
            
            dashboardCards.forEach((card, index) => {
                // Add staggered animation delay
                card.style.animationDelay = `${index * 0.2}s`;
                card.classList.add('fade-in-up');
                
                // Add click effect
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    
                    // Add loading animation
                    this.style.transform = 'scale(0.95)';
                    this.style.opacity = '0.8';
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                });
            });
            
            // System status check simulation
            setTimeout(checkSystemStatus, 1000);
        });
        
        function checkSystemStatus() {
            // Simulate system status check
            const statusIndicators = document.querySelectorAll('.status-indicator');
            
            statusIndicators.forEach((indicator, index) => {
                setTimeout(() => {
                    indicator.classList.add('pulse');
                }, index * 200);
            });
        }
        
        // Add smooth scrolling for any anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        

    </script>
</body>
</html>