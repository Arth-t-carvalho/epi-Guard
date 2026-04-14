<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\MySQLOccurrenceRepository;
use Facchini\Infrastructure\Persistence\MySQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;
use Facchini\Infrastructure\Persistence\MySQLUserRepository;
use Facchini\Infrastructure\Persistence\MySQLEpiRepository;
use Facchini\Infrastructure\Database\Connection;

class OccurrenceApiController
{
    private MySQLOccurrenceRepository $occurrenceRepo;
    private MySQLDepartmentRepository $departmentRepo;
    private \Facchini\Application\Service\DashboardService $dashboardService;

    public function __construct()
    {
        $db = Connection::getInstance();
        $deptRepo = new MySQLDepartmentRepository();
        $employeeRepo = new MySQLEmployeeRepository($deptRepo);
        $userRepo = new MySQLUserRepository();
        $epiRepo = new MySQLEpiRepository();
        $this->occurrenceRepo = new MySQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
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
                DATE_FORMAT(o.data_hora, '%H:%i') AS time,
                f.nome AS employee,
                o.funcionario_id,
                CASE 
                    WHEN EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id) THEN 'Resolvido'
                    ELSE 'Pendente'
                END AS status
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            LEFT JOIN setores s ON f.setor_id = s.id
            WHERE MONTH(o.data_hora) = ? 
              AND YEAR(o.data_hora) = ? 
              AND o.filial_id = ? 
              AND (o.oculto = 0 OR o.oculto IS NULL)
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
        // Usa SEMPRE $nowDt para todos os campos do summary,
        // correspondendo exatamente ao que o /api/charts retorna.
        $nowDt = new \DateTime();

        // Se não há filtro de setor, aplica o filtro da filial ativa
        // (igual à lógica do ChartApiController)
        $summaryIds = $sectorIds;
        if (empty($summaryIds)) {
            $filialId = (int)($_SESSION['active_filial_id'] ?? 1);
            $departments = $this->departmentRepo->findAll($filialId);
            $summaryIds = array_map(fn($d) => $d->getId(), $departments);
            if (empty($summaryIds)) {
                $summaryIds = [-1];
            }
        }

        $yesterdayDt = (clone $nowDt)->modify('-1 day');
        $lastWeekDt  = (clone $nowDt)->modify('-1 week');
        $refMonth    = (int) ($_GET['month'] ?? date('n'));
        $refYear     = (int) ($_GET['year'] ?? date('Y'));
        $currentMonthDt = new \DateTime("$refYear-$refMonth-01");
        $lastMonthDt = (clone $currentMonthDt)->modify('-1 month');

        $todayCount = $this->occurrenceRepo->countDaily($nowDt, $summaryIds, $activeFilial);
        $prevToday   = $this->occurrenceRepo->countDaily($yesterdayDt, $summaryIds, $activeFilial);
        
        $weekCount  = $this->occurrenceRepo->countWeekly($nowDt, $summaryIds, $activeFilial);
        $prevWeek    = $this->occurrenceRepo->countWeekly($lastWeekDt, $summaryIds, $activeFilial);
        
        $monthCount = $this->occurrenceRepo->countMonthly($currentMonthDt, $summaryIds, $activeFilial);
        $prevMonthCount = $this->occurrenceRepo->countMonthly($lastMonthDt, $summaryIds, $activeFilial);

        $calcTrend = function($curr, $prev) {
            if ($prev == 0) return ['percent' => $curr > 0 ? 100 : 0, 'direction' => $curr > 0 ? 'up' : 'stable', 'level' => $curr > 0 ? 'critico' : 'controlado'];
            $diff = (($curr - $prev) / $prev) * 100;
            $dir = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'stable');
            
            $level = 'controlado';
            if ($diff > 20) $level = 'critico';
            elseif ($diff > 5) $level = 'moderado';

            return [
                'percent' => round(abs($diff), 1),
                'direction' => $dir,
                'level' => $level
            ];
        };

        $summary = [
            'today' => $todayCount,
            'week'  => $weekCount,
            'month' => $monthCount,
            'trends' => [
                'today' => $calcTrend($todayCount, $prevToday),
                'week'  => $calcTrend($weekCount, $prevWeek),
                'month' => $calcTrend($monthCount, $prevMonthCount)
            ]
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
                DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data, 
                f.nome AS aluno, 
                f.id AS aluno_id, 
                COALESCE(s.nome, 'Sem Setor') AS curso,
                COALESCE(GROUP_CONCAT(e.nome SEPARATOR ', '), 'Sem EPI') AS epis, 
                DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
                CASE 
                    WHEN EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id) THEN 'Resolvido'
                    ELSE 'Pendente'
                END AS status_formatado
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            LEFT JOIN setores s ON f.setor_id = s.id
            LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            LEFT JOIN epis e ON oe.epi_id = e.id
            WHERE MONTH(o.data_hora) = ? 
              AND YEAR(o.data_hora) = ? 
              AND o.filial_id = ? 
              AND (o.oculto = 0 OR o.oculto IS NULL)
              AND o.tipo = 'INFRACAO'
        ";

        $params = [$month, $year, $activeFilial];

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
            $params = array_merge($params, $sectorIds);
        }

        if (!empty($epiName)) {
            // Se filtramos por um EPI específico no gráfico, 
            // garantimos que ele está entre os agrupados
            $query .= " AND EXISTS (
                SELECT 1 FROM ocorrencia_epis oe2 
                JOIN epis e2 ON oe2.epi_id = e2.id 
                WHERE oe2.ocorrencia_id = o.id AND e2.nome = ?
            )";
            $params[] = $epiName;
        }

        if (!empty($sectorName)) {
            $query .= " AND s.nome = ?";
            $params[] = $sectorName;
        }

        $query .= " GROUP BY o.id, f.id, s.id";
        $query .= " ORDER BY o.data_hora DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $nowDt = new \DateTime();
        $refDt = new \DateTime(date('Y-m-01', strtotime("$year-$month-01")));
        $summary = [
            'today' => $this->occurrenceRepo->countDaily($nowDt, $sectorIds, $activeFilial),
            'week' => $this->occurrenceRepo->countWeekly($nowDt, $sectorIds, $activeFilial),
            'month' => $this->occurrenceRepo->countMonthly($refDt, $sectorIds, $activeFilial),
            'students_today' => $this->occurrenceRepo->countUniqueStudentsDaily($nowDt, $sectorIds, $activeFilial),
            'students_week' => $this->occurrenceRepo->countUniqueStudentsWeekly($nowDt, $sectorIds, $activeFilial),
            'students_month' => $this->occurrenceRepo->countUniqueStudentsMonthly($refDt, $sectorIds, $activeFilial)
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
