<x-layouts.app title="Pagos">
    <x-page-head title="Pagos" subtitle="Cobros de consultas. Un pago anulado se conserva para auditoría.">
        <x-slot:actions><a href="{{ route('admin.pagos.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Registrar pago</a></x-slot:actions>
    </x-page-head>

    <div class="stats" style="margin-bottom:1.25rem">
        <div class="stat"><span class="stat-label">Cobrado en {{ now()->translatedFormat('F') }}</span><span class="stat-value">${{ number_format($totales['mes'], 2) }}</span></div>
        <div class="stat"><span class="stat-label">Pendiente de cobro</span><span class="stat-value">${{ number_format($totales['pendiente'], 2) }}</span><span class="stat-note">pagos marcados como pendientes</span></div>
    </div>

    <form class="filters" method="GET">
        <div class="field"><label for="estado">Estado</label>
            <select id="estado" name="estado" class="select" data-autosubmit><option value="">Todos</option>
                @foreach(\App\Enums\EstadoPago::cases() as $e)<option value="{{ $e->value }}" @selected(request('estado') === $e->value)>{{ $e->label() }}</option>@endforeach</select></div>
        <div class="field"><label for="mes">Mes</label><input id="mes" type="month" name="mes" value="{{ request('mes') }}" class="input" data-autosubmit></div>
    </form>

    @if($pagos->isEmpty())
        <x-empty icon="wallet" title="No hay pagos con este filtro"><a href="{{ route('admin.pagos.create') }}" class="btn btn-sm">Registrar pago</a></x-empty>
    @else
    <div class="table-wrap" role="region" aria-label="Pagos" tabindex="0">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Paciente</th><th>Concepto</th><th>Método</th><th>Monto</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
            @foreach($pagos as $p)
                <tr>
                    <td class="num">{{ $p->fecha_pago->translatedFormat('j M Y') }}</td>
                    <td data-label="Paciente"><a class="row-link" href="{{ route('admin.pacientes.show', $p->paciente) }}">{{ $p->paciente->user->name }}</a></td>
                    <td data-label="Concepto" class="muted">{{ $p->cita ? 'Consulta con '.$p->cita->doctor->user->name : 'Pago sin cita' }}{{ $p->referencia ? ', ref. '.$p->referencia : '' }}</td>
                    <td data-label="Método">{{ $p->metodo->label() }}</td>
                    <td data-label="Monto" class="num" style="font-weight:600">${{ number_format($p->monto, 2) }}</td>
                    <td data-label="Estado"><span class="chip" data-tono="{{ $p->estado->tono() }}">{{ $p->estado->label() }}</span></td>
                    <td class="actions">
                        @if($p->estado === \App\Enums\EstadoPago::Pendiente)
                            <form method="POST" action="{{ route('admin.pagos.cobrar', $p) }}" style="display:inline">@csrf<button class="btn btn-sm">Marcar cobrado</button></form>
                        @endif
                        @if($p->estado !== \App\Enums\EstadoPago::Anulado)
                            <form method="POST" action="{{ route('admin.pagos.anular', $p) }}" style="display:inline" data-confirm="¿Anular este pago de ${{ number_format($p->monto, 2) }}?" data-confirm-detalle="Seguirá visible como anulado." data-confirm-boton="Anular pago">@csrf<button class="btn btn-sm btn-ghost">Anular</button></form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $pagos->links() }}
    @endif
</x-layouts.app>
