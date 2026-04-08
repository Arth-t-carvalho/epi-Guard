<?php
// Autoloader manual PSR-4
spl_autoload_register(function ($class) {
    $prefix = 'Facchini\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use Facchini\Application\Service\DashboardService;
use Facchini\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLUserRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository;

try {
    $deptRepo = new PostgreSQLDepartmentRepository();
    $employeeRepo = new PostgreSQLEmployeeRepository($deptRepo);
    $userRepo = new PostgreSQLUserRepository();
    $epiRepo = new PostgreSQLEpiRepository();
    $occurrenceRepo = new PostgreSQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
    
    $dashboardService = new DashboardService($employeeRepo, $occurrenceRepo, $userRepo);
    
    $data = $dashboardService->getChartData(null, null);
    
    echo json_encode($data, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
