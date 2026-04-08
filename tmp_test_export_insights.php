<?php
/**
 * Manual autoloader
 */
spl_autoload_register(function ($class) {
    $prefix = 'Facchini\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

require_once __DIR__ . '/src/Infrastructure/i18n.php';

// Load environment variables for DB connection
if (file_exists(__DIR__ . '/config/.env')) {
    $lines = file(__DIR__ . '/config/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

function testInsights($filialId) {
    $_SESSION['active_filial_id'] = $filialId;
    $c = new \Facchini\Presentation\Controller\Api\ExportApiController();
    echo "\n--- Insights for Filial $filialId ---\n";
    ob_start();
    $c->insights();
    echo ob_get_clean() . "\n";
}

session_start();
testInsights(1);
testInsights(6);
testInsights(2);
