<?php
header('Content-Type: application/json');
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;
use App\Notifier;

try {
    $db = new Database();
    $mysql = $db->getMySQL();
    $pgsql = $db->getPgSQL();

    if (!$mysql) {
        throw new Exception('MySQL connection failed');
    }

    $vn = $_GET['vn'] ?? $_POST['vn'] ?? null;
    $room = $_GET['room'] ?? $_POST['room'] ?? null;

    if (!$vn || !$room) {
        throw new Exception('Missing VN or Room Number');
    }

    // 1. Check if scan already exists in queue for this room (Prevent Duplicates)
    $checkSql = "SELECT id FROM queues WHERE vn = :vn AND room_number = :room AND status IN ('waiting', 'called') LIMIT 1";
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
                    'patient_name' => trim(($row['pname'] ?? '') . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')),
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
    $mysql->query("UPDATE queues SET display_order = $newId WHERE id = $newId");


    echo json_encode(['success' => true, 'message' => 'Queue added', 'id' => $mysql->lastInsertId(), 'data' => $patientData]);

    // Notify WS
    (new \App\Notifier())->notify(['event' => 'queue_update', 'room' => $room]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
