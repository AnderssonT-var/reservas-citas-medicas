<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoCita;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Especialidad;
use App\Models\Paciente;
use App\Services\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CitaController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function index(Request $request)
    {
        $citas = $this->filtradas($request)
            ->with(['paciente.user', 'doctor.user', 'doctor.especialidad'])
            ->orderByDesc('fecha')->orderBy('hora_inicio')
            ->paginate(20)->withQueryString();

        return view('admin.citas.index', [
            'citas' => $citas,
            'doctores' => Doctor::with('user')->get()->sortBy('user.name'),
            'estados' => EstadoCita::cases(),
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        $citas = $this->filtradas($request)->with(['paciente.user', 'doctor.user', 'doctor.especialidad'])
            ->orderBy('fecha')->orderBy('hora_inicio')->get();

        return response()->streamDownload(function () use ($citas) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea las tildes
            fputcsv($out, ['Fecha', 'Hora', 'Paciente', 'Cédula', 'Doctor', 'Especialidad', 'Estado', 'Motivo']);
            foreach ($citas as $c) {
                fputcsv($out, [
                    $c->fecha->toDateString(), $c->horaInicio(), $c->paciente->user->name, $c->paciente->cedula,
                    $c->doctor->user->name, $c->doctor->especialidad->nombre, $c->estado->label(), $c->motivo,
                ]);
            }
            fclose($out);
        }, 'citas-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        return view('admin.citas.create', [
            'pacientes' => Paciente::with('user')->get()->sortBy('user.name'),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'doctor_id' => ['required', 'exists:doctores,id'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $cita = $this->agenda->reservar(
            Paciente::findOrFail($datos['paciente_id']),
            Doctor::findOrFail($datos['doctor_id']),
            $datos['fecha'], $datos['hora'], $datos['motivo'] ?? null,
            EstadoCita::Confirmada, // agendada por la clínica: ya está confirmada
        );

        return redirect()->route('admin.citas.show', $cita)->with('ok', 'Cita agendada y confirmada.');
    }

    public function show(Cita $cita)
    {
        $cita->load(['paciente.user', 'doctor.user', 'doctor.especialidad', 'consultorio', 'consulta', 'pagos']);

        return view('citas.show', ['cita' => $cita, 'puedeCancelar' => $this->agenda->puedeCancelar($cita, request()->user())]);
    }

    private function filtradas(Request $r)
    {
        $r->validate([
            'estado' => ['nullable', Rule::enum(EstadoCita::class)],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);

        return Cita::query()
            ->when($r->doctor, fn ($q, $d) => $q->where('doctor_id', $d))
            ->when($r->estado, fn ($q, $e) => $q->where('estado', $e))
            ->when($r->desde, fn ($q, $d) => $q->where('fecha', '>=', $d))
            ->when($r->hasta, fn ($q, $h) => $q->where('fecha', '<=', $h))
            ->when($r->q, fn ($q, $t) => $q->whereHas('paciente', fn ($p) => $p->where('cedula', 'like', "%$t%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$t%"))));
    }
}
