<?php

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->rol(Rol::Doctor),
            'especialidad_id' => Especialidad::factory(),
            'licencia_medica' => 'MSP-'.fake()->unique()->numerify('######'),
            'duracion_cita' => 30,
            'tarifa' => 30,
            'activo' => true,
        ];
    }
}
