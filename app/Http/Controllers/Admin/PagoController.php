<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $pagos = Pago::with(['paciente.user', 'cita.doctor.user'])
            ->when($request->estado, fn ($q, $e) => $q->where('estado', $e))
            ->when($request->mes, fn ($q, $m) => $q->where('fecha_pago', 'like', "$m%"))
            ->latest('fecha_pago')->latest('id')->paginate(20)->withQueryString();

        $mes = now()->format('Y-m');
        $totales = [
            'mes' => (float) Pago::where('estado', EstadoPago::Pagado)->where('fecha_pago', 'like', "$mes%")->sum('monto'),
            'pendiente' => (float) Pago::where('estado', EstadoPago::Pendiente)->sum('monto'),
        ];

        return view('admin.pagos.index', compact('pagos', 'totales'));
    }

    public function create(Request $request)
    {
        $cita = $request->cita ? Cita::with('paciente.user', 'doctor')->find($request->cita) : null;

        return view('admin.pagos.create', [
            'cita' => $cita,
            'pacientes' => Paciente::with('user')->get()->sortBy('user.name'),
            'metodos' => MetodoPago::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'cita_id' => ['nullable', 'exists:citas,id'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'metodo' => ['required', Rule::enum(MetodoPago::class)],
            'estado' => ['required', Rule::in([EstadoPago::Pagado->value, EstadoPago::Pendiente->value])],
            'referencia' => ['nullable', 'string', 'max:60'],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
        ]);

        if (! empty($datos['cita_id']) && Cita::find($datos['cita_id'])->paciente_id !== (int) $datos['paciente_id']) {
            return back()->withInput()->withErrors(['cita_id' => 'La cita no pertenece a este paciente.']);
        }

        Pago::create($datos + ['registrado_por' => $request->user()->id]);

        return redirect()->route('admin.pagos.index')->with('ok', 'Pago registrado.');
    }

    public function anular(Pago $pago)
    {
        if ($pago->estado === EstadoPago::Anulado) {
            return back();
        }
        $pago->update(['estado' => EstadoPago::Anulado]);

        return back()->with('ok', 'Pago anulado.');
    }

    public function marcarPagado(Pago $pago)
    {
        $pago->update(['estado' => EstadoPago::Pagado, 'fecha_pago' => now()->toDateString()]);

        return back()->with('ok', 'Pago marcado como cobrado.');
    }
}
