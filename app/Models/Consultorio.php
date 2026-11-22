<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultorio extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'ubicacion', 'telefono', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function doctores(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
