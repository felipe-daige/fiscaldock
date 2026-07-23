<?php

namespace App\Rules;

use App\Support\Cpf;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida o CPF quando PRESENTE (a obrigatoriedade fica com required/required_if no chamador).
 * Fonte única da regra + mensagem de CPF inválido — antes duplicada no signup e no perfil.
 */
class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && ! Cpf::valido($value)) {
            $fail('Informe um CPF válido.');
        }
    }
}
