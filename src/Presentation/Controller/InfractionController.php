<?php

namespace Facchini\Presentation\Controller;

use Facchini\Infrastructure\Persistence\MySQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\MySQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\MySQLUserRepository;
use Facchini\Infrastructure\Persistence\MySQLEpiRepository;
use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;

class InfractionController
{
    private MySQLOccurrenceRepository $occurrenceRepository;
    private MySQLEpiRepository $epiRepository;
    private MySQLDepartmentRepository $departmentRepository;
    private MySQLEmployeeRepository $employeeRepository;

    public function __construct()
    {
        $this->departmentRepository = new MySQLDepartmentRepository();
        $this->employeeRepository = new MySQLEmployeeRepository($this->departmentRepository);
        $userRepo = new MySQLUserRepository();
        $this->epiRepository = new MySQLEpiRepository();
        $this->occurrenceRepository = new MySQLOccurrenceRepository($this->employeeRepository, $userRepo, $this->epiRepository);
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
