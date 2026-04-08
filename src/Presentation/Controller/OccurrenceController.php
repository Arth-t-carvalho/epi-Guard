<?php

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\MySQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\MySQLEpiRepository;

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
