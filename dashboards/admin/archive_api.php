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
        case 'archive_user':
            /**
             * Archive a user with reason
             * POST: user_id, type, reason (deleted/graduated/resigned/inactive), notes
             */
            $userId = $_POST['user_id'] ?? '';
            $reason = $_POST['reason'] ?? 'deleted';
            $notes = $_POST['notes'] ?? '';

            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                break;
            }

            // Validate reason
            $validReasons = ['deleted', 'graduated', 'resigned', 'inactive'];
            if (!in_array($reason, $validReasons)) {
                echo json_encode(['success' => false, 'message' => 'Invalid archive reason']);
                break;
            }

            // Get user data before archiving
            $userData = null;
            $imagePath = null;

            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    SELECT StudentID as UserID, StudentFName as FirstName, StudentMName as MiddleName, 
                           StudentLName as LastName, Course as CourseOrSchedule, YearLvl as YearLevelOrPosition, 
                           Section, Department, Gender, BirthDate, image, created_at
                    FROM students WHERE StudentID = ?
                ");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    SELECT FacultyID as UserID, FacultyFName as FirstName, FacultyMName as MiddleName, 
                           FacultyLName as LastName, Department, Gender, Birthdate as BirthDate, 
                           image, created_at
                    FROM faculty WHERE FacultyID = ?
                ");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("
                    SELECT SecurityID as UserID, SecurityFName as FirstName, SecurityMName as MiddleName, 
                           SecurityLName as LastName, TimeSched as CourseOrSchedule, Gender, BirthDate, 
                           image, created_at
                    FROM security WHERE SecurityID = ?
                ");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("
                    SELECT StaffID as UserID, StaffFName as FirstName, StaffMName as MiddleName, 
                           StaffLName as LastName, Position as YearLevelOrPosition, Department, 
                           Gender, BirthDate, image, created_at
                    FROM staff WHERE StaffID = ?
                ");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$userData) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                break;
            }

            // Insert into archive table
            $stmt = $scanner->conn->prepare("
                INSERT INTO archive (
                    OriginalUserType, OriginalUserID, FirstName, MiddleName, LastName, 
                    Gender, BirthDate, Department, CourseOrSchedule, YearLevelOrPosition, 
                    Section, ImagePath, ArchiveReason, OriginalCreatedDate, AdminNotes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $userType,
                $userData['UserID'],
                $userData['FirstName'] ?? null,
                $userData['MiddleName'] ?? null,
                $userData['LastName'] ?? null,
                $userData['Gender'] ?? null,
                $userData['BirthDate'] ?? null,
                $userData['Department'] ?? null,
                $userData['CourseOrSchedule'] ?? null,
                $userData['YearLevelOrPosition'] ?? null,
                $userData['Section'] ?? null,
                $userData['image'] ?? null,
                $reason,
                $userData['created_at'] ?? date('Y-m-d H:i:s'),
                $notes
            ]);

            if ($result) {
                // Delete the user from original table
                if ($userType === 'students') {
                    $stmt = $scanner->conn->prepare("DELETE FROM students WHERE StudentID = ?");
                } elseif ($userType === 'faculty') {
                    $stmt = $scanner->conn->prepare("DELETE FROM faculty WHERE FacultyID = ?");
                } elseif ($userType === 'security') {
                    $stmt = $scanner->conn->prepare("DELETE FROM security WHERE SecurityID = ?");
                } elseif ($userType === 'staff') {
                    $stmt = $scanner->conn->prepare("DELETE FROM staff WHERE StaffID = ?");
                }

                if ($stmt->execute([$userId])) {
                    // Delete associated image file
                    if ($userData && !empty($userData['image'])) {
                        $imageUploader->deleteImage($userData['image']);
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'User archived successfully as ' . ucfirst($reason)
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Failed to delete user from active records'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to archive user'
                ]);
            }
            break;

        case 'get_archived':
            /**
             * Get archived records with filtering
             * GET: type (optional), reason (optional)
             */
            $typeFilter = $_GET['type'] ?? '';
            $reasonFilter = $_GET['reason'] ?? '';

            $query = "SELECT * FROM archive WHERE 1=1";
            $params = [];

            if ($typeFilter && in_array($typeFilter, ['students', 'faculty', 'security', 'staff'])) {
                $query .= " AND OriginalUserType = ?";
                $params[] = $typeFilter;
            }

            if ($reasonFilter && in_array($reasonFilter, ['deleted', 'graduated', 'resigned', 'inactive'])) {
                $query .= " AND ArchiveReason = ?";
                $params[] = $reasonFilter;
            }

            $query .= " ORDER BY ArchiveDate DESC";

            $stmt = $scanner->conn->prepare($query);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($records);
            break;

        case 'get_archive_stats':
            /**
             * Get archive statistics
             */
            $stats = [
                'total' => 0,
                'by_type' => [],
                'by_reason' => []
            ];

            // Total archived records
            $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM archive");
            $stmt->execute();
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // By user type
            $stmt = $scanner->conn->prepare("
                SELECT OriginalUserType, COUNT(*) as count FROM archive 
                GROUP BY OriginalUserType
            ");
            $stmt->execute();
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // By archive reason
            $stmt = $scanner->conn->prepare("
                SELECT ArchiveReason, COUNT(*) as count FROM archive 
                GROUP BY ArchiveReason
            ");
            $stmt->execute();
            $stats['by_reason'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($stats);
            break;

        case 'restore_user':
            /**
             * Restore a user from archive
             * POST: archive_id
             */
            $archiveId = $_POST['archive_id'] ?? '';

            if (!$archiveId) {
                echo json_encode(['success' => false, 'message' => 'Archive ID is required']);
                break;
            }

            // Get archived user data
            $stmt = $scanner->conn->prepare("SELECT * FROM archive WHERE ArchiveID = ?");
            $stmt->execute([$archiveId]);
            $archivedUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$archivedUser) {
                echo json_encode(['success' => false, 'message' => 'Archived record not found']);
                break;
            }
            
            // Debug: Log what we got
            error_log("Archive record data: " . json_encode($archivedUser));

            // Restore to original table
            $userType = trim(strtolower($archivedUser['OriginalUserType'] ?? ''));
            $userId = $archivedUser['OriginalUserID'];
            $result = false;
            
            // If userType is empty, try to infer it from the data
            if (empty($userType)) {
                // Check if this looks like staff data (has YearLevelOrPosition but no Section/CourseOrSchedule typical for students/faculty)
                if (!empty($archivedUser['YearLevelOrPosition']) && empty($archivedUser['Section']) && empty($archivedUser['CourseOrSchedule'])) {
                    $userType = 'staff';
                } elseif (!empty($archivedUser['Section'])) {
                    $userType = 'students';
                } elseif (!empty($archivedUser['CourseOrSchedule']) && empty($archivedUser['Section'])) {
                    // Could be faculty or security, check the actual value
                    if (strpos($archivedUser['CourseOrSchedule'], ':') !== false || strpos($archivedUser['CourseOrSchedule'], '-') !== false) {
                        $userType = 'security'; // Likely a time schedule like "6AM-6PM"
                    } else {
                        $userType = 'faculty';
                    }
                }
            }

            if ($userType === 'students') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO students (
                        StudentID, StudentFName, StudentMName, StudentLName, 
                        Gender, BirthDate, Course, YearLvl, Section, Department, image
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $userId,
                    $archivedUser['FirstName'],
                    $archivedUser['MiddleName'],
                    $archivedUser['LastName'],
                    $archivedUser['Gender'],
                    $archivedUser['BirthDate'],
                    $archivedUser['CourseOrSchedule'],
                    $archivedUser['YearLevelOrPosition'],
                    $archivedUser['Section'],
                    $archivedUser['Department'],
                    $archivedUser['ImagePath']
                ]);
                
            } elseif ($userType === 'faculty') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO faculty (
                        FacultyID, FacultyFName, FacultyMName, FacultyLName, 
                        Gender, Birthdate, Department, image
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $userId,
                    $archivedUser['FirstName'],
                    $archivedUser['MiddleName'],
                    $archivedUser['LastName'],
                    $archivedUser['Gender'],
                    $archivedUser['BirthDate'],
                    $archivedUser['Department'],
                    $archivedUser['ImagePath']
                ]);
                
            } elseif ($userType === 'security') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO security (
                        SecurityID, SecurityFName, SecurityMName, SecurityLName, 
                        Gender, BirthDate, TimeSched, image
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $userId,
                    $archivedUser['FirstName'],
                    $archivedUser['MiddleName'],
                    $archivedUser['LastName'],
                    $archivedUser['Gender'],
                    $archivedUser['BirthDate'],
                    $archivedUser['CourseOrSchedule'],
                    $archivedUser['ImagePath']
                ]);
            } elseif ($userType === 'staff') {
                $stmt = $scanner->conn->prepare("
                    INSERT INTO staff (
                        StaffID, StaffFName, StaffMName, StaffLName, 
                        Gender, BirthDate, Position, Department, image
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $userId,
                    $archivedUser['FirstName'],
                    $archivedUser['MiddleName'],
                    $archivedUser['LastName'],
                    $archivedUser['Gender'],
                    $archivedUser['BirthDate'],
                    $archivedUser['YearLevelOrPosition'],
                    $archivedUser['Department'],
                    $archivedUser['ImagePath']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid or missing user type: "' . $userType . '"']);
                break;
            }

            if ($result) {
                // Delete from archive
                $stmt = $scanner->conn->prepare("DELETE FROM archive WHERE ArchiveID = ?");
                if ($stmt->execute([$archiveId])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'User restored successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'User restored but failed to remove from archive'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to restore user: ' . ($stmt ? implode(', ', $stmt->errorInfo()) : 'Database error')
                ]);
            }
            break;

        case 'delete_archived':
            /**
             * Permanently delete from archive (with no recovery)
             * POST: archive_id
             */
            $archiveId = $_POST['archive_id'] ?? '';

            if (!$archiveId) {
                echo json_encode(['success' => false, 'message' => 'Archive ID is required']);
                break;
            }

            // Get image path to delete
            $stmt = $scanner->conn->prepare("SELECT ImagePath FROM archive WHERE ArchiveID = ?");
            $stmt->execute([$archiveId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete from archive
            $stmt = $scanner->conn->prepare("DELETE FROM archive WHERE ArchiveID = ?");
            if ($stmt->execute([$archiveId])) {
                // Delete associated image file
                if ($result && !empty($result['ImagePath'])) {
                    $imageUploader->deleteImage($result['ImagePath']);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Archived record deleted permanently'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete archived record'
                ]);
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }

} catch (Exception $e) {
    error_log("Archive management error: " . $e->getMessage());
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
?>
