<?php
declare(strict_types = 1)
;

namespace Facchini\Domain\Entity;

use DateTimeImmutable;

class Department
{
    private ?int $id;
    private string $name;
    private ?string $nameEn;
    private string $code;
    private array $epis;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $code,
        array $epis = [],
        ?string $nameEn = null,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
        )
    {
        $this->name = $name;
        $this->nameEn = $nameEn;
        $this->code = $code;
        $this->epis = $epis;
        $this->id = $id;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
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

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): void
    {
        $this->nameEn = $nameEn;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getEpis(): array
    {
        return $this->epis;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
