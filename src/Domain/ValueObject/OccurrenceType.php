<?php
declare(strict_types=1);

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class OccurrenceType
{
    // Coincidir exatamente com o ENUM do Banco de Dados ('INFRACAO', 'CONFORMIDADE')
    public const INFRACAO = 'INFRACAO';
    public const CONFORMIDADE = 'CONFORMIDADE';
    
    // Outros tipos internos (se existirem)
    public const MISSING_EPI = 'MISSING_EPI';
    public const IMPROPER_USE = 'IMPROPER_USE';
    public const DAMAGED_EPI = 'DAMAGED_EPI';
    public const BEHAVIORAL = 'BEHAVIORAL';
    public const OTHER = 'OTHER';

    private string $type;

    public function __construct(string $type)
    {
        // Normalizar para maiúsculas para bater com o ENUM do MySQL
        $type = strtoupper(trim($type));
        
        if (!in_array($type, self::getAllTypes(), true)) {
            // Debug info no erro para facilitar
            $allowed = implode(', ', self::getAllTypes());
            throw new InvalidArgumentException("Tipo de ocorrência inválido: '{$type}'. Esperado um de: [{$allowed}]");
        }

        $this->type = $type;
    }

    public static function getAllTypes(): array
    {
        return [
            self::INFRACAO,
            self::CONFORMIDADE,
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
