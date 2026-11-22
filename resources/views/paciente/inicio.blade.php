@php($prox = $proximas->first())
<x-layouts.app title="Inicio">
    <x-page-head :title="'Hola, '.auth()->user()->primerNombre()" :subtitle="$prox ? 'Tu próxima cita es '.($prox->fecha->isToday() ? 'hoy' : ($prox->fecha->isTomorrow() ? 'mañana' : 'el '.$prox->fecha->translatedFormat('l j'))).'.' : 'No tienes citas próximas.'">
        <x-slot:actions><a href="{{ route('paciente.reservar') }}" class="btn btn-primary"><x-icon name="calendar-plus" size="18"/> Reservar cita</a></x-slot:actions>
    </x-page-head>

    <div class="split">
        <div class="stack" style="--gap:1.25rem">
            @if($prox)
                <section class="next-appt" aria-label="Próxima cita">
                    <div class="when"><small>{{ $prox->fecha->translatedFormat('M') }}</small><b>{{ $prox->fecha->day }}</b><small>{{ $prox->fecha->translatedFormat('l') }}</small></div>
                    <div>
                        <span class="time">{{ $prox->horaInicio() }}</span>
                        <h2 class="mt-1">{{ $prox->doctor->user->name }}</h2>
                        <p>{{ $prox->doctor->especialidad->nombre }}{{ $prox->consultorio ? ', '.$prox->consultorio->nombre : '' }}</p>
                    </div>
                    <div class="actions">
                        <a href="{{ route('paciente.citas.show', $prox) }}" class="btn">Ver detalle</a>
                        @if($prox->estado === \App\Enums\EstadoCita::Pendiente)<span class="chip" data-tono="amber" style="align-self:center">La clínica aún no la confirma</span>@endif
                    </div>
                </section>
                @if($proximas->count() > 1)
                <section class="card">
                    <div class="card-head"><h2>También tienes</h2><a href="{{ route('paciente.citas') }}">Todas mis citas</a></div>
                    <div class="appt-list">
                        @foreach($proximas->skip(1) as $c)
                            <a href="{{ route('paciente.citas.show', $c) }}" class="appt">
                                <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $c->fecha->translatedFormat('D j M') }}</small></span>
                                <span class="appt-who"><b>{{ $c->doctor->user->name }}</b><span>{{ $c->doctor->especialidad->nombre }}</span></span>
                                <x-estado :estado="$c->estado"/>
                            </a>
                        @endforeach
                    </div>
                </section>
                @endif
            @else
                <section class="card">
                    <x-empty icon="calendar-plus" title="Reserva tu próxima cita">
                        <p>Elige especialidad, doctor y hora. Toma menos de un minuto.</p>
                        <a href="{{ route('paciente.reservar') }}" class="btn btn-primary">Reservar ahora</a>
                    </x-empty>
                </section>
            @endif

            <section class="card">
                <div class="card-head"><h2>Reservar por especialidad</h2></div>
                <div class="pills">
                    @foreach($especialidades as $e)
                        <a class="pill" style="--c:{{ $e->color }}" href="{{ route('paciente.reservar', ['especialidad' => $e->id]) }}">{{ $e->nombre }}</a>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="stack" style="--gap:1.25rem">
            <section class="card">
                <div class="card-head"><h2>Última consulta</h2><a href="{{ route('paciente.historial') }}">Historial</a></div>
                @if($ultimaConsulta)
                    <p class="small muted">{{ $ultimaConsulta->fecha->translatedFormat('j \d\e F') }}, {{ $ultimaConsulta->doctor->user->name }}</p>
                    <h3 class="mt-1">{{ $ultimaConsulta->motivo }}</h3>
                    <p class="mt-1">{{ $ultimaConsulta->diagnostico }}</p>
                    @if($ultimaConsulta->receta)
                        <div class="card flat mt-2"><b style="font-weight:600" class="small">Receta</b><p class="small mt-1" style="white-space:pre-line">{{ $ultimaConsulta->receta }}</p></div>
                    @endif
                    <a href="{{ route('consultas.pdf', $ultimaConsulta) }}" target="_blank" class="btn btn-sm mt-2"><x-icon name="download" size="16"/> PDF</a>
                @else
                    <p class="muted small">Cuando un doctor registre tu consulta, la verás aquí.</p>
                @endif
            </section>
            @if(!$paciente->fecha_nacimiento || !$paciente->contacto_emergencia)
                <section class="card flat">
                    <h3>Completa tu ficha</h3>
                    <p class="small muted mt-1">Tu fecha de nacimiento, alergias y un contacto de emergencia ayudan al doctor a atenderte mejor.</p>
                    <a href="{{ route('perfil') }}" class="btn btn-sm mt-2">Completar ahora</a>
                </section>
            @endif
        </aside>
    </div>
</x-layouts.app>
