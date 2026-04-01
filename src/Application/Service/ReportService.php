<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Domain\Repository\OccurrenceRepositoryInterface;
use epiGuard\Domain\Repository\EmployeeRepositoryInterface;

class ReportService
{
    private OccurrenceRepositoryInterface $occurrenceRepository;
    private EmployeeRepositoryInterface $employeeRepository;

    public function __construct(
        OccurrenceRepositoryInterface $occurrenceRepository,
        EmployeeRepositoryInterface $employeeRepository
        )
    {
        $this->occurrenceRepository = $occurrenceRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function generateEmployeeReport(int $employeeId): array
    {
        // Return a structured array or a Report DTO based on all occurrences of a employee.
        return [];
    }

    public function generateGeneralReport(string $startDate, string $endDate): array
    {
        // Return analytical data summarizing operations between dates.
        return [];
    }
}
