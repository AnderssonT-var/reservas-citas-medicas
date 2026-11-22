<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Ajuste;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AjusteController extends Controller
{
    public function edit()
    {
        return view('admin.ajustes', [
            'ajustes' => Ajuste::todos(),
            'admins' => User::where('rol', Rol::Admin)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'nombre_clinica' => ['required', 'string', 'max:80'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:160'],
            'correo' => ['nullable', 'email'],
            'horas_cancelacion' => ['required', 'integer', 'between:0,72'],
            'dias_reserva' => ['required', 'integer', 'between:1,180'],
        ]);
        Ajuste::guardar($datos);

        return back()->with('ok', 'Ajustes guardados.');
    }

    public function crearAdmin(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
        ]);
        User::create($datos + ['rol' => Rol::Admin]);

        return back()->with('ok', 'Administrador creado.');
    }

    public function alternarAdmin(Request $request, User $user)
    {
        abort_unless($user->esAdmin(), 404);
        if ($user->is($request->user())) {
            return back()->withErrors(['admin' => 'No puedes desactivar tu propia cuenta.']);
        }
        $user->update(['activo' => ! $user->activo]);

        return back()->with('ok', $user->activo ? 'Administrador reactivado.' : 'Administrador desactivado.');
    }
}
