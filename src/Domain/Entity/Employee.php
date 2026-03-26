<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use epiGuard\Domain\ValueObject\CPF;
use epiGuard\Domain\ValueObject\Email;
use DateTimeImmutable;

class Employee
{
    private ?int $id;
    private string $name;
    private CPF $cpf;
    private ?Email $email;
    private string $enrollmentNumber;
    private Department $department;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        CPF $cpf,
        string $enrollmentNumber,
        Department $department,
        ?Email $email = null,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
        )
    {
        $this->name = $name;
        $this->cpf = $cpf;
        $this->enrollmentNumber = $enrollmentNumber;
        $this->department = $department;
        $this->email = $email;
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

    public function getCpf(): CPF
    {
        return $this->cpf;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function getEnrollmentNumber(): string
    {
        return $this->enrollmentNumber;
    }

    public function getDepartment(): Department
    {
        return $this->department;
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
