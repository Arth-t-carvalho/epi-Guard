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

    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->db = Connection::getInstance();
        $this->departmentRepository = $departmentRepository;
    }

    public function findById(int $id): ?Employee
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE id = ? AND filial_id = ?");
        $stmt->bind_param('ii', $id, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
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
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $result = $this->db->query("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE filial_id = $activeFilial ORDER BY nome ASC");
        $employees = [];

        while ($row = $result->fetch_assoc()) {
            $employees[] = $this->hydrate($row);
        }

        return $employees;
    }

    public function findByDepartment(int $departmentId): array
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE setor_id = ? AND filial_id = ?");
        $stmt->bind_param('ii', $departmentId, $activeFilial);
        $stmt->execute();
        $result = $stmt->get_result();

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $this->hydrate($row);
        }

        return $employees;
    }

    public function countAll(?array $sectorIds = null): int
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $query = "SELECT COUNT(*) as total FROM funcionarios WHERE filial_id = $activeFilial";
        
        if (!empty($sectorIds)) {
            $placeholders = implode(',', array_fill(0, count($sectorIds), '?'));
            $query .= " AND setor_id IN ($placeholders)";
            $stmt = $this->db->prepare($query);
            $types = str_repeat('i', count($sectorIds));
            $stmt->bind_param($types, ...$sectorIds);
        } else {
            $stmt = $this->db->prepare($query);
        }

        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int) $res['total'];
    }

    public function save(Employee $employee): void
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $stmt = $this->db->prepare("INSERT INTO funcionarios (nome, setor_id, filial_id) VALUES (?, ?, ?)");
        $nome = $employee->getName();
        $setor_id = $employee->getDepartment()->getId();
        $stmt->bind_param('sii', $nome, $setor_id, $activeFilial);
        $stmt->execute();

        $employee->setId((int) $this->db->insert_id);
    }

    public function update(Employee $employee): void
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $stmt = $this->db->prepare("UPDATE funcionarios SET nome = ?, setor_id = ? WHERE id = ? AND filial_id = ?");
        $nome = $employee->getName();
        $setor_id = $employee->getDepartment()->getId();
        $id = $employee->getId();
        $stmt->bind_param('siii', $nome, $setor_id, $id, $activeFilial);
        $stmt->execute();
    }

    public function delete(Employee $employee): void
    {
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        $stmt = $this->db->prepare("DELETE FROM funcionarios WHERE id = ? AND filial_id = ?");
        $id = $employee->getId();
        $stmt->bind_param('ii', $id, $activeFilial);
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
            createdAt: new DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}
