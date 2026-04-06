<?php
declare(strict_types=1);

namespace Facchini\Domain\Repository;

use Facchini\Domain\Entity\Machine;

interface MachineRepositoryInterface
{
    public function findById(int $id): ?Machine;

    /** @return Machine[] */
    public function findByDepartment(int $departmentId): array;

    public function save(Machine $machine): void;

    public function delete(int $id): void;
}
