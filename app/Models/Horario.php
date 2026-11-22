<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    public const DIAS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    protected $fillable = ['doctor_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    protected $casts = ['dia_semana' => 'integer'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getDiaAttribute(): string
    {
        return self::DIAS[$this->dia_semana] ?? '';
    }

    public function getInicioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5);
    }

    public function getFinAttribute(): string
    {
        return substr($this->hora_fin, 0, 5);
    }
}
