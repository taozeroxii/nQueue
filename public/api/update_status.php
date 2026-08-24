<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\Notifier;
use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['POST']);
ApiSecurity::requireSameOriginForUnsafeMethods();

$db = new Database();
$mysql = $db->getMySQL();

if (!$mysql) {
    ApiSecurity::fail('Database error', 500);
}

// Accept JSON for the new UI and form-encoded POST for legacy Python callers.
$input = $_POST ?: ApiSecurity::readJsonBody();

$id = isset($input['id']) ? ApiSecurity::intValue($input['id'], 'id') : null;
$status = isset($input['status']) ? ApiSecurity::enumValue($input['status'], 'status', ['waiting', 'called', 'completed', 'lab', 'xray', 'not_found']) : null;
$room = isset($input['room']) ? ApiSecurity::stringValue($input['room'], 'room', 20, '/^[0-9A-Za-z_-]+$/') : null; // Optional: If calling "next", we need room, not ID
$action = isset($input['action']) ? ApiSecurity::enumValue($input['action'], 'action', ['call_next', 'call_specific', 'recall']) : null;

try {
    if ($action === 'call_next' && $room) {
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

            ApiSecurity::respond(['success' => true, 'message' => 'Called next patient', 'id' => $next['id']]);
        } else {
            ApiSecurity::respond(['success' => false, 'message' => 'No waiting patients']);
        }
    } elseif ($action === 'call_specific' && $id) {
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

        ApiSecurity::respond(['success' => true, 'message' => 'Called specific patient']);

    } elseif ($action === 'recall' && $room) {
        // Recall Logic: Find currently called patient and re-broadcast
        $sql = "SELECT * FROM queues WHERE room_number = :room AND status = 'called' LIMIT 1";
        $stmt = $mysql->prepare($sql);
        $stmt->execute([':room' => $room]);
        $current = $stmt->fetch();

        if ($current) {
            // Notify WS with specific recall event
            (new \App\Notifier())->notify(['event' => 'recall', 'data' => $current]);
            ApiSecurity::respond(['success' => true, 'message' => 'Recalled', 'data' => $current]);
        } else {
            ApiSecurity::respond(['success' => false, 'message' => 'No active patient to recall']);
        }

    } elseif ($id && $status) {
        // Manual update specific ID
        // Determine timestamp column to update
        $timestampUpdate = "";
        if ($status === 'xray') {
            $timestampUpdate = ", xray_at = NOW()";
        } elseif ($status === 'lab') {
            $timestampUpdate = ", lab_at = NOW()";
        } elseif ($status === 'not_found') {
            $timestampUpdate = ""; // ใช้ updated_at อัตโนมัติ
        } elseif ($status === 'called') {
            $timestampUpdate = ", call_at = NOW()";
        }

        $timestampUpdates = [
            'xray' => ', xray_at = NOW()',
            'lab' => ', lab_at = NOW()',
            'called' => ', call_at = NOW()',
            'waiting' => '',
            'completed' => '',
            'not_found' => '',
        ];
        $timestampUpdate = $timestampUpdates[$status] ?? '';

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

        ApiSecurity::respond(['success' => true, 'message' => 'Status updated']);
    } else {
        throw new Exception('Invalid parameters');
    }

} catch (Exception $e) {
    ApiSecurity::fail('Status update failed', 500, $e);
}
