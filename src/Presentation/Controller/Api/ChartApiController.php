<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Application\Service\DashboardService;
use epiGuard\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLUserRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEpiRepository;
use epiGuard\Application\Validator\OccurrenceValidator;

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
        
        $this->dashboardService = new DashboardService($employeeRepo, $occurrenceRepo);
    }

    public function index()
    {
        header('Content-Type: application/json');
        
        $sectorIds = null;
        if (isset($_GET['sector_id']) && $_GET['sector_id'] !== 'all') {
            $sectorIds = array_map('intval', explode(',', $_GET['sector_id']));
        }
        
        $data = $this->dashboardService->getChartData($sectorIds);

        echo json_encode($data);
    }
}

