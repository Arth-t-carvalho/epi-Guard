<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Database\Connection;

class ExportApiController
{
    private \PDO $db;

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
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY s.id, s.nome
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);
        $result = $stmt->fetch();

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
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY e.id, e.nome
            ORDER BY total_infracoes DESC
            LIMIT 5
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);

        $epis = [];
        while ($row = $stmt->fetch()) {
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
            SELECT EXTRACT(MONTH FROM o.data_hora)::int as mes, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY mes
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);
        $result = $stmt->fetch();

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

        // EXTRACT(DOW FROM ...) retorna 0 para Domingo em PG.
        $query = "
            SELECT (EXTRACT(DOW FROM o.data_hora)::int + 1) as dia_semana, COUNT(o.id) as total_infracoes
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY dia_semana
            ORDER BY total_infracoes DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);
        $result = $stmt->fetch();

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
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY s.id, s.nome
            ORDER BY total_infracoes DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);

        $sectors = [];
        while ($row = $stmt->fetch()) {
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
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY e.id, e.nome
            ORDER BY total_infracoes DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);

        $epis = [];
        while ($row = $stmt->fetch()) {
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
            SELECT EXTRACT(MONTH FROM o.data_hora)::int as mes, COUNT(o.id) as total
            FROM ocorrencias o
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            GROUP BY mes
            ORDER BY mes
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);

        $totals = array_fill(1, 12, 0);
        while ($row = $stmt->fetch()) {
            $totals[(int)$row['mes']] = (int)$row['total'];
        }
        return $totals;
    }
}

