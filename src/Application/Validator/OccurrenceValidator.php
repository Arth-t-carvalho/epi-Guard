<?php
declare(strict_types = 1);

namespace Facchini\Application\Validator;

use Facchini\Application\DTO\Request\CreateOccurrenceRequest;
use Facchini\Domain\Exception\ValidationException;

/**
 * Validador para garantir a integridade dos dados no registro de novas ocorrências.
 * Verifica campos obrigatórios e formatos de data.
 */
class OccurrenceValidator
{
    /**
     * Realiza a validação estrutural de uma nova ocorrência.
     * 
     * @param CreateOccurrenceRequest $request Dados da ocorrência a ser validada
     * @throws ValidationException Se houver dados inconsistentes ou ausentes
     */
    public function validateCreation(CreateOccurrenceRequest $request): void
    {
        $errors = [];

        if ($request->employeeId <= 0) {
            $errors['employeeId'] = 'Valid employee ID is required.';
        }

        if ($request->epiItemId <= 0) {
            $errors['epiItemId'] = 'Valid EPI Item ID is required.';
        }

        // Tipo de ocorrência é obrigatório
        if (empty(trim($request->type))) {
            $errors['type'] = 'Occurrence type is required.';
        }

        // Descrição deve ter um detalhamento mínimo para ser útil
        if (empty(trim($request->description)) || strlen(trim($request->description)) < 5) {
            $errors['description'] = 'Description must be at least 5 characters long.';
        }

        // Validação rigorosa de data para evitar inconsistências no banco
        if (empty(trim($request->date))) {
            $errors['date'] = 'Date is required.';
        }
        else {
            $format = 'Y-m-d';
            $d = \DateTime::createFromFormat($format, $request->date);
            if (!$d || $d->format($format) !== $request->date) {
                $errors['date'] = 'Date must be in Y-m-d format.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Occurrence validation failed.', $errors);
        }
    }
}
