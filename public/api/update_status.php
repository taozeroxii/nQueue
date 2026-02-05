<?php
header('Content-Type: application/json');
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\Notifier;

$db = new Database();
$mysql = $db->getMySQL();

if (!$mysql) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

// Input: JSON or POST? JSON is cleaner for Python requests
$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;
$status = $input['status'] ?? null; // 'called', 'completed'
$room = $input['room'] ?? null; // Optional: If calling "next", we need room, not ID

try {
    if ($input && isset($input['action']) && $input['action'] === 'call_next' && $room) {
        // Logic to call next waiting patient for a room
        // 1. Check if there is already a 'called' patient? Maybe complete them automatically?
        // Let's Auto-complete existing 'called' for this room
        $completeSql = "UPDATE queues SET status = 'completed' WHERE room_number = :room AND status = 'called'";
        $cStmt = $mysql->prepare($completeSql);
        $cStmt->execute([':room' => $room]);

        // 2. Find next waiting
        $nextSql = "SELECT id FROM queues WHERE room_number = :room AND status = 'waiting' AND DATE(created_at) = CURDATE() ORDER BY id ASC LIMIT 1";
        $nStmt = $mysql->prepare($nextSql);
        $nStmt->execute([':room' => $room]);
        $next = $nStmt->fetch();

        if ($next) {
            $updateSql = "UPDATE queues SET status = 'called', call_at = NOW() WHERE id = :id";
            $uStmt = $mysql->prepare($updateSql);
            $uStmt->execute([':id' => $next['id']]);

            // Notify WS
            (new \App\Notifier())->notify(['event' => 'queue_update', 'room' => $room]);

            echo json_encode(['success' => true, 'message' => 'Called next patient', 'id' => $next['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No waiting patients']);
        }
    } elseif ($input && isset($input['action']) && $input['action'] === 'call_specific' && $id) {
        // CALL SPECIFIC ID (Recall from Lab/Xray or Ticket List)
        // 1. Complete currently called for this room (if any)
        if ($room) {
            $completeSql = "UPDATE queues SET status = 'completed' WHERE room_number = :room AND status = 'called'";
            $cStmt = $mysql->prepare($completeSql);
            $cStmt->execute([':room' => $room]);
        } else {
            // If room not provided, we might fail to auto-complete the previous one. 
            // Ideally client sends room.
        }

        // 2. Set specific ID to called
        $updateSql = "UPDATE queues SET status = 'called', call_at = NOW() WHERE id = :id";
        $uStmt = $mysql->prepare($updateSql);
        $uStmt->execute([':id' => $id]);

        // 3. Notify
        // Fetch room if missing
        if (!$room) {
            $rStmt = $mysql->prepare("SELECT room_number FROM queues WHERE id = :id");
            $rStmt->execute([':id' => $id]);
            $rData = $rStmt->fetch();
            $room = $rData ? $rData['room_number'] : null;
        }

        if ($room) {
            (new \App\Notifier())->notify(['event' => 'queue_update', 'room' => $room]);
            // Also trigger sound? The dashboard monitors 'called' status change.
        }

        echo json_encode(['success' => true, 'message' => 'Called specific patient']);

    } elseif ($input && isset($input['action']) && $input['action'] === 'recall' && $room) {
        // Recall Logic: Find currently called patient and re-broadcast
        $sql = "SELECT * FROM queues WHERE room_number = :room AND status = 'called' LIMIT 1";
        $stmt = $mysql->prepare($sql);
        $stmt->execute([':room' => $room]);
        $current = $stmt->fetch();

        if ($current) {
            // Notify WS with specific recall event
            (new \App\Notifier())->notify(['event' => 'recall', 'data' => $current]);
            echo json_encode(['success' => true, 'message' => 'Recalled', 'data' => $current]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No active patient to recall']);
        }

    } elseif ($id && $status) {
        // Manual update specific ID
        // Determine timestamp column to update
        $timestampUpdate = "";
        if ($status === 'xray') {
            $timestampUpdate = ", xray_at = NOW()";
        } elseif ($status === 'lab') {
            $timestampUpdate = ", lab_at = NOW()";
        } elseif ($status === 'called') {
            $timestampUpdate = ", call_at = NOW()";
        }

        $sql = "UPDATE queues SET status = :status $timestampUpdate WHERE id = :id";
        $stmt = $mysql->prepare($sql);
        $stmt->execute([':status' => $status, ':id' => $id]);

        // Notify WS to refresh lists
        // Need to know room? We don't have it in input usually for direct ID updates.
        // Ideally we fetch it or client sends it.
        // For now, broadcast generic or fetch room from DB.
        // Fetch room to be safe:
        $rStmt = $mysql->prepare("SELECT room_number FROM queues WHERE id = :id");
        $rStmt->execute([':id' => $id]);
        $rParams = $rStmt->fetch();
        if ($rParams) {
            (new \App\Notifier())->notify(['event' => 'queue_update', 'room' => $rParams['room_number']]);
        }

        echo json_encode(['success' => true, 'message' => 'Status updated']);
    } else {
        throw new Exception('Invalid parameters');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
