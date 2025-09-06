<?php
session_start();

// Store logout timestamp for analytics
$logoutTime = date('Y-m-d H:i:s');
$adminId = $_SESSION['admin_id'] ?? 'unknown';

// Log the logout event (optional - for audit trails)
error_log("Admin logout: {$adminId} at {$logoutTime}");

// Destroy all session data
session_unset();
session_destroy();

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear any other admin-related cookies if they exist
setcookie('admin_remember', '', time() - 3600, '/');
setcookie('admin_preferences', '', time() - 3600, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - CTU Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #1a252f;
            --light-bg: rgba(255, 255, 255, 0.95);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 25px 50px rgba(0, 0, 0, 0.25);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Floating elements background */
        .bg-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }
        
        .bg-elements::before,
        .bg-elements::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        .bg-elements::before {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: -2s;
        }
        
        .bg-elements::after {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: -4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .logout-container {
            background: var(--light-bg);
            backdrop-filter: blur(20px);
            border-radius: 2rem;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            max-width: 550px;
            width: 100%;
            margin: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .logout-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--success-color), var(--warning-color));
            background-size: 200% 100%;
            animation: borderShimmer 2s linear infinite;
        }
        
        @keyframes borderShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .logout-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .logout-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 4s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.1); }
        }
        
        .logout-icon {
            font-size: clamp(3rem, 8vw, 5rem);
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: fadeInScale 1s ease 0.5s forwards;
            position: relative;
            z-index: 2;
        }
        
        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.3) rotateY(180deg);
            }
            70% {
                transform: scale(1.1) rotateY(0deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotateY(0deg);
            }
        }
        
        .logout-title {
            font-size: clamp(1.5rem, 5vw, 2.2rem);
            font-weight: 800;
            margin-bottom: 0.8rem;
            position: relative;
            z-index: 2;
            opacity: 0;
            animation: slideInLeft 0.8s ease 0.8s forwards;
        }
        
        .logout-subtitle {
            font-size: clamp(0.9rem, 3vw, 1.1rem);
            opacity: 0.9;
            position: relative;
            z-index: 2;
            animation: slideInRight 0.8s ease 1s forwards;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .logout-body {
            padding: 2.5rem;
            text-align: center;
        }
        
        .logout-message {
            color: var(--primary-color);
            font-size: clamp(1rem, 3vw, 1.2rem);
            margin-bottom: 2rem;
            line-height: 1.7;
            opacity: 0;
            animation: fadeInUp 0.8s ease 1.3s forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-checkmark {
            width: clamp(70px, 15vw, 90px);
            height: clamp(70px, 15vw, 90px);
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success-color) 0%, #2ecc71 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            position: relative;
            box-shadow: 0 15px 35px rgba(39, 174, 96, 0.4);
            opacity: 0;
            animation: bounceIn 0.8s ease 1.5s forwards;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .success-checkmark i {
            color: white;
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            animation: checkmarkPop 0.6s ease 1.8s both;
        }
        
        @keyframes checkmarkPop {
            0% {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.3) rotate(0deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }
        
        .logout-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            opacity: 0;
            animation: fadeInUp 0.8s ease 2s forwards;
        }
        
        .btn {
            border-radius: 1rem;
            font-weight: 600;
            padding: 0.9rem 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            position: relative;
            overflow: hidden;
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            min-width: 140px;
            backdrop-filter: blur(10px);
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1c5a85 100%);
            color: white;
        }
        
        .btn-outline-secondary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .btn-outline-secondary:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-3px) scale(1.02);
        }
        
        .security-note {
            background: linear-gradient(135deg, rgba(52, 73, 94, 0.08) 0%, rgba(52, 73, 94, 0.04) 100%);
            border-left: 4px solid var(--secondary-color);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 0.8rem;
            font-size: clamp(0.8rem, 2.5vw, 0.95rem);
            color: #555;
            text-align: left;
            position: relative;
            backdrop-filter: blur(10px);
            opacity: 0;
            animation: slideInUp 0.8s ease 2.2s forwards;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .security-note::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: noteShimmer 3s ease infinite;
        }
        
        @keyframes noteShimmer {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
        
        .security-note i {
            color: var(--secondary-color);
            margin-right: 0.8rem;
            font-size: 1.1rem;
        }
        
        .countdown {
            font-size: clamp(0.8rem, 2.5vw, 0.95rem);
            color: #666;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 0.8rem;
            backdrop-filter: blur(10px);
            opacity: 0;
            animation: fadeIn 1s ease 2.5s forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .countdown span {
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.1em;
        }
        
        .logo-container {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-light);
            backdrop-filter: blur(10px);
            opacity: 0;
            animation: fadeInRotate 1s ease 0.3s forwards;
        }
        
        @keyframes fadeInRotate {
            from {
                opacity: 0;
                transform: rotate(-180deg) scale(0.5);
            }
            to {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
        }
        
        .logo-container img {
            width: 35px;
            height: auto;
            border-radius: 4px;
        }
        
        /* Mobile Optimizations */
        @media (max-width: 576px) {
            .logout-container {
                margin: 0.5rem;
                border-radius: 1.5rem;
                max-width: calc(100vw - 1rem);
            }
            
            .logout-header {
                padding: 2rem 1.5rem;
            }
            
            .logout-body {
                padding: 2rem 1.5rem;
            }
            
            .logout-actions {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .btn {
                width: 100%;
                padding: 1rem;
                min-width: unset;
            }
            
            .security-note {
                padding: 1.2rem;
                font-size: 0.85rem;
            }
            
            .logo-container {
                width: 50px;
                height: 50px;
                top: 0.8rem;
                right: 0.8rem;
            }
            
            .logo-container img {
                width: 28px;
            }
        }
        
        /* Tablet Optimizations */
        @media (min-width: 577px) and (max-width: 768px) {
            .logout-container {
                max-width: 480px;
            }
            
            .logout-actions {
                gap: 1.2rem;
            }
        }
        
        /* Desktop Enhancements */
        @media (min-width: 992px) {
            .logout-container:hover {
                transform: translateY(-2px);
                box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
            }
            
            .btn {
                min-width: 160px;
            }
        }
        
        /* High DPI / Retina displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .success-checkmark {
                box-shadow: 0 15px 35px rgba(39, 174, 96, 0.3);
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --light-bg: rgba(45, 55, 72, 0.95);
                --glass-bg: rgba(45, 55, 72, 0.1);
            }
            
            .logout-message {
                color: #e2e8f0;
            }
            
            .security-note {
                color: #cbd5e0;
            }
            
            .countdown {
                color: #a0aec0;
                background: rgba(45, 55, 72, 0.6);
            }
        }
        
        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .logout-container {
                border: 2px solid #000;
                background: #fff;
            }
            
            .logout-header {
                background: #000;
                color: #fff;
            }
        }
        
        /* Focus indicators for accessibility */
        .btn:focus-visible {
            outline: 3px solid var(--secondary-color);
            outline-offset: 2px;
        }
        
        /* Loading states */
        .btn-loading {
            pointer-events: none;
            position: relative;
            color: transparent !important;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="bg-elements"></div>
    
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="logout-container">
                    <div class="logo-container">
                        <img src="/assets/images/logo.png" alt="CTU Logo">
                    </div>
                    
                    <div class="logout-header">
                        <div class="logout-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <h1 class="logout-title">Successfully Logged Out</h1>
                        <p class="logout-subtitle">Your admin session has ended securely</p>
                    </div>
                    
                    <div class="logout-body">
                        <div class="success-checkmark">
                            <i class="fas fa-check"></i>
                        </div>
                        
                        <div class="logout-message">
                            You have been safely logged out of the CTU Admin Dashboard. 
                            Thank you for using our system responsibly and maintaining the security of our platform.
                        </div>
                        
                        <div class="logout-actions">
                            <a href="login.php" class="btn btn-primary" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Again
                            </a>
                            <a href="../security/login.php" class="btn btn-outline-secondary">
                                <i class="fas fa-shield-alt me-2"></i>Security Portal
                            </a>
                        </div>
                        
                        <div class="security-note">
                            <i class="fas fa-shield-halved"></i>
                            <strong>Security Notice:</strong> For your protection, all session data and authentication tokens have been completely cleared from our servers. If this logout was unexpected or you suspect unauthorized access, please contact the system administrator immediately at <strong>admin@ctu.edu.ph</strong>.
                        </div>
                        
                        <div class="countdown" id="countdownContainer">
                            <i class="fas fa-clock me-2"></i>
                            Automatically redirecting to login page in <span id="countdown">15</span> seconds
                            <br><small class="mt-2 d-block opacity-75">Click anywhere or press any key to cancel auto-redirect</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced auto-redirect countdown with better UX
        let countdown = 15;
        let isRedirectCancelled = false;
        const countdownElement = document.getElementById('countdown');
        const countdownContainer = document.getElementById('countdownContainer');
        
        const timer = setInterval(() => {
            if (isRedirectCancelled) {
                clearInterval(timer);
                return;
            }
            
            countdown--;
            countdownElement.textContent = countdown;
            
            // Visual feedback for urgency
            if (countdown <= 5) {
                countdownElement.style.color = '#e74c3c';
                countdownElement.style.fontWeight = 'bold';
                countdownElement.style.animation = 'pulse 1s infinite';
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
        
        // Cancel auto-redirect function
        function cancelAutoRedirect() {
            if (isRedirectCancelled) return;
            
            isRedirectCancelled = true;
            clearInterval(timer);
            countdownContainer.innerHTML = `
                <i class="fas fa-pause-circle me-2"></i>
                Auto-redirect cancelled - You can stay on this page or manually navigate
                <br><small class="mt-2 d-block opacity-75">Click "Login Again" when ready</small>
            `;
            countdownContainer.style.background = 'rgba(39, 174, 96, 0.1)';
            countdownContainer.style.borderLeft = '3px solid #27ae60';
        }
        
        // Event listeners to cancel auto-redirect
        document.addEventListener('click', cancelAutoRedirect);
        document.addEventListener('keydown', cancelAutoRedirect);
        document.addEventListener('touchstart', cancelAutoRedirect);
        document.addEventListener('scroll', cancelAutoRedirect);
        
        // Enhanced login button functionality
        function redirectToLogin() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('btn-loading');
            btn.innerHTML = '<span>Redirecting...</span>';
            
            // Add smooth transition
            document.querySelector('.logout-container').style.transform = 'scale(0.95)';
            document.querySelector('.logout-container').style.opacity = '0.8';
            
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 500);
        }
        
        document.getElementById('loginBtn').addEventListener('click', function(e) {
            e.preventDefault();
            cancelAutoRedirect();
            redirectToLogin();
        });
        
        // Enhanced security logging
        const logoutData = {
            timestamp: '<?php echo $logoutTime; ?>',
            adminId: '<?php echo $adminId; ?>',
            userAgent: navigator.userAgent,
            viewport: `${window.innerWidth}x${window.innerHeight}`,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        };
        
        // Log detailed logout info for security analysis
        console.group('🔒 Admin Logout Details');
        console.log('Timestamp:', logoutData.timestamp);
        console.log('Admin ID:', logoutData.adminId);
        console.log('User Agent:', logoutData.userAgent);
        console.log('Viewport:', logoutData.viewport);
        console.log('Timezone:', logoutData.timezone);
        console.groupEnd();
        
        // Page visibility API to handle tab switching
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Pause countdown when tab is hidden
                clearInterval(timer);
            } else if (!isRedirectCancelled && countdown > 0) {
                // Resume countdown when tab becomes visible
                setTimeout(() => {
                    if (!isRedirectCancelled) {
                        const newTimer = setInterval(() => {
                            countdown--;
                            countdownElement.textContent = countdown;
                            
                            if (countdown <= 5) {
                                countdownElement.style.color = '#e74c3c';
                                countdownElement.style.fontWeight = 'bold';
                            }
                            
                            if (countdown <= 0) {
                                clearInterval(newTimer);
                                redirectToLogin();
                            }
                        }, 1000);
                    }
                }, 100);
            }
        });
        
        // Keyboard accessibility enhancements
        document.addEventListener('keydown', function(e) {
            // Enter key on login button
            if (e.key === 'Enter' && document.activeElement.id === 'loginBtn') {
                e.preventDefault();
                redirectToLogin();
            }
            
            // Escape key to cancel redirect
            if (e.key === 'Escape') {
                cancelAutoRedirect();
            }
        });
        
        // Responsive font size adjustment
        function adjustFontSizes() {
            const container = document.querySelector('.logout-container');
            const containerWidth = container.offsetWidth;
            
            if (containerWidth < 400) {
                document.documentElement.style.setProperty('--font-scale', '0.9');
            } else if (containerWidth < 500) {
                document.documentElement.style.setProperty('--font-scale', '1');
            } else {
                document.documentElement.style.setProperty('--font-scale', '1.1');
            }
        }
        
        // Initialize and handle resize
        window.addEventListener('load', adjustFontSizes);
        window.addEventListener('resize', adjustFontSizes);
        
        // Performance optimization: Remove unused animations after completion
        setTimeout(() => {
            const animatedElements = document.querySelectorAll('[style*="animation"]');
            animatedElements.forEach(el => {
                el.style.animation = 'none';
            });
        }, 3000);
    </script>
</body>
</html>