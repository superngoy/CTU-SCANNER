<?php
require_once '../config/database.php';

class DatabaseBackup {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function backup() {
        $tables = ['students', 'faculty', 'security', 'admin', 'scanner', 'entrylogs', 'exitlogs'];
        $backup = "-- CTU Scanner Database Backup\n";
        $backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $backup .= $this->backupTable($table);
        }
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $backup);
        
        return $filename;
    }
    
    private function backupTable($table) {
        $result = $this->conn->query("SELECT * FROM $table");
        $backup = "\n-- Table: $table\n";
        $backup .= "TRUNCATE TABLE `$table`;\n";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $backup .= "INSERT INTO `$table` VALUES (";
            $values = array_map(function($value) {
                return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
            }, array_values($row));
            $backup .= implode(', ', $values) . ");\n";
        }
        
        return $backup;
    }
}

if (isset($_GET['backup'])) {
    $backup = new DatabaseBackup();
    $filename = $backup->backup();
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    unlink($filename);
}
?>