<?php
// Webhook para auto-deploy desde GitHub
define('REPO_PATH', '/home/insatcomar/public_html');
define('GIT_BRANCH', 'main');

// Validar que sea una solicitud POST desde GitHub
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Log de requests
$log_file = '/tmp/insatcomar_webhook.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($log_file, "[$timestamp] Webhook triggered\n", FILE_APPEND);

// Cambiar al directorio del repositorio
chdir(REPO_PATH);

// Ejecutar git pull
exec('git pull origin ' . GIT_BRANCH . ' 2>&1', $output, $return_code);

// Log de resultado
if ($return_code === 0) {
    file_put_contents($log_file, "[$timestamp] Deploy successful\n", FILE_APPEND);
    file_put_contents($log_file, "[$timestamp] Output: " . implode("\n", $output) . "\n\n", FILE_APPEND);
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Deploy completed']);
} else {
    file_put_contents($log_file, "[$timestamp] Deploy failed with code $return_code\n", FILE_APPEND);
    file_put_contents($log_file, "[$timestamp] Output: " . implode("\n", $output) . "\n\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Deploy failed']);
}
?>
