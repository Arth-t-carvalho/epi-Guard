<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use DateTimeImmutable;

class EpiItem
{
    private ?int $id;
    private string $name;
    private ?string $description;
    private string $color;
    private bool $isRequired;
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        bool $isRequired = true,
        ?string $description = null,
        string $color = '#E30613',
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null
        )
    {
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->description = $description;
        $this->color = $color;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
