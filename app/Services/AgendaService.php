<?php

namespace App\Services;

use App\Enums\EstadoCita;
use App\Models\Ajuste;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reglas de la agenda: qué bloques existen, cuáles están libres y cómo se
 * reserva, cancela y cambia de estado una cita sin que dos personas tomen
 * el mismo bloque.
 */
class AgendaService
{
    /** Minutos mínimos de anticipación para reservar un bloque de hoy. */
    public const ANTICIPACION_MIN = 30;

    public function diasMaximos(): int
    {
        return max(1, (int) Ajuste::valor('dias_reserva', 45));
    }

    public function horasCancelacion(): int
    {
        return max(0, (int) Ajuste::valor('horas_cancelacion', 2));
    }

    /**
     * Bloques del día para un doctor, marcando cuáles se pueden reservar.
     *
     * @return array<int, array{inicio:string, fin:string, disponible:bool}>
     */
    public function bloques(Doctor $doctor, CarbonImmutable $fecha, ?int $ignorarCitaId = null): array
    {
        $fecha = $fecha->startOfDay();
        $hoy = CarbonImmutable::today();

        if ($fecha->lt($hoy) || $fecha->gt($hoy->addDays($this->diasMaximos()))) {
            return [];
        }

        $horarios = $doctor->horarios()->where('dia_semana', $fecha->dayOfWeekIso)->get();
        if ($horarios->isEmpty()) {
            return [];
        }

        $ocupadas = $this->citasQueOcupan($doctor, $fecha, $ignorarCitaId);
        $limite = CarbonImmutable::now()->addMinutes(self::ANTICIPACION_MIN);
        $duracion = max(5, $doctor->duracion_cita);
        $bloques = [];

        foreach ($horarios as $h) {
            $cursor = $fecha->setTimeFromTimeString($h->hora_inicio);
            $finTurno = $fecha->setTimeFromTimeString($h->hora_fin);

            while ($cursor->addMinutes($duracion)->lte($finTurno)) {
                $fin = $cursor->addMinutes($duracion);
                $libre = $cursor->gte($limite) && ! $this->choca($ocupadas, $cursor, $fin);
                $bloques[$cursor->format('H:i')] = [
                    'inicio' => $cursor->format('H:i'),
                    'fin' => $fin->format('H:i'),
                    'disponible' => $libre,
                ];
                $cursor = $fin;
            }
        }

        ksort($bloques);

        return array_values($bloques);
    }

    /**
     * Fechas de los próximos días con al menos un bloque libre.
     *
     * @return array<int, array{fecha:string, libres:int}>
     */
    public function diasConCupo(Doctor $doctor, int $dias = 21): array
    {
        $dias = min($dias, $this->diasMaximos());
        $diasSemana = $doctor->horarios()->pluck('dia_semana')->unique()->all();
        $hoy = CarbonImmutable::today();
        $res = [];

        for ($i = 0; $i <= $dias; $i++) {
            $f = $hoy->addDays($i);
            if (! in_array($f->dayOfWeekIso, $diasSemana, true)) {
                continue;
            }
            $libres = count(array_filter($this->bloques($doctor, $f), fn ($b) => $b['disponible']));
            $res[] = ['fecha' => $f->toDateString(), 'libres' => $libres];
        }

        return $res;
    }

    /**
     * Reserva un bloque. Serializa las reservas del mismo doctor con un
     * bloqueo de fila y además se apoya en el índice único citas_bloque_unico.
     */
    public function reservar(Paciente $paciente, Doctor $doctor, string $fecha, string $hora, ?string $motivo = null, EstadoCita $estado = EstadoCita::Pendiente): Cita
    {
        $dia = CarbonImmutable::parse($fecha)->startOfDay();
        $hora = substr($hora, 0, 5);

        if (! $doctor->activo) {
            $this->fallar('hora', 'Este doctor no está recibiendo citas por ahora.');
        }

        try {
            return DB::transaction(function () use ($paciente, $doctor, $dia, $hora, $motivo, $estado) {
                // Serializa reservas concurrentes del mismo doctor.
                Doctor::whereKey($doctor->id)->lockForUpdate()->first();

                $bloque = collect($this->bloques($doctor, $dia))->firstWhere('inicio', $hora);
                if (! $bloque) {
                    $this->fallar('hora', 'Ese horario no existe en la agenda del doctor.');
                }
                if (! $bloque['disponible']) {
                    $this->fallar('hora', 'Ese horario acaba de ocuparse. Elige otro, por favor.');
                }

                $inicio = $dia->setTimeFromTimeString($bloque['inicio']);
                $fin = $dia->setTimeFromTimeString($bloque['fin']);

                $chocaPaciente = Cita::activas()
                    ->where('paciente_id', $paciente->id)
                    ->whereDate('fecha', $dia->toDateString())
                    ->where('hora_inicio', '<', $fin->format('H:i:s'))
                    ->where('hora_fin', '>', $inicio->format('H:i:s'))
                    ->exists();
                if ($chocaPaciente) {
                    $this->fallar('hora', 'Ya tienes otra cita a esa misma hora.');
                }

                $pendienteMismoDoctor = Cita::query()
                    ->whereIn('estado', [EstadoCita::Pendiente->value, EstadoCita::Confirmada->value])
                    ->where('paciente_id', $paciente->id)
                    ->where('doctor_id', $doctor->id)
                    ->whereDate('fecha', $dia->toDateString())
                    ->exists();
                if ($pendienteMismoDoctor) {
                    $this->fallar('hora', 'Ya tienes una cita con este doctor ese día.');
                }

                return Cita::create([
                    'paciente_id' => $paciente->id,
                    'doctor_id' => $doctor->id,
                    'consultorio_id' => $doctor->consultorio_id,
                    'fecha' => $dia->toDateString(),
                    'hora_inicio' => $inicio->format('H:i:s'),
                    'hora_fin' => $fin->format('H:i:s'),
                    'motivo' => $motivo,
                    'estado' => $estado,
                    'bloque_activo' => true,
                ]);
            });
        } catch (QueryException $e) {
            // Carrera perdida contra el índice único.
            if (str_contains(strtolower($e->getMessage()), 'unique')) {
                $this->fallar('hora', 'Ese horario acaba de ocuparse. Elige otro, por favor.');
            }
            throw $e;
        }
    }

    public function puedeCancelar(Cita $cita, User $user): bool
    {
        if (! $cita->estado->esModificable()) {
            return false;
        }

        if ($user->esPaciente()) {
            return $cita->paciente?->user_id === $user->id
                && $cita->inicio()->gt(now()->addHours($this->horasCancelacion()));
        }

        if ($user->esDoctor()) {
            return $cita->doctor?->user_id === $user->id;
        }

        return $user->esAdmin();
    }

    public function cancelar(Cita $cita, User $user, ?string $motivo = null): Cita
    {
        if (! $this->puedeCancelar($cita, $user)) {
            $msg = $user->esPaciente() && $cita->estado->esModificable()
                ? "Las citas se pueden cancelar hasta {$this->horasCancelacion()} horas antes. Llama a la clínica para cambiarla."
                : 'Esta cita ya no se puede cancelar.';
            $this->fallar('cita', $msg);
        }

        $cita->update([
            'estado' => EstadoCita::Cancelada,
            'bloque_activo' => null,
            'cancelada_por' => $user->rol->value,
            'motivo_cancelacion' => $motivo,
        ]);

        return $cita;
    }

    public function confirmar(Cita $cita): Cita
    {
        if ($cita->estado !== EstadoCita::Pendiente) {
            $this->fallar('cita', 'Solo se confirman citas por confirmar.');
        }
        $cita->update(['estado' => EstadoCita::Confirmada]);

        return $cita;
    }

    public function marcarNoAsistio(Cita $cita): Cita
    {
        if (! $cita->estado->esModificable()) {
            $this->fallar('cita', 'Esta cita ya fue cerrada.');
        }
        if ($cita->inicio()->isFuture()) {
            $this->fallar('cita', 'Todavía no llega la hora de esta cita.');
        }
        // Libera el bloque: ya pasó, pero así no cuenta como ocupado en reportes.
        $cita->update(['estado' => EstadoCita::NoAsistio, 'bloque_activo' => null]);

        return $cita;
    }

    public function completar(Cita $cita): Cita
    {
        if (! $cita->estado->esModificable()) {
            $this->fallar('cita', 'Esta cita ya fue cerrada.');
        }
        $cita->update(['estado' => EstadoCita::Completada]);

        return $cita;
    }

    /** @return Collection<int, Cita> */
    private function citasQueOcupan(Doctor $doctor, CarbonImmutable $fecha, ?int $ignorar): Collection
    {
        return Cita::activas()
            ->where('doctor_id', $doctor->id)
            ->whereDate('fecha', $fecha->toDateString())
            ->when($ignorar, fn ($q) => $q->whereKeyNot($ignorar))
            ->get(['id', 'hora_inicio', 'hora_fin']);
    }

    private function choca(Collection $citas, CarbonImmutable $inicio, CarbonImmutable $fin): bool
    {
        $i = $inicio->format('H:i:s');
        $f = $fin->format('H:i:s');

        return $citas->contains(fn ($c) => $c->hora_inicio < $f && $c->hora_fin > $i);
    }

    private function fallar(string $campo, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }
}
