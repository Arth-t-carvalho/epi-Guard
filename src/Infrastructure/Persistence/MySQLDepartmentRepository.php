<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Department;
use Facchini\Domain\Repository\DepartmentRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;
use DateTimeImmutable;

class MySQLDepartmentRepository implements DepartmentRepositoryInterface
{
    private \mysqli $db;
    private bool $hasFilialId = false;
    private bool $hasNomeEn = false;

    public function __construct()
    {
        $this->db = Connection::getInstance();
        $this->detectSchema();
    }

    private function detectSchema(): void
    {
        // Safety check: only run query if connection is alive
        if (!$this->db) return;

        $res = $this->db->query("SHOW COLUMNS FROM setores LIKE 'filial_id'");
        $this->hasFilialId = ($res && $res->num_rows > 0);

        $res2 = $this->db->query("SHOW COLUMNS FROM setores LIKE 'nome_en'");
        $this->hasNomeEn = ($res2 && $res2->num_rows > 0);
    }

    public function findById(int $id): ?Department
    {
        $cols = "id, nome, sigla, status, epis_json, criado_em, atualizado_em";
        if ($this->hasNomeEn) {
            $cols = "id, nome, nome_en, sigla, status, epis_json, criado_em, atualizado_em";
        }
        $stmt = $this->db->prepare("SELECT $cols FROM setores WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByCode(string $code): ?Department
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND filial_id = ?" : " AND 1 = ?";
        
        $cols = "id, nome, sigla, status, epis_json, criado_em, atualizado_em";
        if ($this->hasNomeEn) {
            $cols = "id, nome, nome_en, sigla, status, epis_json, criado_em, atualizado_em";
        }

        $stmt = $this->db->prepare("SELECT $cols FROM setores WHERE sigla = ? $filialClause");
        if (!$stmt) {
            error_log("MySQL Prepare Error (findByCode): " . $this->db->error);
            return null;
        }
        $stmt->bind_param('si', $code, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByName(string $name): ?Department
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND filial_id = ?" : " AND 1 = ?";

        $cols = "id, nome, sigla, status, epis_json, criado_em, atualizado_em";
        if ($this->hasNomeEn) {
            $cols = "id, nome, nome_en, sigla, status, epis_json, criado_em, atualizado_em";
        }

        $stmt = $this->db->prepare("SELECT $cols FROM setores WHERE nome = ? $filialClause");
        if (!$stmt) {
            error_log("MySQL Prepare Error (findByName): " . $this->db->error);
            return null;
        }
        $stmt->bind_param('si', $name, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findAll(): array
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? "filial_id = $activeFilial" : "1 = 1";

        $cols = "id, nome, sigla, status, criado_em, atualizado_em";
        if ($this->hasNomeEn) {
            $cols = "id, nome, nome_en, sigla, status, criado_em, atualizado_em";
        }

        $result = $this->db->query("SELECT $cols FROM setores WHERE $filialClause AND status = 'ATIVO' ORDER BY nome ASC");
        $departments = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $departments[] = $this->hydrate($row);
            }
        }

        return $departments;
    }

    public function findAllWithStats(array $filters = []): array
    {
        $cols = "s.id, s.nome, s.sigla, s.status, s.epis_json, s.criado_em, s.atualizado_em";
        if ($this->hasNomeEn) {
            $cols = "s.id, s.nome, s.nome_en, s.sigla, s.status, s.epis_json, s.criado_em, s.atualizado_em";
        }

        $sql = "SELECT $cols, 
                       (SELECT COUNT(*) FROM funcionarios WHERE setor_id = s.id) as total_funcionarios,
                       COALESCE(risk_data.risk_p, 0) as risk_p
                FROM setores s 
                LEFT JOIN (
                    SELECT f_calc.setor_id, 
                           (COUNT(DISTINCT occ_calc.funcionario_id) / 
                            NULLIF((SELECT COUNT(*) FROM funcionarios f_total WHERE f_total.setor_id = f_calc.setor_id), 0) * 100) as risk_p
                    FROM funcionarios f_calc
                    JOIN ocorrencias occ_calc ON f_calc.id = occ_calc.funcionario_id
                    WHERE occ_calc.tipo = 'INFRACAO'
                    GROUP BY f_calc.setor_id
                ) as risk_data ON s.id = risk_data.setor_id
                WHERE " . ($this->hasFilialId ? "s.filial_id = " . ($_SESSION['active_filial_id'] ?? 1) : "1 = 1");

        if (!empty($filters['status']) && $filters['status'] !== 'todos') {
            $sql .= " AND s.status = '" . ($filters['status'] === 'ativo' ? 'ATIVO' : 'INATIVO') . "'";
        }

        if (!empty($filters['risk']) && $filters['risk'] !== 'todos') {
            if ($filters['risk'] === 'baixo') {
                $sql .= " AND (risk_data.risk_p < 5 OR risk_data.risk_p IS NULL)";
            } elseif ($filters['risk'] === 'medio') {
                $sql .= " AND risk_data.risk_p >= 5 AND risk_data.risk_p < 10";
            } elseif ($filters['risk'] === 'alto') {
                $sql .= " AND risk_data.risk_p >= 10";
            }
        }

        $sql .= " ORDER BY s.nome ASC";

        $result = $this->db->query($sql);
        $data = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function save(Department $department): void
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $nome = $department->getName();
        $nomeEn = $department->getNameEn();
        $sigla = $department->getCode();
        $episJson = json_encode($department->getEpis());

        $cols = "nome, sigla, epis_json";
        $vals = "?, ?, ?";
        $types = "sss";
        $params = [$nome, $sigla, $episJson];

        if ($this->hasNomeEn) {
            $cols = "nome, nome_en, sigla, epis_json";
            $vals = "?, ?, ?, ?";
            $types = "ssss";
            $params = [$nome, $nomeEn, $sigla, $episJson];
        }

        if ($this->hasFilialId) {
            $cols .= ", filial_id";
            $vals .= ", ?";
            $types .= "i";
            $params[] = $activeFilial;
        }

        $stmt = $this->db->prepare("INSERT INTO setores ($cols) VALUES ($vals)");
        if (!$stmt) {
            error_log("MySQL Prepare Error (save): " . $this->db->error);
            throw new \Exception("Erro ao preparar inserção (save): " . $this->db->error);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $department->setId((int)$this->db->insert_id);
    }

    public function update(Department $department): void
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $nome = $department->getName();
        $nomeEn = $department->getNameEn();
        $sigla = $department->getCode();
        $episJson = json_encode($department->getEpis());
        $id = $department->getId();

        $sets = "nome = ?, sigla = ?, epis_json = ?";
        $types = "sss";
        $params = [$nome, $sigla, $episJson];

        if ($this->hasNomeEn) {
            $sets = "nome = ?, nome_en = ?, sigla = ?, epis_json = ?";
            $types = "ssss";
            $params = [$nome, $nomeEn, $sigla, $episJson];
        }

        $where = "id = ?";
        $types .= "i";
        $params[] = $id;

        if ($this->hasFilialId) {
            $where .= " AND filial_id = ?";
            $types .= "i";
            $params[] = $activeFilial;
        }

        $stmt = $this->db->prepare("UPDATE setores SET $sets WHERE $where");
        if (!$stmt) {
            error_log("MySQL Prepare Error (update): " . $this->db->error);
            throw new \Exception("Erro ao preparar atualização (update): " . $this->db->error);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    public function delete(Department $department): void
    {
        $stmt = $this->db->prepare("UPDATE setores SET status = 'INATIVO' WHERE id = ?");
        if (!$stmt) return;
        $id = $department->getId();
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    private function hydrate(array $row): Department
    {
        $episList = [];
        if (!empty($row['epis_json'])) {
            $episList = json_decode($row['epis_json'], true) ?: [];
        }

        return new Department(
            name: $row['nome'],
            code: $row['sigla'],
            epis: $episList,
            nameEn: $row['nome_en'] ?? null,
            id: (int) $row['id'],
            createdAt: new \DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new \DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}
