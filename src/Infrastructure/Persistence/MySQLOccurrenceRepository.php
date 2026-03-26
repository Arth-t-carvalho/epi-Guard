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

class MySQLOccurrenceRepository implements OccurrenceRepositoryInterface
{
    private \mysqli $db;
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
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return Occurrence[] */
    public function findAll(): array
    {
        $result = $this->db->query("SELECT * FROM ocorrencias ORDER BY data_hora DESC");
        $occurrences = [];
        while ($row = $result->fetch_assoc()) {
            $occurrences[] = $this->hydrate($row);
        }
        return $occurrences;
    }

    public function findByEmployeeId(int $employeeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM ocorrencias WHERE funcionario_id = ? ORDER BY data_hora DESC");
        $stmt->bind_param('i', $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $occurrences = [];
        while ($row = $result->fetch_assoc()) {
            $occurrences[] = $this->hydrate($row);
        }
        return $occurrences;
    }

    public function findByStatus(string $status): array
    {
        // Nota: A tabela ocorrencias no schema.sql não tem campo 'status' explicitamente,
        // mas o Domain sugere. No schema simplificado, o 'tipo' filtra os dados.
        // Implementação simplificada para evitar erros de SQL se o campo não existir.
        return [];
    }

    public function countDaily(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE DATE(o.data_hora) = ? AND o.tipo = 'INFRACAO'";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }
        
        $stmt = $this->db->prepare($query);
        $dateStr = $date->format('Y-m-d');
        
        if (!empty($sectorIds)) {
            $types = 's' . str_repeat('i', count($sectorIds));
            $params = array_merge([$dateStr], $sectorIds);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('s', $dateStr);
        }
        
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int) $res['total'];
    }

    public function countWeekly(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE YEARWEEK(o.data_hora, 1) = YEARWEEK(?, 1) AND o.tipo = 'INFRACAO'";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }
        
        $stmt = $this->db->prepare($query);
        $dateStr = $date->format('Y-m-d');
        
        if (!empty($sectorIds)) {
            $types = 's' . str_repeat('i', count($sectorIds));
            $params = array_merge([$dateStr], $sectorIds);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('s', $dateStr);
        }
        
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int) $res['total'];
    }

    public function countMonthly(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE MONTH(o.data_hora) = MONTH(?) AND YEAR(o.data_hora) = YEAR(?) AND o.tipo = 'INFRACAO'";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }
        
        $stmt = $this->db->prepare($query);
        $dateStr = $date->format('Y-m-d');
        
        if (!empty($sectorIds)) {
            $types = 'ss' . str_repeat('i', count($sectorIds));
            $params = array_merge([$dateStr, $dateStr], $sectorIds);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('ss', $dateStr, $dateStr);
        }
        
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int) $res['total'];
    }

    public function getMonthlyInfractionStats(int $year, ?array $sectorIds = null): array
    {
        $year = (int) $year;
        $allowedEpiNames = [];

        if (!empty($sectorIds)) {
            $allowedEpiNames = $this->resolveEpiSlugsToNames($sectorIds);
        } else {
            // For Global view, include all active EPI names so legends appear
            $res = $this->db->query("SELECT nome FROM epis WHERE status = 'ATIVO'");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $allowedEpiNames[] = $row['nome'];
                }
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
        $types = "i";
        $params = [$year];

        if (!empty($sectorIds)) {
            $sPlaceholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $sectorClause = " AND f.setor_id IN ($sPlaceholders)";
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }

        $epiTypes = $types;
        $epiParams = $params;

        if (!empty($allowedEpiNames)) {
            $ePlaceholders = implode(',', array_fill(0, count($allowedEpiNames), '?'));
            $epiClause = " AND e.nome IN ($ePlaceholders)";
            $epiTypes .= str_repeat('s', count($allowedEpiNames));
            $epiParams = array_merge($epiParams, $allowedEpiNames);
        }

        $query = "
            SELECT 
                MONTH(o.data_hora) as mes,
                e.nome as epi_nome,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE YEAR(o.data_hora) = ? AND o.tipo = 'INFRACAO'
            $sectorClause
            $epiClause
            GROUP BY mes, epi_nome
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($epiTypes, ...$epiParams);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
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
            SELECT MONTH(o.data_hora) as mes, COUNT(*) as qtd
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE YEAR(o.data_hora) = ? AND o.tipo = 'INFRACAO'
            $sectorClause
            GROUP BY mes
        ";
        
        $totalTypes = "i";
        $totalParams = [$year];
        if (!empty($sectorIds)) {
            $totalTypes .= str_repeat('i', count($sectorIds));
            $totalParams = array_merge($totalParams, $sectorIds);
        }

        $stmtTotal = $this->db->prepare($queryTotal);
        $stmtTotal->bind_param($totalTypes, ...$totalParams);
        $stmtTotal->execute();
        $resTotal = $stmtTotal->get_result();
        
        while ($row = $resTotal->fetch_assoc()) {
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

        // Determine allowed EPIs based on selected sectors
        if (!empty($sectorIds)) {
            $allowedEpiNames = $this->resolveEpiSlugsToNames($sectorIds);

            // If sectors are selected but none have EPIs registered, return empty with flag
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
        $types = "i";
        $params = [$year];

        if (!empty($sectorIds)) {
            $sPlaceholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $sectorClause = " AND f.setor_id IN ($sPlaceholders)";
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }

        if (!empty($allowedEpiNames)) {
            $ePlaceholders = implode(',', array_fill(0, count($allowedEpiNames), '?'));
            $epiClause = " AND e.nome IN ($ePlaceholders)";
            $types .= str_repeat('s', count($allowedEpiNames));
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
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ?
            $sectorClause
            $epiClause
            GROUP BY e.nome
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $labels = [];
        $data = [];
        $totalSum = 0;

        while ($row = $result->fetch_assoc()) {
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
        $fid = $occurrence->getEmployee()->getId();
        $tipo = $occurrence->getType()->getValue();
        $data = $occurrence->getDate()->format('Y-m-d H:i:s');
        $stmt->bind_param('iss', $fid, $tipo, $data);
        $stmt->execute();
        $occurrence->setId((int) $this->db->insert_id);
    }

    public function update(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("UPDATE ocorrencias SET funcionario_id = ?, tipo = ?, data_hora = ? WHERE id = ?");
        $fid = $occurrence->getEmployee()->getId();
        $tipo = $occurrence->getType()->getValue();
        $data = $occurrence->getDate()->format('Y-m-d H:i:s');
        $id = $occurrence->getId();
        $stmt->bind_param('issi', $fid, $tipo, $data, $id);
        $stmt->execute();
    }

    public function delete(Occurrence $occurrence): void
    {
        $stmt = $this->db->prepare("DELETE FROM ocorrencias WHERE id = ?");
        $id = $occurrence->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function findInfractions(array $filters = []): array
    {
        $sql = "SELECT o.id, o.data_hora, o.tipo, f.nome as funcionario_nome, f.foto_referencia as funcionario_foto, s.sigla as setor_sigla, e.nome as epi_nome, o.criado_em
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                WHERE o.tipo = 'INFRACAO'";

        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $sql .= " AND (f.nome LIKE ? OR s.sigla LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        if (!empty($filters['epi']) && $filters['epi'] !== 'todos') {
            $sql .= " AND e.nome LIKE ?";
            $params[] = "%" . $filters['epi'] . "%";
            $types .= "s";
        }

        if (!empty($filters['periodo']) && $filters['periodo'] !== 'todos') {
            if ($filters['periodo'] === 'hoje') {
                $sql .= " AND DATE(o.data_hora) = CURDATE()";
            } elseif ($filters['periodo'] === 'semana') {
                $sql .= " AND YEARWEEK(o.data_hora, 1) = YEARWEEK(CURDATE(), 1)";
            } elseif ($filters['periodo'] === 'mes') {
                $sql .= " AND MONTH(o.data_hora) = MONTH(CURDATE()) AND YEAR(o.data_hora) = YEAR(CURDATE())";
            }
        }

        $sql .= " ORDER BY o.data_hora DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    private function hydrate(array $row): Occurrence
    {
        $employee = $this->employeeRepository->findById((int) $row['funcionario_id']);
        // Para simplificar, o sistema usa o primeiro usuário admin como registradoBy se não houver campo na tabela
        $user = $this->userRepository->findById(1); 
        
        // No schema simplificado, uma ocorrência pode ter vários EPIs, mas a entidadeOccurrence sugere um.
        // Pegamos o primeiro EPI relacionado.
        $stmt = $this->db->prepare("SELECT epi_id FROM ocorrencia_epis WHERE ocorrencia_id = ? LIMIT 1");
        $stmt->bind_param('i', $row['id']);
        $stmt->execute();
        $epiRow = $stmt->get_result()->fetch_assoc();
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

    /**
     * Resolve slugs from setores.epis_json into actual e.nome references
     */
    private function resolveEpiSlugsToNames(array $sectorIds): array
    {
        $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
        $stmt = $this->db->prepare("SELECT epis_json FROM setores WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($sectorIds)), ...$sectorIds);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $slugs = [];
        while ($row = $res->fetch_assoc()) {
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

        // Fetch all active EPI names
        $epiRes = $this->db->query("SELECT nome FROM epis WHERE status = 'ATIVO'");
        $allEpiNames = [];
        while($row = $epiRes->fetch_assoc()) {
            $allEpiNames[] = $row['nome'];
        }

        $resolved = [];
        foreach ($slugs as $slug) {
            $normalizedSlug = strtolower($slug);
            // Replace common accent variations for matching
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
