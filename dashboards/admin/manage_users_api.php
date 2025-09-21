<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';
require_once '../../includes/image_upload_helper.php';

try {
    $scanner = new CTUScanner();
    $imageUploader = new ImageUploadHelper();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $userType = $_GET['type'] ?? $_POST['type'] ?? '';

    switch ($action) {
        case 'get_users':
            $users = [];
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    SELECT StudentID, StudentFName, StudentMName, StudentLName, 
                           Course, YearLvl, Section, Department, Gender, BirthDate, 
                           isActive, image, created_at
                    FROM students 
                    ORDER BY StudentLName ASC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    SELECT FacultyID, FacultyFName, FacultyMName, FacultyLName, 
                           Department, Gender, Birthdate, isActive, image, created_at
                    FROM faculty 
                    ORDER BY FacultyLName ASC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("
                    SELECT SecurityID, SecurityFName, SecurityMName, SecurityLName, 
                           Gender, BirthDate, TimeSched, isActive, image, created_at
                    FROM security 
                    ORDER BY SecurityLName ASC
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Add image URLs to response
            foreach ($users as &$user) {
                $user['imageUrl'] = $imageUploader->getImageUrl($user['image']);
            }
            
            echo json_encode($users);
            break;
            
        case 'add_user':
            $imagePath = null;
            $imageUploadResult = ['success' => true];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $userId = $_POST[$userType === 'students' ? 'student_id' : 
                                ($userType === 'faculty' ? 'faculty_id' : 'security_id')];
                $imageUploadResult = $imageUploader->uploadImage($_FILES['image'], $userType, $userId);
                if ($imageUploadResult['success']) {
                    $imagePath = $imageUploadResult['path'];
                }
            }
            
            if (!$imageUploadResult['success']) {
                echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $imageUploadResult['message']]);
                break;
            }
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO students (StudentID, StudentFName, StudentMName, StudentLName, 
                                        Gender, BirthDate, Course, YearLvl, Section, Department, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $_POST['department'],
                    $imagePath
                ]);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO faculty (FacultyID, FacultyFName, FacultyMName, FacultyLName, 
                                       Gender, Birthdate, Department, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $_POST['faculty_id'],
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['department'],
                    $imagePath
                ]);
                
            } elseif ($userType === 'security') {
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $scanner->conn->prepare("
                    INSERT INTO security (SecurityID, SecurityFName, SecurityMName, SecurityLName, 
                                        Gender, BirthDate, TimeSched, password, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $_POST['security_id'],
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['time_sched'],
                    $hashedPassword,
                    $imagePath
                ]);
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User added successfully']);
            } else {
                // If database insert fails, remove uploaded image
                if ($imagePath) {
                    $imageUploader->deleteImage($imagePath);
                }
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
                $user['imageUrl'] = $imageUploader->getImageUrl($user['image']);
                echo json_encode($user);
            } else {
                echo json_encode(['error' => 'User not found']);
            }
            break;
            
        case 'update_user':
            $userId = $_POST['user_id'];
            $imagePath = null;
            $imageUploadResult = ['success' => true];
            
            // Get current user data to preserve existing image if no new image is uploaded
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("SELECT image FROM students WHERE StudentID = ?");
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("SELECT image FROM faculty WHERE FacultyID = ?");
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("SELECT image FROM security WHERE SecurityID = ?");
            }
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagePath = $currentUser['image']; // Keep existing image by default
            
            // Handle new image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageUploadResult = $imageUploader->uploadImage($_FILES['image'], $userType, $userId);
                if ($imageUploadResult['success']) {
                    $imagePath = $imageUploadResult['path'];
                }
            }
            
            if (!$imageUploadResult['success']) {
                echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $imageUploadResult['message']]);
                break;
            }
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    UPDATE students SET 
                        StudentFName = ?, StudentMName = ?, StudentLName = ?, 
                        Gender = ?, BirthDate = ?, Course = ?, YearLvl = ?, 
                        Section = ?, Department = ?, image = ?
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
                    $imagePath,
                    $userId
                ]);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    UPDATE faculty SET 
                        FacultyFName = ?, FacultyMName = ?, FacultyLName = ?, 
                        Gender = ?, Birthdate = ?, Department = ?, image = ?
                    WHERE FacultyID = ?
                ");
                $result = $stmt->execute([
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['department'],
                    $imagePath,
                    $userId
                ]);
                
            } elseif ($userType === 'security') {
                // Check if password should be updated
                if (!empty($_POST['password'])) {
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $scanner->conn->prepare("
                        UPDATE security SET 
                            SecurityFName = ?, SecurityMName = ?, SecurityLName = ?, 
                            Gender = ?, BirthDate = ?, TimeSched = ?, password = ?, image = ?
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
                        $imagePath,
                        $userId
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $scanner->conn->prepare("
                        UPDATE security SET 
                            SecurityFName = ?, SecurityMName = ?, SecurityLName = ?, 
                            Gender = ?, BirthDate = ?, TimeSched = ?, image = ?
                        WHERE SecurityID = ?
                    ");
                    $result = $stmt->execute([
                        $_POST['first_name'],
                        $_POST['middle_name'],
                        $_POST['last_name'],
                        $_POST['gender'],
                        $_POST['birthdate'],
                        $_POST['time_sched'],
                        $imagePath,
                        $userId
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
            
            // Get user data to delete associated image
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("SELECT image FROM students WHERE StudentID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $scanner->conn->prepare("DELETE FROM students WHERE StudentID = ?");
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("SELECT image FROM faculty WHERE FacultyID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $scanner->conn->prepare("DELETE FROM faculty WHERE FacultyID = ?");
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("SELECT image FROM security WHERE SecurityID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $scanner->conn->prepare("DELETE FROM security WHERE SecurityID = ?");
            }
            
            if ($stmt->execute([$userId])) {
                // Delete associated image file
                if ($userData && !empty($userData['image'])) {
                    $imageUploader->deleteImage($userData['image']);
                }
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
            }
            break;
            
        case 'delete_image':
            $userId = $_POST['user_id'];
            
            // Remove image from filesystem
            $imageUploader->removeOldImage($userType, $userId);
            
            // Update database to remove image reference
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("UPDATE students SET image = NULL WHERE StudentID = ?");
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("UPDATE faculty SET image = NULL WHERE FacultyID = ?");
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("UPDATE security SET image = NULL WHERE SecurityID = ?");
            }
            
            if ($stmt->execute([$userId])) {
                echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
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