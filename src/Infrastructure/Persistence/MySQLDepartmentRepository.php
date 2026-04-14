<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Department;
use Facchini\Domain\Repository\DepartmentRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;

class MySQLDepartmentRepository implements DepartmentRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById(int $id): ?Department
    {
        $stmt = $this->db->prepare("SELECT id, nome, nome_en, sigla, filial_id, status, epis_json, criado_em, atualizado_em FROM setores WHERE id = ? AND deletado_em IS NULL");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByCode(string $code): ?Department
    {
        $stmt = $this->db->prepare("SELECT id, nome, nome_en, sigla, filial_id, status, epis_json, criado_em, atualizado_em FROM setores WHERE sigla = ? AND deletado_em IS NULL");
        $stmt->execute([$code]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    public function findByName(string $name): ?Department
    {
        $stmt = $this->db->prepare("SELECT id, nome, nome_en, sigla, filial_id, status, epis_json, criado_em, atualizado_em FROM setores WHERE nome = ? AND deletado_em IS NULL");
        $stmt->execute([$name]);

        if ($row = $stmt->fetch()) {
            return $this->hydrate($row);
        }

        return null;
    }

    /** @return Department[] */
    public function findAll(?int $filialId = null): array
    {
        $sql = "SELECT id, nome, nome_en, sigla, filial_id, status, criado_em, atualizado_em FROM setores WHERE deletado_em IS NULL";
        $params = [];
        if ($filialId !== null) {
            $sql .= " AND filial_id = ?";
            $params[] = $filialId;
        }
        $sql .= " ORDER BY nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $departments = [];

        while ($row = $stmt->fetch()) {
            $departments[] = $this->hydrate($row);
        }

        return $departments;
    }

    /**
     * Retorna array associativo com dados do setor, contagem de funcionários e cálculo de risco
     */
    public function findAllWithStats(array $filters = []): array
    {
        // MySQL Version
        $sql = "SELECT s.id, s.nome, s.nome_en, s.sigla, s.status, s.epis_json, s.criado_em, s.atualizado_em, 
                       (SELECT COUNT(*) FROM funcionarios WHERE setor_id = s.id AND deletado_em IS NULL) as total_funcionarios,
                       COALESCE(risk_data.risk_p, 0) as risk_p
                FROM setores s 
                LEFT JOIN (
                    SELECT f_calc.setor_id, 
                           (COUNT(DISTINCT occ_calc.funcionario_id) / 
                            NULLIF((SELECT COUNT(*) FROM funcionarios f_total WHERE f_total.setor_id = f_calc.setor_id AND f_total.deletado_em IS NULL), 0) * 100) as risk_p
                    FROM funcionarios f_calc
                    JOIN ocorrencias occ_calc ON f_calc.id = occ_calc.funcionario_id
                    WHERE occ_calc.tipo = 'INFRACAO' AND f_calc.deletado_em IS NULL
                    GROUP BY f_calc.setor_id
                ) as risk_data ON s.id = risk_data.setor_id
                WHERE s.deletado_em IS NULL";

        /*
        // PostgreSQL (Commented)
        $sql = "SELECT s.id, s.nome, s.nome_en, s.sigla, s.status, s.epis_json, s.criado_em, s.atualizado_em, 
                       (SELECT COUNT(*) FROM funcionarios WHERE setor_id = s.id AND deletado_em IS NULL) as total_funcionarios,
                       COALESCE(risk_data.risk_p, 0) as risk_p
                FROM setores s 
                LEFT JOIN (
                    SELECT f_calc.setor_id, 
                           (COUNT(DISTINCT occ_calc.funcionario_id)::float / 
                            NULLIF((SELECT COUNT(*) FROM funcionarios f_total WHERE f_total.setor_id = f_calc.setor_id AND f_total.deletado_em IS NULL), 0) * 100) as risk_p
                    FROM funcionarios f_calc
                    JOIN ocorrencias occ_calc ON f_calc.id = occ_calc.funcionario_id
                    WHERE occ_calc.tipo = 'INFRACAO' AND f_calc.deletado_em IS NULL
                    GROUP BY f_calc.setor_id
                ) as risk_data ON s.id = risk_data.setor_id
                WHERE s.deletado_em IS NULL";
        */

        $params = [];
        if (!empty($filters['filial_id'])) {
            $sql .= " AND s.filial_id = ?";
            $params[] = $filters['filial_id'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'todos') {
            $sql .= " AND s.status = ?";
            $params[] = ($filters['status'] === 'ativo' ? 'ATIVO' : 'INATIVO');
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function save(Department $department): void
    {
        $stmt = $this->db->prepare("INSERT INTO setores (nome, nome_en, sigla, epis_json, filial_id) VALUES (?, ?, ?, ?, ?)");
        $params = [
            $department->getName(),
            $department->getNameEn(),
            $department->getCode(),
            json_encode($department->getEpis()),
            $department->getFilialId()
        ];
        $stmt->execute($params);

        $department->setId((int)$this->db->lastInsertId());
    }

    public function update(Department $department): void
    {
        $stmt = $this->db->prepare("UPDATE setores SET nome = ?, nome_en = ?, sigla = ?, epis_json = ?, filial_id = ? WHERE id = ?");
        $params = [
            $department->getName(),
            $department->getNameEn(),
            $department->getCode(),
            json_encode($department->getEpis()),
            $department->getFilialId(),
            $department->getId()
        ];
        $stmt->execute($params);
    }

    public function delete(Department $department): void
    {
        $stmt = $this->db->prepare("UPDATE setores SET deletado_em = CURRENT_TIMESTAMP, status = 'INATIVO' WHERE id = ?");
        $stmt->execute([$department->getId()]);
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
            nameEn: $row['nome_en'],
            filialId: (int)($row['filial_id'] ?? 1),
            id: (int)$row['id'],
            createdAt: new \DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new \DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}
