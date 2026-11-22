<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Services\AgendaService;
use Illuminate\Http\Request;

/** Acciones de estado compartidas por admin, doctor y paciente. */
class CitaAccionController extends Controller
{
    public function __construct(private AgendaService $agenda) {}

    public function cancelar(Request $request, Cita $cita)
    {
        $this->authorize('view', $cita);
        $request->validate(['motivo_cancelacion' => ['nullable', 'string', 'max:255']]);
        $this->agenda->cancelar($cita, $request->user(), $request->input('motivo_cancelacion'));

        return back()->with('ok', 'Cita cancelada. El horario quedó libre.');
    }

    public function confirmar(Cita $cita)
    {
        $this->authorize('gestionar', $cita);
        $this->agenda->confirmar($cita);

        return back()->with('ok', 'Cita confirmada.');
    }

    public function noAsistio(Cita $cita)
    {
        $this->authorize('gestionar', $cita);
        $this->agenda->marcarNoAsistio($cita);

        return back()->with('ok', 'Marcada como inasistencia.');
    }
}
