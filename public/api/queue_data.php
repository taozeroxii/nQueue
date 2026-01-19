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
    // Show 'called' first (maybe valid for few minutes?), then 'waiting'.
    // Or just all non-completed?
    // User requested: 1 screen all, 1 screen separated.
    // Let's filter out 'completed' unless requested.
    $where[] = "status != 'completed'";

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
