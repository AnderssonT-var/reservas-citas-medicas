<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SesionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $clave = Str::lower($datos['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $seg = RateLimiter::availableIn($clave);
            throw ValidationException::withMessages(['email' => "Demasiados intentos. Prueba de nuevo en {$seg} segundos."]);
        }

        if (! Auth::attempt($datos + ['activo' => true], $request->boolean('recordar'))) {
            RateLimiter::hit($clave, 60);
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no coinciden.']);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }
}
