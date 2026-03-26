<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Request;

class ResolveOccurrenceRequest
{
    public int $occurrenceId;
    public int $resolvedById;
    public string $actionType;
    public string $actionDescription;

    public function __construct(
        int $occurrenceId,
        int $resolvedById,
        string $actionType,
        string $actionDescription
        )
    {
        $this->occurrenceId = $occurrenceId;
        $this->resolvedById = $resolvedById;
        $this->actionType = $actionType;
        $this->actionDescription = $actionDescription;
    }
}
