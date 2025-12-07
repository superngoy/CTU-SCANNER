<?php
/**
 * Security Personnel Status API
 * Returns all security staff with their current duty status and schedule info
 */
session_start();

// Authentication check for admin only
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/database.php';
require_once '../../includes/notification_helpers.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }
    
    // Get all active security personnel
    $query = "SELECT SecurityID, SecurityFName, SecurityLName, TimeSched, image FROM security WHERE isActive = 1 ORDER BY SecurityFName ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $security_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $personnel_list = [];
    
    foreach ($security_staff as $person) {
        $status = calculateSecurityStatus($person['TimeSched']);
        
        $personnel_list[] = [
            'id' => $person['SecurityID'],
            'name' => $person['SecurityFName'] . ' ' . $person['SecurityLName'],
            'first_name' => $person['SecurityFName'],
            'last_name' => $person['SecurityLName'],
            'schedule' => $person['TimeSched'],
            'image' => $person['image'] ?? null,
            'on_duty' => $status['on_duty'],
            'status_text' => $status['status_text'],
            'status_class' => $status['status_class'],
            'minutes_until_end' => $status['minutes_until_end'],
            'shift_start' => $status['shift_start'],
            'shift_end' => $status['shift_end'],
            'shift_complete' => $status['shift_complete']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'personnel' => $personnel_list,
        'total' => count($personnel_list),
        'on_duty_count' => count(array_filter($personnel_list, fn($p) => $p['on_duty'])),
        'off_duty_count' => count(array_filter($personnel_list, fn($p) => !$p['on_duty']))
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Calculate security personnel status based on their schedule
 */
function calculateSecurityStatus($schedule) {
    $current_time = new DateTime();
    $shift_info = parseScheduleHelper($schedule);
    
    if (!$shift_info) {
        return [
            'on_duty' => false,
            'status_text' => 'Invalid Schedule',
            'status_class' => 'status-error',
            'minutes_until_end' => 0,
            'shift_start' => 'N/A',
            'shift_end' => 'N/A',
            'shift_complete' => false
        ];
    }
    
    $shift_start = clone $current_time;
    $shift_start->setTime($shift_info['start_hour'], $shift_info['start_minute'], 0);
    
    $shift_end = clone $current_time;
    $shift_end->setTime($shift_info['end_hour'], $shift_info['end_minute'], 0);
    
    // Determine if we're in the shift that started today or yesterday
    if ($current_time < $shift_start) {
        $shift_start->modify('-1 day');
        $shift_end->modify('-1 day');
        
        if ($current_time > $shift_end) {
            $shift_start->modify('+1 day');
            $shift_end->modify('+1 day');
        }
    } elseif ($current_time > $shift_end) {
        $shift_start->modify('+1 day');
        $shift_end->modify('+1 day');
    }
    
    $is_on_duty = ($current_time >= $shift_start && $current_time < $shift_end);
    
    // Calculate time until shift end
    $interval = $current_time->diff($shift_end);
    $minutes_until_end = $interval->h * 60 + $interval->i;
    
    if ($current_time > $shift_end && !$is_on_duty) {
        $minutes_until_end = -(($interval->h * 60) + $interval->i);
    }
    
    // Determine status text and class
    $status_text = 'Off Duty';
    $status_class = 'status-off-duty';
    $shift_complete = false;
    
    if ($is_on_duty) {
        if ($minutes_until_end <= 15 && $minutes_until_end > 0) {
            $status_text = 'On Duty - Shift Ending Soon (' . $minutes_until_end . 'm)';
            $status_class = 'status-ending-soon';
        } else {
            $status_text = 'On Duty';
            $status_class = 'status-on-duty';
        }
    } else if ($current_time < $shift_start) {
        $time_until_start = $shift_start->diff($current_time);
        $hours_until = $time_until_start->h;
        $mins_until = $time_until_start->i;
        
        if ($hours_until === 0 && $mins_until < 30) {
            $status_text = 'Starting Soon (' . $mins_until . 'm)';
            $status_class = 'status-starting-soon';
        } else {
            $status_text = 'Off Duty - Next shift: ' . $shift_start->format('g:i A');
            $status_class = 'status-off-duty';
        }
    } else {
        $status_text = 'Shift Complete';
        $status_class = 'status-complete';
        $shift_complete = true;
    }
    
    return [
        'on_duty' => $is_on_duty,
        'status_text' => $status_text,
        'status_class' => $status_class,
        'minutes_until_end' => $minutes_until_end,
        'shift_start' => $shift_start->format('g:i A'),
        'shift_end' => $shift_end->format('g:i A'),
        'shift_complete' => $shift_complete
    ];
}

/**
 * Parse schedule string helper
 */
function parseScheduleHelper($schedule) {
    $schedule = trim($schedule);
    $schedule = preg_replace('/\s+/', ' ', $schedule);
    $parts = preg_split('/[-–]/', $schedule);
    
    if (count($parts) !== 2) {
        return null;
    }
    
    $start_str = trim($parts[0]);
    $end_str = trim($parts[1]);
    
    $start_time = parseTimeStringHelper($start_str);
    $end_time = parseTimeStringHelper($end_str);
    
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
 * Parse individual time string helper
 */
function parseTimeStringHelper($time_str) {
    $time_str = trim($time_str);
    $time_str = strtoupper($time_str);
    
    if (preg_match('/^(\d{1,2}):?(\d{2})?\s*(AM|PM)?$/', $time_str, $matches)) {
        $hour = (int)$matches[1];
        $minute = isset($matches[2]) ? (int)$matches[2] : 0;
        $period = $matches[3] ?? null;
        
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

?>
