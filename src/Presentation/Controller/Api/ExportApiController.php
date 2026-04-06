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
        header('Content-Type: application/json');

        $year = (int) date('Y');

        $response = [
            'status' => 'success',
            'generated_at' => date('d/m/Y H:i'),
            'year' => $year,
            'worst_sector' => $this->getWorstSector($year),
            'worst_epis' => $this->getWorstEpis($year),
            'worst_month' => $this->getWorstMonth($year),
            'worst_day_of_week' => $this->getWorstDayOfWeek($year),
            'sectors_ranking' => $this->getSectorsRanking($year),
            'epis_ranking' => $this->getEpisRanking($year),
            'monthly_totals' => $this->getMonthlyTotals($year),
        ];

        echo json_encode($response);
    }

    private function getWorstSector(int $year): array
    {
        $query = "
            SELECT s.nome, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY s.id, s.nome
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result ? [
            'nome' => $result['nome'],
            'total' => (int) $result['total_infracoes']
        ] : ['nome' => 'Nenhum dado', 'total' => 0];
    }

    private function getWorstEpis(int $year): array
    {
        $query = "
            SELECT e.nome, COUNT(oe.id) as total_infracoes
            FROM ocorrencias o
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY e.id, e.nome
            ORDER BY total_infracoes DESC
            LIMIT 5
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
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

    private function getWorstMonth(int $year): array
    {
        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        $query = "
            SELECT MONTH(o.data_hora) as mes, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY mes
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
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

    private function getWorstDayOfWeek(int $year): array
    {
        $dayNames = [
            1 => 'Domingo', 2 => 'Segunda-feira', 3 => 'Terça-feira',
            4 => 'Quarta-feira', 5 => 'Quinta-feira', 6 => 'Sexta-feira', 7 => 'Sábado'
        ];

        $query = "
            SELECT DAYOFWEEK(o.data_hora) as dia_semana, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY dia_semana
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
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

    private function getSectorsRanking(int $year): array
    {
        $query = "
            SELECT s.nome, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN setores s ON f.setor_id = s.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY s.id, s.nome
            ORDER BY total_infracoes DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
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

    private function getEpisRanking(int $year): array
    {
        $query = "
            SELECT e.nome, COUNT(oe.id) as total_infracoes
            FROM ocorrencias o
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY e.id, e.nome
            ORDER BY total_infracoes DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
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

    private function getMonthlyTotals(int $year): array
    {
        $query = "
            SELECT MONTH(o.data_hora) as mes, COUNT(o.id) as total
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            GROUP BY mes
            ORDER BY mes
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $year);
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
        header('Content-Type: application/json; charset=utf-8');

        try {
            $ids = $_GET['ids'] ?? '';
            if (empty($ids)) {
                echo json_encode(['success' => false, 'error' => 'Nenhum funcionário selecionado.']);
                return;
            }

            $idArray = explode(',', $ids);
            $idArray = array_map('intval', $idArray);
            $placeholders = implode(',', array_fill(0, count($idArray), '?'));

            $query = "
                SELECT 
                    f.nome as nome,
                    f.id as funcionario_id,
                    s.nome as departamento,
                    (SELECT COUNT(*) FROM ocorrencias o WHERE o.funcionario_id = f.id AND o.tipo = 'INFRACAO') as total_infracoes,
                    (SELECT e.nome 
                     FROM ocorrencia_epis oe 
                     JOIN epis e ON oe.epi_id = e.id 
                     JOIN ocorrencias o2 ON oe.ocorrencia_id = o2.id 
                     WHERE o2.funcionario_id = f.id AND o2.tipo = 'INFRACAO'
                     GROUP BY e.id 
                     ORDER BY COUNT(*) DESC 
                     LIMIT 1) as natureza
                FROM funcionarios f
                LEFT JOIN setores s ON f.setor_id = s.id
                WHERE f.id IN ($placeholders)
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bind_param(str_repeat('i', count($idArray)), ...$idArray);
            $stmt->execute();
            $result = $stmt->get_result();

            $data = [];
            while ($row = $result->fetch_assoc()) {
                // Simulação de CPF (pois não está no DB ainda, usamos um padrão para que o usuário possa editar depois)
                $row['cpf'] = '***.' . rand(100, 999) . '.' . rand(100, 999) . '-**';
                $data[] = $row;
            }

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
