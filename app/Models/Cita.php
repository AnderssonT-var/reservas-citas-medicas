<?php

namespace App\Models;

use App\Enums\EstadoCita;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id', 'doctor_id', 'consultorio_id', 'fecha', 'hora_inicio', 'hora_fin',
        'motivo', 'estado', 'bloque_activo', 'cancelada_por', 'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'estado' => EstadoCita::class,
        'bloque_activo' => 'boolean',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function consulta(): HasOne
    {
        return $this->hasOne(Consulta::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->whereIn('estado', array_map(fn ($e) => $e->value, EstadoCita::activos()));
    }

    public function scopeProximas(Builder $q): Builder
    {
        $ahora = now();

        return $q->where(function ($w) use ($ahora) {
            $w->where('fecha', '>', $ahora->toDateString())
                ->orWhere(fn ($h) => $h->where('fecha', $ahora->toDateString())->where('hora_inicio', '>=', $ahora->format('H:i:s')));
        })->orderBy('fecha')->orderBy('hora_inicio');
    }

    public function inicio(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->fecha->toDateString().' '.$this->hora_inicio);
    }

    public function fin(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->fecha->toDateString().' '.$this->hora_fin);
    }

    public function horaInicio(): string
    {
        return substr($this->hora_inicio, 0, 5);
    }

    public function horaFin(): string
    {
        return substr($this->hora_fin, 0, 5);
    }

    /** Ruta de detalle según el perfil de quien la mira. */
    public function rutaPara(?User $user): string
    {
        return match (true) {
            $user?->esDoctor() => route('doctor.citas.show', $this),
            $user?->esPaciente() => route('paciente.citas.show', $this),
            default => route('admin.citas.show', $this),
        };
    }

    public function yaPaso(): bool
    {
        return $this->fin()->isPast();
    }
}
