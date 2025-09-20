<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">
                <i class="fas fa-qrcode me-3"></i>
                CTU Scanner System
            </h1>
            <p class="hero-subtitle">Advanced QR Code Access Control System</p>
            <p class="hero-description">
                Streamlined campus access management with real-time monitoring, 
                comprehensive analytics, and secure authentication for students, faculty, and staff.
            </p>
        </div>
    </div>
    
    <!-- Dashboard Cards Section -->
    <div class="container">
        <div class="row justify-content-center">
            <!-- Scanner Dashboard -->
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="dashboards/scanner/" class="dashboard-card scanner-card d-block text-center">
                    <div class="dashboard-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h3>Scanner Dashboard</h3>
                    <p class="text-muted">
                        Scan QR codes for entry and exit tracking with real-time validation
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
                        Powered by advanced QR technology for seamless campus management
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
        
        // Add particle effect on hero section (optional enhancement)
        function createFloatingElements() {
            const heroSection = document.querySelector('.hero-section');
            if (!heroSection) return;
            
            for (let i = 0; i < 5; i++) {
                const element = document.createElement('div');
                element.className = 'floating-element';
                element.style.cssText = `
                    position: absolute;
                    width: 4px;
                    height: 4px;
                    background: rgba(255, 255, 255, 0.6);
                    border-radius: 50%;
                    pointer-events: none;
                    animation: float ${3 + Math.random() * 4}s linear infinite;
                    left: ${Math.random() * 100}%;
                    top: 100%;
                    z-index: 1;
                `;
                heroSection.appendChild(element);
            }
        }
        
        // Add floating animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0% {
                    transform: translateY(0) translateX(0);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100vh) translateX(${Math.random() * 200 - 100}px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Initialize floating elements
        createFloatingElements();
        
        // Recreate floating elements periodically
        setInterval(createFloatingElements, 3000);
    </script>
</body>
</html>