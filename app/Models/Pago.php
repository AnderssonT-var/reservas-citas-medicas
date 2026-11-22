<?php

namespace App\Models;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = ['cita_id', 'paciente_id', 'registrado_por', 'monto', 'metodo', 'estado', 'referencia', 'fecha_pago'];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
        'metodo' => MetodoPago::class,
        'estado' => EstadoPago::class,
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
