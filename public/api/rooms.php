<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['GET']);

$db = new Database();
$mysql = $db->getMySQL();

$dept = ApiSecurity::optionalStringValue($_GET['department'] ?? null, 'department', 100);

try {
    $sql = "SELECT id, room_name, room_number, department, description FROM rooms";
    $params = [];

    if ($dept) {
        $sql .= " WHERE department = :dept";
        $params[':dept'] = $dept;
    }

    $sql .= " ORDER BY room_name ASC";

    $stmt = $mysql->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ApiSecurity::respond(['success' => true, 'data' => $rooms]);
} catch (Exception $e) {
    ApiSecurity::fail('Rooms failed', 500, $e);
}
