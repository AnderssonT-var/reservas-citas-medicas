<x-layouts.app title="Citas">
    <x-page-head title="Citas" subtitle="Filtra por doctor, estado o fechas. La exportación respeta los filtros.">
        <x-slot:actions>
            <a href="{{ route('admin.citas.exportar', request()->query()) }}" class="btn"><x-icon name="download" size="18"/> Exportar CSV</a>
            <a href="{{ route('admin.citas.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Agendar cita</a>
        </x-slot:actions>
    </x-page-head>

    <form class="filters" method="GET" role="search">
        <div class="field grow"><label for="q">Paciente</label>
            <div class="search"><x-icon name="search"/><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Nombre o cédula" type="search"></div></div>
        <div class="field"><label for="doctor">Doctor</label>
            <select id="doctor" name="doctor" class="select" data-autosubmit><option value="">Todos</option>
                @foreach($doctores as $d)<option value="{{ $d->id }}" @selected(request('doctor') == $d->id)>{{ $d->user->name }}</option>@endforeach</select></div>
        <div class="field"><label for="estado">Estado</label>
            <select id="estado" name="estado" class="select" data-autosubmit><option value="">Todos</option>
                @foreach($estados as $e)<option value="{{ $e->value }}" @selected(request('estado') === $e->value)>{{ $e->label() }}</option>@endforeach</select></div>
        <div class="field"><label for="desde">Desde</label><input id="desde" type="date" name="desde" value="{{ request('desde') }}" class="input" data-autosubmit></div>
        <div class="field"><label for="hasta">Hasta</label><input id="hasta" type="date" name="hasta" value="{{ request('hasta') }}" class="input" data-autosubmit></div>
        @if(request()->hasAny(['q', 'doctor', 'estado', 'desde', 'hasta']))<a href="{{ route('admin.citas.index') }}" class="btn btn-ghost">Limpiar</a>@endif
    </form>

    @if($citas->isEmpty())
        <x-empty title="No hay citas con estos filtros"><a href="{{ route('admin.citas.index') }}" class="btn btn-sm">Ver todas</a></x-empty>
    @else
    <div class="table-wrap" role="region" aria-label="Citas" tabindex="0">
        <table class="table">
            <thead><tr><th>Fecha y hora</th><th>Paciente</th><th>Doctor</th><th>Motivo</th><th>Estado</th></tr></thead>
            <tbody>
            @foreach($citas as $c)
                <tr>
                    <td class="num"><a class="row-link" href="{{ route('admin.citas.show', $c) }}">{{ $c->fecha->translatedFormat('D j M') }}, {{ $c->horaInicio() }}</a></td>
                    <td data-label="Paciente">{{ $c->paciente->user->name }}<span class="muted" style="display:block">{{ $c->paciente->cedula }}</span></td>
                    <td data-label="Doctor">{{ $c->doctor->user->name }}<span class="muted" style="display:block">{{ $c->doctor->especialidad->nombre }}</span></td>
                    <td data-label="Motivo" class="muted">{{ \Illuminate\Support\Str::limit($c->motivo, 40) ?: '—' }}</td>
                    <td data-label="Estado"><x-estado :estado="$c->estado"/></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $citas->links() }}
    @endif
</x-layouts.app>
