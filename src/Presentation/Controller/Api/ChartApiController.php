<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Application\Service\DashboardService;
use Facchini\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLUserRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository;

class ChartApiController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        // Injeção de dependências manual para o contexto desse projeto PHP puro
        $deptRepo = new PostgreSQLDepartmentRepository();
        $employeeRepo = new PostgreSQLEmployeeRepository($deptRepo);
        $userRepo = new PostgreSQLUserRepository();
        $epiRepo = new PostgreSQLEpiRepository();
        $occurrenceRepo = new PostgreSQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
        
        $this->dashboardService = new DashboardService($employeeRepo, $occurrenceRepo, $userRepo);
    }

    public function index()
    {
        header('Content-Type: application/json');
        
        $sectorIds = null;
        if (isset($_GET['sector_id']) && $_GET['sector_id'] !== 'all' && trim($_GET['sector_id']) !== '') {
            $sectorIds = array_map('intval', explode(',', $_GET['sector_id']));
        } else {
            $filialId = $_SESSION['active_filial_id'] ?? 1;
            $deptRepo = new PostgreSQLDepartmentRepository();
            $departments = $deptRepo->findAll($filialId);
            $sectorIds = [];
            foreach ($departments as $dept) {
                $sectorIds[] = $dept->getId();
            }
            if (empty($sectorIds)) {
                $sectorIds = [-1]; // Prevenir que retorne vazio e busque de todas as filiais
            }
        }

        $refDate = null;
        if (isset($_GET['ref_date'])) {
            $refDate = new \DateTimeImmutable($_GET['ref_date']);
        }
        
        $data = $this->dashboardService->getChartData($sectorIds, $refDate);

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
