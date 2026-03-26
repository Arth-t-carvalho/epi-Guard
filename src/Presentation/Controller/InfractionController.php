<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\MySQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLUserRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;
use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;

class InfractionController
{
    private MySQLOccurrenceRepository $occurrenceRepository;
    private MySQLEpiRepository $epiRepository;

    public function __construct()
    {
        $deptRepo = new MySQLDepartmentRepository();
        $employeeRepo = new MySQLEmployeeRepository($deptRepo);
        $userRepo = new MySQLUserRepository();
        $this->epiRepository = new MySQLEpiRepository();
        $this->occurrenceRepository = new MySQLOccurrenceRepository($employeeRepo, $userRepo, $this->epiRepository);
    }

    public function index()
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'periodo' => $_GET['periodo'] ?? 'todos',
            'status' => $_GET['status'] ?? 'todos',
            'epi' => $_GET['epi'] ?? 'todos',
            'visualizacao' => $_GET['visualizacao'] ?? 'nome'
        ];

        $infractions = $this->occurrenceRepository->findInfractions($filters);
        $episList = $this->epiRepository->findAll();
        
        require_once __DIR__ . '/../View/infractions/index.php';
    }
}
