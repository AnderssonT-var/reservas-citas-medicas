<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\User;
use App\Rules\CedulaEcuatoriana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $pacientes = Paciente::with('user')
            ->withCount('citas')
            ->withMax('citas', 'fecha')
            ->when($request->q, function ($q, $t) {
                $q->where('cedula', 'like', "%$t%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$t%")->orWhere('email', 'like', "%$t%"));
            })
            ->latest()->paginate(15)->withQueryString();

        return view('admin.pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        return view('admin.pacientes.form', ['paciente' => new Paciente]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $paciente = DB::transaction(function () use ($datos) {
            $user = User::create([
                'name' => $datos['name'], 'email' => $datos['email'], 'telefono' => $datos['telefono'] ?? null,
                // Contraseña inicial = cédula; el paciente la cambia desde su perfil.
                'password' => $datos['cedula'], 'rol' => Rol::Paciente,
            ]);

            return $user->paciente()->create($this->soloPaciente($datos));
        });

        return redirect()->route('admin.pacientes.show', $paciente)
            ->with('ok', 'Paciente registrado. Su contraseña inicial es su número de cédula.');
    }

    public function show(Paciente $paciente)
    {
        $paciente->load(['user', 'consultas.doctor.user', 'consultas.doctor.especialidad']);
        $citas = $paciente->citas()->with('doctor.user', 'doctor.especialidad')->latest('fecha')->latest('hora_inicio')->take(10)->get();
        $pagos = $paciente->pagos()->latest('fecha_pago')->take(5)->get();

        return view('admin.pacientes.show', compact('paciente', 'citas', 'pagos'));
    }

    public function edit(Paciente $paciente)
    {
        return view('admin.pacientes.form', ['paciente' => $paciente->load('user')]);
    }

    public function update(Request $request, Paciente $paciente)
    {
        $datos = $this->validar($request, $paciente);
        DB::transaction(function () use ($datos, $paciente) {
            $paciente->user->update(['name' => $datos['name'], 'email' => $datos['email'], 'telefono' => $datos['telefono'] ?? null]);
            $paciente->update($this->soloPaciente($datos));
        });

        return redirect()->route('admin.pacientes.show', $paciente)->with('ok', 'Cambios guardados.');
    }

    public function destroy(Paciente $paciente)
    {
        if ($paciente->consultas()->exists()) {
            $paciente->user->update(['activo' => false]);

            return redirect()->route('admin.pacientes.index')->with('ok', 'El paciente tiene historial clínico, así que se desactivó su cuenta en lugar de eliminarla.');
        }
        $paciente->user->delete();

        return redirect()->route('admin.pacientes.index')->with('ok', 'Paciente eliminado.');
    }

    private function validar(Request $request, ?Paciente $p = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($p?->user_id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'cedula' => ['required', 'digits:10', Rule::unique('pacientes')->ignore($p?->id), new CedulaEcuatoriana],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero' => ['nullable', Rule::in(['femenino', 'masculino', 'otro'])],
            'direccion' => ['nullable', 'string', 'max:255'],
            'grupo_sanguineo' => ['nullable', Rule::in(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'])],
            'alergias' => ['nullable', 'string', 'max:500'],
            'contacto_emergencia' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function soloPaciente(array $d): array
    {
        return collect($d)->except(['name', 'email', 'telefono'])->all();
    }
}
