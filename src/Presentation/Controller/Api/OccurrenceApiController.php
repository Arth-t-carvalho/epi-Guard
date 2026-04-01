<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Persistence\MySQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\MySQLUserRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;
use epiGuard\Infrastructure\Database\Connection;

class OccurrenceApiController
{
    private MySQLOccurrenceRepository $occurrenceRepo;
    private MySQLDepartmentRepository $departmentRepo;

    public function __construct()
    {
        $db = Connection::getInstance();
        $deptRepo = new MySQLDepartmentRepository();
        $employeeRepo = new MySQLEmployeeRepository($deptRepo);
        $userRepo = new MySQLUserRepository();
        $epiRepo = new MySQLEpiRepository();
        $this->occurrenceRepo = new MySQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
        $this->departmentRepo = $deptRepo;
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

        // Visão Empresarial: Filtro de Setor Dinâmico
        $db = Connection::getInstance();
        $query = "
            SELECT 
                o.data_hora as full_date, 
                s.nome AS name, 
                e.nome AS `desc`, 
                DATE_FORMAT(o.data_hora, '%H:%i') AS time,
                f.nome AS employee,
                o.funcionario_id
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?
        ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
        }

        $query .= " ORDER BY o.data_hora ASC";

        $stmt = $db->prepare($query);
        if (!empty($sectorIds)) {
            $types = 'ii' . str_repeat('i', count($sectorIds));
            $params = array_merge([$month, $year], $sectorIds);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('ii', $month, $year);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($data);
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

        $db = Connection::getInstance();
        $query = "
            SELECT 
                o.id AS ocorrencia_id, 
                DATE_FORMAT(o.data_hora, '%d/%m/%Y') AS data, 
                f.nome AS aluno, 
                f.id AS aluno_id, 
                IFNULL(s.nome, 'Sem Setor') AS curso,
                e.nome AS epis, 
                DATE_FORMAT(o.data_hora, '%H:%i') AS hora,
                'Pendente' AS status_formatado
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            LEFT JOIN setores s ON f.setor_id = s.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE MONTH(o.data_hora) = ? AND YEAR(o.data_hora) = ?
        ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
        }
        if (!empty($epiName)) {
            $query .= " AND e.nome = ?";
        }

        $query .= " ORDER BY o.data_hora DESC";

        $now = date('Y-m-d');
        $ref = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $stmt = $db->prepare($query);
        $nowDt = new \DateTime($now);
        $refDt = new \DateTime($ref);

        $summary = [
            'today' => $this->occurrenceRepo->countDaily($nowDt, $sectorIds),
            'week' => $this->occurrenceRepo->countWeekly($nowDt, $sectorIds),
            'month' => $this->occurrenceRepo->countMonthly($refDt, $sectorIds),
            'students_today' => $this->occurrenceRepo->countUniqueStudentsDaily($nowDt, $sectorIds),
            'students_week' => $this->occurrenceRepo->countUniqueStudentsWeekly($nowDt, $sectorIds),
            'students_month' => $this->occurrenceRepo->countUniqueStudentsMonthly($refDt, $sectorIds)
        ];

        $types = "ii";
        $params = [$month, $year];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        if (!empty($epiName)) {
            $types .= 's';
            $params[] = $epiName;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($data);
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
