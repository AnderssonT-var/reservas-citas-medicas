<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Valida el dígito verificador de una cédula ecuatoriana (módulo 10). */
class CedulaEcuatoriana implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::esValida((string) $value)) {
            $fail('La cédula no es válida. Revisa los 10 dígitos.');
        }
    }

    public static function esValida(string $cedula): bool
    {
        if (! preg_match('/^\d{10}$/', $cedula)) {
            return false;
        }
        $provincia = (int) substr($cedula, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            return false;
        }
        if ((int) $cedula[2] >= 6) {
            return false;
        }
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $n = (int) $cedula[$i] * ($i % 2 === 0 ? 2 : 1);
            $suma += $n > 9 ? $n - 9 : $n;
        }
        $verificador = (10 - ($suma % 10)) % 10;

        return $verificador === (int) $cedula[9];
    }
}
