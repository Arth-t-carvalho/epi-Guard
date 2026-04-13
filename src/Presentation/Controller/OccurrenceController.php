<?php

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository;

class OccurrenceController
{
    public function index()
    {
        $deptRepo = new PostgreSQLDepartmentRepository();
        $empRepo = new PostgreSQLEmployeeRepository($deptRepo);
        $epiRepo = new PostgreSQLEpiRepository();

        $departments = $deptRepo->findAll();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/occurrences/list.php';
    }
}
