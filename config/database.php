<?php
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
?>