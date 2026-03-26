<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Exception;

class AuthenticationException extends DomainException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid email or password.');
    }

    public static function inactiveUser(): self
    {
        return new self('User account is inactive.');
    }
}
