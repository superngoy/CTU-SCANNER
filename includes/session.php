<?php
session_start();

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function loginAdmin($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM admin WHERE email = ? AND isActive = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['AdminID'];
                $_SESSION['admin_name'] = $admin['AdminFName'] . ' ' . $admin['AdminLName'];
                $_SESSION['user_type'] = 'admin';
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function loginSecurity($securityId, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM security WHERE SecurityID = ? AND isActive = 1");
            $stmt->execute([$securityId]);
            $security = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($security && password_verify($password, $security['password'])) {
                $_SESSION['security_id'] = $security['SecurityID'];
                $_SESSION['security_name'] = $security['SecurityFName'] . ' ' . $security['SecurityLName'];
                $_SESSION['user_type'] = 'security';
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function isLoggedIn($userType = null) {
        if ($userType) {
            return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $userType;
        }
        return isset($_SESSION['user_type']);
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function requireAuth($userType = null) {
        if (!$this->isLoggedIn($userType)) {
            header('Location: login.php');
            exit();
        }
    }
}
?>