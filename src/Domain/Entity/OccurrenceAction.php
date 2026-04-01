<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use epiGuard\Domain\ValueObject\ActionType;
use DateTimeImmutable;

class OccurrenceAction
{
    private ?int $id;
    private int $occurrenceId;
    private ActionType $type;
    private string $description;
    private User $performedBy;
    private DateTimeImmutable $createdAt;

    public function __construct(
        int $occurrenceId,
        ActionType $type,
        string $description,
        User $performedBy,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null
        )
    {
        $this->occurrenceId = $occurrenceId;
        $this->type = $type;
        $this->description = $description;
        $this->performedBy = $performedBy;
        $this->id = $id;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOccurrenceId(): int
    {
        return $this->occurrenceId;
    }

    public function getType(): ActionType
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPerformedBy(): User
    {
        return $this->performedBy;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
