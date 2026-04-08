<?php
session_start();
spl_autoload_register(function ($class) {
    $prefix = 'Facchini\\'; $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/src/Infrastructure/i18n.php';
if (file_exists(__DIR__ . '/config/.env')) {
    $lines = file(__DIR__ . '/config/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}
$_SESSION['active_filial_id'] = $_SESSION['active_filial_id'] ?? 1;
$_GET['month'] = 4;
$_GET['year'] = 2026;
$c = new \Facchini\Presentation\Controller\Api\OccurrenceApiController();
ob_start();
$c->calendar();
$json = ob_get_clean();
echo "FILIAL: " . $_SESSION['active_filial_id'] . "\n";
echo "DATA: " . $json . "\n";
