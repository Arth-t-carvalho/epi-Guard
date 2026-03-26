<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class OccurrenceStatus
{
    public const OPEN = 'open';
    public const IN_PROGRESS = 'in_progress';
    public const RESOLVED = 'resolved';
    public const CLOSED = 'closed';

    private string $status;

    public function __construct(string $status)
    {
        $status = strtolower(trim($status));
        if (!in_array($status, self::getAllStatuses(), true)) {
            throw new InvalidArgumentException("Invalid occurrence status: {$status}");
        }

        $this->status = $status;
    }

    public static function getAllStatuses(): array
    {
        return [
            self::OPEN,
            self::IN_PROGRESS,
            self::RESOLVED,
            self::CLOSED,
        ];
    }

    public function getValue(): string
    {
        return $this->status;
    }

    public function isOpen(): bool
    {
        return $this->status === self::OPEN;
    }

    public function equals(OccurrenceStatus $other): bool
    {
        return $this->status === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
