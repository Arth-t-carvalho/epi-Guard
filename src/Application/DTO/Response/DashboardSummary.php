<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Response;

class DashboardSummary
{
    public int $totalEmployees;
    public int $totalOccurrences;
    public int $openOccurrences;
    public int $resolvedOccurrences;

    public function __construct(
        int $totalEmployees = 0,
        int $totalOccurrences = 0,
        int $openOccurrences = 0,
        int $resolvedOccurrences = 0
        )
    {
        $this->totalEmployees = $totalEmployees;
        $this->totalOccurrences = $totalOccurrences;
        $this->openOccurrences = $openOccurrences;
        $this->resolvedOccurrences = $resolvedOccurrences;
    }
}
