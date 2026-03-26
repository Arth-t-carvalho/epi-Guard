<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Repository;

use epiGuard\Domain\Entity\Employee;
use epiGuard\Domain\ValueObject\CPF;

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

    public function save(Employee $employee): void;

    public function update(Employee $employee): void;

    public function delete(Employee $employee): void;
}
