<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLUserRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEpiRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository;

class InfractionController
{
    private PostgreSQLOccurrenceRepository $occurrenceRepository;
    private PostgreSQLEpiRepository $epiRepository;

    public function __construct()
    {
        $deptRepo = new PostgreSQLDepartmentRepository();
        $employeeRepo = new PostgreSQLEmployeeRepository($deptRepo);
        $userRepo = new PostgreSQLUserRepository();
        $this->epiRepository = new PostgreSQLEpiRepository();
        $this->occurrenceRepository = new PostgreSQLOccurrenceRepository($employeeRepo, $userRepo, $this->epiRepository);
    }

    public function index()
    {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'periodo' => $_GET['periodo'] ?? 'todos',
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
            'status' => $_GET['status'] ?? 'todos',
            'epi' => $_GET['epi'] ?? 'todos',
            'visualizacao' => $_GET['visualizacao'] ?? 'nome',
            'ordenacao' => $_GET['ordenacao'] ?? 'tempo'
        ];

        $infractions = $this->occurrenceRepository->findInfractions($filters);
        $episList = $this->epiRepository->findAll();
        
        require_once __DIR__ . '/../View/infractions/index.php';
    }
}

