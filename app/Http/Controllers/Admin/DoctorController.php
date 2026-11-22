<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Consultorio;
use App\Models\Doctor;
use App\Models\Especialidad;
use App\Models\User;
use App\Services\HorarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DoctorController extends Controller
{
    public function __construct(private HorarioService $horarios) {}

    public function index(Request $request)
    {
        $doctores = Doctor::with(['user', 'especialidad', 'consultorio'])
            ->withCount('horarios')
            ->when($request->q, fn ($q, $t) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%$t%")))
            ->when($request->especialidad, fn ($q, $e) => $q->where('especialidad_id', $e))
            ->get()->sortBy('user.name');

        return view('admin.doctores.index', [
            'doctores' => $doctores,
            'especialidades' => Especialidad::orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.doctores.form', $this->datosForm(new Doctor(['duracion_cita' => 30, 'activo' => true])));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $doctor = DB::transaction(function () use ($datos) {
            $user = User::create([
                'name' => $datos['name'], 'email' => $datos['email'], 'telefono' => $datos['telefono'] ?? null,
                'password' => $datos['password'], 'rol' => Rol::Doctor,
            ]);
            $doctor = $user->doctor()->create($this->soloDoctor($datos));
            $this->horarios->sincronizar($doctor, $datos['horarios'] ?? []);

            return $doctor;
        });

        return redirect()->route('admin.doctores.show', $doctor)->with('ok', 'Doctor registrado. Ya puede iniciar sesión.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'especialidad', 'consultorio', 'horarios']);
        $proximas = $doctor->citas()->activas()->proximas()->with('paciente.user')->take(8)->get();
        $stats = [
            'atendidas' => $doctor->citas()->where('estado', 'completada')->count(),
            'pacientes' => $doctor->citas()->distinct('paciente_id')->count('paciente_id'),
            'proximas' => $doctor->citas()->activas()->proximas()->count(),
        ];

        return view('admin.doctores.show', compact('doctor', 'proximas', 'stats'));
    }

    public function edit(Doctor $doctor)
    {
        return view('admin.doctores.form', $this->datosForm($doctor->load('user', 'horarios')));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $datos = $this->validar($request, $doctor);

        DB::transaction(function () use ($datos, $doctor) {
            $doctor->user->fill([
                'name' => $datos['name'], 'email' => $datos['email'], 'telefono' => $datos['telefono'] ?? null,
                'activo' => $datos['activo'],
            ]);
            if (! empty($datos['password'])) {
                $doctor->user->password = $datos['password'];
            }
            $doctor->user->save();
            $doctor->update($this->soloDoctor($datos));
            $this->horarios->sincronizar($doctor, $datos['horarios'] ?? []);
        });

        return redirect()->route('admin.doctores.show', $doctor)->with('ok', 'Cambios guardados.');
    }

    public function destroy(Doctor $doctor)
    {
        if ($doctor->citas()->exists()) {
            $doctor->update(['activo' => false]);
            $doctor->user->update(['activo' => false]);

            return redirect()->route('admin.doctores.index')->with('ok', 'El doctor tiene citas registradas, así que se desactivó en lugar de eliminarse.');
        }
        $doctor->user->delete();

        return redirect()->route('admin.doctores.index')->with('ok', 'Doctor eliminado.');
    }

    private function datosForm(Doctor $doctor): array
    {
        return [
            'doctor' => $doctor,
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'consultorios' => Consultorio::where('activo', true)->orderBy('nombre')->get(),
        ];
    }

    private function validar(Request $request, ?Doctor $doctor = null): array
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($doctor?->user_id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => [$doctor ? 'nullable' : 'required', Password::min(8)],
            'especialidad_id' => ['required', 'exists:especialidades,id'],
            'consultorio_id' => ['nullable', 'exists:consultorios,id'],
            'licencia_medica' => ['required', 'string', 'max:40', Rule::unique('doctores')->ignore($doctor?->id)],
            'duracion_cita' => ['required', 'integer', 'min:10', 'max:180'],
            'tarifa' => ['required', 'numeric', 'min:0', 'max:9999'],
            'biografia' => ['nullable', 'string', 'max:800'],
            'horarios' => ['array'],
            'horarios.*.dia_semana' => ['nullable', 'integer', 'between:1,7'],
            'horarios.*.hora_inicio' => ['nullable', 'date_format:H:i'],
            'horarios.*.hora_fin' => ['nullable', 'date_format:H:i'],
        ]);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }

    private function soloDoctor(array $d): array
    {
        return collect($d)->only(['especialidad_id', 'consultorio_id', 'licencia_medica', 'duracion_cita', 'tarifa', 'biografia', 'activo'])->all();
    }
}
