<?php
/**
 * Notification Helper Functions
 * Lightweight notification system using shared file storage
 * (Session storage won't work since security staff and admin have different sessions)
 */

// Create notifications directory if it doesn't exist
$notificationsDir = dirname(__DIR__) . '/notifications';
if (!is_dir($notificationsDir)) {
    @mkdir($notificationsDir, 0755, true);
}

/**
 * Get notifications file path for storage
 */
function getNotificationsFilePath() {
    $dir = dirname(__DIR__) . '/notifications';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . '/notifications.json';
}

/**
 * Load all notifications from file
 */
function loadNotifications() {
    $filePath = getNotificationsFilePath();
    
    if (!file_exists($filePath)) {
        return [];
    }
    
    $content = @file_get_contents($filePath);
    if (!$content) {
        return [];
    }
    
    $notifications = json_decode($content, true);
    if (!is_array($notifications)) {
        return [];
    }
    
    return $notifications;
}

/**
 * Save notifications to file
 */
function saveNotifications($notifications) {
    $filePath = getNotificationsFilePath();
    $dir = dirname($filePath);
    
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    // Keep only last 100 notifications
    $notifications = array_slice($notifications, 0, 100);
    
    @file_put_contents($filePath, json_encode($notifications), LOCK_EX);
}

/**
 * Add notification to file storage
 * 
 * @param string $title - Notification title
 * @param string $message - Notification message
 * @param string $type - Type: info, success, warning, error
 * @param string $category - Category: system, visitor, user, scanner
 * @param string $icon - FontAwesome icon class
 * @param string $action_url - Optional URL to navigate to
 * @return void
 */
function addNotification($title, $message, $type = 'info', $category = 'system', $icon = 'fa-bell', $action_url = null) {
    $notifications = loadNotifications();

    $notification = [
        'id' => uniqid(),
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'category' => $category,
        'icon' => $icon,
        'action_url' => $action_url,
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'timestamp' => time()
    ];

    // Add to beginning of array (newest first)
    array_unshift($notifications, $notification);

    // Keep only last 100 notifications
    $notifications = array_slice($notifications, 0, 100);
    
    saveNotifications($notifications);
}

/**
 * Notification for new visitor registration
 */
function notifyVisitorRegistered($visitorName, $visitorType, $purpose) {
    $title = "New Visitor Registered";
    $message = "$visitorName ($visitorType) registered for: $purpose";
    addNotification($title, $message, 'success', 'visitor', 'fa-user-plus', '/dashboards/admin/visitor_analytics.php');
}

/**
 * Notification for visitor check-in
 */
function notifyVisitorCheckIn($visitorName, $visitorType, $location = null) {
    $title = "Visitor Checked In";
    $message = "$visitorName ($visitorType) has checked in" . ($location ? " at $location" : "");
    addNotification($title, $message, 'info', 'visitor', 'fa-sign-in-alt', '/dashboards/admin/visitor_analytics.php');
}

/**
 * Notification for visitor check-out
 */
function notifyVisitorCheckOut($visitorName) {
    $title = "Visitor Checked Out";
    $message = "$visitorName has checked out";
    addNotification($title, $message, 'info', 'visitor', 'fa-sign-out-alt', '/dashboards/admin/visitor_analytics.php');
}

/**
 * Notification for user creation
 */
function notifyUserCreated($userName, $userType) {
    $title = "New User Created";
    $message = "$userName ($userType) has been added to the system";
    addNotification($title, $message, 'success', 'user', 'fa-user-plus', '/dashboards/admin/manage_users.php');
}

/**
 * Notification for user update
 */
function notifyUserUpdated($userName) {
    $title = "User Updated";
    $message = "$userName profile has been updated";
    addNotification($title, $message, 'info', 'user', 'fa-user-edit', '/dashboards/admin/manage_users.php');
}

/**
 * Notification for scanner activity
 */
function notifyHighScannerActivity($count, $timeframe = '1 hour') {
    $title = "High Scanner Activity";
    $message = "$count entries recorded in the last $timeframe";
    addNotification($title, $message, 'warning', 'scanner', 'fa-camera', '/dashboards/admin/index.php');
}

/**
 * Notification for system events
 */
function notifySystemEvent($title, $message, $type = 'info') {
    addNotification($title, $message, $type, 'system', 'fa-cog');
}

// ==========================================
// SECURITY DASHBOARD NOTIFICATIONS (Failed Scans Only)
// ==========================================

/**
 * Get security notifications file path for storage
 */
function getSecurityNotificationsFilePath() {
    $dir = dirname(__DIR__) . '/notifications';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . '/notifications_security.json';
}

/**
 * Load all security notifications from file
 */
function loadSecurityNotifications() {
    $filePath = getSecurityNotificationsFilePath();
    
    if (!file_exists($filePath)) {
        return [];
    }
    
    $content = @file_get_contents($filePath);
    if (!$content) {
        return [];
    }
    
    $notifications = json_decode($content, true);
    if (!is_array($notifications)) {
        return [];
    }
    
    return $notifications;
}

/**
 * Save security notifications to file
 */
function saveSecurityNotifications($notifications) {
    $filePath = getSecurityNotificationsFilePath();
    $dir = dirname($filePath);
    
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    // Keep only last 100 notifications
    $notifications = array_slice($notifications, 0, 100);
    
    @file_put_contents($filePath, json_encode($notifications), LOCK_EX);
}

/**
 * Add security notification to file storage (Failed scans only)
 * 
 * @param string $title - Notification title
 * @param string $message - Notification message
 * @param string $type - Type: info, success, warning, error
 * @param string $icon - FontAwesome icon class
 * @param string $action_url - Optional URL to navigate to
 * @return void
 */
function addSecurityNotification($title, $message, $type = 'error', $icon = 'fa-exclamation-triangle', $action_url = null) {
    $notifications = loadSecurityNotifications();

    $notification = [
        'id' => uniqid(),
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'category' => 'scan_failure',
        'icon' => $icon,
        'action_url' => $action_url,
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'timestamp' => time()
    ];

    // Add to beginning of array (newest first)
    array_unshift($notifications, $notification);

    // Keep only last 100 notifications
    $notifications = array_slice($notifications, 0, 100);
    
    saveSecurityNotifications($notifications);
}

/**
 * Notification for failed scan - sent to security staff
 */
function notifySecurityFailedScan($personName, $personType, $reason, $location = null) {
    $title = "Failed Scan Alert";
    $locationStr = $location ? " at $location" : "";
    $message = "$personName ($personType) scan failed: $reason$locationStr";
    addSecurityNotification($title, $message, 'error', 'fa-exclamation-circle', '/dashboards/security/index.php');
}

/**
 * Notification for not enrolled student - sent to security
 */
function notifySecurityNotEnrolled($studentName, $studentId, $location = null) {
    $title = "Access Denied - Not Enrolled";
    $message = "$studentName ($studentId) attempted entry" . ($location ? " at $location" : "") . " but is not enrolled.";
    addSecurityNotification($title, $message, 'error', 'fa-user-slash', '/dashboards/security/index.php');
}

/**
 * Notification for inactive account - sent to security
 */
function notifySecurityInactiveAccount($personName, $personType, $personId, $location = null) {
    $title = "Access Denied - Inactive Account";
    $message = ucfirst($personType) . " " . $personName . " (" . $personId . ") attempted entry" . ($location ? " at $location" : "") . " but account is inactive.";
    addSecurityNotification($title, $message, 'warning', 'fa-user-lock', '/dashboards/security/index.php');
}

/**
 * Notification for invalid barcode - sent to security
 */
function notifySecurityInvalidBarcode($barcode, $location = null) {
    $title = "Invalid Barcode Detected";
    $truncatedBarcode = strlen($barcode) > 50 ? substr($barcode, 0, 50) : $barcode;
    $message = "Unrecognized barcode '" . $truncatedBarcode . "' scanned" . ($location ? " at $location" : "") . ". This barcode does not exist in the system.";
    addSecurityNotification($title, $message, 'error', 'fa-exclamation-circle', '/dashboards/security/index.php');
}

/**
 * Notification for security schedule end - sent to security staff
 */
function notifySecurityScheduleEnd($securityName, $endTime, $securityId = null) {
    $title = "Your Shift is Ending Soon";
    $message = "$securityName, your scheduled shift ends at $endTime. Please prepare for shift handover.";
    addSecurityNotification($title, $message, 'warning', 'fa-clock', '/dashboards/security/index.php');
}

/**
 * Notification for security shift complete - sent to security staff
 */
function notifySecurityShiftComplete($securityName, $securityId = null) {
    $title = "Shift Complete";
    $message = "$securityName, your shift has ended. Thank you for your service. Please log out.";
    addSecurityNotification($title, $message, 'success', 'fa-check-circle', '/dashboards/security/index.php');
}

?>

