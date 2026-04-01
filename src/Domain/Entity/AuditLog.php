<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use DateTimeImmutable;

class AuditLog
{
    private ?int $id;
    private int $userId;
    private string $action;
    private string $entityType;
    private int $entityId;
    private ?string $oldValues;
    private ?string $newValues;
    private string $ipAddress;
    private DateTimeImmutable $createdAt;

    public function __construct(
        int $userId,
        string $action,
        string $entityType,
        int $entityId,
        string $ipAddress,
        ?string $oldValues = null,
        ?string $newValues = null,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null
        )
    {
        $this->userId = $userId;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->ipAddress = $ipAddress;
        $this->oldValues = $oldValues;
        $this->newValues = $newValues;
        $this->id = $id;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getOldValues(): ?string
    {
        return $this->oldValues;
    }

    public function getNewValues(): ?string
    {
        return $this->newValues;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
