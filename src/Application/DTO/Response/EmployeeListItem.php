<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Response;

class EmployeeListItem
{
    public int $id;
    public string $name;
    public string $enrollmentNumber;
    public string $departmentCode;
    public int $occurrencesCount;

    public function __construct(
        int $id,
        string $name,
        string $enrollmentNumber,
        string $departmentCode,
        int $occurrencesCount = 0
        )
    {
        $this->id = $id;
        $this->name = $name;
        $this->enrollmentNumber = $enrollmentNumber;
        $this->departmentCode = $departmentCode;
        $this->occurrencesCount = $occurrencesCount;
    }
}
