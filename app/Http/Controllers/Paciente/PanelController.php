<?php

namespace App\Http\Controllers\Paciente;

use App\Enums\EstadoCita;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Especialidad;
use App\Services\AgendaService;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function inicio(Request $request)
    {
        $paciente = $request->user()->paciente;
        $proximas = $paciente->citas()->activas()->whereIn('estado', [EstadoCita::Pendiente, EstadoCita::Confirmada])
            ->proximas()->with('doctor.user', 'doctor.especialidad', 'consultorio')->take(3)->get();
        $ultimaConsulta = $paciente->consultas()->with('doctor.user', 'doctor.especialidad')->first();

        return view('paciente.inicio', [
            'paciente' => $paciente,
            'proximas' => $proximas,
            'ultimaConsulta' => $ultimaConsulta,
            'totalConsultas' => $paciente->consultas()->count(),
            'especialidades' => Especialidad::whereHas('doctores', fn ($q) => $q->activos())->orderBy('nombre')->get(),
        ]);
    }

    public function reservar(Request $request)
    {
        return view('paciente.reservar', [
            'especialidades' => Especialidad::whereHas('doctores', fn ($q) => $q->activos()->whereHas('horarios'))->orderBy('nombre')->get(),
            'preDoctor' => $request->integer('doctor') ?: null,
            'preEspecialidad' => $request->integer('especialidad')
                ?: ($request->integer('doctor') ? Doctor::find($request->integer('doctor'))?->especialidad_id : null),
        ]);
    }

    public function guardarReserva(Request $request)
    {
        $datos = $request->validate([
            'doctor_id' => ['required', 'exists:doctores,id'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $cita = $this->agenda->reservar(
            $request->user()->paciente, Doctor::findOrFail($datos['doctor_id']),
            $datos['fecha'], $datos['hora'], $datos['motivo'] ?? null,
        );

        return redirect()->route('paciente.citas.show', $cita)->with('ok', 'Cita reservada. Te esperamos.');
    }

    public function citas(Request $request)
    {
        $todas = $request->user()->paciente->citas()
            ->with('doctor.user', 'doctor.especialidad', 'consultorio')
            ->orderByDesc('fecha')->orderByDesc('hora_inicio')->get();

        [$proximas, $pasadas] = $todas->partition(fn (Cita $c) => $c->estado->esModificable() && ! $c->yaPaso());

        return view('paciente.citas', ['proximas' => $proximas->reverse()->values(), 'pasadas' => $pasadas]);
    }

    public function cita(Request $request, Cita $cita)
    {
        $this->authorize('view', $cita);
        $cita->load(['doctor.user', 'doctor.especialidad', 'consultorio', 'consulta', 'paciente.user']);

        return view('citas.show', ['cita' => $cita, 'puedeCancelar' => $this->agenda->puedeCancelar($cita, $request->user())]);
    }

    public function historial(Request $request)
    {
        $paciente = $request->user()->paciente->load(['user', 'consultas.doctor.user', 'consultas.doctor.especialidad']);

        return view('pacientes.historial', compact('paciente'));
    }
}
