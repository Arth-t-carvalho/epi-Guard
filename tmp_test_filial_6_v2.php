<?php
/**
 * Manual autoloader to match index.php
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

// Load i18n
require_once __DIR__ . '/src/Infrastructure/i18n.php';

// Mock session and constants
session_start();
$_SESSION['active_filial_id'] = 6;
define('BASE_PATH', '');

// Load environment variables for DB connection
if (file_exists(__DIR__ . '/config/.env')) {
    $lines = file(__DIR__ . '/config/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

// Test OccurrenceApiController::calendar()
$c = new \Facchini\Presentation\Controller\Api\OccurrenceApiController();
$_GET['month'] = 4;
$_GET['year'] = 2026;

echo "--- Filial 6 Calendar Results ---\n";
ob_start();
$c->calendar();
$output = ob_get_clean();
echo $output . "\n\n";

// Test MySQLEmployeeRepository::findAll()
$deptRepo = new \Facchini\Infrastructure\Persistence\MySQLDepartmentRepository();
$empRepo = new \Facchini\Infrastructure\Persistence\MySQLEmployeeRepository($deptRepo);
$employees = $empRepo->findAll();
echo "--- Filial 6 Employees Count ---\n";
echo count($employees) . "\n";
foreach($employees as $e) {
    echo " - " . $e->getName() . "\n";
}

// Test MySQLDepartmentRepository::findAll()
echo "\n--- Filial 6 Sectors Count ---\n";
$sectors = $deptRepo->findAll();
echo count($sectors) . "\n";
foreach($sectors as $s) {
    echo " - " . $s->getName() . "\n";
}
