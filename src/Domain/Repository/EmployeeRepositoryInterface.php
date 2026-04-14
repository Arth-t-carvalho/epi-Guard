<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Repository;

use Facchini\Domain\Entity\Employee;
use Facchini\Domain\ValueObject\CPF;

interface EmployeeRepositoryInterface
{
    public function findById(int $id): ?Employee;

    public function findByCpf(CPF $cpf): ?Employee;

    public function findByEnrollmentNumber(string $enrollmentNumber): ?Employee;

    /**
     * @return Employee[]
     */
    public function findAll(): array;

    /**
     * @param int $departmentId
     * @return Employee[]
     */
    public function findByDepartment(int $departmentId): array;

    public function countAll(?array $sectorIds = null): int;

    public function save(Employee $employee): void;

    public function update(Employee $employee): void;

    public function delete(Employee $employee): void;
}
