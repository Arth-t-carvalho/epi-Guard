<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class UserRole
{
    public const ADMIN = 'admin';
    public const OPERATOR = 'operator';
    public const VIEWER = 'viewer';
    public const MANAGER = 'manager';

    private string $role;

    public function __construct(string $role)
    {
        $role = strtolower(trim($role));
        if (!in_array($role, self::getAllRoles(), true)) {
            throw new InvalidArgumentException("Invalid user role: {$role}");
        }

        $this->role = $role;
    }

    public static function getAllRoles(): array
    {
        return [
            self::ADMIN,
            self::OPERATOR,
            self::VIEWER,
            self::MANAGER,
        ];
    }

    public function getValue(): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function equals(UserRole $other): bool
    {
        return $this->role === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
