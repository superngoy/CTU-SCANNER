<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

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
                           isActive, IsEnroll, image, created_at
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
                
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("
                    SELECT StaffID, StaffFName, StaffMName, StaffLName, 
                           Position, Department, Gender, BirthDate, isActive, image, created_at
                    FROM staff 
                    ORDER BY StaffLName ASC
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
                                ($userType === 'faculty' ? 'faculty_id' : 
                                 ($userType === 'security' ? 'security_id' : 'staff_id'))];
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
                                        Gender, BirthDate, Course, YearLvl, Section, Department, IsEnroll, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $isEnroll = isset($_POST['is_enroll']) ? 1 : 0;
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
                    $isEnroll,
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
                
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO staff (StaffID, StaffFName, StaffMName, StaffLName, 
                                     Gender, BirthDate, Position, Department, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $_POST['staff_id'],
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['position'],
                    $_POST['department'],
                    $imagePath
                ]);
            } else {
                $result = false;
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
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("UPDATE staff SET isActive = ? WHERE StaffID = ?");
            }
            
            if ($stmt->execute([$status, $userId])) {
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            break;
            
        case 'update_enrollment':
            $userId = $_POST['user_id'];
            $isEnroll = $_POST['is_enroll'];
            
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("UPDATE students SET IsEnroll = ? WHERE StudentID = ?");
                if ($stmt->execute([$isEnroll, $userId])) {
                    echo json_encode(['success' => true, 'message' => 'Enrollment status updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update enrollment status']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Enrollment status can only be updated for students']);
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
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
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
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("SELECT image FROM staff WHERE StaffID = ?");
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid user type']);
                break;
            }
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user exists
            if (!$currentUser) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                break;
            }
            
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
                        Section = ?, Department = ?, IsEnroll = ?, image = ?
                    WHERE StudentID = ?
                ");
                $isEnroll = isset($_POST['is_enroll']) ? 1 : 0;
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
                    $isEnroll,
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
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("
                    UPDATE staff SET 
                        StaffFName = ?, StaffMName = ?, StaffLName = ?, 
                        Gender = ?, BirthDate = ?, Position = ?, Department = ?, image = ?
                    WHERE StaffID = ?
                ");
                $result = $stmt->execute([
                    $_POST['first_name'],
                    $_POST['middle_name'],
                    $_POST['last_name'],
                    $_POST['gender'],
                    $_POST['birthdate'],
                    $_POST['position'],
                    $_POST['department'],
                    $imagePath,
                    $userId
                ]);
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update user']);
            }
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'];
            $userData = null;
            
            // Get user data to delete associated image
            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("SELECT image FROM students WHERE StudentID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }
                
                $stmt = $scanner->conn->prepare("DELETE FROM students WHERE StudentID = ?");
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("SELECT image FROM faculty WHERE FacultyID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }
                
                $stmt = $scanner->conn->prepare("DELETE FROM faculty WHERE FacultyID = ?");
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("SELECT image FROM security WHERE SecurityID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }
                
                $stmt = $scanner->conn->prepare("DELETE FROM security WHERE SecurityID = ?");
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("SELECT image FROM staff WHERE StaffID = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }
                
                $stmt = $scanner->conn->prepare("DELETE FROM staff WHERE StaffID = ?");
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid user type']);
                break;
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