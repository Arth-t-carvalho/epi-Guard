<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class OccurrenceType
{
    public const MISSING_EPI = 'missing_epi';
    public const IMPROPER_USE = 'improper_use';
    public const DAMAGED_EPI = 'damaged_epi';
    public const BEHAVIORAL = 'behavioral';
    public const OTHER = 'other';

    private string $type;

    public function __construct(string $type)
    {
        $type = strtolower(trim($type));
        if (!in_array($type, self::getAllTypes(), true)) {
            throw new InvalidArgumentException("Invalid occurrence type: {$type}");
        }

        $this->type = $type;
    }

    public static function getAllTypes(): array
    {
        return [
            self::MISSING_EPI,
            self::IMPROPER_USE,
            self::DAMAGED_EPI,
            self::BEHAVIORAL,
            self::OTHER,
        ];
    }

    public function getValue(): string
    {
        return $this->type;
    }

    public function equals(OccurrenceType $other): bool
    {
        return $this->type === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
