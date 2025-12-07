<?php
/**
 * Schedule End Checker API for Security Personnel
 * Checks if security staff's shift is ending soon and creates notifications
 */
session_start();

// Authentication check for security staff only
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';
require_once '../includes/notification_helpers.php';

header('Content-Type: application/json');

$security_id = $_SESSION['security_id'];
$minutes_before_end = $_GET['minutes_before'] ?? 15; // Notify 15 minutes before shift end by default

try {
    // Initialize database connection
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }
    
    // Get current security staff info
    $query = "SELECT SecurityID, SecurityFName, SecurityLName, TimeSched FROM security WHERE SecurityID = ? AND isActive = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([$security_id]);
    $security = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$security) {
        echo json_encode(['error' => 'Security staff not found']);
        exit();
    }
    
    $full_name = $security['SecurityFName'] . ' ' . $security['SecurityLName'];
    $time_sched = $security['TimeSched'];
    
    // Parse the time schedule to extract end time
    // Formats supported: "6AM-6PM", "10:00am - 10:00pm", "6:00 AM - 6:00 PM"
    $shift_info = parseSchedule($time_sched);
    
    if (!$shift_info) {
        echo json_encode([
            'success' => false,
            'message' => 'Could not parse schedule format',
            'schedule' => $time_sched
        ]);
        exit();
    }
    
    $current_time = new DateTime();
    $shift_start = clone $current_time;
    $shift_start->setTime($shift_info['start_hour'], $shift_info['start_minute'], 0);
    
    $shift_end = clone $current_time;
    $shift_end->setTime($shift_info['end_hour'], $shift_info['end_minute'], 0);
    
    // Determine if we're in the shift that started today or yesterday
    // If current time < start time, shift started yesterday
    if ($current_time < $shift_start) {
        $shift_start->modify('-1 day');
        $shift_end->modify('-1 day');
        
        // If shift_end is still in the past (e.g., shift was midnight to 6AM and it's now 10AM)
        if ($current_time > $shift_end) {
            // Today's shift hasn't started yet, so calculate for today's end time
            $shift_start->modify('+1 day');
            $shift_end->modify('+1 day');
        }
    } elseif ($current_time > $shift_end) {
        // If current time is after shift end time on the same day,
        // the next shift ends tomorrow
        $shift_start->modify('+1 day');
        $shift_end->modify('+1 day');
    }
    
    $is_shift_active = ($current_time >= $shift_start && $current_time < $shift_end);
    
    // Calculate time until shift end
    $interval = $current_time->diff($shift_end);
    $minutes_until_end = $interval->h * 60 + $interval->i;
    
    // If shift has already ended, minutes will be negative
    if ($current_time > $shift_end && $is_shift_active === false) {
        $minutes_until_end = -(($interval->h * 60) + $interval->i);
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'is_shift_active' => $is_shift_active,
        'current_time' => $current_time->format('Y-m-d H:i:s'),
        'shift_start' => $shift_start->format('Y-m-d H:i:s'),
        'shift_end' => $shift_end->format('Y-m-d H:i:s'),
        'minutes_until_end' => $minutes_until_end,
        'schedule' => $time_sched,
        'shift_ending_soon' => false,
        'shift_ended' => false
    ];
    
    // Check if shift is ending (within notification window)
    if ($is_shift_active && $minutes_until_end <= $minutes_before_end && $minutes_until_end > 0) {
        $response['shift_ending_soon'] = true;
        $response['message'] = "Your shift is ending in $minutes_until_end minutes";
        
        // Create notification only if it hasn't been created already
        $last_notification = getLastShiftEndNotification($security_id);
        if (!$last_notification || strtotime($last_notification) < strtotime('-30 minutes')) {
            notifySecurityScheduleEnd($full_name, $shift_end->format('H:i'));
            $response['notification_created'] = true;
        } else {
            $response['notification_created'] = false;
            $response['reason'] = 'Notification already created recently';
        }
    }
    
    // Check if shift has ended
    if ($is_shift_active === false && $current_time >= $shift_end) {
        $response['shift_ended'] = true;
        $response['message'] = "Your shift has ended";
        
        // Create shift complete notification
        $last_complete_notification = getLastShiftCompleteNotification($security_id);
        if (!$last_complete_notification || strtotime($last_complete_notification) < strtotime('-1 hour')) {
            notifySecurityShiftComplete($full_name);
            $response['notification_created'] = true;
        } else {
            $response['notification_created'] = false;
            $response['reason'] = 'Shift complete notification already created recently';
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Parse schedule string and extract start/end times
 * Supports formats: "6AM-6PM", "10:00am - 10:00pm", "6:00 AM - 6:00 PM"
 */
function parseSchedule($schedule) {
    $schedule = trim($schedule);
    
    // Remove extra spaces
    $schedule = preg_replace('/\s+/', ' ', $schedule);
    
    // Split by dash/hyphen
    $parts = preg_split('/[-–]/', $schedule);
    if (count($parts) !== 2) {
        return null;
    }
    
    $start_str = trim($parts[0]);
    $end_str = trim($parts[1]);
    
    $start_time = parseTimeString($start_str);
    $end_time = parseTimeString($end_str);
    
    if (!$start_time || !$end_time) {
        return null;
    }
    
    return [
        'start_hour' => $start_time['hour'],
        'start_minute' => $start_time['minute'],
        'end_hour' => $end_time['hour'],
        'end_minute' => $end_time['minute']
    ];
}

/**
 * Parse individual time string
 * Supports: "6AM", "6 AM", "06:00 AM", "6:00AM", "06:00", "6:00am"
 */
function parseTimeString($time_str) {
    $time_str = trim($time_str);
    $time_str = strtoupper($time_str);
    
    // Regex to match various time formats
    if (preg_match('/^(\d{1,2}):?(\d{2})?\s*(AM|PM)?$/', $time_str, $matches)) {
        $hour = (int)$matches[1];
        $minute = isset($matches[2]) ? (int)$matches[2] : 0;
        $period = $matches[3] ?? null;
        
        // Handle 12-hour format
        if ($period === 'PM' && $hour !== 12) {
            $hour += 12;
        } elseif ($period === 'AM' && $hour === 12) {
            $hour = 0;
        }
        
        return [
            'hour' => $hour,
            'minute' => $minute
        ];
    }
    
    return null;
}

/**
 * Get timestamp of last shift end notification for this security staff
 */
function getLastShiftEndNotification($security_id) {
    $notifications = loadSecurityNotifications();
    
    foreach ($notifications as $notif) {
        if (strpos($notif['title'] ?? '', 'Shift is Ending Soon') !== false) {
            return $notif['created_at'] ?? null;
        }
    }
    
    return null;
}

/**
 * Get timestamp of last shift complete notification for this security staff
 */
function getLastShiftCompleteNotification($security_id) {
    $notifications = loadSecurityNotifications();
    
    foreach ($notifications as $notif) {
        if (strpos($notif['title'] ?? '', 'Shift Complete') !== false) {
            return $notif['created_at'] ?? null;
        }
    }
    
    return null;
}

?>
