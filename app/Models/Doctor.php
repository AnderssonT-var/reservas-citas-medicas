<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctores';

    protected $fillable = [
        'user_id', 'especialidad_id', 'consultorio_id', 'licencia_medica',
        'biografia', 'duracion_cita', 'tarifa', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'tarifa' => 'decimal:2',
        'duracion_cita' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class)->orderBy('dia_semana')->orderBy('hora_inicio');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true)->whereHas('user', fn ($u) => $u->where('activo', true));
    }

    public function getNombreAttribute(): string
    {
        return 'Dr(a). '.$this->user?->name;
    }
}
