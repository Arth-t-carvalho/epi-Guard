<?php
declare(strict_types=1);

namespace epiGuard\Infrastructure\Persistence;

use epiGuard\Domain\Entity\Employee;
use epiGuard\Domain\ValueObject\CPF;
use epiGuard\Domain\Repository\EmployeeRepositoryInterface;
use epiGuard\Domain\Repository\DepartmentRepositoryInterface;
use epiGuard\Infrastructure\Database\Connection;
use DateTimeImmutable;

class PostgreSQLEmployeeRepository implements EmployeeRepositoryInterface
{
    private \PDO $db;
    private DepartmentRepositoryInterface $departmentRepository;

    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->db = Connection::getInstance();
        $this->departmentRepository = $departmentRepository;
    }

    public function findById(int $id): ?Employee
    {
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
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
        $stmt = $this->db->query("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios ORDER BY nome ASC");
        $employees = [];

        while ($row = $stmt->fetch()) {
            $employees[] = $this->hydrate($row);
        }

        return $employees;
    }

    public function findByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare("SELECT id, nome, setor_id, criado_em, atualizado_em FROM funcionarios WHERE setor_id = ?");
        $stmt->execute([$departmentId]);
        
        $employees = [];
        while ($row = $stmt->fetch()) {
            $employees[] = $this->hydrate($row);
        }

        return $employees;
    }

    public function save(Employee $employee): void
    {
        $stmt = $this->db->prepare("INSERT INTO funcionarios (nome, setor_id) VALUES (?, ?)");
        $params = [
            $employee->getName(),
            $employee->getDepartment()->getId()
        ];
        $stmt->execute($params);

        $employee->setId((int) $this->db->lastInsertId());
    }

    public function update(Employee $employee): void
    {
        $stmt = $this->db->prepare("UPDATE funcionarios SET nome = ?, setor_id = ? WHERE id = ?");
        $params = [
            $employee->getName(),
            $employee->getDepartment()->getId(),
            $employee->getId()
        ];
        $stmt->execute($params);
    }

    public function delete(Employee $employee): void
    {
        $stmt = $this->db->prepare("DELETE FROM funcionarios WHERE id = ?");
        $stmt->execute([$employee->getId()]);
    }

    private function hydrate(array $row): Employee
    {
        $department = $this->departmentRepository->findById((int) $row['setor_id']);
        
        if (!$department) {
            $department = new \epiGuard\Domain\Entity\Department(
                name: 'Setor Desconhecido',
                code: 'N/A',
                epis: [],
                id: (int) $row['setor_id']
            );
        }
        
        return new Employee(
            name: $row['nome'],
            cpf: new CPF('12345678909'),
            enrollmentNumber: (string) $row['id'],
            department: $department,
            id: (int) $row['id'],
            createdAt: new DateTimeImmutable($row['criado_em']),
            updatedAt: $row['atualizado_em'] ? new DateTimeImmutable($row['atualizado_em']) : null
        );
    }
}

