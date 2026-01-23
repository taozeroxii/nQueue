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

    // [CHANGE 1] รับค่า oqueue แทน vn
    $oqueue = $_GET['oqueue'] ?? $_POST['oqueue'] ?? null;
    $room = $_GET['room'] ?? $_POST['room'] ?? null;

    if (!$oqueue || !$room) {
        throw new Exception('Missing Queue Number (oqueue) or Room Number');
    }

    // [CHANGE 3] Check duplicates in MySQL using oqueue instead of vn
    // ตรวจสอบว่าคิวนี้มีอยู่ในห้องนี้แล้วหรือยัง
    $checkSql = "SELECT id FROM queues WHERE oqueue = :oqueue AND room_number = :room AND status IN ('waiting', 'called') LIMIT 1";
    $stm = $mysql->prepare($checkSql);
    $stm->execute([':oqueue' => $oqueue, ':room' => $room]);
    if ($stm->fetch()) {
        throw new Exception("Queue $oqueue is already in queue for Room $room");
    }

    // 2. Fetch from PostgreSQL
    $patientData = null;
    if ($pgsql) {
        // [CHANGE 2] Query using oqueue and CURRENT_DATE
        // ต้อง Select ov.vn ออกมาด้วย เพื่อเอาไปใช้ insert ลง MySQL
        $sql = "SELECT ov.vn, ov.oqueue, pt.pname, pt.fname, pt.lname, ov.hn
                FROM ovst ov 
                LEFT JOIN patient pt on pt.hn = ov.hn
                WHERE ov.oqueue = :oqueue 
                AND ov.vstdate = CURRENT_DATE -- ตรวจสอบเฉพาะวันที่ปัจจุบัน
                LIMIT 1";

        try {
            $stmt = $pgsql->prepare($sql);
            $stmt->execute([':oqueue' => $oqueue]);
            $row = $stmt->fetch();

            if ($row) {
                // Convert Encoding (TIS-620 -> UTF-8) & Build Name
                foreach ($row as $key => $val) {
                    if (is_string($val)) {
                        $utf8 = @iconv('TIS-620', 'UTF-8//IGNORE', $val);
                        if ($utf8 !== false) {
                            $row[$key] = $utf8;
                        }
                    }
                }

                $patientData = [
                    'vn' => $row['vn'], // รับ vn มาจาก Database
                    'oqueue' => $row['oqueue'],
                    'patient_name' => trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')),
                    'hn' => $row['hn']
                ];
            }
        } catch (PDOException $e) {
            throw new Exception("PostgreSQL Error: " . $e->getMessage());
        }
    }

    // Check if patient found
    if (!$patientData) {
        if ($pgsql) {
            throw new Exception("Patient not found for Queue: $oqueue today.");
        } else {
            // Mock for dev mode if PG not connected
            $patientData = [
                'vn' => 'MOCK_VN_' . time(),
                'oqueue' => $oqueue,
                'patient_name' => 'Demo Patient',
                'hn' => '000000'
            ];
        }
    }

    // 3. Insert into MySQL
    // ใช้ vn ที่ได้จาก $patientData['vn']
    $insertSql = "INSERT INTO queues (vn, hn, patient_name, oqueue, room_number, status, display_order) 
                  VALUES (:vn, :hn, :patient_name, :oqueue, :room_number, 'waiting', 0)";

    $stmt = $mysql->prepare($insertSql);
    $stmt->execute([
        ':vn' => $patientData['vn'], // ใช้ค่าที่ query มาได้
        ':hn' => $patientData['hn'],
        ':patient_name' => $patientData['patient_name'],
        ':oqueue' => $patientData['oqueue'],
        ':room_number' => $room
    ]);

    $newId = $mysql->lastInsertId();
    // Set display_order = id
    $mysql->query("UPDATE queues SET display_order = $newId WHERE id = $newId");

    echo json_encode(['success' => true, 'message' => 'Queue added', 'id' => $newId, 'data' => $patientData]);

    // Notify WS
    (new \App\Notifier())->notify(['event' => 'queue_update', 'room' => $room]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}