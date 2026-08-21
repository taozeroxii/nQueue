<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\Notifier;
use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['GET', 'POST']);
ApiSecurity::requireSameOriginForUnsafeMethods();

try {
    $db = new Database();
    $mysql = $db->getMySQL();
    $pgsql = $db->getPgSQL();

    if (!$mysql) {
        throw new Exception('MySQL connection failed');
    }

    $input = $_POST ?: $_GET;
    if (!$input && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $input = ApiSecurity::readJsonBody();
    }

    $vn = ApiSecurity::optionalStringValue($input['vn'] ?? null, 'vn', 50, '/^[0-9A-Za-z_-]+$/');
    $room = ApiSecurity::optionalStringValue($input['room'] ?? null, 'room', 20, '/^[0-9A-Za-z_-]+$/');

    if (!$vn || !$room) {
        throw new Exception('Missing VN or Room Number');
    }

    // 1. Check if scan already exists in queue for this room (Prevent Duplicates)
    $checkSql = "SELECT id FROM queues WHERE vn = :vn AND room_number = :room AND status IN ('waiting', 'called') AND DATE(created_at) = CURDATE() LIMIT 1";
    $stm = $mysql->prepare($checkSql);
    $stm->execute([':vn' => $vn, ':room' => $room]);
    if ($stm->fetch()) {
        throw new Exception("Patient already in queue for Room $room");
    }

    // 2. Fetch from PostgreSQL
    $patientData = null;
    if ($pgsql) {
        // Fetch raw columns to handle concatenation and encoding in PHP (safer)
        $sql = "SELECT ov.oqueue, pt.pname, pt.fname, pt.lname, ov.hn
                FROM ovst ov 
                LEFT JOIN patient pt on pt.hn = ov.hn
                WHERE ov.vn = :vn";

        try {
            $stmt = $pgsql->prepare($sql);
            $stmt->execute([':vn' => $vn]);
            $row = $stmt->fetch();

            if ($row) {
                // Convert Encoding (TIS-620 -> UTF-8) & Build Name
                foreach ($row as $key => $val) {
                    // Check only if string
                    if (is_string($val)) {
                        // iconv can fail with notices if chars are invalid, use @/IGNORE
                        $utf8 = @iconv('TIS-620', 'UTF-8//IGNORE', $val);
                        if ($utf8 !== false) {
                            $row[$key] = $utf8;
                        }
                    }
                }

                $patientData = [
                    'oqueue' => $row['oqueue'],
                    'patient_name' => trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')),
                    'hn' => $row['hn']
                ];
            }
        } catch (PDOException $e) {
            throw new Exception("PostgreSQL Error: " . $e->getMessage());
        }
    }

    // fallback or mock if PG invalid/empty (for dev purpose if PG not connected)
    if (!$patientData) {
        if ($pgsql) {
            throw new Exception("Patient not found for VN: $vn");
        } else {
            // Mock for dev mode if PG not connected
            $patientData = [
                'oqueue' => '000',
                'patient_name' => 'Demo Patient',
                'hn' => '000000'
            ];
        }
    }

    // 3. Insert into MySQL with display_order logic
    // We'll set display_order = 0 initially, then update it to ID to ensure correct default sorting by insertion
    $insertSql = "INSERT INTO queues (vn, hn, patient_name, oqueue, room_number, status, display_order) 
                  VALUES (:vn, :hn, :patient_name, :oqueue, :room_number, 'waiting', 0)";

    $stmt = $mysql->prepare($insertSql);
    $stmt->execute([
        ':vn' => $vn,
        ':hn' => $patientData['hn'],
        ':patient_name' => $patientData['patient_name'],
        ':oqueue' => $patientData['oqueue'],
        ':room_number' => $room
    ]);

    $newId = $mysql->lastInsertId();
    // Set display_order = id
    $orderStmt = $mysql->prepare("UPDATE queues SET display_order = :display_order WHERE id = :id");
    $orderStmt->execute([':display_order' => $newId, ':id' => $newId]);

    // Notify WS
    (new \App\Notifier())->notify(['event' => 'queue_update', 'room' => $room]);

    ApiSecurity::respond(['success' => true, 'message' => 'Queue added', 'id' => $newId, 'data' => $patientData]);

} catch (Exception $e) {
    ApiSecurity::fail('Scan failed', 400, $e);
}
