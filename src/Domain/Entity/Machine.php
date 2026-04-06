<?php

namespace epiGuard\Domain\Entity;

use DateTimeInterface;

class Machine
{
    private ?int $id;
    private string $name;
    private int $sectorId;
    private int $epiId;
    private ?DateTimeInterface $createdAt;

    public function __construct(
        string $name,
        int $sectorId,
        int $epiId,
        ?int $id = null,
        ?DateTimeInterface $createdAt = null
    ) {
        $this->name = $name;
        $this->sectorId = $sectorId;
        $this->epiId = $epiId;
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSectorId(): int
    {
        return $this->sectorId;
    }

    public function setSectorId(int $sectorId): self
    {
        $this->sectorId = $sectorId;
        return $this;
    }

    public function getEpiId(): int
    {
        return $this->epiId;
    }

    public function setEpiId(int $epiId): self
    {
        $this->epiId = $epiId;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }
}
