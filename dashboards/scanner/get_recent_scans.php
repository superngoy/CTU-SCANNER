<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    $recentEntries = $scanner->getRecentEntries(10);
    $recentExits = $scanner->getRecentExits(10);
    
    // Get database connection
    $database = new Database();
    if (method_exists($database, 'connect')) {
        $conn = $database->connect();
    } elseif (method_exists($database, 'getConnection')) {
        $conn = $database->getConnection();
    } elseif (method_exists($database, 'connection')) {
        $conn = $database->connection();
    } else {
        throw new Exception('Cannot find database connection method');
    }
    
    $scans = [];
    
    // Process entries
    foreach ($recentEntries as $entry) {
        $personData = getPersonWithImage($conn, $entry['PersonID'], $entry['PersonCategory']);
        
        $firstName = $entry['StudentFName'] ?? $entry['FacultyFName'] ?? $entry['SecurityFName'] ?? 'Unknown';
        $middleName = $entry['StudentMName'] ?? $entry['FacultyMName'] ?? $entry['SecurityMName'] ?? '';
        $lastName = $entry['StudentLName'] ?? $entry['FacultyLName'] ?? $entry['SecurityLName'] ?? '';
        
        $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
        $fullName = preg_replace('/\s+/', ' ', $fullName); // Remove extra spaces
        
        $scans[] = [
            'name' => $fullName,
            'id' => $entry['PersonID'],
            'type' => ucfirst($entry['PersonCategory']),
            'action' => 'Entry',
            'time' => date('h:i A', strtotime($entry['Timestamp'])),
            'timestamp' => $entry['Timestamp'],
            'image' => $personData['image'],
            'department' => $personData['department'] ?? null,
            'course' => $personData['course'] ?? null,
            'year' => $personData['year'] ?? null,
            'section' => $personData['section'] ?? null,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName
        ];
    }
    
    // Process exits
    foreach ($recentExits as $exit) {
        $personData = getPersonWithImage($conn, $exit['PersonID'], $exit['PersonCategory']);
        
        $firstName = $exit['StudentFName'] ?? $exit['FacultyFName'] ?? $exit['SecurityFName'] ?? 'Unknown';
        $middleName = $exit['StudentMName'] ?? $exit['FacultyMName'] ?? $exit['SecurityMName'] ?? '';
        $lastName = $exit['StudentLName'] ?? $exit['FacultyLName'] ?? $exit['SecurityLName'] ?? '';
        
        $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
        $fullName = preg_replace('/\s+/', ' ', $fullName); // Remove extra spaces
        
        $scans[] = [
            'name' => $fullName,
            'id' => $exit['PersonID'],
            'type' => ucfirst($exit['PersonCategory']),
            'action' => 'Exit',
            'time' => date('h:i A', strtotime($exit['Timestamp'])),
            'timestamp' => $exit['Timestamp'],
            'image' => $personData['image'],
            'department' => $personData['department'] ?? null,
            'course' => $personData['course'] ?? null,
            'year' => $personData['year'] ?? null,
            'section' => $personData['section'] ?? null,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName
        ];
    }
    
    // Sort by timestamp (most recent first)
    usort($scans, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    echo json_encode(['scans' => array_slice($scans, 0, 10)]);
    
} catch (Exception $e) {
    error_log("get_recent_scans error: " . $e->getMessage());
    echo json_encode(['scans' => [], 'error' => $e->getMessage()]);
}

/**
 * Get person data with image
 */
function getPersonWithImage($conn, $personId, $personCategory) {
    $image_path = null;
    
    try {
        if (strtolower($personCategory) === 'student') {
            $stmt = $conn->prepare("SELECT image FROM students WHERE StudentID = ?");
        } elseif (strtolower($personCategory) === 'faculty') {
            $stmt = $conn->prepare("SELECT image FROM faculty WHERE FacultyID = ?");
        } elseif (strtolower($personCategory) === 'security') {
            $stmt = $conn->prepare("SELECT image FROM security WHERE SecurityID = ?");
        } else {
            return ['image' => null];
        }
        
        $stmt->execute([$personId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['image'])) {
            // Check if image file exists
            $fullPath = '../../' . $result['image'];
            if (file_exists($fullPath)) {
                $image_path = '../../' . $result['image'];
            }
        }
        
    } catch (Exception $e) {
        error_log("Error getting image for $personCategory $personId: " . $e->getMessage());
    }
    
    // Try to include some handy additional info (department, course, year, section)
    $additional = [
        'department' => null,
        'course' => null,
        'year' => null,
        'section' => null
    ];

    try {
        if (strtolower($personCategory) === 'student') {
            $stmt2 = $conn->prepare("SELECT Department, Course, YearLvl, Section FROM students WHERE StudentID = ?");
            $stmt2->execute([$personId]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $additional['department'] = $row['Department'] ?? null;
                $additional['course'] = $row['Course'] ?? null;
                $additional['year'] = $row['YearLvl'] ?? null;
                $additional['section'] = $row['Section'] ?? null;
            }
        } elseif (strtolower($personCategory) === 'faculty') {
            $stmt2 = $conn->prepare("SELECT Department FROM faculty WHERE FacultyID = ?");
            $stmt2->execute([$personId]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $additional['department'] = $row['Department'] ?? null;
            }
        }
    } catch (Exception $e) {
        error_log('Error getting additional info: ' . $e->getMessage());
    }

    return array_merge(['image' => $image_path], $additional);
}
?>