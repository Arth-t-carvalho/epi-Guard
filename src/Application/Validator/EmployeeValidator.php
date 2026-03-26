<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Validator;

use epiGuard\Application\DTO\Request\CreateEmployeeRequest;
use epiGuard\Domain\Exception\ValidationException;

class EmployeeValidator
{
    /**
     * @param CreateEmployeeRequest $request
     * @throws ValidationException
     */
    public function validateCreation(CreateEmployeeRequest $request): void
    {
        $errors = [];

        if (empty(trim($request->name)) || strlen(trim($request->name)) < 3) {
            $errors['name'] = 'Name must be at least 3 characters long.';
        }

        if (empty($request->cpf)) {
            $errors['cpf'] = 'CPF is required.';
        }

        if (empty($request->enrollmentNumber)) {
            $errors['enrollmentNumber'] = 'Enrollment number is required.';
        }

        if ($request->departmentId <= 0) {
            $errors['departmentId'] = 'Valid department ID is required.';
        }

        if ($request->email !== null && !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email format is invalid.';
        }

        if (!empty($errors)) {
            throw new ValidationException('Employee validation failed.', $errors);
        }
    }
}
