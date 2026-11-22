<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Consulta;
use App\Services\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function create(Cita $cita)
    {
        $this->authorize('atender', $cita);
        abort_unless($cita->estado->esModificable(), 403, 'Esta cita ya fue cerrada.');
        $cita->load(['paciente.user', 'paciente.consultas.doctor.user', 'paciente.consultas.doctor.especialidad']);

        return view('doctor.atender', ['cita' => $cita, 'consulta' => new Consulta(['motivo' => $cita->motivo])]);
    }

    public function store(Request $request, Cita $cita)
    {
        $this->authorize('atender', $cita);
        $datos = $this->validar($request);

        DB::transaction(function () use ($cita, $datos) {
            Consulta::create($datos + [
                'cita_id' => $cita->id,
                'paciente_id' => $cita->paciente_id,
                'doctor_id' => $cita->doctor_id,
                'fecha' => now(),
            ]);
            $this->agenda->completar($cita);
        });

        return redirect()->route('doctor.dashboard')->with('ok', 'Consulta guardada en el historial de '.$cita->paciente->user->primerNombre().'.');
    }

    public function edit(Consulta $consulta)
    {
        $this->authorize('update', $consulta);
        $consulta->load('paciente.user', 'cita');

        return view('doctor.atender', ['cita' => $consulta->cita, 'consulta' => $consulta, 'paciente' => $consulta->paciente]);
    }

    public function update(Request $request, Consulta $consulta)
    {
        $this->authorize('update', $consulta);
        $consulta->update($this->validar($request));

        return redirect()->route('consultas.show', $consulta)->with('ok', 'Consulta actualizada.');
    }

    private function validar(Request $r): array
    {
        return $r->validate([
            'motivo' => ['required', 'string', 'max:255'],
            'sintomas' => ['nullable', 'string', 'max:2000'],
            'diagnostico' => ['required', 'string', 'max:2000'],
            'tratamiento' => ['nullable', 'string', 'max:2000'],
            'receta' => ['nullable', 'string', 'max:2000'],
            'presion' => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'between:20,250'],
            'temperatura' => ['nullable', 'numeric', 'between:30,45'],
            'peso' => ['nullable', 'numeric', 'between:0.5,400'],
            'talla' => ['nullable', 'integer', 'between:30,250'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ], ['presion.regex' => 'Escribe la presión como 120/80.']);
    }
}
