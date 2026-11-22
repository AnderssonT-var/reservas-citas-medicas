<?php

namespace App\Enums;

enum EstadoPago: string
{
    case Pagado = 'pagado';
    case Pendiente = 'pendiente';
    case Anulado = 'anulado';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tono(): string
    {
        return match ($this) {
            self::Pagado => 'pine',
            self::Pendiente => 'amber',
            self::Anulado => 'stone',
        };
    }
}
