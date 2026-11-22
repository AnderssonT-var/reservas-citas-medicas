<?php

namespace Database\Seeders;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Enums\Rol;
use App\Models\Ajuste;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\Doctor;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;
use App\Services\AgendaService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        fake()->seed(2026);

        Ajuste::guardar([
            'nombre_clinica' => 'TovelMedical',
            'telefono' => '02 600 1234',
            'direccion' => 'Av. Amazonas N34-120, Quito',
            'correo' => 'citas@tovelmedical.com',
            'horas_cancelacion' => '2',
            'dias_reserva' => '45',
        ]);

        User::create(['name' => 'Andrea Salazar', 'email' => 'admin@tovelmedical.com', 'password' => 'password', 'rol' => Rol::Admin]);

        $esp = collect([
            ['Medicina general', 'Chequeos, certificados y primera consulta.', '#0F6B5C'],
            ['Pediatría', 'Control de niño sano, vacunas y enfermedades infantiles.', '#2F7FD1'],
            ['Cardiología', 'Corazón, presión arterial y electrocardiogramas.', '#C2414B'],
            ['Dermatología', 'Piel, cabello y uñas.', '#B9701A'],
            ['Ginecología', 'Salud femenina, control prenatal y planificación.', '#9A4BB8'],
            ['Traumatología', 'Huesos, articulaciones y lesiones deportivas.', '#46607A'],
            ['Psicología', 'Terapia individual y de pareja.', '#3F8F6B'],
        ])->mapWithKeys(fn ($e) => [$e[0] => Especialidad::create(['nombre' => $e[0], 'descripcion' => $e[1], 'color' => $e[2]])]);

        $cons = collect([
            ['Consultorio 101', 'Planta baja, ala norte'],
            ['Consultorio 102', 'Planta baja, ala norte'],
            ['Consultorio 201', 'Primer piso, ala sur'],
            ['Consultorio 202', 'Primer piso, ala sur'],
            ['Sala de terapia', 'Segundo piso'],
        ])->map(fn ($c) => Consultorio::create(['nombre' => $c[0], 'ubicacion' => $c[1], 'telefono' => '02 600 12'.random_int(10, 99)]));

        $plantillaDoctores = [
            ['Carlos Mendoza', 'doctor@tovelmedical.com', 'Medicina general', 0, 20, 25, [[1, '08:00', '13:00'], [2, '08:00', '13:00'], [3, '14:00', '18:00'], [4, '08:00', '13:00'], [5, '08:00', '12:00']]],
            ['Valeria Ortiz', 'vortiz@tovelmedical.com', 'Pediatría', 1, 30, 35, [[1, '09:00', '13:00'], [3, '09:00', '13:00'], [5, '14:00', '18:00']]],
            ['Diego Paredes', 'dparedes@tovelmedical.com', 'Cardiología', 2, 40, 60, [[2, '10:00', '14:00'], [4, '10:00', '14:00']]],
            ['Lucía Andrade', 'landrade@tovelmedical.com', 'Dermatología', 3, 30, 45, [[1, '14:00', '18:00'], [2, '14:00', '18:00'], [6, '09:00', '12:00']]],
            ['María José Cevallos', 'mcevallos@tovelmedical.com', 'Ginecología', 2, 30, 50, [[3, '08:00', '12:00'], [5, '08:00', '12:00']]],
            ['Andrés Villacís', 'avillacis@tovelmedical.com', 'Traumatología', 3, 30, 45, [[1, '08:00', '12:00'], [4, '14:00', '18:00']]],
            ['Gabriela Ruiz', 'gruiz@tovelmedical.com', 'Psicología', 4, 50, 40, [[2, '15:00', '19:00'], [4, '15:00', '19:00'], [6, '09:00', '13:00']]],
        ];

        $doctores = collect($plantillaDoctores)->map(function ($d) use ($esp, $cons) {
            $user = User::create(['name' => $d[0], 'email' => $d[1], 'password' => 'password', 'rol' => Rol::Doctor, 'telefono' => '09'.fake()->numerify('########')]);
            $doctor = $user->doctor()->create([
                'especialidad_id' => $esp[$d[2]]->id,
                'consultorio_id' => $cons[$d[3]]->id,
                'licencia_medica' => 'MSP-'.fake()->unique()->numerify('######'),
                'duracion_cita' => $d[4],
                'tarifa' => $d[5],
                'biografia' => fake()->randomElement([
                    'Más de diez años de práctica clínica. Atiende con calma y explica cada paso.',
                    'Formación en el Hospital Metropolitano. Enfoque preventivo y seguimiento cercano.',
                    'Especialista certificado. Le interesa que cada paciente salga con un plan claro.',
                ]),
            ]);
            foreach ($d[6] as [$dia, $ini, $fin]) {
                $doctor->horarios()->create(['dia_semana' => $dia, 'hora_inicio' => "$ini:00", 'hora_fin' => "$fin:00"]);
            }

            return $doctor->load('horarios');
        });

        $pacienteDemo = User::create(['name' => 'Sofía Herrera', 'email' => 'paciente@tovelmedical.com', 'password' => 'password', 'rol' => Rol::Paciente, 'telefono' => '0991234567']);
        $pacienteDemo->paciente()->create([
            'cedula' => Cedulas::generar(), 'fecha_nacimiento' => '1994-03-12', 'genero' => 'femenino',
            'grupo_sanguineo' => 'O+', 'alergias' => 'Penicilina', 'direccion' => 'La Floresta, Quito',
            'contacto_emergencia' => 'Martín Herrera, 0987654321',
        ]);

        $pacientes = Paciente::factory()->count(34)->create()->prepend($pacienteDemo->paciente);

        $this->generarCitas($doctores, $pacientes);
    }

    private function generarCitas($doctores, $pacientes): void
    {
        $hoy = CarbonImmutable::today();
        $diagnosticos = [
            ['Control general', 'Paciente en buen estado general.', 'Mantener hábitos. Control en 6 meses.', null],
            ['Dolor de garganta', 'Faringitis viral.', 'Hidratación, reposo relativo.', 'Paracetamol 500 mg cada 8 horas por 3 días.'],
            ['Presión alta', 'Hipertensión arterial grado 1.', 'Dieta baja en sodio, caminar 30 min diarios.', 'Losartán 50 mg una vez al día.'],
            ['Manchas en la piel', 'Dermatitis de contacto.', 'Evitar el agente irritante.', 'Hidrocortisona crema 1% dos veces al día por 7 días.'],
            ['Dolor de rodilla', 'Esguince leve de rodilla derecha.', 'Reposo, hielo y compresión.', 'Ibuprofeno 400 mg cada 8 horas por 5 días.'],
            ['Ansiedad', 'Trastorno de ansiedad leve.', 'Terapia semanal, técnicas de respiración.', null],
        ];

        foreach ($doctores as $doctor) {
            for ($i = -21; $i <= 14; $i++) {
                $dia = $hoy->addDays($i);
                $turnos = $doctor->horarios->where('dia_semana', $dia->dayOfWeekIso);
                foreach ($turnos as $t) {
                    $cursor = $dia->setTimeFromTimeString($t->hora_inicio);
                    $fin = $dia->setTimeFromTimeString($t->hora_fin);
                    while ($cursor->addMinutes($doctor->duracion_cita)->lte($fin)) {
                        $finBloque = $cursor->addMinutes($doctor->duracion_cita);
                        $ocupar = random_int(1, 100) <= ($i < 0 ? 65 : ($i <= 3 ? 55 : 30));
                        if ($ocupar) {
                            $this->crearCita($doctor, $pacientes->random(), $cursor, $finBloque, $diagnosticos);
                        }
                        $cursor = $finBloque;
                    }
                }
            }
        }

        // Garantiza que la cuenta demo tenga historial y una cita próxima.
        $demo = $pacientes->first();
        if (! $demo->citas()->where('fecha', '>=', $hoy->toDateString())->activas()->exists()) {
            $doc = $doctores->first();
            $libre = app(AgendaService::class)->diasConCupo($doc, 10);
            foreach ($libre as $d) {
                $b = collect(app(AgendaService::class)->bloques($doc, CarbonImmutable::parse($d['fecha'])))->firstWhere('disponible', true);
                if ($b) {
                    app(AgendaService::class)->reservar($demo, $doc, $d['fecha'], $b['inicio'], 'Control anual', EstadoCita::Confirmada);
                    break;
                }
            }
        }
    }

    private function crearCita(Doctor $doctor, Paciente $paciente, CarbonImmutable $inicio, CarbonImmutable $fin, array $diag): void
    {
        $pasada = $fin->isPast();
        $r = random_int(1, 100);
        $estado = $pasada
            ? ($r <= 80 ? EstadoCita::Completada : ($r <= 90 ? EstadoCita::NoAsistio : EstadoCita::Cancelada))
            : ($r <= 55 ? EstadoCita::Confirmada : ($r <= 92 ? EstadoCita::Pendiente : EstadoCita::Cancelada));
        $d = $diag[array_rand($diag)];

        $cita = Cita::create([
            'paciente_id' => $paciente->id, 'doctor_id' => $doctor->id, 'consultorio_id' => $doctor->consultorio_id,
            'fecha' => $inicio->toDateString(), 'hora_inicio' => $inicio->format('H:i:s'), 'hora_fin' => $fin->format('H:i:s'),
            'motivo' => $d[0], 'estado' => $estado,
            'bloque_activo' => $estado->ocupaAgenda() ? true : null,
            'cancelada_por' => $estado === EstadoCita::Cancelada ? 'paciente' : null,
        ]);

        if ($estado === EstadoCita::Completada) {
            Consulta::create([
                'cita_id' => $cita->id, 'paciente_id' => $paciente->id, 'doctor_id' => $doctor->id,
                'fecha' => $inicio, 'motivo' => $d[0], 'diagnostico' => $d[1], 'tratamiento' => $d[2], 'receta' => $d[3],
                'presion' => random_int(105, 140).'/'.random_int(65, 90), 'frecuencia_cardiaca' => random_int(60, 95),
                'temperatura' => random_int(360, 378) / 10, 'peso' => random_int(450, 950) / 10, 'talla' => random_int(150, 185),
            ]);
            Pago::create([
                'cita_id' => $cita->id, 'paciente_id' => $paciente->id, 'monto' => $doctor->tarifa,
                'metodo' => fake()->randomElement(MetodoPago::cases()),
                'estado' => random_int(1, 10) > 1 ? EstadoPago::Pagado : EstadoPago::Pendiente,
                'fecha_pago' => $inicio->toDateString(),
            ]);
        }
    }
}
