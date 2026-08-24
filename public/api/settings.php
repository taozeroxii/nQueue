<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['GET', 'POST']);
ApiSecurity::requireSameOriginForUnsafeMethods();

$db = new Database();
$mysql = $db->getMySQL();

if (!$mysql) {
    ApiSecurity::fail('Database error', 500);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $mysql->query("SELECT key_name, key_value FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        ApiSecurity::respond(['success' => true, 'data' => $settings]);
    } catch (Exception $e) {
        ApiSecurity::fail('Settings failed', 500, $e);
    }
} elseif ($method === 'POST') {
    // Accept JSON and form-encoded POST for backward compatibility with local tools.
    $input = $_POST ?: ApiSecurity::readJsonBody();

    try {
        $stmt = $mysql->prepare("INSERT INTO settings (key_name, key_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE key_value = :val");

        if (isset($input['dept_name'])) {
            $deptName = trim((string) $input['dept_name']);
            if (strlen($deptName) > 150) {
                ApiSecurity::fail('Invalid dept_name', 400);
            }
            $stmt->execute([':key' => 'dept_name', ':val' => $deptName]);
        }
        if (isset($input['dept_sub'])) {
            $deptSub = trim((string) $input['dept_sub']);
            if (strlen($deptSub) > 150) {
                ApiSecurity::fail('Invalid dept_sub', 400);
            }
            $stmt->execute([':key' => 'dept_sub', ':val' => $deptSub]);
        }

        ApiSecurity::respond(['success' => true, 'message' => 'Settings saved']);
    } catch (Exception $e) {
        ApiSecurity::fail('Settings save failed', 500, $e);
    }
}
