<?php

namespace Facchini\Presentation\Controller;

class ManagementController
{
    public function departments()
    {
        $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
        $filters = [
            'status' => $_GET['status'] ?? 'ativo',
            'risk' => $_GET['risk'] ?? 'todos',
            'filial_id' => $activeFilialId
        ];
        
        $repo = new \Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository();
        $setores = $repo->findAllWithStats($filters);
        require_once __DIR__ . '/../View/management/departments.php';
    }

    public function employees()
    {
        $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
        $deptRepo = new \Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository();
        $setores = $deptRepo->findAll($activeFilialId);
        require_once __DIR__ . '/../View/management/employees.php';
    }

    public function instructors()
    {
        require_once __DIR__ . '/../View/management/instructors.php';
    }

    public function adManagement()
    {
        if (($_SESSION['user_email'] ?? '') !== 'pietra.12@gmail.com') {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }
        require_once __DIR__ . '/../View/management/ad.php';
    }

    public function templatePdf()
    {
        require_once __DIR__ . '/../View/management/template_pdf.php';
    }
}


