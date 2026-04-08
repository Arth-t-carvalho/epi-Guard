<?php

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLUserRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository;

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
            'status' => $_GET['status'] ?? 'todos',
            'epi' => $_GET['epi'] ?? 'todos',
            'visualizacao' => $_GET['visualizacao'] ?? 'nome',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'visibilidade' => $_GET['visibilidade'] ?? 'ativos',
            'order' => $_GET['order'] ?? 'recentes',
            'funcionario_id' => $_GET['funcionario_id'] ?? null,
            'setor_id' => $_GET['setor_id'] ?? ($_GET['sector_id'] ?? null),
            'ref_date' => date('Y-m-d')
        ];

        $highlightId = $_GET['highlight'] ?? null;

        $infractions = $this->occurrenceRepository->findInfractions($filters);
        $episList = $this->epiRepository->findAll();
        
        require_once __DIR__ . '/../View/infractions/index.php';
    }
}
