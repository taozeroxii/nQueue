<?php
header('Content-Type: application/json');
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\Notifier;

try {
    $db = new Database();
    $mysql = $db->getMySQL();

    if (!$mysql) {
        throw new Exception('Database error');
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;

    if ($method === 'GET') {
        // LIST Queues (Ordered by display_order)
        // Optional: filter by room, dept
        $room = $_GET['room'] ?? null;
        $sql = "SELECT id, vn, patient_name, room_number, status, oqueue, display_order FROM queues WHERE status = 'waiting' AND DATE(created_at) = CURDATE()";
        $params = [];

        if ($room) {
            $sql .= " AND room_number = :room";
            $params[':room'] = $room;
        }

        $sql .= " ORDER BY display_order ASC"; // Critical: Order by display_order

        $stmt = $mysql->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $act = $input['action'] ?? '';

        if ($act === 'delete' && isset($input['id'])) {
            // DELETE
            $stmt = $mysql->prepare("DELETE FROM queues WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            echo json_encode(['success' => true, 'message' => 'Deleted']);

            // Notify?
            // (new Notifier())->notify(['event' => 'queue_update']); // Generic update

        } elseif ($act === 'move' && isset($input['id']) && isset($input['direction'])) {
            // MOVE UP/DOWN
            // 1. Find Current Item
            $id = $input['id'];
            $dir = $input['direction']; // 'up' or 'down'

            $currStmt = $mysql->prepare("SELECT id, display_order, room_number FROM queues WHERE id = :id");
            $currStmt->execute([':id' => $id]);
            $current = $currStmt->fetch(PDO::FETCH_ASSOC);

            if (!$current)
                throw new Exception('Item not found');

            $currOrder = $current['display_order'];
            $room = $current['room_number'];

            // 2. Find Neighbor
            if ($dir === 'up') {
                // Find item with LOWER display_order (closest)
                $neighborSql = "SELECT id, display_order FROM queues WHERE room_number = :room AND status='waiting' AND display_order < :ord ORDER BY display_order DESC LIMIT 1";
            } else {
                // Find item with HIGHER display_order (closest)
                $neighborSql = "SELECT id, display_order FROM queues WHERE room_number = :room AND status='waiting' AND display_order > :ord ORDER BY display_order ASC LIMIT 1";
            }

            $nStmt = $mysql->prepare($neighborSql);
            $nStmt->execute([':room' => $room, ':ord' => $currOrder]);
            $neighbor = $nStmt->fetch(PDO::FETCH_ASSOC);

            if ($neighbor) {
                // Swap display_order
                $nOrder = $neighbor['display_order'];
                $nId = $neighbor['id'];

                $mysql->beginTransaction();

                // Swap logic
                $u1 = $mysql->prepare("UPDATE queues SET display_order = :ord WHERE id = :id");
                $u1->execute([':ord' => $nOrder, ':id' => $id]);

                $u2 = $mysql->prepare("UPDATE queues SET display_order = :ord WHERE id = :id");
                $u2->execute([':ord' => $currOrder, ':id' => $nId]);

                $mysql->commit();

                echo json_encode(['success' => true, 'message' => 'Moved']);
                (new Notifier())->notify(['event' => 'queue_update', 'room' => $room]);

            } else {
                echo json_encode(['success' => false, 'message' => 'Cannot move further']);
            }

        } else {
            throw new Exception('Invalid action');
        }
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
