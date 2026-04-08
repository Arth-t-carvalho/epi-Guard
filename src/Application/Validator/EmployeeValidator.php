<?php
declare(strict_types=1);

namespace Facchini\Application\Validator;

use Facchini\Application\DTO\Request\CreateEmployeeRequest;
use Facchini\Domain\Exception\ValidationException;

/**
 * Validador responsável pelas regras de integridade dos dados de entrada
 * relacionados ao cadastro de colaboradores.
 */
class EmployeeValidator
{
    /**
     * Valida os dados para a criação de um novo colaborador.
     * 
     * @param CreateEmployeeRequest $request Objeto contendo os dados brutos da requisição
     * @throws ValidationException Caso algum critério de validação não seja atendido
     */
    public function validateCreation(CreateEmployeeRequest $request): void
    {
        $errors = [];

        // Validação de nome: não pode ser vazio e deve ter pelo menos 3 caracteres úteis
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

        // Validação de formato de e-mail usando filtro nativo do PHP
        if ($request->email !== null && !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email format is invalid.';
        }

        if (!empty($errors)) {
            throw new ValidationException('Employee validation failed.', $errors);
        }
    }
}
