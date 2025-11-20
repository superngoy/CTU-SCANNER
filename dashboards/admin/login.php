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
        :root {
            --gold: #E5C573;
            --red: #972529;
            --dark-red: #972529;
            --gold-light: rgba(229, 197, 115, 0.2);
        }
        
        body {
            background: #972529;
            min-height: 100vh;
            display: flex;
            align-items: center;
            animation: gradientShift 15s ease infinite;
            background-size: 200% 200%;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            width: 600px;
            height: 600px;
            transform: translate(-50%, -50%);
            background: url('../../assets/images/logo.png') no-repeat center center;
            background-size: contain;
            opacity: 0.1;
            z-index: -1;
            border-radius: 50%;
            box-shadow: 0 0 200px 100px rgba(138, 33, 37, 0.3);
            animation: slowRotate 30s linear infinite;
        }

        @keyframes slowRotate {
            from {
                transform: translate(-50%, -50%) rotate(0deg);
            }
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: slideIn 0.8s ease-out;
            border: 1px solid rgba(223, 187, 101, 0.5);
            color: #ffffff;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }
        
        .login-header {
            background: linear-gradient(45deg, rgba(138, 33, 37, 0.7), rgba(138, 33, 37, 0.5));
            color: var(--gold);
            border-radius: 20px 20px 0 0;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto;
            padding: 5px;
            background: linear-gradient(45deg, var(--gold), rgba(223, 187, 101, 0.5));
            box-shadow: 0 0 20px rgba(223, 187, 101, 0.3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: glowPulse 2s infinite;
        }

        .logo-circle .logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.9);
            padding: 5px;
        }

        @keyframes glowPulse {
            0% { box-shadow: 0 0 20px rgba(223, 187, 101, 0.3); }
            50% { box-shadow: 0 0 30px rgba(223, 187, 101, 0.5); }
            100% { box-shadow: 0 0 20px rgba(223, 187, 101, 0.3); }
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(223, 187, 101, 0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            100% { left: 100%; }
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .form-floating input {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(223, 187, 101, 0.3);
            transition: all 0.3s ease;
            color: white;
        }
        
        .form-floating input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(223, 187, 101, 0.25);
            transform: scale(1.02);
            color: white;
        }
        
        .form-floating label {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .btn-login {
            background: linear-gradient(45deg, var(--red), rgba(138, 33, 37, 0.9));
            border: 2px solid var(--gold);
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: var(--gold);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(45deg, rgba(138, 33, 37, 0.9), var(--red));
            box-shadow: 0 5px 15px rgba(223, 187, 101, 0.3);
            color: var(--gold);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .fa-shield-alt {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .text-decoration-none {
            color: var(--gold);
            transition: all 0.3s ease;
            position: relative;
            opacity: 0.8;
        }
        
        .text-decoration-none:hover {
            color: var(--gold);
            opacity: 1;
        }
        
        .text-decoration-none::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(45deg, var(--gold), rgba(223, 187, 101, 0.5));
            transition: width 0.3s ease;
        }
        
        .text-decoration-none:hover::after {
            width: 100%;
        }
        
        .alert {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <div class="logo-circle mb-3">
                            <img src="../../assets/images/logo.png" alt="CTU Logo" class="logo">
                        </div>
                        <h3>Admin Login</h3>
                        <p class="mb-0">CTU Scanner System</p>
                    </div>
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <a href="../security/login.php" class="text-decoration-none">Security Login</a> |
                                <a href="../scanner/" class="text-decoration-none">Scanner Dashboard</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>