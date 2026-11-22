<?php

namespace App\Enums;

enum EstadoCita: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Completada = 'completada';
    case Cancelada = 'cancelada';
    case NoAsistio = 'no_asistio';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Por confirmar',
            self::Confirmada => 'Confirmada',
            self::Completada => 'Atendida',
            self::Cancelada => 'Cancelada',
            self::NoAsistio => 'No asistió',
        };
    }

    /** Tono visual usado por <x-estado>. */
    public function tono(): string
    {
        return match ($this) {
            self::Pendiente => 'amber',
            self::Confirmada => 'pine',
            self::Completada => 'indigo',
            self::Cancelada => 'rose',
            self::NoAsistio => 'stone',
        };
    }

    /** Una cita activa ocupa su bloque en la agenda del doctor. */
    public function ocupaAgenda(): bool
    {
        return in_array($this, [self::Pendiente, self::Confirmada, self::Completada], true);
    }

    public function esModificable(): bool
    {
        return in_array($this, [self::Pendiente, self::Confirmada], true);
    }

    /** @return array<int, self> */
    public static function activos(): array
    {
        return array_values(array_filter(self::cases(), fn (self $e) => $e->ocupaAgenda()));
    }
}
