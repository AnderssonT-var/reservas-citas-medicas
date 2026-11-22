<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EspecialidadController extends Controller
{
    public function index()
    {
        return view('admin.especialidades.index', [
            'especialidades' => Especialidad::withCount('doctores')->orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.especialidades.form', ['especialidad' => new Especialidad(['color' => '#0F6B5C'])]);
    }

    public function store(Request $request)
    {
        Especialidad::create($this->validar($request));

        return redirect()->route('admin.especialidades.index')->with('ok', 'Especialidad creada.');
    }

    public function edit(Especialidad $especialidad)
    {
        return view('admin.especialidades.form', compact('especialidad'));
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $especialidad->update($this->validar($request, $especialidad));

        return redirect()->route('admin.especialidades.index')->with('ok', 'Especialidad actualizada.');
    }

    public function destroy(Especialidad $especialidad)
    {
        if ($especialidad->doctores()->exists()) {
            return back()->withErrors(['especialidad' => 'Hay doctores con esta especialidad. Reasígnalos antes de eliminarla.']);
        }
        $especialidad->delete();

        return back()->with('ok', 'Especialidad eliminada.');
    }

    private function validar(Request $r, ?Especialidad $e = null): array
    {
        return $r->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('especialidades')->ignore($e?->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
    }
}
