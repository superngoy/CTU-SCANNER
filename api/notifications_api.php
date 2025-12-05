<?php
session_start();

// Authentication check
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Include notification helpers for file-based storage functions
require_once '../includes/notification_helpers.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$admin_id = $_SESSION['admin_id'];

try {
    switch ($action) {
        case 'get_unread':
            // Get count of unread notifications
            $notifications = loadNotifications();
            $unread_count = 0;
            foreach ($notifications as $notif) {
                if (!isset($notif['is_read']) || !$notif['is_read']) {
                    $unread_count++;
                }
            }
            echo json_encode([
                'success' => true,
                'unread_count' => $unread_count
            ]);
            break;

        case 'get_all':
            // Get all notifications
            $limit = $_GET['limit'] ?? 20;
            $notifications = loadNotifications();
            $notifications = array_slice($notifications, 0, $limit);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'total' => count(loadNotifications())
            ]);
            break;

        case 'mark_read':
            // Mark notification as read
            $notif_id = $_GET['notification_id'] ?? null;
            
            if (!$notif_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Notification ID required']);
                exit();
            }

            $notifications = loadNotifications();
            $found = false;
            
            foreach ($notifications as &$notif) {
                if ($notif['id'] === $notif_id) {
                    $notif['is_read'] = true;
                    $notif['read_at'] = date('Y-m-d H:i:s');
                    $found = true;
                    break;
                }
            }

            if ($found) {
                saveNotifications($notifications);
            }

            echo json_encode(['success' => $found]);
            break;

        case 'mark_all_read':
            // Mark all notifications as read
            $notifications = loadNotifications();
            
            foreach ($notifications as &$notif) {
                if (!isset($notif['is_read']) || !$notif['is_read']) {
                    $notif['is_read'] = true;
                    $notif['read_at'] = date('Y-m-d H:i:s');
                }
            }

            saveNotifications($notifications);
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            // Delete specific notification
            $notif_id = $_GET['notification_id'] ?? null;
            
            if (!$notif_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Notification ID required']);
                exit();
            }

            $notifications = loadNotifications();
            $notifications = array_filter(
                $notifications,
                function ($notif) use ($notif_id) {
                    return $notif['id'] !== $notif_id;
                }
            );

            // Re-index array
            $notifications = array_values($notifications);
            saveNotifications($notifications);

            echo json_encode(['success' => true]);
            break;

        case 'clear_all':
            // Clear all notifications
            saveNotifications([]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>
