<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use DateTimeImmutable;

class EpiItem
{
    private ?int $id;
    private string $name;
    private ?string $nameEn;
    private ?string $description;
    private bool $isRequired;
    private string $color;
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        string $color = '#E30613',
        bool $isRequired = true,
        ?string $description = null,
        ?string $nameEn = null,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null
        )
    {
        $this->name = $name;
        $this->nameEn = $nameEn;
        $this->color = $color;
        $this->isRequired = $isRequired;
        $this->description = $description;
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

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): void
    {
        $this->nameEn = $nameEn;
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
