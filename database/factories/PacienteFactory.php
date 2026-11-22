<?php

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\User;
use Database\Seeders\Cedulas;
use Illuminate\Database\Eloquent\Factories\Factory;

class PacienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->rol(Rol::Paciente),
            'cedula' => Cedulas::generar(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-80 years', '-2 years')->format('Y-m-d'),
            'genero' => fake()->randomElement(['femenino', 'masculino']),
            'grupo_sanguineo' => fake()->randomElement(['O+', 'O+', 'O+', 'A+', 'B+', 'O-', 'AB+']),
            'direccion' => fake()->streetAddress(),
        ];
    }
}
