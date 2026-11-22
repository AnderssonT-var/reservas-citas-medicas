<?php

namespace App\Models;

use App\Enums\Rol;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'rol', 'telefono', 'activo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => Rol::class,
            'activo' => 'boolean',
        ];
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function paciente(): HasOne
    {
        return $this->hasOne(Paciente::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === Rol::Admin;
    }

    public function esDoctor(): bool
    {
        return $this->rol === Rol::Doctor;
    }

    public function esPaciente(): bool
    {
        return $this->rol === Rol::Paciente;
    }

    public function iniciales(): string
    {
        $partes = preg_split('/\s+/', trim($this->name)) ?: [];
        $ini = mb_substr($partes[0] ?? '', 0, 1).mb_substr($partes[1] ?? '', 0, 1);

        return mb_strtoupper($ini ?: '?');
    }

    public function primerNombre(): string
    {
        return explode(' ', trim($this->name))[0];
    }
}
