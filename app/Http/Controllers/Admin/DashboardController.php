<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoCita;
use App\Enums\EstadoPago;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use App\Models\Pago;
use Carbon\CarbonImmutable;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $hoy = CarbonImmutable::today();

        $citasHoy = Cita::with(['paciente.user', 'doctor.user', 'doctor.especialidad'])
            ->whereDate('fecha', $hoy)->orderBy('hora_inicio')->get();

        // Citas por día en las últimas 2 semanas y la próxima.
        $desde = $hoy->subDays(13);
        $porDia = Cita::selectRaw('fecha, estado, count(*) as total')
            ->whereBetween('fecha', [$desde->toDateString(), $hoy->addDays(7)->toDateString()])
            ->groupBy('fecha', 'estado')->get()
            ->groupBy(fn ($r) => CarbonImmutable::parse($r->fecha)->toDateString());

        $serie = collect(range(0, 20))->map(function ($i) use ($desde, $porDia, $hoy) {
            $d = $desde->addDays($i);
            $filas = $porDia->get($d->toDateString(), collect());

            return [
                'fecha' => $d,
                'hoy' => $d->equalTo($hoy),
                'atendidas' => (int) $filas->where('estado', EstadoCita::Completada)->sum('total'),
                'agendadas' => (int) $filas->whereIn('estado', [EstadoCita::Pendiente, EstadoCita::Confirmada])->sum('total'),
                'perdidas' => (int) $filas->whereIn('estado', [EstadoCita::Cancelada, EstadoCita::NoAsistio])->sum('total'),
            ];
        });

        $inicioMes = $hoy->startOfMonth();
        $ingresosMes = (float) Pago::where('estado', EstadoPago::Pagado)->whereBetween('fecha_pago', [$inicioMes, $hoy])->sum('monto');
        $ingresosMesAnterior = (float) Pago::where('estado', EstadoPago::Pagado)
            ->whereBetween('fecha_pago', [$inicioMes->subMonth(), $hoy->subMonth()])->sum('monto');

        $mes = Cita::whereBetween('fecha', [$inicioMes, $hoy])->get(['estado']);
        $cerradas = $mes->whereIn('estado', [EstadoCita::Completada, EstadoCita::NoAsistio])->count();
        $asistencia = $cerradas ? round($mes->where('estado', EstadoCita::Completada)->count() / $cerradas * 100) : null;

        $doctores = Doctor::activos()->with(['user', 'especialidad'])
            ->withCount(['citas as citas_semana' => fn ($q) => $q->activas()->whereBetween('fecha', [$hoy->startOfWeek(), $hoy->endOfWeek()])])
            ->orderByDesc('citas_semana')->take(5)->get();

        return view('admin.dashboard', [
            'citasHoy' => $citasHoy,
            'serie' => $serie,
            'ingresosMes' => $ingresosMes,
            'ingresosMesAnterior' => $ingresosMesAnterior,
            'asistencia' => $asistencia,
            'totalPacientes' => Paciente::count(),
            'pacientesNuevos' => Paciente::where('created_at', '>=', $inicioMes)->count(),
            'porConfirmar' => Cita::where('estado', EstadoCita::Pendiente)->where('fecha', '>=', $hoy->toDateString())->count(),
            'doctores' => $doctores,
        ]);
    }
}
