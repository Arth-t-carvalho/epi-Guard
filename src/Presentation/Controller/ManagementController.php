<?php

namespace Facchini\Presentation\Controller;

class ManagementController
{
    public function departments()
    {
        $filters = [
            'status' => $_GET['status'] ?? 'ativo',
            'risk' => $_GET['risk'] ?? 'todos'
        ];
        
        $repo = new \Facchini\Infrastructure\Persistence\MySQLDepartmentRepository();
        $setores = $repo->findAllWithStats($filters);
        require_once __DIR__ . '/../View/management/departments.php';
    }

    public function employees()
    {
        $deptRepo = new \Facchini\Infrastructure\Persistence\MySQLDepartmentRepository();
        $setores = $deptRepo->findAll();
        require_once __DIR__ . '/../View/management/employees.php';
    }

    public function instructors()
    {
        require_once __DIR__ . '/../View/management/instructors.php';
    }
}
