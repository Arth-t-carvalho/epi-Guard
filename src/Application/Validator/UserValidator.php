<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Validator;

use epiGuard\Domain\Exception\ValidationException;

class UserValidator
{
    /**
     * @param array $data Expected keys: name, email, password, role
     * @throws ValidationException
     */
    public function validateCreation(array $data): void
    {
        $errors = [];

        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors['name'] = 'Name must be at least 3 characters long.';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email is required.';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if (empty($data['role'])) {
            $errors['role'] = 'Role is required.';
        }

        if (!empty($errors)) {
            throw new ValidationException('User validation failed.', $errors);
        }
    }
}
