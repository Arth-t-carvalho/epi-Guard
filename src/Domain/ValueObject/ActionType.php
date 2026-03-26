<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class ActionType
{
    public const VERBAL_WARNING = 'verbal_warning';
    public const WRITTEN_WARNING = 'written_warning';
    public const SUSPENSION = 'suspension';
    public const DISMISSAL = 'dismissal';
    public const EDUCATIONAL = 'educational';

    private string $type;

    public function __construct(string $type)
    {
        $type = strtolower(trim($type));
        if (!in_array($type, self::getAllTypes(), true)) {
            throw new InvalidArgumentException("Invalid action type: {$type}");
        }

        $this->type = $type;
    }

    public static function getAllTypes(): array
    {
        return [
            self::VERBAL_WARNING,
            self::WRITTEN_WARNING,
            self::SUSPENSION,
            self::DISMISSAL,
            self::EDUCATIONAL,
        ];
    }

    public function getValue(): string
    {
        return $this->type;
    }

    public function equals(ActionType $other): bool
    {
        return $this->type === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
