<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Response;

class OccurrenceDetail
{
    public int $id;
    public string $employeeName;
    public string $employeeDepartment;
    public string $registeredBy;
    public string $epiItem;
    public string $type;
    public string $status;
    public string $description;
    public string $date;
    public array $evidences = [];
    public array $actions = [];

    public function __construct(
        int $id,
        string $employeeName,
        string $employeeDepartment,
        string $registeredBy,
        string $epiItem,
        string $type,
        string $status,
        string $description,
        string $date,
        array $evidences = [],
        array $actions = []
        )
    {
        $this->id = $id;
        $this->employeeName = $employeeName;
        $this->employeeDepartment = $employeeDepartment;
        $this->registeredBy = $registeredBy;
        $this->epiItem = $epiItem;
        $this->type = $type;
        $this->status = $status;
        $this->description = $description;
        $this->date = $date;
        $this->evidences = $evidences;
        $this->actions = $actions;
    }
}
