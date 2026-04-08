<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\Occurrence;
use epiGuard\Domain\Repository\OccurrenceRepositoryInterface;
use epiGuard\Domain\Repository\EmployeeRepositoryInterface;
use epiGuard\Domain\Repository\UserRepositoryInterface;
use epiGuard\Domain\Repository\EpiRepositoryInterface;
use epiGuard\Domain\ValueObject\OccurrenceStatus;
use epiGuard\Domain\ValueObject\OccurrenceType;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;
use DateTimeInterface;

class PostgreSQLOccurrenceRepository implements OccurrenceRepositoryInterface
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
        return [];
    }

    public function countDaily(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE o.data_hora::date = ? AND o.tipo = 'INFRACAO'";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }
        
        $stmt = $this->db->prepare($query);
        $dateStr = $date->format('Y-m-d');
        
        $params = [$dateStr];
        if (!empty($sectorIds)) {
            $params = array_merge($params, $sectorIds);
        }
        
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int) $res['total'];
    }

    public function countWeekly(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE to_char(o.data_hora, 'IYYYIW') = to_char(?::date, 'IYYYIW') AND o.tipo = 'INFRACAO'";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }
        
        $stmt = $this->db->prepare($query);
        $dateStr = $date->format('Y-m-d');
        
        $params = [$dateStr];
        if (!empty($sectorIds)) {
            $params = array_merge($params, $sectorIds);
        }
        
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int) $res['total'];
    }

    public function countMonthly(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE EXTRACT(MONTH FROM o.data_hora) = EXTRACT(MONTH FROM ?::date) 
                  AND EXTRACT(YEAR FROM o.data_hora) = EXTRACT(YEAR FROM ?::date) AND o.tipo = 'INFRACAO'";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }
        
        $stmt = $this->db->prepare($query);
        $dateStr = $date->format('Y-m-d');
        
        $params = [$dateStr, $dateStr];
        if (!empty($sectorIds)) {
            $params = array_merge($params, $sectorIds);
        }
        
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int) $res['total'];
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
            'capacete' => array_fill(0, 12, 0),
            'oculos' => array_fill(0, 12, 0),
            'jaqueta' => array_fill(0, 12, 0),
            'avental' => array_fill(0, 12, 0),
            'luvas' => array_fill(0, 12, 0),
            'mascara' => array_fill(0, 12, 0),
            'protetor' => array_fill(0, 12, 0),
            'total' => array_fill(0, 12, 0)
        ];

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

        $query = "
            SELECT 
                EXTRACT(MONTH FROM o.data_hora)::int as mes,
                e.nome as epi_nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE EXTRACT(YEAR FROM o.data_hora) = ? AND o.tipo = 'INFRACAO'
            $sectorClause
            $epiClause
            GROUP BY mes, epi_nome
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute($epiParams);

        while ($row = $stmt->fetch()) {
            $mesIdx = (int) $row['mes'] - 1;
            if ($mesIdx < 0 || $mesIdx >= 12) continue;
            
            $nome = strtolower($row['epi_nome']);
            if (str_contains($nome, 'capacete')) {
                $stats['capacete'][$mesIdx] += (int) $row['total'];
            } elseif (str_contains($nome, 'oculos') || str_contains($nome, 'óculos')) {
                $stats['oculos'][$mesIdx] += (int) $row['total'];
            } elseif (str_contains($nome, 'jaqueta')) {
                $stats['jaqueta'][$mesIdx] += (int) $row['total'];
            } elseif (str_contains($nome, 'avental')) {
                $stats['avental'][$mesIdx] += (int) $row['total'];
            } elseif (str_contains($nome, 'luva')) {
                $stats['luvas'][$mesIdx] += (int) $row['total'];
            } elseif (str_contains($nome, 'mascara') || str_contains($nome, 'máscara')) {
                $stats['mascara'][$mesIdx] += (int) $row['total'];
            } elseif (str_contains($nome, 'protetor')) {
                $stats['protetor'][$mesIdx] += (int) $row['total'];
            }
        }

        $queryTotal = "
            SELECT EXTRACT(MONTH FROM o.data_hora)::int as mes, COUNT(*) as qtd
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE EXTRACT(YEAR FROM o.data_hora) = ? AND o.tipo = 'INFRACAO'
            $sectorClause
            GROUP BY mes
        ";
        
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

        return [
            'stats' => $stats,
            'allowed_epis' => $allowedEpiNames
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

        $query = "
            SELECT 
                e.nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND EXTRACT(YEAR FROM o.data_hora) = ?
            $sectorClause
            $epiClause
            GROUP BY e.nome
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $labels = [];
        $data = [];
        $totalSum = 0;

        while ($row = $stmt->fetch()) {
            $labels[] = $row['nome'];
            $data[] = (int) $row['total'];
            $totalSum += (int) $row['total'];
        }

        if (empty($labels) || $totalSum === 0) {
            return [
                'labels' => [],
                'data' => [],
                'total' => 0
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => $totalSum
        ];
    }

    public function save(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("INSERT INTO ocorrencias (funcionario_id, tipo, data_hora) VALUES (?, ?, ?)");
        $params = [
            $occurrence->getEmployee()->getId(),
            $occurrence->getType()->getValue(),
            $occurrence->getDate()->format('Y-m-d H:i:s')
        ];
        $stmt->execute($params);
        $occurrence->setId((int) $this->db->lastInsertId());
    }

    public function update(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("UPDATE ocorrencias SET funcionario_id = ?, tipo = ?, data_hora = ? WHERE id = ?");
        $params = [
            $occurrence->getEmployee()->getId(),
            $occurrence->getType()->getValue(),
            $occurrence->getDate()->format('Y-m-d H:i:s'),
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
        $stmt = $this->db->prepare("UPDATE ocorrencias SET favorito = NOT favorito WHERE id = ?");
        $stmt->execute([$id]);

        $stmt2 = $this->db->prepare("SELECT favorito FROM ocorrencias WHERE id = ?");
        $stmt2->execute([$id]);
        $row = $stmt2->fetch();
        return ['success' => true, 'favorito' => (bool) $row['favorito']];
    }

    public function findInfractions(array $filters = []): array
    {
        $sql = "SELECT o.id, o.data_hora, o.tipo, o.favorito, f.id as funcionario_id, f.nome as funcionario_nome, f.foto_referencia as funcionario_foto, s.id as setor_id, s.sigla as setor_sigla, s.nome as setor_nome, e.id as epi_id, e.nome as epi_nome, o.criado_em,
                (SELECT ev.caminho_imagem FROM evidencias ev WHERE ev.ocorrencia_id = o.id LIMIT 1) as evidencia_foto,
                (CASE WHEN EXISTS (SELECT 1 FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id) THEN 'resolvido' ELSE 'pendente' END) as status
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                WHERE o.tipo = 'INFRACAO' AND o.oculto = FALSE";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (f.nome ILIKE ? OR s.sigla ILIKE ? OR s.nome ILIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['epi']) && $filters['epi'] !== 'todos') {
            $sql .= " AND e.nome ILIKE ?";
            $params[] = "%" . $filters['epi'] . "%";
        }

        if (!empty($filters['periodo']) && $filters['periodo'] !== 'todos') {
            if ($filters['periodo'] === 'hoje') {
                $sql .= " AND o.data_hora::date = CURRENT_DATE";
            } elseif ($filters['periodo'] === 'semana') {
                $sql .= " AND to_char(o.data_hora, 'IYYYIW') = to_char(CURRENT_DATE, 'IYYYIW')";
            } elseif ($filters['periodo'] === 'mes') {
                $sql .= " AND EXTRACT(MONTH FROM o.data_hora) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM o.data_hora) = EXTRACT(YEAR FROM CURRENT_DATE)";
            } elseif ($filters['periodo'] === 'personalizado' && !empty($filters['data_inicio']) && !empty($filters['data_fim'])) {
                $sql .= " AND o.data_hora::date BETWEEN ? AND ?";
                $params[] = $filters['data_inicio'];
                $params[] = $filters['data_fim'];
            }
        }

        $orderBy = "o.favorito DESC, o.data_hora DESC";
        if (!empty($filters['ordenacao'])) {
            if ($filters['ordenacao'] === 'alfabetica') {
                $orderBy = "o.favorito DESC, f.nome ASC";
            } elseif ($filters['ordenacao'] === 'tempo') {
                $orderBy = "o.favorito DESC, o.data_hora DESC";
            } elseif ($filters['ordenacao'] === 'frequente') {
                $orderBy = "o.favorito DESC, (SELECT COUNT(*) FROM ocorrencias o2 WHERE o2.funcionario_id = f.id AND o2.tipo = 'INFRACAO') DESC, o.data_hora DESC";
            }
        }

        $sql .= " ORDER BY " . $orderBy;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findNewInfractions(int $lastId): array
    {
        $params = [];
        if ($lastId === -1) {
            $where = "o.data_hora >= NOW() - INTERVAL '24 hours'";
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
                WHERE {$where} AND o.tipo = 'INFRACAO' AND o.oculto = FALSE
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
            createdAt: new DateTimeImmutable($row['criado_em'])
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
            $normalizedSlug = strtolower($slug);
            $fuzzySlug = str_replace(['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú'], ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u'], $normalizedSlug);
            
            foreach ($allEpiNames as $fullName) {
                $normalizedName = strtolower($fullName);
                $fuzzyName = str_replace(['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú'], ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u'], $normalizedName);

                if (str_contains($fuzzyName, $fuzzySlug) || str_contains($fuzzySlug, $fuzzyName)) {
                    $resolved[] = $fullName;
                }
            }
        }

        return array_unique($resolved);
    }
}

