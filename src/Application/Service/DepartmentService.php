<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Domain\Entity\Department;
use epiGuard\Domain\Repository\DepartmentRepositoryInterface;

class DepartmentService
{
    private DepartmentRepositoryInterface $departmentRepository;

    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * @return Department[]
     */
    public function getAllDepartments(): array
    {
        return $this->departmentRepository->findAll();
    }
}
