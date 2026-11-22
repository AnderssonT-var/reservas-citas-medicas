<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    public function view(User $user, Cita $cita): bool
    {
        return $user->esAdmin()
            || ($user->esDoctor() && $cita->doctor?->user_id === $user->id)
            || ($user->esPaciente() && $cita->paciente?->user_id === $user->id);
    }

    /** Confirmar, marcar inasistencia y atender. */
    public function gestionar(User $user, Cita $cita): bool
    {
        return $user->esAdmin() || ($user->esDoctor() && $cita->doctor?->user_id === $user->id);
    }

    public function atender(User $user, Cita $cita): bool
    {
        return $user->esDoctor() && $cita->doctor?->user_id === $user->id;
    }
}
