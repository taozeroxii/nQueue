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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $mysql->query("SELECT key_name, key_value FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        echo json_encode(['success' => true, 'data' => $settings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    try {
        $stmt = $mysql->prepare("INSERT INTO settings (key_name, key_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE key_value = :val");

        if (isset($input['dept_name'])) {
            $stmt->execute([':key' => 'dept_name', ':val' => $input['dept_name']]);
        }
        if (isset($input['dept_sub'])) {
            $stmt->execute([':key' => 'dept_sub', ':val' => $input['dept_sub']]);
        }

        echo json_encode(['success' => true, 'message' => 'Settings saved']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
