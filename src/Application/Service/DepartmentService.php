<?php
declare(strict_types = 1)
;

namespace Facchini\Application\Service;

use Facchini\Domain\Entity\Department;
use Facchini\Domain\Repository\DepartmentRepositoryInterface;

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
