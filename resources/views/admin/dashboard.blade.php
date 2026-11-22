@php
    $max = max(1, $serie->max(fn ($d) => $d['atendidas'] + $d['agendadas'] + $d['perdidas']));
    $variacion = $ingresosMesAnterior > 0 ? round(($ingresosMes - $ingresosMesAnterior) / $ingresosMesAnterior * 100) : null;
    $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
    $ahora = now()->format('H:i:s');
@endphp
<x-layouts.app title="Resumen">
    <x-page-head :title="$saludo.', '.auth()->user()->primerNombre()" :subtitle="ucfirst(now()->translatedFormat('l j \d\e F')).'. Esto es lo que pasa hoy en la clínica.'">
        <x-slot:actions>
            <a href="{{ route('admin.citas.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Agendar cita</a>
        </x-slot:actions>
    </x-page-head>

    <div class="stats">
        <div class="stat">
            <span class="stat-label">Citas de hoy</span>
            <span class="stat-value">{{ $citasHoy->where('estado', '!=', \App\Enums\EstadoCita::Cancelada)->count() }}</span>
            <span class="stat-note">{{ $citasHoy->where('estado', \App\Enums\EstadoCita::Completada)->count() }} atendidas hasta ahora</span>
        </div>
        <div class="stat">
            <span class="stat-label">Por confirmar</span>
            <span class="stat-value">{{ $porConfirmar }}</span>
            <span class="stat-note">reservas en línea de hoy en adelante</span>
        </div>
        <div class="stat">
            <span class="stat-label">Ingresos de {{ now()->translatedFormat('F') }}</span>
            <span class="stat-value">${{ number_format($ingresosMes, 0, ',', '.') }}</span>
            @if($variacion !== null)
                <span class="stat-note {{ $variacion >= 0 ? 'up' : 'down' }}">{{ $variacion >= 0 ? '+' : '' }}{{ $variacion }}% frente al mismo corte del mes pasado</span>
            @else
                <span class="stat-note">sin datos del mes pasado</span>
            @endif
        </div>
        <div class="stat">
            <span class="stat-label">Asistencia del mes</span>
            <span class="stat-value">{{ $asistencia !== null ? $asistencia.'%' : '—' }}</span>
            <span class="stat-note">{{ $totalPacientes }} pacientes, {{ $pacientesNuevos }} nuevos este mes</span>
        </div>
    </div>

    <div class="split mt-3">
        <section class="card" aria-labelledby="g-titulo">
            <div class="card-head">
                <div><h2 id="g-titulo">Citas por día</h2><p>Dos semanas atrás y la próxima semana</p></div>
                <a href="{{ route('admin.citas.index') }}">Ver todas</a>
            </div>
            <div class="chart">
                <div class="chart-legend">
                    <span style="--c:var(--serie-1)">Atendidas</span>
                    <span style="--c:var(--serie-2)">Agendadas</span>
                    <span style="--c:var(--serie-3)">Canceladas o inasistencia</span>
                </div>
                <div class="bars" style="--n:{{ $serie->count() }}" role="img" aria-label="Gráfico de barras apiladas de citas por día. El detalle está en la tabla de abajo.">
                    @foreach($serie as $i => $d)
                        @php($total = $d['atendidas'] + $d['agendadas'] + $d['perdidas'])
                        <div class="bar {{ $d['hoy'] ? 'is-today' : '' }} {{ $d['fecha']->isFuture() ? 'is-future' : '' }}" tabindex="0">
                            @foreach([['atendidas', 1], ['agendadas', 2], ['perdidas', 3]] as [$k, $s])
                                @if($d[$k] > 0)<i style="--h:{{ $d[$k] / $max * 100 }}%;--c:var(--serie-{{ $s }})"></i>@endif
                            @endforeach
                            @if($i % 2 === 0 || $d['hoy'])<span class="tick">{{ $d['hoy'] ? 'Hoy' : $d['fecha']->format('j') }}</span>@endif
                            <span class="bar-tip"><b>{{ ucfirst($d['fecha']->translatedFormat('D j M')) }}</b>{{ $d['atendidas'] }} atendidas<br>{{ $d['agendadas'] }} agendadas<br>{{ $d['perdidas'] }} canceladas o inasistencia</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <details class="data-table">
                <summary>Ver datos en tabla</summary>
                <div class="table-wrap mt-1">
                    <table class="table">
                        <thead><tr><th>Día</th><th>Atendidas</th><th>Agendadas</th><th>Canceladas o inasistencia</th></tr></thead>
                        <tbody>
                        @foreach($serie as $d)
                            <tr><td>{{ $d['fecha']->translatedFormat('D j M') }}</td><td class="num">{{ $d['atendidas'] }}</td><td class="num">{{ $d['agendadas'] }}</td><td class="num">{{ $d['perdidas'] }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>

        <section class="card" aria-labelledby="hoy-titulo">
            <div class="card-head">
                <div><h2 id="hoy-titulo">Agenda de hoy</h2><p>{{ $citasHoy->count() }} citas en total</p></div>
                <a href="{{ route('admin.citas.index', ['desde' => today()->toDateString(), 'hasta' => today()->toDateString()]) }}">Abrir</a>
            </div>
            @if($citasHoy->isEmpty())
                <x-empty title="Hoy no hay citas">
                    <a class="btn btn-sm" href="{{ route('admin.citas.create') }}">Agendar una</a>
                </x-empty>
            @else
                <div class="appt-list">
                @foreach($citasHoy->take(8) as $c)
                    <a href="{{ route('admin.citas.show', $c) }}" class="appt {{ $c->hora_fin < $ahora ? 'is-past' : '' }} {{ $c->hora_inicio <= $ahora && $c->hora_fin > $ahora ? 'is-now' : '' }}">
                        <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $c->doctor->duracion_cita }} min</small></span>
                        <span class="appt-who"><b>{{ $c->paciente->user->name }}</b><span>{{ $c->doctor->user->name }}, {{ $c->doctor->especialidad->nombre }}</span></span>
                        <x-estado :estado="$c->estado"/>
                    </a>
                @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="card mt-3" aria-labelledby="doc-titulo">
        <div class="card-head">
            <div><h2 id="doc-titulo">Carga de esta semana</h2><p>Citas activas por doctor</p></div>
            <a href="{{ route('admin.doctores.index') }}">Doctores</a>
        </div>
        <div class="grid grid-3">
            @php($maxDoc = max(1, $doctores->max('citas_semana')))
            @foreach($doctores as $d)
                <a href="{{ route('admin.doctores.show', $d) }}" class="person" style="padding:.4rem;border-radius:12px">
                    <x-avatar :user="$d->user" :color="$d->especialidad->color"/>
                    <span style="flex:1;min-width:0">
                        <b>{{ $d->user->name }}</b>
                        <span>{{ $d->citas_semana }} citas, {{ $d->especialidad->nombre }}</span>
                        <span style="display:block;block-size:4px;border-radius:4px;background:var(--surface-2);margin-top:.4rem"><span style="display:block;block-size:100%;inline-size:{{ $d->citas_semana / $maxDoc * 100 }}%;border-radius:4px;background:{{ $d->especialidad->color }}"></span></span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
