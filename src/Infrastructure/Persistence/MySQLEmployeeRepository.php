<?php
declare(strict_types=1);

namespace Facchini\Infrastructure\Persistence;

use Facchini\Domain\Entity\Employee;
use Facchini\Domain\ValueObject\CPF;
use Facchini\Domain\Repository\EmployeeRepositoryInterface;
use Facchini\Domain\Repository\DepartmentRepositoryInterface;
use Facchini\Infrastructure\Database\Connection;
use DateTimeImmutable;

class MySQLEmployeeRepository implements EmployeeRepositoryInterface
{
    private \mysqli $db;
    private DepartmentRepositoryInterface $departmentRepository;
    private bool $hasFilialId = false;

    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->db = Connection::getInstance();
        $this->departmentRepository = $departmentRepository;
        if ($this->db) {
            $this->detectSchema();
        }
    }

    private function detectSchema(): void
    {
        if (!$this->db) return;
        $res = $this->db->query("SHOW COLUMNS FROM funcionarios LIKE 'filial_id'");
        $this->hasFilialId = ($res && $res->num_rows > 0);
    }

    public function findById(int $id): ?Employee
    {
        if (!$this->db) return null;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND filial_id = ?" : "";
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE id = ? $filialClause");
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

    public function findByCpf(CPF $cpf): ?Employee
    {
        return null;
    }

    public function findByEnrollmentNumber(string $enrollmentNumber): ?Employee
    {
        return null;
    }

    /** @return Employee[] */
    public function findAll(): array
    {
        if (!$this->db) return [];
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? "WHERE filial_id = $activeFilial" : "WHERE 1 = 1";
        $result = $this->db->query("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios $filialClause ORDER BY nome ASC");
        $employees = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $this->hydrate($row);
            }
        }

        return $employees;
    }

    public function findByDepartment(int $departmentId): array
    {
        if (!$this->db) return [];
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? " AND filial_id = ?" : "";
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE setor_id = ? $filialClause");
        if (!$stmt) return [];
        
        if ($this->hasFilialId) {
            $stmt->bind_param('ii', $departmentId, $activeFilial);
        } else {
            $stmt->bind_param('i', $departmentId);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $employees = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $this->hydrate($row);
            }
        }

        return $employees;
    }

    public function countAll(?array $sectorIds = null): int
    {
        if (!$this->db) return 0;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $filialClause = $this->hasFilialId ? "WHERE filial_id = $activeFilial" : "WHERE 1 = 1";
        $query = "SELECT COUNT(*) as total FROM funcionarios $filialClause";
        
        if (!empty($sectorIds)) {
            $query .= " AND setor_id IN (" . implode(',', array_fill(0, count($sectorIds), '?')) . ")";
        }

        $stmt = $this->db->prepare($query);
        if (!$stmt) return 0;

        if (!empty($sectorIds)) {
            $types = str_repeat('i', count($sectorIds));
            $stmt->bind_param($types, ...$sectorIds);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    public function save(Employee $employee): void
    {
        if (!$this->db) return;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $nome = $employee->getName();
        $setor_id = $employee->getDepartment()->getId();

        $cols = "nome, setor_id";
        $vals = "?, ?";
        $types = "si";
        $params = [$nome, $setor_id];

        if ($this->hasFilialId) {
            $cols .= ", filial_id";
            $vals .= ", ?";
            $types .= "i";
            $params[] = $activeFilial;
        }

        $stmt = $this->db->prepare("INSERT INTO funcionarios ($cols) VALUES ($vals)");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $employee->setId((int) $this->db->insert_id);
    }

    public function update(Employee $employee): void
    {
        if (!$this->db) return;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $nome = $employee->getName();
        $setor_id = $employee->getDepartment()->getId();
        $id = $employee->getId();

        $sets = "nome = ?, setor_id = ?";
        $types = "si";
        $params = [$nome, $setor_id];

        $where = "id = ?";
        $types .= "i";
        $params[] = $id;

        if ($this->hasFilialId) {
            $where .= " AND filial_id = ?";
            $types .= "i";
            $params[] = $activeFilial;
        }

        $stmt = $this->db->prepare("UPDATE funcionarios SET $sets WHERE $where");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    public function delete(Employee $employee): void
    {
        if (!$this->db) return;
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $id = $employee->getId();
        
        $where = "id = ?";
        $types = "i";
        $params = [$id];

        if ($this->hasFilialId) {
            $where .= " AND filial_id = ?";
            $types .= "i";
            $params[] = $activeFilial;
        }

        $stmt = $this->db->prepare("DELETE FROM funcionarios WHERE $where");
        if (!$stmt) return;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    private function hydrate(array $row): Employee
    {
        $department = $this->departmentRepository->findById((int) $row['setor_id']);

        if (!$department) {
            $department = new \Facchini\Domain\Entity\Department(
                name: 'Setor Desconhecido',
                code: 'N/A',
                epis: [],
                id: (int) $row['setor_id']
            );
        }

        return new Employee(
            name: $row['nome'],
            cpf: new CPF('11144477735'),
            enrollmentNumber: (string) $row['id'],
            department: $department,
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em'] ?? 'now'),
            updatedAt: !empty($row['atualizado_em']) ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}
