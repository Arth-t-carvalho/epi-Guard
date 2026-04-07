<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Application\Service\DashboardService;
use Facchini\Infrastructure\Persistence\MySQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\MySQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\MySQLUserRepository;
use Facchini\Infrastructure\Persistence\MySQLEpiRepository;
use Facchini\Application\Validator\OccurrenceValidator;

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
        
        $this->dashboardService = new DashboardService($employeeRepo, $occurrenceRepo, $userRepo);
    }

    public function index()
    {
        header('Content-Type: application/json');
        
        $sectorIds = null;
        if (isset($_GET['sector_id']) && $_GET['sector_id'] !== 'all') {
            $sectorIds = array_map('intval', explode(',', $_GET['sector_id']));
        }

        $refDate = null;
        if (isset($_GET['ref_date'])) {
            $refDate = new \DateTimeImmutable($_GET['ref_date']);
        }
        
        $data = $this->dashboardService->getChartData($sectorIds, $refDate);

        echo json_encode($data);
    }
}
