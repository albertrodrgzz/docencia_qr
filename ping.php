<?php
declare(strict_types=1);

// =============================================================================
// Kredentia - Keep-Alive Heartbeat / Healthcheck Endpoint
// Diseñado para cron-job.org y monitoreo de alta disponibilidad en Render.
// =============================================================================

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Robots-Tag: noindex, nofollow');
http_response_code(200);

$dbStatus = 'untested';
try {
    $configFile = __DIR__ . '/configuracion/ConexionBD.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        $pdo = \Configuracion\ConexionBD::getInstancia();
        $pdo->query('SELECT 1');
        $dbStatus = 'connected';
    }
} catch (\Throwable $e) {
    $dbStatus = 'error: ' . $e->getMessage();
}

echo json_encode([
    'status'    => 'ok',
    'app'       => 'Kredentia',
    'heartbeat' => 'alive',
    'timestamp' => date('Y-m-d H:i:s'),
    'database'  => $dbStatus,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit();
