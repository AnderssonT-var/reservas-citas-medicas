<?php

namespace App\Http\Controllers;

use App\Rules\CedulaEcuatoriana;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function edit(Request $request)
    {
        return view('perfil.edit', ['user' => $request->user()->load('paciente', 'doctor.especialidad')]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $reglas = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];

        if ($user->esPaciente()) {
            $reglas += [
                'cedula' => ['required', 'digits:10', Rule::unique('pacientes')->ignore($user->paciente->id), new CedulaEcuatoriana],
                'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
                'genero' => ['nullable', Rule::in(['femenino', 'masculino', 'otro'])],
                'direccion' => ['nullable', 'string', 'max:255'],
                'grupo_sanguineo' => ['nullable', Rule::in(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'])],
                'alergias' => ['nullable', 'string', 'max:500'],
                'contacto_emergencia' => ['nullable', 'string', 'max:255'],
            ];
        }
        if ($user->esDoctor()) {
            $reglas += ['biografia' => ['nullable', 'string', 'max:800']];
        }

        $datos = $request->validate($reglas);
        $user->update(collect($datos)->only(['name', 'email', 'telefono'])->all());

        if ($user->esPaciente()) {
            $user->paciente->update(collect($datos)->except(['name', 'email', 'telefono'])->all());
        }
        if ($user->esDoctor()) {
            $user->doctor->update(['biografia' => $datos['biografia'] ?? null]);
        }

        return back()->with('ok', 'Perfil actualizado.');
    }

    public function password(Request $request)
    {
        $datos = $request->validate([
            'actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], ['actual.current_password' => 'La contraseña actual no es correcta.']);

        $request->user()->update(['password' => $datos['password']]);

        return back()->with('ok', 'Contraseña actualizada.');
    }
}
