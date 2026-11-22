<?php

namespace App\Enums;

enum Rol: string
{
    case Admin = 'admin';
    case Doctor = 'doctor';
    case Paciente = 'paciente';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administración',
            self::Doctor => 'Doctor',
            self::Paciente => 'Paciente',
        };
    }

    public function inicio(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Doctor => 'doctor.dashboard',
            self::Paciente => 'paciente.inicio',
        };
    }
}
