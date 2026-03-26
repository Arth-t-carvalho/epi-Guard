<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Application\Service\DashboardService;
use epiGuard\Infrastructure\Persistence\MySQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\MySQLUserRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;
use epiGuard\Application\Validator\OccurrenceValidator;

class ChartApiController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        // Injeção de dependências manual para o contexto desse projeto PHP puro
        $deptRepo = new MySQLDepartmentRepository();
        $employeeRepo = new MySQLEmployeeRepository($deptRepo);
        $userRepo = new MySQLUserRepository();
        $epiRepo = new MySQLEpiRepository();
        $occurrenceRepo = new MySQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
        
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
