<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\HorarioService;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function edit(Request $request)
    {
        return view('doctor.horario', ['doctor' => $request->user()->doctor->load('horarios')]);
    }

    public function update(Request $request, HorarioService $servicio)
    {
        $request->validate([
            'horarios' => ['array'],
            'horarios.*.dia_semana' => ['nullable', 'integer', 'between:1,7'],
            'horarios.*.hora_inicio' => ['nullable', 'date_format:H:i'],
            'horarios.*.hora_fin' => ['nullable', 'date_format:H:i'],
        ]);
        $servicio->sincronizar($request->user()->doctor, $request->input('horarios', []));

        return back()->with('ok', 'Horario actualizado. Las citas ya reservadas se mantienen.');
    }
}
