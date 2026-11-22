<x-layouts.app title="Mis citas">
    <x-page-head title="Mis citas">
        <x-slot:actions><a href="{{ route('paciente.reservar') }}" class="btn btn-primary"><x-icon name="calendar-plus" size="18"/> Reservar cita</a></x-slot:actions>
    </x-page-head>

    <section class="card">
        <div class="card-head"><h2>Próximas</h2><p>Puedes cancelar hasta {{ $clinica['horas_cancelacion'] }} horas antes</p></div>
        @if($proximas->isEmpty())
            <x-empty title="No tienes citas próximas"><a href="{{ route('paciente.reservar') }}" class="btn btn-sm">Reservar una</a></x-empty>
        @else
        <div class="appt-list">
            @foreach($proximas as $c)
                <a href="{{ route('paciente.citas.show', $c) }}" class="appt">
                    <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $c->fecha->translatedFormat('D j M') }}</small></span>
                    <span class="appt-who"><b>{{ $c->doctor->user->name }}</b><span>{{ $c->doctor->especialidad->nombre }}{{ $c->consultorio ? ', '.$c->consultorio->nombre : '' }}</span></span>
                    <x-estado :estado="$c->estado"/>
                </a>
            @endforeach
        </div>
        @endif
    </section>

    <section class="card mt-3">
        <div class="card-head"><h2>Anteriores</h2></div>
        @if($pasadas->isEmpty())
            <p class="muted small">Aquí aparecerán tus citas pasadas y canceladas.</p>
        @else
        <div class="appt-list">
            @foreach($pasadas as $c)
                <a href="{{ route('paciente.citas.show', $c) }}" class="appt">
                    <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $c->fecha->translatedFormat('j M y') }}</small></span>
                    <span class="appt-who"><b>{{ $c->doctor->user->name }}</b><span>{{ $c->doctor->especialidad->nombre }}</span></span>
                    <x-estado :estado="$c->estado"/>
                </a>
            @endforeach
        </div>
        @endif
    </section>
</x-layouts.app>
