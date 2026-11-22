<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Especialidad;

class WebController extends Controller
{
    public function inicio()
    {
        $especialidades = Especialidad::withCount(['doctores' => fn ($q) => $q->activos()])->orderBy('nombre')->get();
        $doctores = Doctor::activos()->with(['user', 'especialidad', 'horarios'])->take(6)->get();

        return view('web.inicio', compact('especialidades', 'doctores'));
    }
}
