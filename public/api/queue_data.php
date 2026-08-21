<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['GET']);

$db = new Database();
$mysql = $db->getMySQL();

if (!$mysql) {
    ApiSecurity::fail('Database error', 500);
}

$room = ApiSecurity::optionalStringValue($_GET['room'] ?? null, 'room', 20, '/^[0-9A-Za-z_-]+$/');
$limit = ApiSecurity::intValue($_GET['limit'] ?? 50, 'limit', 1, 200);
$department = ApiSecurity::optionalStringValue($_GET['department'] ?? null, 'department', 100);

try {
    $where = [];
    $params = [];

    if ($room) {
        $where[] = "room_number = :room";
        $params[':room'] = $room;
    }

    if ($department) {
        $where[] = "room_number IN (SELECT id FROM rooms WHERE department = :department)";
        $params[':department'] = $department;
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
    $sql .= " ORDER BY room_number ASC, display_order ASC, id ASC LIMIT " . $limit;

    $stmt = $mysql->prepare($sql);
    $stmt->execute($params);
    $queues = $stmt->fetchAll();

    // Also get last called for header or sound?
    // Separate query or client side logic.

    ApiSecurity::respond(['success' => true, 'data' => $queues]);

} catch (Exception $e) {
    ApiSecurity::fail('Queue data failed', 500, $e);
}
