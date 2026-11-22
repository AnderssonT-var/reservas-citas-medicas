<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\CedulaEcuatoriana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class RegistroController extends Controller
{
    public function create()
    {
        return view('auth.registro');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'cedula' => ['required', 'digits:10', 'unique:pacientes,cedula', new CedulaEcuatoriana],
            'telefono' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($datos) {
            $user = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'],
                'password' => $datos['password'],
                'rol' => Rol::Paciente,
            ]);
            $user->paciente()->create(['cedula' => $datos['cedula']]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('paciente.reservar')->with('ok', '¡Listo, '.$user->primerNombre().'! Tu cuenta está creada. Reserva tu primera cita.');
    }
}
