<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Database\Connection;

class ExportApiController
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function insights()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;

        $year = (int) date('Y');

        try {
            $response = [
                'status' => 'success',
                'generated_at' => date('d/m/Y H:i'),
                'year' => $year,
                'active_filial_id' => $activeFilial,
                'worst_sector' => $this->getWorstSector($year, $activeFilial),
                'worst_epis' => $this->getWorstEpis($year, $activeFilial),
                'worst_month' => $this->getWorstMonth($year, $activeFilial),
                'worst_day_of_week' => $this->getWorstDayOfWeek($year, $activeFilial),
                'sectors_ranking' => $this->getSectorsRanking($year, $activeFilial),
                'epis_ranking' => $this->getEpisRanking($year, $activeFilial),
                'monthly_totals' => $this->getMonthlyTotals($year, $activeFilial),
            ];

            echo json_encode($response);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro interno no servidor: ' . $e->getMessage()
            ]);
        }
    }

    private function getWorstSector(int $year, int $activeFilial): array
    {
        $query = "
            SELECT s.nome, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY s.id, s.nome
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result ? [
            'nome' => $result['nome'],
            'total' => (int) $result['total_infracoes']
        ] : ['nome' => 'Nenhum dado', 'total' => 0];
    }

    private function getWorstEpis(int $year, int $activeFilial): array
    {
        $query = "
            SELECT e.nome, COUNT(oe.id) as total_infracoes
            FROM ocorrencias o
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY e.id, e.nome
            ORDER BY total_infracoes DESC
            LIMIT 5
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        $epis = [];
        while ($row = $result->fetch_assoc()) {
            $epis[] = [
                'nome' => $row['nome'],
                'total' => (int) $row['total_infracoes']
            ];
        }
        return $epis;
    }

    private function getWorstMonth(int $year, int $activeFilial): array
    {
        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        $query = "
            SELECT MONTH(o.data_hora) as mes, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY mes
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $mesNum = (int) $result['mes'];
            return [
                'nome' => $monthNames[$mesNum] ?? 'Desconhecido',
                'numero' => $mesNum,
                'total' => (int) $result['total_infracoes']
            ];
        }
        return ['nome' => 'Nenhum dado', 'numero' => 0, 'total' => 0];
    }

    private function getWorstDayOfWeek(int $year, int $activeFilial): array
    {
        $dayNames = [
            1 => 'Domingo', 2 => 'Segunda-feira', 3 => 'Terça-feira',
            4 => 'Quarta-feira', 5 => 'Quinta-feira', 6 => 'Sexta-feira', 7 => 'Sábado'
        ];

        $query = "
            SELECT DAYOFWEEK(o.data_hora) as dia_semana, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY dia_semana
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $diaNum = (int) $result['dia_semana'];
            return [
                'nome' => $dayNames[$diaNum] ?? 'Desconhecido',
                'total' => (int) $result['total_infracoes']
            ];
        }
        return ['nome' => 'Nenhum dado', 'total' => 0];
    }

    private function getSectorsRanking(int $year, int $activeFilial): array
    {
        $query = "
            SELECT s.nome, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY s.id, s.nome
            ORDER BY total_infracoes DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        $sectors = [];
        while ($row = $result->fetch_assoc()) {
            $sectors[] = [
                'nome' => $row['nome'],
                'total' => (int) $row['total_infracoes']
            ];
        }
        return $sectors;
    }

    private function getEpisRanking(int $year, int $activeFilial): array
    {
        $query = "
            SELECT e.nome, COUNT(oe.id) as total_infracoes
            FROM ocorrencias o
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY e.id, e.nome
            ORDER BY total_infracoes DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        $epis = [];
        while ($row = $result->fetch_assoc()) {
            $epis[] = [
                'nome' => $row['nome'],
                'total' => (int) $row['total_infracoes']
            ];
        }
        return $epis;
    }

    private function getMonthlyTotals(int $year, int $activeFilial): array
    {
        $query = "
            SELECT MONTH(o.data_hora) as mes, COUNT(o.id) as total
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? AND o.filial_id = ?
            GROUP BY mes
            ORDER BY mes
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $year, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        $totals = array_fill(1, 12, 0);
        while ($row = $result->fetch_assoc()) {
            $totals[(int)$row['mes']] = (int)$row['total'];
        }
        return $totals;
    }
    public function infractionsReport()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        try {
            $ids = $_GET['ids'] ?? '';
            $periodo = $_GET['periodo'] ?? 'todos';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $activeFilial = $_SESSION['active_filial_id'] ?? 1;

            if (empty($ids)) {
                echo json_encode(['success' => false, 'error' => 'Nenhum funcionário selecionado.']);
                return;
            }

            $idArray = explode(',', $ids);
            $idArray = array_map('intval', $idArray);
            $placeholders = implode(',', array_fill(0, count($idArray), '?'));

            // Construir filtro de data para as subqueries
            $dateFilter = "";
            $subParams = [];
            $subTypes = "";

            if ($periodo === 'hoje') {
                $dateFilter = " AND DATE(data_hora) = CURDATE()";
            } elseif ($periodo === 'semana') {
                $dateFilter = " AND YEARWEEK(data_hora, 1) = YEARWEEK(CURDATE(), 1)";
            } elseif ($periodo === 'mes') {
                $dateFilter = " AND MONTH(data_hora) = MONTH(CURDATE()) AND YEAR(data_hora) = YEAR(CURDATE())";
            } elseif ($periodo === 'personalizado' && !empty($dateFrom) && !empty($dateTo)) {
                $dateFilter = " AND DATE(data_hora) BETWEEN ? AND ?";
                $subParams[] = $dateFrom;
                $subParams[] = $dateTo;
                $subTypes .= "ss";
            }

            // Precisamos que as subqueries de contagem e natureza respeitem o período.
            // Removemos a trava estrita de filial para garantir que os dados de infração do colaborador apareçam,
            // independentemente de em qual unidade ele estava quando a infração ocorreu.
            $query = "
                SELECT 
                    f.nome as nome,
                    f.cpf as cpf,
                    f.id as funcionario_id,
                    f.turno as turno,
                    f.status as status_funcionario,
                    s.nome as departamento,
                    s.sigla as setor_sigla,
                    (SELECT COUNT(*) FROM ocorrencias o 
                     WHERE o.funcionario_id = f.id AND o.tipo = 'INFRACAO' $dateFilter) as total_infracoes,
                    (SELECT e.nome 
                     FROM ocorrencia_epis oe 
                     JOIN epis e ON oe.epi_id = e.id 
                     JOIN ocorrencias o2 ON oe.ocorrencia_id = o2.id 
                     WHERE o2.funcionario_id = f.id AND o2.tipo = 'INFRACAO' $dateFilter
                     GROUP BY e.id 
                     ORDER BY COUNT(*) DESC, e.nome ASC
                     LIMIT 1) as natureza,
                    (SELECT AVG(
                             TIMESTAMPDIFF(MINUTE, o3.data_hora, 
                                           COALESCE((SELECT MIN(data_hora) FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o3.id), NOW())
                             )
                           ) 
                     FROM ocorrencias o3 WHERE o3.funcionario_id = f.id AND o3.tipo = 'INFRACAO' $dateFilter) as media_minutos,
                    (SELECT MAX(o4.data_hora) FROM ocorrencias o4 WHERE o4.funcionario_id = f.id AND o4.tipo = 'INFRACAO' $dateFilter) as ultima_infracao
                FROM funcionarios f
                LEFT JOIN setores s ON f.setor_id = s.id
                WHERE f.id IN ($placeholders)
            ";

            $stmt = $this->db->prepare($query);
            
            // Parâmetros: [subParams (count), subParams (natureza), subParams (media), subParams (ultima), ids da lista principal]
            $allParams = array_merge($subParams, $subParams, $subParams, $subParams, $idArray);
            $allTypes = $subTypes . $subTypes . $subTypes . $subTypes . str_repeat('i', count($idArray));

            $stmt->bind_param($allTypes, ...$allParams);
            $stmt->execute();
            $result = $stmt->get_result();

            $data = [];
            while ($row = $result->fetch_assoc()) {
                // Formatting media
                $minutos = $row['media_minutos'] ? (int) $row['media_minutos'] : 0;
                if ($minutos > 0) {
                    $hours = floor($minutos / 60);
                    $mins = $minutos % 60;
                    $row['media_tempo'] = ($hours > 0 ? "{$hours}h " : "") . "{$mins}m";
                } else {
                    $row['media_tempo'] = '0m';
                }
                
                // Formatting ultima_infracao
                $row['ultima_infracao'] = $row['ultima_infracao'] ? date('d/m/Y', strtotime($row['ultima_infracao'])) : 'Nunca';

                // Garantir que campos não sejam nulos para o PDF
                $row['cpf'] = $row['cpf'] ?: '---';
                $row['departamento'] = $row['departamento'] ?: '---';
                $row['setor_sigla'] = $row['setor_sigla'] ?: '';
                $row['turno'] = $row['turno'] ?: '---';
                $row['status_funcionario'] = $row['status_funcionario'] ?: '---';
                $row['natureza'] = $row['natureza'] ?: '---';
                $data[] = $row;
            }

            $detailedQuery = "
                SELECT 
                    o.id as ocorrencia_id,
                    DATE_FORMAT(o.data_hora, '%d/%m/%Y %H:%i') as data_ocorrencia,
                    f.nome as funcionario_nome,
                    IFNULL(s.sigla, s.nome) as setor,
                    (SELECT GROUP_CONCAT(e.nome SEPARATOR ', ') FROM ocorrencia_epis oe JOIN epis e ON oe.epi_id = e.id WHERE oe.ocorrencia_id = o.id) as epis,
                    (SELECT ao.tipo FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id ORDER BY ao.data_hora DESC LIMIT 1) as acao_tomada,
                    (SELECT DATE_FORMAT(ao.data_hora, '%d/%m/%Y %H:%i') FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id ORDER BY ao.data_hora DESC LIMIT 1) as data_resolucao,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id) THEN 'Resolvido'
                        ELSE 'Pendente'
                    END as status_ocorrencia,
                    TIMESTAMPDIFF(MINUTE, o.data_hora, COALESCE((SELECT MIN(data_hora) FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id), NOW())) as minutos_sem_epi
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                WHERE o.funcionario_id IN ($placeholders) AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $dateFilter
                ORDER BY o.data_hora DESC
            ";

            $stmtDetail = $this->db->prepare($detailedQuery);
            $allParamsDetail = array_merge($idArray, $subParams);
            $allTypesDetail = str_repeat('i', count($idArray)) . $subTypes;
            $stmtDetail->bind_param($allTypesDetail, ...$allParamsDetail);
            $stmtDetail->execute();
            $resultDetail = $stmtDetail->get_result();
            $detailedData = [];
            
            while ($rowD = $resultDetail->fetch_assoc()) {
                $minutos = (int) $rowD['minutos_sem_epi'];
                if ($minutos > 0) {
                    $hours = floor($minutos / 60);
                    $mins = $minutos % 60;
                    $rowD['tempo_sem_epi'] = ($hours > 0 ? "{$hours}h " : "") . "{$mins}m";
                } else {
                    $rowD['tempo_sem_epi'] = '0m';
                }
                
                $acao_labels = [
                    'OBSERVACAO' => 'Orientação Técnica',
                    'ADVERTENCIA_VERBAL' => 'Adv. Verbal',
                    'ADVERTENCIA_ESCRITA' => 'Adv. Escrita',
                    'SUSPENSAO' => 'Suspensão'
                ];
                
                $rowD['acao_tomada'] = $rowD['acao_tomada'] ? ($acao_labels[$rowD['acao_tomada']] ?? $rowD['acao_tomada']) : '---';
                $rowD['data_resolucao'] = $rowD['data_resolucao'] ?: '---';
                $rowD['setor'] = $rowD['setor'] ?: '---';
                $rowD['epis'] = $rowD['epis'] ?: '---';
                
                $detailedData[] = $rowD;
            }

            echo json_encode(['success' => true, 'data' => $data, 'detailed' => $detailedData]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
