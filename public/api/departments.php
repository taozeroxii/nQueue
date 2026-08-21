<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['GET']);

$db = new Database();
$mysql = $db->getMySQL();

try {
    $stmt = $mysql->query("SELECT DISTINCT department FROM rooms WHERE department IS NOT NULL");
    $depts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    ApiSecurity::respond(['success' => true, 'data' => $depts]);
} catch (Exception $e) {
    ApiSecurity::fail('Departments failed', 500, $e);
}
