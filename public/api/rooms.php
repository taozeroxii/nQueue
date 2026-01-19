<?php
header('Content-Type: application/json');
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;

$db = new Database();
$mysql = $db->getMySQL();

$dept = $_GET['department'] ?? null;

try {
    $sql = "SELECT id, room_name, department, description FROM rooms";
    $params = [];

    if ($dept) {
        $sql .= " WHERE department = :dept";
        $params[':dept'] = $dept;
    }

    $sql .= " ORDER BY room_name ASC";

    $stmt = $mysql->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rooms]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
