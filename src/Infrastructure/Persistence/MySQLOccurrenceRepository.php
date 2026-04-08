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
    private \mysqli $db;
    private EmployeeRepositoryInterface $employeeRepository;
    private UserRepositoryInterface $userRepository;
    private EpiRepositoryInterface $epiRepository;
    private bool $hasFilialId = false;
    private bool $hasFavorito = false;
    private bool $hasEpiNomeEn = false;
    private bool $hasSetorNomeEn = false;

    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        UserRepositoryInterface $userRepository,
        EpiRepositoryInterface $epiRepository
    ) {
        $this->db = Connection::getInstance();
        $this->employeeRepository = $employeeRepository;
        $this->userRepository = $userRepository;
        $this->epiRepository = $epiRepository;
        if ($this->db) {
            // Sincronizar fuso horário com o PHP (America/Sao_Paulo)
            $this->db->query("SET time_zone = '-03:00'");
            $this->detectSchema();
        }
    }

    private function detectSchema(): void
    {
        if (!$this->db) return;

        $res = $this->db->query("SHOW COLUMNS FROM ocorrencias LIKE 'filial_id'");
        $this->hasFilialId = ($res && $res->num_rows > 0);

        $res2 = $this->db->query("SHOW COLUMNS FROM ocorrencias LIKE 'favorito'");
        $this->hasFavorito = ($res2 && $res2->num_rows > 0);

        $res3 = $this->db->query("SHOW COLUMNS FROM epis LIKE 'nome_en'");
        $this->hasEpiNomeEn = ($res3 && $res3->num_rows > 0);

        $res4 = $this->db->query("SHOW COLUMNS FROM setores LIKE 'nome_en'");
        $this->hasSetorNomeEn = ($res4 && $res4->num_rows > 0);
    }

    public function findById(int $id): ?Occurrence
    {
        if (!$this->db) return null;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND filial_id = ?" : "";
        $stmt = $this->db->prepare("SELECT * FROM ocorrencias WHERE id = ? $filialClause");
        if (!$stmt) return null;
        
        if ($this->hasFilialId) {
            $stmt->bind_param('ii', $id, $activeFilial);
        } else {
            $stmt->bind_param('i', $id);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return Occurrence[] */
    public function findAll(): array
    {
        if (!$this->db) return [];
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? "filial_id = $activeFilial" : "1 = 1";
        $result = $this->db->query("SELECT * FROM ocorrencias WHERE $filialClause ORDER BY data_hora DESC");
        $occurrences = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $occurrences[] = $this->hydrate($row);
            }
        }
        return $occurrences;
    }

    public function findByEmployeeId(int $employeeId): array
    {
        if (!$this->db) return [];
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND filial_id = ?" : "";
        $stmt = $this->db->prepare("SELECT * FROM ocorrencias WHERE funcionario_id = ? $filialClause ORDER BY data_hora DESC");
        if (!$stmt) return [];
        
        if ($this->hasFilialId) {
            $stmt->bind_param('ii', $employeeId, $activeFilial);
        } else {
            $stmt->bind_param('i', $employeeId);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $occurrences = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $occurrences[] = $this->hydrate($row);
            }
        }
        return $occurrences;
    }

    public function findByStatus(string $status): array
    {
        return [];
    }

    public function countDaily(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE DATE(o.data_hora) = ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "s";
        $params = [$dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countWeekly(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE YEARWEEK(o.data_hora, 1) = YEARWEEK(?, 1) AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "s";
        $params = [$dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countMonthly(DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE MONTH(o.data_hora) = MONTH(?) AND YEAR(o.data_hora) = YEAR(?) AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "ss";
        $params = [$dateStr, $dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countUniqueStudentsDaily(\DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(DISTINCT o.funcionario_id) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE DATE(o.data_hora) = ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "s";
        $params = [$dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countUniqueStudentsWeekly(\DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(DISTINCT o.funcionario_id) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE YEARWEEK(o.data_hora, 1) = YEARWEEK(?, 1) AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "s";
        $params = [$dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countUniqueStudentsMonthly(\DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(DISTINCT o.funcionario_id) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE MONTH(o.data_hora) = MONTH(?) AND YEAR(o.data_hora) = YEAR(?) AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "ss";
        $params = [$dateStr, $dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countUniqueStudentsYearly(\DateTimeInterface $date, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(DISTINCT o.funcionario_id) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE YEAR(o.data_hora) = YEAR(?) AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $dateStr = $date->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "s";
        $params = [$dateStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countRange(\DateTimeInterface $start, \DateTimeInterface $end, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(*) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE DATE(o.data_hora) BETWEEN ? AND ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "ss";
        $params = [$startStr, $endStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function countUniqueStudentsRange(\DateTimeInterface $start, \DateTimeInterface $end, ?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $query = "SELECT COUNT(DISTINCT o.funcionario_id) as total FROM ocorrencias o 
                  JOIN funcionarios f ON o.funcionario_id = f.id 
                  WHERE DATE(o.data_hora) BETWEEN ? AND ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE $filialClause ";

        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND f.setor_id IN ($placeholders)";
        }

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        $types = "ss";
        $params = [$startStr, $endStr];

        if (!empty($sectorIds)) {
            $types .= str_repeat('i', count($sectorIds));
            $params = array_merge($params, $sectorIds);
        }
        
        if ($this->hasFilialId) {
            $types .= 'i';
            $params[] = $activeFilial;
        }

        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function getMonthlyInfractionStats(int $year, ?array $sectorIds = null): array
    {
        if (!$this->db) return ['stats' => [], 'allowed_epis' => [], 'epi_colors' => []];
        $year = (int) $year;

        // Fetch translation mapping for all active and system EPIs
        $lang = $_COOKIE['Facchini-lang'] ?? 'pt-br';
        $translations = [];
        $epiColors = [];
        $epiNomeEnCol = $this->hasEpiNomeEn ? ", nome_en" : "";
        $resMap = $this->db->query("SELECT nome $epiNomeEnCol, cor FROM epis");
        if ($resMap) {
            while ($row = $resMap->fetch_assoc()) {
                $name = $row['nome'];
                $translated = ($lang === 'en' && !empty($row['nome_en'])) ? $row['nome_en'] : $name;
                $translations[$name] = $translated;
                $epiColors[$translated] = ($row['cor'] ?? '') ?: '#E30613';
            }
        }

        // Initialize stats with Total series
        $stats = [
            'total' => array_fill(0, 12, 0)
        ];

        // Ensure all active EPIs are represented in stats if they have colors
        foreach ($translations as $original => $translated) {
            $stats[$translated] = array_fill(0, 12, 0);
        }

        $sectorClause = "";
        if (!empty($sectorIds)) {
            $sPlaceholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $sectorClause = " AND f.setor_id IN ($sPlaceholders)";
        }

        $epiResilience = "e.nome as epi_nome";
        if ($this->hasEpiNomeEn) $epiResilience .= ", e.nome_en as epi_nome_en";

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        
        // 1. Fetch EPI-specific counts
        $query = "
            SELECT 
                MONTH(o.data_hora) as mes,
                $epiResilience,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE YEAR(o.data_hora) = ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE " . ($this->hasFilialId ? "AND o.filial_id = ?" : "") . " 
            $sectorClause
            GROUP BY mes, epi_nome" . ($this->hasEpiNomeEn ? ", epi_nome_en" : "");

        $stmt = $this->db->prepare($query);
        if ($stmt) {
            $finalTypes = "i";
            $finalParams = [$year];
            if ($this->hasFilialId) {
                $finalTypes .= 'i';
                $finalParams[] = $activeFilial;
            }
            if (!empty($sectorIds)) {
                $finalTypes .= str_repeat('i', count($sectorIds));
                $finalParams = array_merge($finalParams, $sectorIds);
            }

            $stmt->bind_param($finalTypes, ...$finalParams);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $mesIdx = (int) $row['mes'] - 1;
                    if ($mesIdx < 0 || $mesIdx >= 12) continue;

                    $nomeOriginal = $row['epi_nome'];
                    $nomeEn = $row['epi_nome_en'] ?? '';
                    $translated = ($lang === 'en' && !empty($nomeEn)) ? $nomeEn : $nomeOriginal;
                    
                    if (!isset($stats[$translated])) {
                        $stats[$translated] = array_fill(0, 12, 0);
                    }
                    $stats[$translated][$mesIdx] += (int) $row['total'];
                }
            }
        }

        // 2. Fetch Total counts (global for occurrences)
        $queryTotal = "
            SELECT MONTH(o.data_hora) as mes, COUNT(*) as qtd
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            WHERE YEAR(o.data_hora) = ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE " . ($this->hasFilialId ? "AND o.filial_id = ?" : "") . " 
            $sectorClause
            GROUP BY mes
        ";

        $stmtTotal = $this->db->prepare($queryTotal);
        if ($stmtTotal) {
            $stmtTotal->bind_param("i" . ($this->hasFilialId ? "i" : "") . (!empty($sectorIds) ? str_repeat('i', count($sectorIds)) : ""), ...$finalParams);
            $stmtTotal->execute();
            $resTotal = $stmtTotal->get_result();
            if ($resTotal) {
                while ($row = $resTotal->fetch_assoc()) {
                    $mesIdx = (int) $row['mes'] - 1;
                    if ($mesIdx >= 0 && $mesIdx < 12) {
                        $stats['total'][$mesIdx] = (int) $row['qtd'];
                    }
                }
            }
        }

        // Ensure allowed_epis contains all keys from stats (except total)
        $finalAllowedEpis = [];
        foreach (array_keys($stats) as $key) {
            if ($key !== 'total') {
                $finalAllowedEpis[] = $key;
            }
        }

        return [
            'stats' => $stats,
            'allowed_epis' => $finalAllowedEpis,
            'epi_colors' => (object)$epiColors
        ];
    }

    public function getInfractionDistributionByEpi(?array $sectorIds = null): array
    {
        if (!$this->db) return ['labels' => [], 'data' => [], 'total' => 0];
        $year = (int) date('Y');
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;

        $sectorClause = "";
        if (!empty($sectorIds)) {
            $sPlaceholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $sectorClause = " AND f.setor_id IN ($sPlaceholders)";
        }

        $epiResilience = "e.nome";
        if ($this->hasEpiNomeEn) $epiResilience .= ", e.nome_en";

        $query = "
            SELECT 
                $epiResilience,
                COUNT(*) as total
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND YEAR(o.data_hora) = ? " . ($this->hasFilialId ? "AND o.filial_id = ?" : "") . " 
            $sectorClause
            GROUP BY e.nome" . ($this->hasEpiNomeEn ? ", e.nome_en" : "");

        $stmt = $this->db->prepare($query);
        if (!$stmt) return ['labels' => [], 'data' => [], 'total' => 0];

        $finalTypes = "i";
        $finalParams = [$year];
        if ($this->hasFilialId) {
            $finalTypes .= 'i';
            $finalParams[] = $activeFilial;
        }
        if (!empty($sectorIds)) {
            $finalTypes .= str_repeat('i', count($sectorIds));
            $finalParams = array_merge($finalParams, $sectorIds);
        }

        $stmt->bind_param($finalTypes, ...$finalParams);
        $stmt->execute();
        $result = $stmt->get_result();

        $labels = [];
        $data = [];
        $colors = [];
        $totalSum = 0;

        $epiColorMap = [];
        $cMapRes = $this->db->query("SELECT nome" . ($this->hasEpiNomeEn ? ", nome_en" : "") . ", cor FROM epis WHERE status IN ('ATIVO', 'SISTEMA')");
        $lang = $_COOKIE['Facchini-lang'] ?? 'pt-br';
        if ($cMapRes) {
            while ($cMapRow = $cMapRes->fetch_assoc()) {
                $name = ($lang === 'en' && !empty($cMapRow['nome_en'])) ? $cMapRow['nome_en'] : $cMapRow['nome'];
                $epiColorMap[$name] = ($cMapRow['cor'] ?? '') ?: '#E30613';
            }
        }

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $name = ($lang === 'en' && !empty($row['nome_en'])) ? $row['nome_en'] : $row['nome'];
                $labels[] = $name;
                $data[] = (int) $row['total'];
                $colors[] = $epiColorMap[$name] ?? '#E30613';
                $totalSum += (int) $row['total'];
            }
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
        if (!$this->db) return;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $fid = $occurrence->getEmployee()->getId();
        $tipo = $occurrence->getType()->getValue();
        $data = $occurrence->getDate()->format('Y-m-d H:i:s');

        $cols = "funcionario_id, tipo, data_hora";
        $vals = "?, ?, ?";
        $types = "iss";
        $params = [$fid, $tipo, $data];

        if ($this->hasFilialId) {
            $cols .= ", filial_id";
            $vals .= ", ?";
            $types .= "i";
            $params[] = $activeFilial;
        }

        $stmt = $this->db->prepare("INSERT INTO ocorrencias ($cols) VALUES ($vals)");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $occurrence->setId((int) $this->db->insert_id);
    }

    public function update(Occurrence $occurrence): void
    {
        if (!$this->db) return;
        $fid = $occurrence->getEmployee()->getId();
        $tipo = $occurrence->getType()->getValue();
        $data = $occurrence->getDate()->format('Y-m-d H:i:s');
        $id = $occurrence->getId();

        $sets = "funcionario_id = ?, tipo = ?, data_hora = ?";
        $types = "issi";
        $params = [$fid, $tipo, $data, $id];

        $stmt = $this->db->prepare("UPDATE ocorrencias SET $sets WHERE id = ?");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    public function delete(Occurrence $occurrence): void
    {
        if (!$this->db) return;
        $stmt = $this->db->prepare("DELETE FROM ocorrencias WHERE id = ?");
        if (!$stmt) return;
        $id = $occurrence->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function hide(int $id): bool
    {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE ocorrencias SET oculto = TRUE WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function toggleFavorite(int $id): array
    {
        if (!$this->db) return ['success' => false];
        if ($this->hasFavorito) {
            $stmt = $this->db->prepare("UPDATE ocorrencias SET favorito = NOT favorito WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
            }
        }

        $stmt2 = $this->db->prepare("SELECT " . ($this->hasFavorito ? "favorito" : "FALSE as favorito") . " FROM ocorrencias WHERE id = ?");
        if ($stmt2) {
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $res = $stmt2->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                return ['success' => true, 'favorito' => (bool) $row['favorito']];
            }
        }
        return ['success' => true, 'favorito' => false];
    }

    public function findInfractions(array $filters = []): array
    {
        if (!$this->db) return [];
        $favCol = $this->hasFavorito ? "o.favorito" : "FALSE as favorito";
        $setorNomeEnCol = $this->hasSetorNomeEn ? "s.nome_en as setor_nome_en" : "'N/A' as setor_nome_en";
        $epiNomeEnCol = $this->hasEpiNomeEn ? "e.nome_en as epi_nome_en" : "'N/A' as epi_nome_en";        $sql = "SELECT o.id, o.data_hora, o.tipo, $favCol, f.id as funcionario_id, f.nome as funcionario_nome, f.foto_referencia as funcionario_foto, s.id as setor_id, s.sigla as setor_sigla, s.nome as setor_nome, $setorNomeEnCol, e.id as epi_id, e.nome as epi_nome, $epiNomeEnCol, o.criado_em,
                (SELECT ev.caminho_imagem FROM evidencias ev WHERE ev.ocorrencia_id = o.id LIMIT 1) as evidencia_foto,
                (SELECT MAX(ao.data_hora) FROM acoes_ocorrencia ao WHERE ao.ocorrencia_id = o.id) as resolvido_em,
                ao.tipo as acao_tipo, ao.observacao as acao_obs, u.nome as responsavel_nome, u.cargo as responsavel_cargo,
                (CASE WHEN EXISTS (SELECT 1 FROM acoes_ocorrencia ao2 WHERE ao2.ocorrencia_id = o.id) THEN 'resolvido' ELSE 'pendente' END) as status
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                LEFT JOIN acoes_ocorrencia ao ON o.id = ao.ocorrencia_id
                LEFT JOIN usuarios u ON ao.usuario_id = u.id
                WHERE o.tipo = 'INFRACAO'";

        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND o.filial_id = ?" : "";
        $sql .= $filialClause;

        $params = [];
        $types = "";
        if ($this->hasFilialId) {
            $params[] = $activeFilial;
            $types .= "i";
        }

        // Filtro de Visibilidade (Oculto)
        if (!empty($filters['status']) && $filters['status'] === 'inativo') {
            $sql .= " AND o.oculto = TRUE";
        } else {
            $sql .= " AND o.oculto = FALSE";
        }

        // Filtro de Busca (Nome ou Sigla)
        if (!empty($filters['search'])) {
            $sql .= " AND (f.nome LIKE ? OR s.sigla LIKE ? OR s.nome LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        // Filtro de Período (Igual ao Dashboard)
        if (!empty($filters['periodo']) && $filters['periodo'] !== 'todos') {
            $refDate = $filters['ref_date'] ?? date('Y-m-d');
            if ($filters['periodo'] === 'hoje') {
                $sql .= " AND DATE(o.data_hora) = ?";
                $params[] = $refDate;
                $types .= "s";
            } elseif ($filters['periodo'] === 'semana') {
                $sql .= " AND YEARWEEK(o.data_hora, 1) = YEARWEEK(?, 1)";
                $params[] = $refDate;
                $types .= "s";
            } elseif ($filters['periodo'] === 'mes') {
                $sql .= " AND MONTH(o.data_hora) = MONTH(?) AND YEAR(o.data_hora) = YEAR(?)";
                $params[] = $refDate;
                $params[] = $refDate;
                $types .= "ss";
            }
        }

        // Filtro de Status (Pendente/Resolvido)
        if (!empty($filters['status']) && $filters['status'] !== 'todos' && $filters['status'] !== 'inativo') {
            if ($filters['status'] === 'resolvido') {
                $sql .= " AND EXISTS (SELECT 1 FROM acoes_ocorrencia ao2 WHERE ao2.ocorrencia_id = o.id)";
            } elseif ($filters['status'] === 'pendente') {
                $sql .= " AND NOT EXISTS (SELECT 1 FROM acoes_ocorrencia ao2 WHERE ao2.ocorrencia_id = o.id)";
            }
        }

        // Filtro de EPI
        if (!empty($filters['epi']) && $filters['epi'] !== 'todos') {
            // Se for nome do EPI vindo do filtro
            $sql .= " AND (e.nome = ? OR e.id = ?)";
            $params[] = $filters['epi'];
            $params[] = (int) $filters['epi'];
            $types .= "si";
        }

        // Filtro de Datas Customizadas
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.data_hora) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.data_hora) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        // Filtro por Funcionário Específico
        if (!empty($filters['funcionario_id'])) {
            $sql .= " AND f.id = ?";
            $params[] = (int) $filters['funcionario_id'];
            $types .= "i";
        }

        // Agrupamento para evitar duplicatas por múltiplos EPIs
        $sql .= " GROUP BY o.id";

        // Ordenação
        $order = $filters['order'] ?? 'recentes';
        if ($order === 'antigos') {
            $sql .= " ORDER BY o.data_hora ASC";
        } elseif ($order === 'nome' || $order === 'alfabetica') {
            $sql .= " ORDER BY f.nome ASC";
        } elseif ($order === 'frequentes') {
            $sql .= " ORDER BY (SELECT COUNT(*) FROM ocorrencias o2 WHERE o2.funcionario_id = f.id) DESC";
        } else {
            $sql .= " ORDER BY o.data_hora DESC";
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    private function hydrate(array $row): Occurrence
    {
        $employee = $this->employeeRepository->findById((int) ($row['funcionario_id'] ?? 0));
        
        // If employee not found, create dummy to avoid crash
        if (!$employee) {
            $employee = new \Facchini\Domain\Entity\Employee(
                'Desconhecido', 
                new \Facchini\Domain\ValueObject\CPF('00000000000'), 
                '0', 
                new \Facchini\Domain\Entity\Department('Desconhecido', 'N/A', [])
            );
        }

        // The ocorrencias table doesn't store these directly, using defaults for now to maintain stability
        $registeredBy = $this->userRepository->findById(1) ?? new \Facchini\Domain\Entity\User(
            "Admin", 
            new \Facchini\Domain\ValueObject\Email("admin@facchini.com.br"), 
            "", 
            new \Facchini\Domain\ValueObject\UserRole(\Facchini\Domain\ValueObject\UserRole::ADMIN)
        );

        $epiItem = $this->epiRepository->findById(1) ?? new \Facchini\Domain\Entity\EpiItem(
            "EPI", 
            "Equipamento"
        );

        $dataHoraStr = $row['data_hora'] ?? 'now';
        if ($dataHoraStr === '0000-00-00 00:00:00') {
            $dataHoraStr = 'now';
        }

        return new Occurrence(
            employee: $employee,
            registeredBy: $registeredBy,
            epiItem: $epiItem,
            type: new OccurrenceType($row['tipo'] ?? 'INFRACAO'),
            description: $row['descricao'] ?? '',
            date: new DateTimeImmutable($dataHoraStr),
            id: (int) ($row['id'] ?? null)
        );
    }
}
