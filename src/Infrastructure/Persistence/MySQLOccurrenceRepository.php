<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Occurrence;
use Facchini\Domain\Repository\OccurrenceRepositoryInterface;
use Facchini\Domain\Repository\EmployeeRepositoryInterface;
use Facchini\Domain\Repository\UserRepositoryInterface;
use Facchini\Domain\Repository\EpiRepositoryInterface;
use Facchini\Domain\ValueObject\OccurrenceStatus;
use Facchini\Domain\ValueObject\OccurrenceType;
use Facchini\Infrastructure\Database\Connection;
use DateTimeImmutable;
use DateTimeInterface;

class MySQLOccurrenceRepository implements OccurrenceRepositoryInterface
{
    private \PDO $db;
    private EmployeeRepositoryInterface $employeeRepository;
    private UserRepositoryInterface $userRepository;
    private EpiRepositoryInterface $epiRepository;

    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        UserRepositoryInterface $userRepository,
        EpiRepositoryInterface $epiRepository
    ) {
        $this->db = Connection::getInstance();
        $this->employeeRepository = $employeeRepository;
        $this->userRepository = $userRepository;
        $this->epiRepository = $epiRepository;
    }

    public function findById(int $id): ?Occurrence
    {
        $stmt = $this->db->prepare("SELECT * FROM ocorrencias WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return Occurrence[] */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM ocorrencias ORDER BY data_hora DESC");
        $occurrences = [];
        while ($row = $stmt->fetch()) {
            $occurrences[] = $this->hydrate($row);
        }
        return $occurrences;
    }

    public function findByEmployeeId(int $employeeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM ocorrencias WHERE funcionario_id = ? ORDER BY data_hora DESC");
        $stmt->execute([$employeeId]);
        
        $occurrences = [];
        while ($row = $stmt->fetch()) {
            $occurrences[] = $this->hydrate($row);
        }
        return $occurrences;
    }

    public function findByStatus(string $status): array
    {
        // Método não utilizado no fluxo atual do dashboard
        return [];
    }

    private function genericCount(
        string $period, 
        ?\DateTimeInterface $date = null, 
        ?array $sectorIds = null, 
        bool $uniqueOnly = false,
        ?\DateTimeInterface $end = null,
        int $filialId = 1
    ): int {
        $select = $uniqueOnly ? "COUNT(DISTINCT o.funcionario_id)" : "COUNT(*)";
        $query = "SELECT $select as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND o.filial_id = ?";
        
        $params = [$filialId];

        switch ($period) {
            case 'daily':
                // MySQL
                $query .= " AND DATE(o.data_hora) = ?";
                /*
                // PostgreSQL (Commented)
                $query .= " AND o.data_hora::date = ?";
                */
                $params[] = $date->format('Y-m-d');
                break;
            case 'weekly':
                // MySQL
                $query .= " AND YEARWEEK(o.data_hora, 3) = YEARWEEK(?, 3)";
                /*
                // PostgreSQL (Commented)
                $query .= " AND to_char(o.data_hora, 'IYYYIW') = to_char(?::date, 'IYYYIW')";
                */
                $params[] = $date->format('Y-m-d');
                break;
            case 'monthly':
                // MySQL
                $query .= " AND MONTH(o.data_hora) = MONTH(?) AND YEAR(o.data_hora) = YEAR(?)";
                /*
                // PostgreSQL (Commented)
                $query .= " AND EXTRACT(MONTH FROM o.data_hora) = EXTRACT(MONTH FROM ?::date) AND EXTRACT(YEAR FROM o.data_hora) = EXTRACT(YEAR FROM ?::date)";
                */
                $params[] = $date->format('Y-m-d');
                $params[] = $date->format('Y-m-d');
                break;
            case 'yearly':
                // MySQL
                $query .= " AND YEAR(o.data_hora) = YEAR(?)";
                /*
                // PostgreSQL (Commented)
                $query .= " AND EXTRACT(YEAR FROM o.data_hora) = EXTRACT(YEAR FROM ?::date)";
                */
                $params[] = $date->format('Y-m-d');
                break;
            case 'range':
                // MySQL
                $query .= " AND DATE(o.data_hora) BETWEEN ? AND ?";
                /*
                // PostgreSQL (Commented)
                $query .= " AND o.data_hora::date BETWEEN ? AND ?";
                */
                $params[] = $date->format('Y-m-d');
                $params[] = $end->format('Y-m-d');
                break;
        }

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
            $params = array_merge($params, $sectorIds);
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countDaily(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('daily', $date, $sectorIds, false, null, $filialId);
    }

    public function countWeekly(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('weekly', $date, $sectorIds, false, null, $filialId);
    }

    public function countMonthly(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('monthly', $date, $sectorIds, false, null, $filialId);
    }

    public function countRange(\DateTimeInterface $start, \DateTimeInterface $end, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('range', $start, $sectorIds, false, $end, $filialId);
    }

    public function countUniqueStudentsDaily(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('daily', $date, $sectorIds, true, null, $filialId);
    }

    public function countUniqueStudentsWeekly(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('weekly', $date, $sectorIds, true, null, $filialId);
    }

    public function countUniqueStudentsMonthly(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('monthly', $date, $sectorIds, true, null, $filialId);
    }

    public function countUniqueStudentsYearly(\DateTimeInterface $date, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('yearly', $date, $sectorIds, true, null, $filialId);
    }

    public function countUniqueStudentsRange(\DateTimeInterface $start, \DateTimeInterface $end, ?array $sectorIds = null, int $filialId = 1): int
    {
        return $this->genericCount('range', $start, $sectorIds, true, $end, $filialId);
    }

    public function getMonthlyInfractionStats(int $year, ?array $sectorIds = null): array
    {
        $allowedEpiNames = [];

        if (!empty($sectorIds)) {
            $allowedEpiNames = $this->resolveEpiSlugsToNames($sectorIds);
        } else {
            $stmt = $this->db->query("SELECT nome FROM epis WHERE status = 'ATIVO'");
            while ($row = $stmt->fetch()) {
                $allowedEpiNames[] = $row['nome'];
            }
        }

        $stats = [
            'total' => array_fill(0, 12, 0)
        ];

        foreach ($allowedEpiNames as $name) {
            $stats[$name] = array_fill(0, 12, 0);
        }

        $sectorClause = "";
        $epiClause = "";
        $params = [$year];

        if (!empty($sectorIds)) {
            $sPlaceholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $sectorClause = " AND f.setor_id IN ($sPlaceholders)";
            $params = array_merge($params, $sectorIds);
        }

        $epiParams = $params;

        if (!empty($allowedEpiNames)) {
            $ePlaceholders = implode(',', array_fill(0, count($allowedEpiNames), '?'));
            $epiClause = " AND e.nome IN ($ePlaceholders)";
            $epiParams = array_merge($epiParams, $allowedEpiNames);
        }

        // MySQL Version
        $query = "
            SELECT 
                MONTH(o.data_hora) as mes,
                e.nome as epi_nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE YEAR(o.data_hora) = ? AND o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL)
            $sectorClause
            $epiClause
            GROUP BY mes, epi_nome
        ";

        /*
        // PostgreSQL (Commented)
        $query = "
            SELECT 
                EXTRACT(MONTH FROM o.data_hora)::int as mes,
                e.nome as epi_nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE EXTRACT(YEAR FROM o.data_hora) = ? AND o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL)
            $sectorClause
            $epiClause
            GROUP BY mes, epi_nome
        ";
        */

        $stmt = $this->db->prepare($query);
        $stmt->execute($epiParams);

        while ($row = $stmt->fetch()) {
            $mesIdx = (int) $row['mes'] - 1;
            if ($mesIdx < 0 || $mesIdx >= 12) continue;
            
            $nome = $row['epi_nome'];
            if (isset($stats[$nome])) {
                $stats[$nome][$mesIdx] += (int) $row['total'];
            }
        }

        // MySQL Version
        $queryTotal = "
            SELECT MONTH(o.data_hora) as mes, COUNT(*) as qtd
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE YEAR(o.data_hora) = ? AND o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL)
            $sectorClause
            GROUP BY mes
        ";
        
        /*
        // PostgreSQL (Commented)
        $queryTotal = "
            SELECT EXTRACT(MONTH FROM o.data_hora)::int as mes, COUNT(*) as qtd
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE EXTRACT(YEAR FROM o.data_hora) = ? AND o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL)
            $sectorClause
            GROUP BY mes
        ";
        */
        
        $totalParams = [$year];
        if (!empty($sectorIds)) {
            $totalParams = array_merge($totalParams, $sectorIds);
        }

        $stmtTotal = $this->db->prepare($queryTotal);
        $stmtTotal->execute($totalParams);
        
        while ($row = $stmtTotal->fetch()) {
            $mesIdx = (int) $row['mes'] - 1;
            if ($mesIdx >= 0 && $mesIdx < 12) {
                $stats['total'][$mesIdx] = (int) $row['qtd'];
            }
        }

        // Recuperar cores dos EPIs
        $epiColors = [];
        $epiStmt = $this->db->query("SELECT nome, cor FROM epis WHERE status = 'ATIVO'");
        while ($row = $epiStmt->fetch()) {
            $epiColors[$row['nome']] = $row['cor'];
        }
        
        // Recuperar a cor configurada para o gráfico Total
        $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
        $totalColorStmt = $this->db->prepare("SELECT cor_grafico_total FROM filiais WHERE id = ?");
        $totalColorStmt->execute([$activeFilialId]);
        $totalColor = $totalColorStmt->fetchColumn();
        $epiColors['Total'] = $totalColor ?: '#10B981';

        return [
            'stats' => $stats,
            'allowed_epis' => $allowedEpiNames,
            'epi_colors' => $epiColors
        ];
    }

    public function getInfractionDistributionByEpi(?array $sectorIds = null): array
    {
        $year = (int) date('Y');
        $allowedEpiNames = [];

        if (!empty($sectorIds)) {
            $allowedEpiNames = $this->resolveEpiSlugsToNames($sectorIds);

            if (empty($allowedEpiNames)) {
                return [
                    'labels' => [], 
                    'data' => [], 
                    'total' => 0,
                    'no_epis_configured' => true
                ];
            }
        }

        $sectorClause = "";
        $epiClause = "";
        $params = [$year];

        if (!empty($sectorIds)) {
            $sPlaceholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $sectorClause = " AND f.setor_id IN ($sPlaceholders)";
            $params = array_merge($params, $sectorIds);
        }

        if (!empty($allowedEpiNames)) {
            $ePlaceholders = implode(',', array_fill(0, count($allowedEpiNames), '?'));
            $epiClause = " AND e.nome IN ($ePlaceholders)";
            $params = array_merge($params, $allowedEpiNames);
        }

        // MySQL Version
        $query = "
            SELECT 
                e.nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND YEAR(o.data_hora) = ?
            $sectorClause
            $epiClause
            GROUP BY e.nome
        ";

        /*
        // PostgreSQL (Commented)
        $query = "
            SELECT 
                e.nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND EXTRACT(YEAR FROM o.data_hora) = ?
            $sectorClause
            $epiClause
            GROUP BY e.nome
        ";
        */

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $labels = [];
        $data = [];
        $colors = [];
        $totalSum = 0;

        while ($row = $stmt->fetch()) {
            $labels[] = $row['nome'];
            $data[] = (int) $row['total'];
            
            // Buscar cor do EPI
            $cStmt = $this->db->prepare("SELECT cor FROM epis WHERE nome = ? LIMIT 1");
            $cStmt->execute([$row['nome']]);
            $colors[] = $cStmt->fetchColumn() ?: '#E30613';
            
            $totalSum += (int) $row['total'];
        }

        if (empty($labels) || $totalSum === 0) {
            return [
                'labels' => [],
                'data' => [],
                'colors' => [],
                'total' => 0
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'total' => $totalSum
        ];
    }

    public function save(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("INSERT INTO ocorrencias (funcionario_id, tipo, data_hora, foto_evidencia) VALUES (?, ?, ?, ?)");
        $params = [
            $occurrence->getEmployee()->getId(),
            $occurrence->getType()->getValue(),
            $occurrence->getDate()->format('Y-m-d H:i:s'),
            $occurrence->getPrimaryEvidencePath()
        ];
        $stmt->execute($params);
        $occurrence->setId((int) $this->db->lastInsertId());
    }

    public function update(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("UPDATE ocorrencias SET funcionario_id = ?, tipo = ?, data_hora = ?, foto_evidencia = ? WHERE id = ?");
        $params = [
            $occurrence->getEmployee()->getId(),
            $occurrence->getType()->getValue(),
            $occurrence->getDate()->format('Y-m-d H:i:s'),
            $occurrence->getPrimaryEvidencePath(),
            $occurrence->getId()
        ];
        $stmt->execute($params);
    }

    public function delete(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("DELETE FROM ocorrencias WHERE id = ?");
        $stmt->execute([$occurrence->getId()]);
    }

    public function hide(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE ocorrencias SET oculto = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleFavorite(int $id): array
    {
        // MySQL/MariaDB standard: FAVORITO = NOT FAVORITO usually works if it's a boolean-like column, but for clarity:
        $stmt = $this->db->prepare("UPDATE ocorrencias SET favorito = CASE WHEN favorito = 1 THEN 0 ELSE 1 END WHERE id = ?");
        /*
        // PostgreSQL (Commented)
        $stmt = $this->db->prepare("UPDATE ocorrencias SET favorito = NOT favorito WHERE id = ?");
        */
        $stmt->execute([$id]);

        $stmt2 = $this->db->prepare("SELECT favorito FROM ocorrencias WHERE id = ?");
        $stmt2->execute([$id]);
        $row = $stmt2->fetch();
        return ['success' => true, 'favorito' => (bool) $row['favorito']];
    }

    public function findInfractions(array $filters = []): array
    {
        $sql = "SELECT o.id, o.data_hora, o.tipo, o.favorito, f.id as funcionario_id, f.nome as funcionario_nome, f.foto_referencia as funcionario_foto, s.id as setor_id, s.sigla as setor_sigla, s.nome as setor_nome, e.id as epi_id, e.nome as epi_nome, o.criado_em,
                o.foto_evidencia as evidencia_foto,
                (CASE WHEN EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id) THEN 'resolvido' ELSE 'pendente' END) as status
                FROM ocorrencias o
                LEFT JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                WHERE o.tipo = 'INFRACAO'";

        $params = [];

        // Filtro de Visibilidade (Oculto)
        if (!empty($filters['status']) && $filters['status'] === 'inativo') {
            $sql .= " AND o.oculto = 1";
            /* PostgreSQL: o.oculto = TRUE */
        } else {
            $sql .= " AND (o.oculto = 0 OR o.oculto IS NULL)";
            /* PostgreSQL: (o.oculto = FALSE OR o.oculto IS NULL) */
        }
        
        if (!empty($filters['funcionario_id']) && is_numeric($filters['funcionario_id'])) {
            $sql .= " AND f.id = ?";
            $params[] = $filters['funcionario_id'];
        }

        if (!empty($filters['filial_id'])) {
            $sql .= " AND o.filial_id = ?";
            $params[] = $filters['filial_id'];
        }

        if (!empty($filters['search'])) {
            // MySQL: LIKE instead of ILIKE (MySQL is generally case-insensitive unless using binary collation)
            $sql .= " AND (f.nome LIKE ? OR s.sigla LIKE ? OR s.nome LIKE ?)";
            /* PostgreSQL: (f.nome ILIKE ? OR s.sigla ILIKE ? OR s.nome ILIKE ?) */
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['epi']) && $filters['epi'] !== 'todos') {
            $sql .= " AND e.nome LIKE ?";
            $params[] = "%" . $filters['epi'] . "%";
        }

        if (!empty($filters['periodo']) && $filters['periodo'] !== 'todos') {
            if ($filters['periodo'] === 'hoje') {
                // MySQL
                $sql .= " AND DATE(o.data_hora) = CURRENT_DATE";
                /* PostgreSQL: $sql .= " AND o.data_hora::date = CURRENT_DATE"; */
            } elseif ($filters['periodo'] === 'semana') {
                // MySQL
                $sql .= " AND YEARWEEK(o.data_hora, 3) = YEARWEEK(CURRENT_DATE, 3)";
                /* PostgreSQL: $sql .= " AND to_char(o.data_hora, 'IYYYIW') = to_char(CURRENT_DATE, 'IYYYIW')"; */
            } elseif ($filters['periodo'] === 'mes') {
                // MySQL
                $sql .= " AND MONTH(o.data_hora) = MONTH(CURRENT_DATE) AND YEAR(o.data_hora) = YEAR(CURRENT_DATE)";
                /* PostgreSQL: $sql .= " AND EXTRACT(MONTH FROM o.data_hora) = ..."; */
            } elseif ($filters['periodo'] === 'personalizado') {
                $start = $filters['date_from'] ?? ($filters['data_inicio'] ?? null);
                $end = $filters['date_to'] ?? ($filters['data_fim'] ?? null);
                if ($start && $end) {
                    // MySQL
                    $sql .= " AND DATE(o.data_hora) BETWEEN ? AND ?";
                    /* PostgreSQL: $sql .= " AND o.data_hora::date BETWEEN ? AND ?"; */
                    $params[] = $start;
                    $params[] = $end;
                }
            }
        }

        if (!empty($filters['status']) && $filters['status'] !== 'todos' && $filters['status'] !== 'inativo') {
            if ($filters['status'] === 'pendente') {
                $sql .= " AND NOT EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id)";
            } elseif ($filters['status'] === 'resolvido') {
                $sql .= " AND EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id)";
            }
        }

        $orderBy = "o.favorito DESC, o.data_hora DESC";
        if (!empty($filters['order'])) {
            if ($filters['order'] === 'alfabetica') {
                $orderBy = "o.favorito DESC, f.nome ASC";
            } elseif ($filters['order'] === 'frequentes') {
                $orderBy = "o.favorito DESC, (SELECT COUNT(*) FROM ocorrencias o2 WHERE o2.funcionario_id = f.id AND o2.tipo = 'INFRACAO') DESC, o.data_hora DESC";
            }
        }

        $sql .= " ORDER BY " . $orderBy;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getWorstMonth(?array $sectorIds = null): array
    {
        // MySQL Version
        $query = "
            SELECT 
                MONTH(o.data_hora) as mes,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND YEAR(o.data_hora) = YEAR(CURRENT_DATE)
        ";
        /*
        // PostgreSQL (Commented)
        $query = "
            SELECT 
                EXTRACT(MONTH FROM o.data_hora)::int as mes,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND EXTRACT(YEAR FROM o.data_hora) = EXTRACT(YEAR FROM CURRENT_DATE)
        ";
        */
        
        $params = [];
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
            $params = $sectorIds;
        }
        
        $query .= " GROUP BY mes ORDER BY total DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch();
        
        if (!$row) return ['month' => date('n'), 'count' => 0];
        return ['month' => (int) $row['mes'], 'count' => (int) $row['total']];
    }

    public function findNewInfractions(int $lastId): array
    {
        $params = [];
        if ($lastId === -1) {
            // MySQL
            $where = "o.data_hora >= NOW() - INTERVAL 24 HOUR";
            /* PostgreSQL: $where = "o.data_hora >= NOW() - INTERVAL '24 hours'"; */
        } else {
            $where = "o.id > ?";
            $params[] = $lastId;
        }

        $sql = "SELECT o.id, f.nome as funcionario_nome, s.sigla as setor_sigla, e.nome as epi_nome, o.data_hora
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                WHERE {$where} AND o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL)
                ORDER BY o.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    private function hydrate(array $row): Occurrence
    {
        $employee = $this->employeeRepository->findById((int) $row['funcionario_id']);
        $user = $this->userRepository->findById(1); 
        
        $stmt = $this->db->prepare("SELECT epi_id FROM ocorrencia_epis WHERE ocorrencia_id = ? LIMIT 1");
        $stmt->execute([$row['id']]);
        $epiRow = $stmt->fetch();
        $epi = $epiRow ? $this->epiRepository->findById((int) $epiRow['epi_id']) : null;

        return new Occurrence(
            employee: $employee,
            registeredBy: $user,
            epiItem: $epi,
            type: new OccurrenceType($row['tipo']),
            description: "Ocorrência registrada via sistema",
            date: new DateTimeImmutable($row['data_hora']),
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em']),
            primaryEvidencePath: $row['foto_evidencia'] ?? null
        );
    }

    private function resolveEpiSlugsToNames(array $sectorIds): array
    {
        $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
        $stmt = $this->db->prepare("SELECT epis_json FROM setores WHERE id IN ($placeholders)");
        $stmt->execute($sectorIds);
        
        $slugs = [];
        while ($row = $stmt->fetch()) {
            if (!empty($row['epis_json'])) {
                $json = json_decode($row['epis_json'], true) ?: [];
                foreach ($json as $epi) {
                    if (is_string($epi)) $slugs[] = $epi;
                    elseif (isset($epi['nome'])) $slugs[] = $epi['nome'];
                }
            }
        }
        $slugs = array_unique($slugs);
        if (empty($slugs)) return [];

        $epiStmt = $this->db->query("SELECT nome FROM epis WHERE status = 'ATIVO'");
        $allEpiNames = [];
        while($row = $epiStmt->fetch()) {
            $allEpiNames[] = $row['nome'];
        }

        $resolved = [];
        foreach ($slugs as $slug) {
            $normalizedSlug = mb_strtolower($slug, 'UTF-8');
            $fuzzySlug = str_replace(['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú', 'ç'], ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u', 'c'], $normalizedSlug);
            
            foreach ($allEpiNames as $fullName) {
                $normalizedName = mb_strtolower($fullName, 'UTF-8');
                $fuzzyName = str_replace(['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú', 'ç'], ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u', 'c'], $normalizedName);

                if (str_contains($fuzzyName, $fuzzySlug) || str_contains($fuzzySlug, $fuzzyName)) {
                    $resolved[] = $fullName;
                }
            }
        }

        return array_unique($resolved);
    }
}
