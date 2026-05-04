<?php
// Temporary diagnostic — REMOVE AFTER USE
if (!isset($_GET['t']) || $_GET['t'] !== 'itf2568diag') { http_response_code(404); exit; }
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo '<pre>';
echo 'PHP: ' . PHP_VERSION . "\n";
echo 'OS: ' . PHP_OS . "\n";
echo 'Extensions: ' . implode(', ', array_filter(get_loaded_extensions(), fn($e) => in_array($e, ['mysqli','pdo_mysql','mbstring','json','session','openssl']))) . "\n\n";
echo 'Writable /tmp: ' . (is_writable(sys_get_temp_dir()) ? 'YES' : 'NO') . ' (' . sys_get_temp_dir() . ")\n";
$base = __DIR__;
$sess_path = $base . '/application/sessions/';
echo 'Sessions dir: ' . $sess_path . "\n";
echo 'Sessions exists: ' . (is_dir($sess_path) ? 'YES' : 'NO') . "\n";
echo 'Sessions writable: ' . (is_dir($sess_path) && is_writable($sess_path) ? 'YES' : 'NO') . "\n";
$logs_path = $base . '/application/logs/';
echo 'Logs writable: ' . (is_writable($logs_path) ? 'YES' : 'NO') . "\n\n";
// Test DB
try {
    $pdo = new PDO('mysql:host=srv2094.hstgr.io;port=3306;dbname=u527918014_boss;charset=utf8mb4', 'u527918014_bossdba', 'Boss@0851330199', [PDO::ATTR_TIMEOUT => 5]);
    echo "DB: CONNECTED ✓\n";
    $r = $pdo->query("SELECT COUNT(*) FROM settings LIMIT 1");
    echo "Settings table: " . ($r ? "EXISTS" : "MISSING") . "\n";
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
echo "\nMemory limit: " . ini_get('memory_limit') . "\n";
echo 'Max exec time: ' . ini_get('max_execution_time') . "s\n";
echo '</pre>';
