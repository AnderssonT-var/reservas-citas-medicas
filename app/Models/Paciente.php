<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'cedula', 'fecha_nacimiento', 'genero', 'direccion',
        'grupo_sanguineo', 'alergias', 'contacto_emergencia',
    ];

    protected $casts = ['fecha_nacimiento' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class)->latest('fecha');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function edad(): ?int
    {
        return $this->fecha_nacimiento?->age;
    }
}
