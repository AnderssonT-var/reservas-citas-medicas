<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consulta extends Model
{
    protected $fillable = [
        'cita_id', 'paciente_id', 'doctor_id', 'fecha', 'motivo', 'sintomas', 'diagnostico',
        'tratamiento', 'receta', 'presion', 'frecuencia_cardiaca', 'temperatura', 'peso', 'talla', 'notas',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'temperatura' => 'decimal:1',
        'peso' => 'decimal:1',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function imc(): ?float
    {
        if (! $this->peso || ! $this->talla) {
            return null;
        }
        $m = $this->talla / 100;

        return round((float) $this->peso / ($m * $m), 1);
    }
}
