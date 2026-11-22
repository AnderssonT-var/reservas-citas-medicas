<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\EstadoCita;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Doctor;
use App\Services\AgendaService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function dashboard(Request $request)
    {
        return $this->agendaDel($request, CarbonImmutable::today(), 'doctor.dashboard');
    }

    public function agenda(Request $request)
    {
        $request->validate(['fecha' => ['nullable', 'date_format:Y-m-d']]);
        $fecha = $request->fecha ? CarbonImmutable::parse($request->fecha) : CarbonImmutable::today();

        return $this->agendaDel($request, $fecha, 'doctor.agenda');
    }

    public function cita(Request $request, Cita $cita)
    {
        $this->authorize('view', $cita);
        $cita->load(['paciente.user', 'paciente.consultas.doctor.user', 'paciente.consultas.doctor.especialidad', 'doctor.user', 'doctor.especialidad', 'consultorio', 'consulta']);

        return view('citas.show', ['cita' => $cita, 'puedeCancelar' => $this->agenda->puedeCancelar($cita, $request->user())]);
    }

    private function agendaDel(Request $request, CarbonImmutable $fecha, string $vista)
    {
        /** @var Doctor $doctor */
        $doctor = $request->user()->doctor()->with(['especialidad', 'consultorio', 'horarios'])->firstOrFail();

        $citas = $doctor->citas()->with('paciente.user')
            ->whereDate('fecha', $fecha)->where('estado', '!=', EstadoCita::Cancelada)
            ->orderBy('hora_inicio')->get();

        $semana = collect(range(0, 6))->map(function ($i) use ($fecha, $doctor) {
            $d = $fecha->startOfWeek()->addDays($i);

            return [
                'fecha' => $d,
                'total' => $doctor->citas()->activas()->whereDate('fecha', $d)->count(),
                'atiende' => $doctor->horarios->contains('dia_semana', $d->dayOfWeekIso),
            ];
        });

        $resumen = [
            'pendientes' => $doctor->citas()->where('estado', EstadoCita::Pendiente)->where('fecha', '>=', today()->toDateString())->count(),
            'semana' => $semana->sum('total'),
            'atendidasMes' => $doctor->citas()->where('estado', EstadoCita::Completada)->where('fecha', '>=', today()->startOfMonth()->toDateString())->count(),
        ];

        return view($vista, compact('doctor', 'citas', 'fecha', 'semana', 'resumen'));
    }
}
