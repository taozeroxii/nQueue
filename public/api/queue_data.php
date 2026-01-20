<?php
header('Content-Type: application/json');
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;

$db = new Database();
$mysql = $db->getMySQL();

if (!$mysql) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$room = $_GET['room'] ?? null;
$limit = $_GET['limit'] ?? 50;

try {
    $where = [];
    $params = [];

    if ($room) {
        $where[] = "room_number = :room";
        $params[':room'] = $room;
    }

    // Logic:
    // User requested: "Show all statuses but only for TODAY"

    // 1. Filter by Date (Today)
    // assuming created_at is datetime or timestamp
    $where[] = "DATE(created_at) = CURDATE()";

    // 2. Remove 'completed' exclusion because user said "Show every status"
    // $where[] = "status != 'completed'"; 

    $sql = "SELECT * FROM queues";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    // Order: 'called' (highest priority to show), then id/created_at
    // Actually, usually we want to see who is waiting in order.
    // 'called' might be currently in room.
    // Order: 'called' (highest priority), then by room, then order, then id
    // Ideally we want to see 'called' ones on top?
    // User requested "orderby room then display_order".
    // If we sort strictly by room, 'called' and 'waiting' for Room 1 will be next to each other.
    // Dashboard logic filters 'called' and 'waiting' separately on JS side.
    // So sorting by room number is fine.
    $sql .= " ORDER BY room_number ASC, display_order ASC, id ASC LIMIT " . (int) $limit;

    $stmt = $mysql->prepare($sql);
    $stmt->execute($params);
    $queues = $stmt->fetchAll();

    // Also get last called for header or sound?
    // Separate query or client side logic.

    echo json_encode(['success' => true, 'data' => $queues]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
