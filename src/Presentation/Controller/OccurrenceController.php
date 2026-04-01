<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEpiRepository;

class OccurrenceController
{
    public function index()
    {
        $deptRepo = new PostgreSQLDepartmentRepository();
        $empRepo = new PostgreSQLEmployeeRepository($deptRepo);
        $epiRepo = new PostgreSQLEpiRepository();

        $departments = $deptRepo->findAll();
        $employees = $empRepo->findAll();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/occurrences/list.php';
    }
}

