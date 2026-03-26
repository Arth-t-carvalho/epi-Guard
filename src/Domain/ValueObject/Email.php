<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if (empty($value)) {
            throw new \InvalidArgumentException("Identifier cannot be empty.");
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
