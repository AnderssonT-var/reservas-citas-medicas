<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\User;

class ConsultaPolicy
{
    public function view(User $user, Consulta $consulta): bool
    {
        return $user->esAdmin()
            || $user->esDoctor() // los doctores consultan el historial completo del paciente
            || ($user->esPaciente() && $consulta->paciente?->user_id === $user->id);
    }

    public function update(User $user, Consulta $consulta): bool
    {
        return $user->esDoctor() && $consulta->doctor?->user_id === $user->id;
    }
}
