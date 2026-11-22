<?php

namespace Database\Seeders;

/** Genera cédulas ecuatorianas válidas para datos de prueba. */
class Cedulas
{
    private static array $usadas = [];

    public static function generar(): string
    {
        do {
            $base = str_pad((string) random_int(1, 24), 2, '0', STR_PAD_LEFT)
                .random_int(0, 5)
                .str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $suma = 0;
            for ($i = 0; $i < 9; $i++) {
                $n = (int) $base[$i] * ($i % 2 === 0 ? 2 : 1);
                $suma += $n > 9 ? $n - 9 : $n;
            }
            $cedula = $base.((10 - ($suma % 10)) % 10);
        } while (isset(self::$usadas[$cedula]));

        return self::$usadas[$cedula] = $cedula;
    }
}
