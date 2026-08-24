<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\ApiSecurity;

ApiSecurity::applyJsonHeaders();
ApiSecurity::requireMethods(['GET']);

ApiSecurity::respond([
    'success' => true,
    'message' => 'nQueue API is running',
    'endpoints' => [
        'departments' => 'departments.php',
        'rooms' => 'rooms.php',
        'queue_data' => 'queue_data.php',
        'read_queue' => 'readq.php',
        'scan' => 'scan.php',
        'manage_queue' => 'manage_queue.php',
        'settings' => 'settings.php',
        'update_status' => 'update_status.php',
    ],
]);
