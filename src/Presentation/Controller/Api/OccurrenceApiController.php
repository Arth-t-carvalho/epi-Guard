<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLUserRepository;
use Facchini\Infrastructure\Persistence\PostgreSQLEpiRepository;
use Facchini\Infrastructure\Database\Connection;

class OccurrenceApiController
{
    private PostgreSQLOccurrenceRepository $occurrenceRepo;
    private PostgreSQLDepartmentRepository $departmentRepo;
    private \Facchini\Application\Service\DashboardService $dashboardService;

    public function __construct()
    {
        $db = Connection::getInstance();
        $deptRepo = new PostgreSQLDepartmentRepository();
        $employeeRepo = new PostgreSQLEmployeeRepository($deptRepo);
        $userRepo = new PostgreSQLUserRepository();
        $epiRepo = new PostgreSQLEpiRepository();
        $this->occurrenceRepo = new PostgreSQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
        $this->departmentRepo = $deptRepo;
        $this->dashboardService = new \Facchini\Application\Service\DashboardService($employeeRepo, $this->occurrenceRepo, $userRepo);
    }
    public function calendar()
    {
        header('Content-Type: application/json');

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $sectorIds = null;
        if (isset($_GET['sector_id']) && $_GET['sector_id'] !== 'all') {
            $sectorIds = array_map('intval', explode(',', $_GET['sector_id']));
        }

        $activeFilial = (int)($_SESSION['active_filial_id'] ?? 1);
        $db = Connection::getInstance();
        
        $query = "
            SELECT DISTINCT
                o.id,
                o.data_hora as full_date, 
                s.nome AS name, 
                s.id AS sector_id,
                to_char(o.data_hora, 'HH24:MI') AS time,
                f.nome AS employee,
                o.funcionario_id
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            WHERE EXTRACT(MONTH FROM o.data_hora) = ? 
              AND EXTRACT(YEAR FROM o.data_hora) = ? 
              AND o.filial_id = ? 
              AND o.oculto = FALSE
        ";

        $params = [$month, $year, $activeFilial];

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
            $params = array_merge($params, $sectorIds);
        }

        $query .= " ORDER BY o.data_hora ASC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $occurrences = $stmt->fetchAll();

        // Calculate Summary for Dashboard Cards
        $nowDt = new \DateTime();
        $refDt = new \DateTime("$year-$month-01");
        $summary = [
            'today' => $this->occurrenceRepo->countDaily($nowDt, $sectorIds),
            'week' => $this->occurrenceRepo->countWeekly($nowDt, $sectorIds),
            'month' => $this->occurrenceRepo->countMonthly($refDt, $sectorIds)
        ];

        echo json_encode([
            'occurrences' => $occurrences,
            'summary' => $summary
        ]);
    }

    public function details()
    {
        header('Content-Type: application/json');

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $sectorIds = null;
        if (isset($_GET['sector_id']) && $_GET['sector_id'] !== 'all') {
            $sectorIds = array_map('intval', explode(',', $_GET['sector_id']));
        }
        $epiName = $_GET['epi'] ?? '';
        $sectorName = $_GET['sector_name'] ?? '';

        $activeFilial = (int)($_SESSION['active_filial_id'] ?? 1);
        $db = Connection::getInstance();
        
        $query = "
            SELECT 
                o.id AS ocorrencia_id, 
                to_char(o.data_hora, 'DD/MM/YYYY') AS data, 
                f.nome AS aluno, 
                f.id AS aluno_id, 
                COALESCE(s.nome, 'Sem Setor') AS curso,
                e.nome AS epis, 
                to_char(o.data_hora, 'HH24:MI') AS hora,
                'Pendente' AS status_formatado
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            LEFT JOIN setores s ON f.setor_id = s.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE EXTRACT(MONTH FROM o.data_hora) = ? 
              AND EXTRACT(YEAR FROM o.data_hora) = ? 
              AND o.filial_id = ? 
              AND o.oculto = FALSE
        ";

        $params = [$month, $year, $activeFilial];

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
            $params = array_merge($params, $sectorIds);
        }
        if (!empty($epiName)) {
            $query .= " AND e.nome = ?";
            $params[] = $epiName;
        }
        if (!empty($sectorName)) {
            $query .= " AND s.nome = ?";
            $params[] = $sectorName;
        }

        $query .= " ORDER BY o.data_hora DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $nowDt = new \DateTime();
        $refDt = new \DateTime(date('Y-m-01', strtotime("$year-$month-01")));
        $summary = [
            'today' => $this->occurrenceRepo->countDaily($nowDt, $sectorIds),
            'week' => $this->occurrenceRepo->countWeekly($nowDt, $sectorIds),
            'month' => $this->occurrenceRepo->countMonthly($refDt, $sectorIds),
            'students_today' => $this->occurrenceRepo->countUniqueStudentsDaily($nowDt, $sectorIds),
            'students_week' => $this->occurrenceRepo->countUniqueStudentsWeekly($nowDt, $sectorIds),
            'students_month' => $this->occurrenceRepo->countUniqueStudentsMonthly($refDt, $sectorIds)
        ];

        echo json_encode([
            'data' => $data,
            'summary' => $summary
        ]);
    }

    public function hide()
    {
        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $success = $this->occurrenceRepo->hide($id);
        echo json_encode(['success' => $success]);
    }

    public function toggleFavorite()
    {
        header('Content-Type: application/json');
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $result = $this->occurrenceRepo->toggleFavorite($id);
        echo json_encode($result);
    }
}
