<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $userType = $_GET['type'] ?? $_POST['type'] ?? '';

    switch ($action) {
        case 'get_users':
            $users = [];
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    SELECT StudentID, StudentFName, StudentMName, StudentLName, 
                           Course, YearLvl, Section, Department, Gender, BirthDate, isActive
                    FROM students 
                    ORDER BY StudentLName ASC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    SELECT FacultyID, FacultyFName, FacultyMName, FacultyLName, 
                           Department, Gender, Birthdate, isActive
                    FROM faculty 
                    ORDER BY FacultyLName ASC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("
                    SELECT SecurityID, SecurityFName, SecurityMName, SecurityLName, 
                           Gender, BirthDate, TimeSched, isActive
                    FROM security 
                    ORDER BY SecurityLName ASC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode($users);
            break;
            
        case 'add_user':
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO students (StudentID, StudentFName, StudentMName, StudentLName, 
                                        Gender, BirthDate, Course, YearLvl, Section, Department) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $_POST['student_id'],
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['course'],
                    $_POST['year_level'],
                    $_POST['section'],
                    $_POST['department']
                ]);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO faculty (FacultyID, FacultyFName, FacultyMName, FacultyLName, 
                                       Gender, Birthdate, Department) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $_POST['faculty_id'],
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['department']
                ]);
                
            } elseif ($userType === 'security') {
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $scanner->conn->prepare("
                    INSERT INTO security (SecurityID, SecurityFName, SecurityMName, SecurityLName, 
                                        Gender, BirthDate, TimeSched, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $_POST['security_id'],
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['time_sched'],
                    $hashedPassword
                ]);
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add user']);
            }
            break;
            
        case 'update_status':
            $userId = $_POST['user_id'];
            $status = $_POST['status'];
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("UPDATE students SET isActive = ? WHERE StudentID = ?");
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("UPDATE faculty SET isActive = ? WHERE FacultyID = ?");
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("UPDATE security SET isActive = ? WHERE SecurityID = ?");
            }
            
            if ($stmt->execute([$status, $userId])) {
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            break;
            
        case 'get_user':
            $userId = $_GET['user_id'];
            $user = null;
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("SELECT * FROM students WHERE StudentID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("SELECT * FROM faculty WHERE FacultyID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("SELECT * FROM security WHERE SecurityID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                // Remove password from response for security
                if ($user) {
                    unset($user['password']);
                }
            }
            
            if ($user) {
                echo json_encode($user);
            } else {
                echo json_encode(['error' => 'User not found']);
            }
            break;
            
        case 'update_user':
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    UPDATE students SET 
                        StudentFName = ?, StudentMName = ?, StudentLName = ?, 
                        Gender = ?, BirthDate = ?, Course = ?, YearLvl = ?, 
                        Section = ?, Department = ?
                    WHERE StudentID = ?
                ");
                $result = $stmt->execute([
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['course'],
                    $_POST['year_level'],
                    $_POST['section'],
                    $_POST['department'],
                    $_POST['user_id']
                ]);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    UPDATE faculty SET 
                        FacultyFName = ?, FacultyMName = ?, FacultyLName = ?, 
                        Gender = ?, Birthdate = ?, Department = ?
                    WHERE FacultyID = ?
                ");
                $result = $stmt->execute([
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['department'],
                    $_POST['user_id']
                ]);
                
            } elseif ($userType === 'security') {
                // Check if password should be updated
                if (!empty($_POST['password'])) {
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $scanner->conn->prepare("
                        UPDATE security SET 
                            SecurityFName = ?, SecurityMName = ?, SecurityLName = ?, 
                            Gender = ?, BirthDate = ?, TimeSched = ?, password = ?
                        WHERE SecurityID = ?
                    ");
                    $result = $stmt->execute([
                        $_POST['first_name'],
                        $_POST['middle_name'],
                        $_POST['last_name'],
                        $_POST['gender'],
                        $_POST['birthdate'],
                        $_POST['time_sched'],
                        $hashedPassword,
                        $_POST['user_id']
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $scanner->conn->prepare("
                        UPDATE security SET 
                            SecurityFName = ?, SecurityMName = ?, SecurityLName = ?, 
                            Gender = ?, BirthDate = ?, TimeSched = ?
                        WHERE SecurityID = ?
                    ");
                    $result = $stmt->execute([
                        $_POST['first_name'],
                        $_POST['middle_name'],
                        $_POST['last_name'],
                        $_POST['gender'],
                        $_POST['birthdate'],
                        $_POST['time_sched'],
                        $_POST['user_id']
                    ]);
                }
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update user']);
            }
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'];
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("DELETE FROM students WHERE StudentID = ?");
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("DELETE FROM faculty WHERE FacultyID = ?");
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("DELETE FROM security WHERE SecurityID = ?");
            }
            
            if ($stmt->execute([$userId])) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log("User management error: " . $e->getMessage());
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
?>