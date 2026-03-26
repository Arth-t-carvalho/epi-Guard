<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use epiGuard\Domain\ValueObject\Email;
use epiGuard\Domain\ValueObject\UserRole;
use DateTimeImmutable;

class User
{
    private ?int $id;
    private string $name;
    private Email $email;
    private string $passwordHash;
    private UserRole $role;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        Email $email,
        string $passwordHash,
        UserRole $role,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
        )
    {
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
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

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changePassword(string $newPasswordHash): void
    {
        $this->passwordHash = $newPasswordHash;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeRole(UserRole $newRole): void
    {
        $this->role = $newRole;
        $this->updatedAt = new DateTimeImmutable();
    }
}
