<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;

class OccurrenceController
{
    public function index()
    {
        $departmentRepo = new MySQLDepartmentRepository();
        $employeeRepo = new MySQLEmployeeRepository($departmentRepo);
        $epiRepo = new MySQLEpiRepository();

        $departments = $departmentRepo->findAll();
        $employees = $employeeRepo->findAll();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/occurrences/list.php';
    }
}
