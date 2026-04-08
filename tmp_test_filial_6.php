<?php
session_start();
$_SESSION['active_filial_id'] = 6;
require 'vendor/autoload.php';

// Simulate index.php environment
define('BASE_PATH', '');

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
