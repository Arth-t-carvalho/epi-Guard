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
    private PostgreSQLDepartmentRepository $departmentRepository;
    private PostgreSQLEmployeeRepository $employeeRepository;

    public function __construct()
    {
        $this->departmentRepository = new PostgreSQLDepartmentRepository();
        $this->employeeRepository = new PostgreSQLEmployeeRepository($this->departmentRepository);
        $userRepo = new PostgreSQLUserRepository();
        $this->epiRepository = new PostgreSQLEpiRepository();
        $this->occurrenceRepository = new PostgreSQLOccurrenceRepository($this->employeeRepository, $userRepo, $this->epiRepository);
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
            'ref_date' => date('Y-m-d'),
            'filial_id' => $_SESSION['active_filial_id'] ?? 1
        ];

        $highlightId = $_GET['highlight'] ?? null;

        $infractions = $this->occurrenceRepository->findInfractions($filters);
        $episList = $this->epiRepository->findAll();
        $sectorsList = $this->departmentRepository->findAll($filters['filial_id']);
        
        $employeesList = [];
        if (!empty($filters['setor_id']) && $filters['setor_id'] !== 'todos') {
            $employeesList = $this->employeeRepository->findByDepartment((int)$filters['setor_id']);
        }

        require_once __DIR__ . '/../View/infractions/index.php';
    }
}
