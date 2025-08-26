<?php
/**
 * System Status Check
 * Determines if the system is online or in maintenance mode
 */

header('Content-Type: application/json');

// Check if maintenance flag exists
$maintenanceFlag = file_exists(__DIR__ . '/../maintenance.flag');

if ($maintenanceFlag) {
    echo json_encode([
        'status' => 'maintenance',
        'message' => 'System is currently under maintenance',
        'timestamp' => date('Y-m-d H:i:s'),
        'estimated_completion' => 'TBD'
    ]);
} else {
    echo json_encode([
        'status' => 'online',
        'message' => 'System is operational',
        'timestamp' => date('Y-m-d H:i:s'),
        'uptime' => '100%'
    ]);
}
?>
