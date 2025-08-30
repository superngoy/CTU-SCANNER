<?php
// create_users.php - Run this once to create proper user accounts
require_once 'config/database.php';

$database = new Database();
$conn = $database->connect();

// Create admin user
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$securityPassword = password_hash('security123', PASSWORD_DEFAULT);

try {
    // Update admin
    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE AdminID = 'ADM-001'");
    $result1 = $stmt->execute([$adminPassword]);
    
    // Update security
    $stmt = $conn->prepare("UPDATE security SET password = ? WHERE SecurityID = 'SEC-001'");
    $result2 = $stmt->execute([$securityPassword]);
    
    if ($result1 && $result2) {
        echo "<div style='color: green; font-family: Arial; padding: 20px;'>";
        echo "<h2>✅ Passwords Updated Successfully!</h2>";
        echo "<p><strong>Admin Login:</strong></p>";
        echo "<p>Email: admin@ctu.edu.ph</p>";
        echo "<p>Password: admin123</p>";
        echo "<br>";
        echo "<p><strong>Security Login:</strong></p>";
        echo "<p>Security ID: SEC-001</p>";
        echo "<p>Password: security123</p>";
        echo "<br>";
        echo "<p style='color: red;'><strong>⚠️ Delete this file after use for security!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='color: red;'>❌ Error updating passwords</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>❌ Database Error: " . $e->getMessage() . "</div>";
}
?>