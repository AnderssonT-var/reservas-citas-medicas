<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Consulta;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsultaPdfController extends Controller
{
    public function __invoke(Consulta $consulta)
    {
        $this->authorize('view', $consulta);
        $consulta->load('paciente.user', 'doctor.user', 'doctor.especialidad');

        return Pdf::loadView('pdf.consulta', ['consulta' => $consulta, 'ajustes' => Ajuste::todos()])
            ->setOption('isFontSubsettingEnabled', true)
            ->setPaper('a5')
            ->stream('consulta-'.$consulta->id.'.pdf');
    }
}
