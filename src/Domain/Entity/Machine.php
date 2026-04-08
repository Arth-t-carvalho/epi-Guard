<?php
declare(strict_types=1);

namespace Facchini\Domain\Entity;

use DateTimeImmutable;

class Machine
{
    private ?int $id;
    private string $name;
    private int $departmentId;
    private ?int $epiId;
    private ?DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        int $departmentId,
        ?int $epiId = null,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->name = $name;
        $this->departmentId = $departmentId;
        $this->epiId = $epiId;
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDepartmentId(): int
    {
        return $this->departmentId;
    }

    public function getEpiId(): ?int
    {
        return $this->epiId;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
