<?php
header('Content-Type: application/json');
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;

$db = new Database();
$mysql = $db->getMySQL();

try {
    $stmt = $mysql->query("SELECT DISTINCT department FROM rooms WHERE department IS NOT NULL");
    $depts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'data' => $depts]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
