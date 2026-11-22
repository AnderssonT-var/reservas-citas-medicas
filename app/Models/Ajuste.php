<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Ajuste extends Model
{
    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['clave', 'valor'];

    public const DEFAULTS = [
        'nombre_clinica' => 'TovelMedical',
        'telefono' => '',
        'direccion' => '',
        'correo' => '',
        'horas_cancelacion' => '2',
        'dias_reserva' => '45',
    ];

    public static function todos(): array
    {
        return Cache::rememberForever('ajustes', function () {
            try {
                return array_merge(self::DEFAULTS, self::query()->pluck('valor', 'clave')->all());
            } catch (\Throwable) {
                return self::DEFAULTS;
            }
        });
    }

    public static function valor(string $clave, mixed $defecto = null): mixed
    {
        return self::todos()[$clave] ?? $defecto;
    }

    public static function guardar(array $valores): void
    {
        foreach ($valores as $clave => $valor) {
            self::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        }
        Cache::forget('ajustes');
    }
}
