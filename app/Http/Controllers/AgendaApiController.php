<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Services\AgendaService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AgendaApiController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function doctores(Request $request)
    {
        $doctores = Doctor::activos()
            ->with(['user:id,name', 'especialidad:id,nombre,color', 'consultorio:id,nombre,ubicacion'])
            ->when($request->integer('especialidad'), fn ($q, $id) => $q->where('especialidad_id', $id))
            ->whereHas('horarios')
            ->get()
            ->map(fn (Doctor $d) => [
                'id' => $d->id,
                'nombre' => $d->user->name,
                'iniciales' => $d->user->iniciales(),
                'especialidad' => $d->especialidad->nombre,
                'color' => $d->especialidad->color,
                'consultorio' => $d->consultorio?->nombre,
                'duracion' => $d->duracion_cita,
                'tarifa' => (float) $d->tarifa,
            ]);

        return response()->json($doctores);
    }

    public function dias(Doctor $doctor)
    {
        abort_unless($doctor->activo, 404);

        return response()->json($this->agenda->diasConCupo($doctor, 28));
    }

    public function bloques(Request $request, Doctor $doctor)
    {
        abort_unless($doctor->activo, 404);
        $request->validate(['fecha' => ['required', 'date_format:Y-m-d']]);

        return response()->json($this->agenda->bloques($doctor, CarbonImmutable::parse($request->fecha)));
    }
}
