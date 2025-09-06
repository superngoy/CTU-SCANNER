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
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .logout-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
            padding: 40px 30px;
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
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }
        
        .logout-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeInScale 1s ease 0.5s forwards;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .logout-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .logout-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .logout-body {
            padding: 40px 30px;
            text-align: center;
        }
        
        .logout-message {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .success-checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success-color) 0%, #2ecc71 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            position: relative;
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
        }
        
        .success-checkmark i {
            color: white;
            font-size: 2rem;
            animation: checkmarkPop 0.6s ease 1.2s both;
        }
        
        @keyframes checkmarkPop {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .logout-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            border-radius: 15px;
            font-weight: 600;
            padding: 12px 25px;
            transition: all 0.3s ease;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
        }
        
        .btn-outline-secondary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            background: transparent;
        }
        
        .btn-outline-secondary:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .security-note {
            background: rgba(52, 73, 94, 0.05);
            border-left: 4px solid var(--secondary-color);
            padding: 15px 20px;
            margin-top: 25px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
            text-align: left;
        }
        
        .security-note i {
            color: var(--secondary-color);
            margin-right: 8px;
        }
        
        .countdown {
            font-size: 0.9rem;
            color: #888;
            margin-top: 20px;
        }
        
        .countdown span {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        @media (max-width: 576px) {
            .logout-container {
                margin: 20px;
                border-radius: 20px;
            }
            
            .logout-header {
                padding: 30px 25px;
            }
            
            .logout-body {
                padding: 30px 25px;
            }
            
            .logout-title {
                font-size: 1.7rem;
            }
            
            .logout-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        .fade-in {
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="logout-container fade-in">
                    <div class="logout-header">
                        <div class="logout-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <h2 class="logout-title">Successfully Logged Out</h2>
                        <p class="logout-subtitle">Your admin session has ended</p>
                    </div>
                    
                    <div class="logout-body">
                        <div class="success-checkmark">
                            <i class="fas fa-check"></i>
                        </div>
                        
                        <div class="logout-message">
                            You have been safely logged out of the CTU Admin Dashboard. 
                            Thank you for using our system responsibly.
                        </div>
                        
                        <div class="logout-actions">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Again
                            </a>
                            <a href="../security/login.php" class="btn btn-outline-secondary">
                                <i class="fas fa-shield-alt me-2"></i>Security Portal
                            </a>
                        </div>
                        
                        <div class="security-note">
                            <i class="fas fa-info-circle"></i>
                            <strong>Security Note:</strong> For your protection, all session data has been cleared. 
                            If this logout was unexpected, please contact the system administrator immediately.
                        </div>
                        
                        <div class="countdown">
                            Redirecting to login page in <span id="countdown">10</span> seconds
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect countdown
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'login.php';
            }
        }, 1000);
        
        // Allow user to cancel auto-redirect
        document.addEventListener('click', () => {
            clearInterval(timer);
            countdownElement.parentElement.innerHTML = 'Auto-redirect cancelled';
        });
        
        document.addEventListener('keydown', () => {
            clearInterval(timer);
            countdownElement.parentElement.innerHTML = 'Auto-redirect cancelled';
        });
        
        // Add loading animation when login button is clicked
        document.querySelector('.btn-primary').addEventListener('click', function(e) {
            e.preventDefault();
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting...';
            btn.style.pointerEvents = 'none';
            
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1000);
        });
        
        // Console log for debugging (remove in production)
        console.log('Admin logout completed at:', '<?php echo $logoutTime; ?>');
    </script>
</body>
</html>