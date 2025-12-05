<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

$auth = new Auth();

if ($auth->isLoggedIn('scanner')) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_POST) {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->loginScanner($identifier, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid credentials. Please use Security Guard or Admin account.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Login - CTU Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #972529;
            --secondary: #E5C573;
            --white: #ffffff;
            --dark: #1a1a1a;
            --light: #f5f5f5;
            --border: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #972529 0%, #c44536 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
            overflow-y: auto;
            padding: 20px 0;
        }
        
        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(229, 197, 115, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }
        
        body::after {
            content: '';
            position: fixed;
            bottom: -20%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
            z-index: 1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }
        
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 480px;
            padding: 20px;
            margin: auto;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
            animation: slideUp 0.6s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-card:hover {
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            transform: translateY(-8px);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, rgba(151, 37, 41, 0.85) 0%, rgba(196, 69, 54, 0.85) 100%);
            backdrop-filter: blur(15px);
            padding: 32px 25px;
            text-align: center;
            color: white;
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }
        
        .logo-wrapper {
            margin-bottom: 18px;
        }
        
        .logo-circle {
            width: 75px;
            height: 75px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border: 3px solid var(--secondary);
            animation: pulse-glow 3s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); }
            50% { box-shadow: 0 10px 50px rgba(229, 197, 115, 0.15); }
        }
        
        .logo-circle img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .login-header h2 {
            font-size: 26px;
            font-weight: 800;
            margin: 14px 0 6px;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            font-size: 13px;
            opacity: 0.92;
            margin: 0;
            font-weight: 400;
            letter-spacing: 0.3px;
        }
        
        .login-header .badge {
            display: inline-block;
            background: rgba(229, 197, 115, 0.25);
            color: var(--secondary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 8px;
            border: 1px solid rgba(229, 197, 115, 0.4);
        }
        
        .login-body {
            padding: 30px;
        }
        
        .error-alert {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease-in-out;
            font-size: 14px;
        }
        
        .error-alert i {
            font-size: 16px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .form-group {
            margin-bottom: 16px;
            position: relative;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
            text-transform: none;
            letter-spacing: 0;
        }
        
        .form-group label i {
            font-size: 15px;
            color: var(--secondary);
        }
        
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            color: white;
            font-weight: 500;
        }
        
        .form-group.password-field input {
            padding-right: 42px;
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(229, 197, 115, 0.2);
            background: rgba(255, 255, 255, 0.18);
        }
        
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
            transition: all 0.3s ease;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: white;
        }
        
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #972529 0%, #c44536 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 16px;
            box-shadow: 0 8px 24px rgba(151, 37, 41, 0.25);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(151, 37, 41, 0.35);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .btn-login i {
            font-size: 15px;
        }
        
        .login-info {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(229, 197, 115, 0.3);
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 20px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }
        
        .login-info strong {
            color: var(--secondary);
            display: block;
            margin-bottom: 6px;
        }
        
        .login-footer {
            padding: 18px 30px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            font-size: 12px;
            color: white;
        }
        
        .login-footer a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 3px;
        }
        
        .login-footer a:hover {
            color: white;
            text-shadow: 0 0 8px rgba(229, 197, 115, 0.3);
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }
            
            .login-header {
                padding: 25px 20px;
            }
            
            .logo-circle {
                width: 60px;
                height: 60px;
            }
            
            .logo-circle img {
                width: 50px;
                height: 50px;
            }
            
            .login-header h2 {
                font-size: 20px;
            }
            
            .login-body {
                padding: 25px;
            }
            
            .login-footer {
                padding: 15px 20px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-wrapper">
                    <div class="logo-circle">
                        <img src="../../assets/images/logo.png" alt="CTU Logo">
                    </div>
                </div>
                <h2>Scanner Access</h2>
                <p>CTU Campus Entry System</p>
                <span class="badge"><i class="fas fa-lock"></i> Secure Access Required</span>
            </div>
            <div class="login-body">
                <div class="login-info">
                    <strong><i class="fas fa-info-circle"></i> Authorized Personnel Only</strong>
                    Use your Security Guard or Admin credentials to access the scanner system.
                </div>
                
                <?php if ($error): ?>
                    <div class="error-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="identifier"><i class="fas fa-id-badge"></i> ID or Email</label>
                        <input type="text" id="identifier" name="identifier" placeholder="Enter Security ID or Admin Email" required autofocus>
                    </div>
                    
                    <div class="form-group password-field">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Access Scanner
                    </button>
                </form>
            </div>
            <div class="login-footer">
                <a href="../admin/login.php">Admin Login</a> | 
                <a href="../security/login.php">Security Login</a> | 
                <a href="../../index.php">Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleButton = event.target.closest('.password-toggle');
            const icon = toggleButton.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
