<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
header('Content-Type: text/plain');

echo "Active Filial: " . ($_SESSION['active_filial_id'] ?? 'Not set') . "\n\n";

$deptRepo = new \Facchini\Infrastructure\Persistence\MySQLDepartmentRepository();
$empRepo = new \Facchini\Infrastructure\Persistence\MySQLEmployeeRepository($deptRepo);

$depts = $deptRepo->findAll();
echo "Departments in current filial:\n";
foreach ($depts as $d) {
    echo "- ID: {$d->getId()}, Name: {$d->getName()}\n";
    $emps = $empRepo->findByDepartment($d->getId());
    foreach ($emps as $e) {
        echo "  * Employee ID: {$e->getId()}, Name: {$e->getName()}\n";
    }
}
?>
