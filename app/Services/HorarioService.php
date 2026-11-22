<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HorarioService
{
    /**
     * Reemplaza los turnos semanales del doctor.
     *
     * @param  array<int, array{dia_semana:int|string, hora_inicio:string, hora_fin:string}>  $filas
     */
    public function sincronizar(Doctor $doctor, array $filas): void
    {
        $limpias = collect($filas)
            ->filter(fn ($f) => ! empty($f['dia_semana']) && ! empty($f['hora_inicio']) && ! empty($f['hora_fin']))
            ->map(fn ($f) => [
                'dia_semana' => (int) $f['dia_semana'],
                'hora_inicio' => substr($f['hora_inicio'], 0, 5).':00',
                'hora_fin' => substr($f['hora_fin'], 0, 5).':00',
            ])
            ->values();

        $errores = [];
        foreach ($limpias as $i => $f) {
            if ($f['dia_semana'] < 1 || $f['dia_semana'] > 7) {
                $errores["horarios.$i.dia_semana"] = 'Elige un día válido.';
            }
            if ($f['hora_inicio'] >= $f['hora_fin']) {
                $errores["horarios.$i.hora_fin"] = 'La hora de fin debe ser posterior al inicio.';
            }
        }

        foreach ($limpias->groupBy('dia_semana') as $turnos) {
            $ordenados = $turnos->sortBy('hora_inicio')->values();
            for ($i = 1; $i < $ordenados->count(); $i++) {
                if ($ordenados[$i]['hora_inicio'] < $ordenados[$i - 1]['hora_fin']) {
                    $idx = $limpias->search($ordenados[$i]);
                    $errores["horarios.$idx.hora_inicio"] = 'Este turno se cruza con otro del mismo día.';
                }
            }
        }

        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        DB::transaction(function () use ($doctor, $limpias) {
            $doctor->horarios()->delete();
            $doctor->horarios()->createMany($limpias->all());
        });
    }
}
