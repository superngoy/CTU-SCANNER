<?php
// ============================================
// SECURITY HEADERS - Must be called before any output
// ============================================
if (!headers_sent()) {
    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content-Security-Policy (allows necessary external resources)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' cdnjs.cloudflare.com; connect-src 'self'; frame-ancestors 'self';");
}

class Database {
    private $host = 'localhost';
    private $db_name = 'ctu_scanner';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set timezone for MySQL connection (UTC+8 for Philippine Time)
            $this->conn->exec("SET time_zone = '+08:00'");
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }

    public function getConnection() {
        return $this->connect();
    }

    public function connection() {
        return $this->connect();
    }
}

// Set PHP timezone globally for all files that include this config
date_default_timezone_set('Asia/Manila');

// Include security sanitization functions
require_once __DIR__ . '/../includes/sanitize.php';
?>