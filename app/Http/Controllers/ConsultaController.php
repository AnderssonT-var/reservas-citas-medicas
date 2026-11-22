<?php

namespace App\Http\Controllers;

use App\Models\Consulta;

class ConsultaController extends Controller
{
    public function show(Consulta $consulta)
    {
        $this->authorize('view', $consulta);
        $consulta->load('paciente.user', 'doctor.user', 'doctor.especialidad', 'cita');

        return view('consultas.show', compact('consulta'));
    }
}
