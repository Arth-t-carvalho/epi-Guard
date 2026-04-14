<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Entity;

use Facchini\Domain\ValueObject\OccurrenceStatus;
use Facchini\Domain\ValueObject\OccurrenceType;
use DateTimeImmutable;

class Occurrence
{
    private ?int $id;
    private Employee $employee;
    private User $registeredBy;
    private EpiItem $epiItem;
    private OccurrenceType $type;
    private OccurrenceStatus $status;
    private string $description;
    private DateTimeImmutable $date;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;
    private ?string $primaryEvidencePath;

    /** @var Evidence[] */
    private array $evidences = [];

    /** @var OccurrenceAction[] */
    private array $actions = [];

    public function __construct(
        Employee $employee,
        User $registeredBy,
        EpiItem $epiItem,
        OccurrenceType $type,
        string $description,
        DateTimeImmutable $date,
        ?OccurrenceStatus $status = null,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?string $primaryEvidencePath = null
        )
    {
        $this->employee = $employee;
        $this->registeredBy = $registeredBy;
        $this->epiItem = $epiItem;
        $this->type = $type;
        $this->description = $description;
        $this->date = $date;
        $this->status = $status ?? new OccurrenceStatus(OccurrenceStatus::OPEN);
        $this->id = $id;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt;
        $this->primaryEvidencePath = $primaryEvidencePath;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getRegisteredBy(): User
    {
        return $this->registeredBy;
    }

    public function getEpiItem(): EpiItem
    {
        return $this->epiItem;
    }

    public function getType(): OccurrenceType
    {
        return $this->type;
    }

    public function getStatus(): OccurrenceStatus
    {
        return $this->status;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changeStatus(OccurrenceStatus $newStatus): void
    {
        $this->status = $newStatus;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getEvidences(): array
    {
        return $this->evidences;
    }

    public function addEvidence(Evidence $evidence): void
    {
        $this->evidences[] = $evidence;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function addAction(OccurrenceAction $action): void
    {
        $this->actions[] = $action;
    }

    public function getPrimaryEvidencePath(): ?string
    {
        return $this->primaryEvidencePath;
    }

    public function setPrimaryEvidencePath(?string $path): void
    {
        $this->primaryEvidencePath = $path;
    }
}
