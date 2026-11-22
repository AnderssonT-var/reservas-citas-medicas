<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultorio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsultorioController extends Controller
{
    public function index()
    {
        return view('admin.consultorios.index', [
            'consultorios' => Consultorio::with('doctores.user')->orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.consultorios.form', ['consultorio' => new Consultorio(['activo' => true])]);
    }

    public function store(Request $request)
    {
        Consultorio::create($this->validar($request));

        return redirect()->route('admin.consultorios.index')->with('ok', 'Consultorio creado.');
    }

    public function edit(Consultorio $consultorio)
    {
        return view('admin.consultorios.form', compact('consultorio'));
    }

    public function update(Request $request, Consultorio $consultorio)
    {
        $consultorio->update($this->validar($request, $consultorio));

        return redirect()->route('admin.consultorios.index')->with('ok', 'Consultorio actualizado.');
    }

    public function destroy(Consultorio $consultorio)
    {
        $consultorio->delete(); // los doctores quedan sin consultorio asignado (nullOnDelete)

        return back()->with('ok', 'Consultorio eliminado.');
    }

    private function validar(Request $r, ?Consultorio $c = null): array
    {
        $d = $r->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('consultorios')->ignore($c?->id)],
            'ubicacion' => ['required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);
        $d['activo'] = $r->boolean('activo');

        return $d;
    }
}
