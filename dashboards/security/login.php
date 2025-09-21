<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

$auth = new Auth();

if ($auth->isLoggedIn('security')) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_POST) {
    $securityId = $_POST['security_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->loginSecurity($securityId, $password)) {
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
    <title>Security Login - CTU Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --gold: #D8AC41;
            --red: #E00000;
            --orange: #FF9600;
            --dark-red: #DB362D;
        }
        
        body {
            background: linear-gradient(135deg, var(--gold) 0%, var(--dark-red) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: slideIn 0.8s ease-out;
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
            background: linear-gradient(45deg, var(--red), var(--orange));
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            100% { left: 100%; }
        }
        
        .btn-login {
            background: linear-gradient(45deg, var(--gold), var(--dark-red));
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(45deg, var(--orange), var(--red));
            box-shadow: 0 5px 15px rgba(219, 54, 45, 0.3);
        }
        
        .form-floating input {
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(216, 172, 65, 0.25);
            transform: scale(1.02);
        }
        
        .fa-user-shield {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .text-decoration-none {
            color: var(--dark-red);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .text-decoration-none:hover {
            color: var(--red);
        }
        
        .text-decoration-none::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(45deg, var(--gold), var(--dark-red));
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
        
        .form-floating i {
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus + label i {
            color: var(--gold);
            transform: rotate(360deg);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-shield fa-3x mb-3"></i>
                        <h3>Security Login</h3>
                        <p class="mb-0">CTU Scanner System</p>
                    </div>
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="security_id" name="security_id" required>
                                <label for="security_id"><i class="fas fa-id-badge me-2"></i>Security ID</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <a href="../admin/login.php" class="text-decoration-none">Admin Login</a> |
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