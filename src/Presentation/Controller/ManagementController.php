<?php

namespace epiGuard\Presentation\Controller;

class ManagementController
{
    public function departments()
    {
        $filters = [
            'status' => $_GET['status'] ?? 'todos',
            'risk' => $_GET['risk'] ?? 'todos'
        ];
        
        $repo = new \epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository();
        $setores = $repo->findAllWithStats($filters);
        
        $epiRepo = new \epiGuard\Infrastructure\Persistence\PostgreSQLEpiRepository();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/management/departments.php';
    }

    public function employees()
    {
        require_once __DIR__ . '/../View/management/employees.php';
    }

    public function instructors()
    {
        require_once __DIR__ . '/../View/management/instructors.php';
    }
}

