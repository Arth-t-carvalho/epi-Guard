<?php
session_start();
header('Content-Type: text/plain');

require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../src/Domain/Entity/Department.php';
require_once __DIR__ . '/../src/Domain/Entity/Employee.php';
require_once __DIR__ . '/../src/Domain/ValueObject/CPF.php';
require_once __DIR__ . '/../src/Domain/Repository/DepartmentRepositoryInterface.php';
require_once __DIR__ . '/../src/Domain/Repository/EmployeeRepositoryInterface.php';
require_once __DIR__ . '/../src/Infrastructure/Persistence/MySQLDepartmentRepository.php';
require_once __DIR__ . '/../src/Infrastructure/Persistence/MySQLEmployeeRepository.php';

echo "Active Filial: " . ($_SESSION['active_filial_id'] ?? 'Not set') . "\n\n";

$deptRepo = new \Facchini\Infrastructure\Persistence\MySQLDepartmentRepository();
$empRepo = new \Facchini\Infrastructure\Persistence\MySQLEmployeeRepository($deptRepo);

$depts = $deptRepo->findAll();
echo "Departments in current filial:\n";
if (empty($depts)) {
    echo "NO DEPARTMENTS FOUND IN FILIAL " . ($_SESSION['active_filial_id'] ?? 1) . "\n";
}
foreach ($depts as $d) {
    echo "- ID: {$d->getId()}, Name: {$d->getName()}\n";
    $emps = $empRepo->findByDepartment($d->getId());
    if (empty($emps)) {
        echo "  (No employees in this sector)\n";
    }
    foreach ($emps as $e) {
        echo "  * Employee ID: {$e->getId()}, Name: {$e->getName()}\n";
    }
}
?>
