<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;

class OccurrenceController
{
    public function index()
    {
        $deptRepo = new MySQLDepartmentRepository();
        $empRepo = new MySQLEmployeeRepository($deptRepo);
        $epiRepo = new MySQLEpiRepository();

        $departments = $deptRepo->findAll();
        $employees = $empRepo->findAll();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/occurrences/list.php';
    }
}
