<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $doctor = $request->user()->doctor;
        $pacientes = Paciente::with('user')
            ->whereHas('citas', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->withCount(['citas as visitas' => fn ($q) => $q->where('doctor_id', $doctor->id)->where('estado', 'completada')])
            ->withMax(['citas as ultima' => fn ($q) => $q->where('doctor_id', $doctor->id)], 'fecha')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('cedula', 'like', "%$t%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$t%"))))
            ->orderByDesc('ultima')->paginate(20)->withQueryString();

        return view('doctor.pacientes', compact('pacientes'));
    }

    public function show(Request $request, Paciente $paciente)
    {
        // Solo pacientes que han tenido cita con este doctor.
        abort_unless($paciente->citas()->where('doctor_id', $request->user()->doctor->id)->exists(), 403);
        $paciente->load(['user', 'consultas.doctor.user', 'consultas.doctor.especialidad']);

        return view('pacientes.historial', compact('paciente'));
    }
}
