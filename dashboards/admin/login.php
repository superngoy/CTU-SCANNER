<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

$auth = new Auth();

if ($auth->isLoggedIn('admin')) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->loginAdmin($email, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CTU Scanner</title>
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
            --dark: #1a1a1a;
            --light: #f5f5f5;
            --border: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #972529 0%, #c44536 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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
            background: linear-gradient(135deg, rgba(151, 37, 41, 0.8) 0%, rgba(196, 69, 54, 0.8) 100%);
            backdrop-filter: blur(10px);
            padding: 30px 25px;
            text-align: center;
            color: white;
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo-wrapper {
            margin-bottom: 15px;
        }
        
        .logo-circle {
            width: 70px;
            height: 70px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(229, 197, 115, 0.3);
        }
        
        .logo-circle img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .login-header h2 {
            font-size: 24px;
            font-weight: 700;
            margin: 12px 0 4px;
            letter-spacing: -0.3px;
        }
        
        .login-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
            font-weight: 300;
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
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #333;
        }
        
        .form-group.password-field input {
            padding-right: 40px;
        }
        
        .form-group input::placeholder {
            color: #aaa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: rgba(229, 197, 115, 0.5);
            box-shadow: 0 0 0 3px rgba(229, 197, 115, 0.15);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 16px;
            transition: all 0.3s ease;
            padding: 4px;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, rgba(151, 37, 41, 0.9) 0%, rgba(196, 69, 54, 0.9) 100%);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(229, 197, 115, 0.3);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(151, 37, 41, 0.35);
            border-color: rgba(229, 197, 115, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .login-footer {
            padding: 18px 30px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
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
                <h2>Admin Dashboard</h2>
                <p>CTU Scanner System</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="error-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter Email" required autofocus>
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
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
            </div>
            <div class="login-footer">
                <a href="../security/login.php">Security Login</a> | 
                <a href="../scanner/">Scanner</a>
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