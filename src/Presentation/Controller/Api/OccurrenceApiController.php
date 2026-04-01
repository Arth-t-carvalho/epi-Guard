<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Persistence\PostgreSQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLDepartmentRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLUserRepository;
use epiGuard\Infrastructure\Persistence\PostgreSQLEpiRepository;
use epiGuard\Infrastructure\Database\Connection;

class OccurrenceApiController
{
    private PostgreSQLOccurrenceRepository $occurrenceRepo;
    private PostgreSQLDepartmentRepository $departmentRepo;

    public function __construct()
    {
        $db = Connection::getInstance();
        $deptRepo = new PostgreSQLDepartmentRepository();
        $employeeRepo = new PostgreSQLEmployeeRepository($deptRepo);
        $userRepo = new PostgreSQLUserRepository();
        $epiRepo = new PostgreSQLEpiRepository();
        $this->occurrenceRepo = new PostgreSQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
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

        $db = Connection::getInstance();
        $query = "
            SELECT 
                o.data_hora as full_date, 
                s.nome AS name, 
                e.nome AS \"desc\", 
                to_char(o.data_hora, 'HH24:MI') AS time,
                o.funcionario_id
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE EXTRACT(MONTH FROM o.data_hora) = ? AND EXTRACT(YEAR FROM o.data_hora) = ?
        ";

        $params = [$month, $year];
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
            $params = array_merge($params, $sectorIds);
        }

        $query .= " ORDER BY o.data_hora ASC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

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
            WHERE EXTRACT(MONTH FROM o.data_hora) = ? AND EXTRACT(YEAR FROM o.data_hora) = ?
        ";

        $params = [$month, $year];
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND s.id IN ($placeholders)";
            $params = array_merge($params, $sectorIds);
        }
        if (!empty($epiName)) {
            $query .= " AND e.nome = ?";
            $params[] = $epiName;
        }

        $query .= " ORDER BY o.data_hora DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

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

