<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Request;

class CreateOccurrenceRequest
{
    public int $employeeId;
    public int $registeredById;
    public int $epiItemId;
    public string $type;
    public string $description;
    public string $date;
    public array $evidences = [];

    public function __construct(
        int $employeeId,
        int $registeredById,
        int $epiItemId,
        string $type,
        string $description,
        string $date,
        array $evidences = []
        )
    {
        $this->employeeId = $employeeId;
        $this->registeredById = $registeredById;
        $this->epiItemId = $epiItemId;
        $this->type = $type;
        $this->description = $description;
        $this->date = $date;
        $this->evidences = $evidences;
    }
}
