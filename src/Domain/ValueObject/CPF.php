<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\ValueObject;

use InvalidArgumentException;

final class CPF
{
    private string $value;

    public function __construct(string $cpf)
    {
        $this->value = $this->validateAndFormat($cpf);
    }

    private function validateAndFormat(string $cpf): string
    {
        // Remove tudo que não é dígito
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) !== 11) {
            throw new InvalidArgumentException("CPF must contain exactly 11 digits.");
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            throw new InvalidArgumentException("Invalid CPF format.");
        }

        // Calcula e valida o primeiro dígito
        $soma = 0;
        for ($i = 0, $peso = 10; $i < 9; $i++, $peso--) {
            $soma += $cpf[$i] * $peso;
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        if ($cpf[9] != $digito1) {
            throw new InvalidArgumentException("Invalid CPF.");
        }

        // Calcula e valida o segundo dígito
        $soma = 0;
        for ($i = 0, $peso = 11; $i < 10; $i++, $peso--) {
            $soma += $cpf[$i] * $peso;
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        if ($cpf[10] != $digito2) {
            throw new InvalidArgumentException("Invalid CPF.");
        }

        // Return the formatted CPF
        return $cpf;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->value);
    }

    public function equals(CPF $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
