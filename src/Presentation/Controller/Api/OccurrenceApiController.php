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

        $stmt = $db->prepare($query);
        
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

    public function store()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        $db = Connection::getInstance();
        $db->begin_transaction();

        try {
            $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
            $epiId = (int) ($_POST['epi_id'] ?? 0);
            $dataHora = $_POST['data_hora'] ?? date('Y-m-d H:i:s');
            $tipoAcao = $_POST['tipo_acao'] ?? 'OBSERVACAO';
            $observacao = $_POST['observacao'] ?? '';
            $tipoOcorrencia = ($epiId > 0 && $epiId !== 'none') ? 'INFRACAO' : 'CONFORMIDADE';

            if ($funcionarioId <= 0) {
                throw new \Exception("ID do funcionário é obrigatório.");
            }

            // 1. Inserir Ocorrência
            $stmt = $db->prepare("INSERT INTO ocorrencias (funcionario_id, tipo, data_hora) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $funcionarioId, $tipoOcorrencia, $dataHora);
            $stmt->execute();
            $ocorrenciaId = $db->insert_id;

            // 2. Inserir EPI (se houver)
            if ($epiId > 0) {
                $stmtEpi = $db->prepare("INSERT INTO ocorrencia_epis (ocorrencia_id, epi_id) VALUES (?, ?)");
                $stmtEpi->bind_param('ii', $ocorrenciaId, $epiId);
                $stmtEpi->execute();
            }

            // 3. Inserir Ação Disciplinar
            $enumTipoAcao = 'OBSERVACAO';
            if (str_contains($tipoAcao, 'Verbal')) $enumTipoAcao = 'ADVERTENCIA_VERBAL';
            elseif (str_contains($tipoAcao, 'Escrita')) $enumTipoAcao = 'ADVERTENCIA_ESCRITA';
            elseif (str_contains($tipoAcao, 'Suspensão')) $enumTipoAcao = 'SUSPENSAO';

            $usuarioId = $_SESSION['user_id'] ?? 1;
            $stmtAcao = $db->prepare("INSERT INTO acoes_ocorrencia (ocorrencia_id, usuario_id, tipo, observacao, data_hora) VALUES (?, ?, ?, ?, ?)");
            $stmtAcao->bind_param('iisss', $ocorrenciaId, $usuarioId, $enumTipoAcao, $observacao, $dataHora);
            $stmtAcao->execute();

            // 4. Salvar Evidências
            if (!empty($_FILES['evidencias']['name'][0])) {
                $uploadDir = __DIR__ . '/../../../../public/uploads/evidences/';
                foreach ($_FILES['evidencias']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['evidencias']['error'][$key] === UPLOAD_ERR_OK) {
                        $extension = pathinfo($_FILES['evidencias']['name'][$key], PATHINFO_EXTENSION);
                        $fileName = $ocorrenciaId . '_' . $key . '_' . time() . '.' . $extension;
                        $destination = $uploadDir . $fileName;

                        if (move_uploaded_file($tmpName, $destination)) {
                            $dbPath = 'public/uploads/evidences/' . $fileName;
                            $stmtImg = $db->prepare("INSERT INTO evidencias (ocorrencia_id, caminho_imagem) VALUES (?, ?)");
                            $stmtImg->bind_param('is', $ocorrenciaId, $dbPath);
                            $stmtImg->execute();
                        }
                    }
                }
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Ocorrência registrada com sucesso.', 'id' => $ocorrenciaId]);

        } catch (\Exception $e) {
            $db->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }
}
