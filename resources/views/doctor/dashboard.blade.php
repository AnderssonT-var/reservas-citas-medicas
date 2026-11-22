@php
    $ahora = now()->format('H:i:s');
    $siguiente = $citas->first(fn ($c) => $c->estado->esModificable() && $c->hora_fin > $ahora);
    $pendientesHoy = $citas->filter(fn ($c) => $c->estado->esModificable())->count();
@endphp
<x-layouts.app title="Hoy">
    <x-page-head :title="'Hola, '.auth()->user()->primerNombre()" :subtitle="ucfirst(now()->translatedFormat('l j \d\e F')).'. '.($pendientesHoy ? trans_choice('{1} Te queda 1 paciente por atender hoy.|[2,*] Te quedan :count pacientes por atender hoy.', $pendientesHoy) : 'No tienes pacientes pendientes hoy.')">
        <x-slot:actions><a class="btn" href="{{ route('doctor.agenda') }}"><x-icon name="calendar" size="18"/> Ver semana</a></x-slot:actions>
    </x-page-head>

    <div class="split">
        <div class="stack" style="--gap:1.25rem">
            @if($siguiente)
                <section class="next-appt" aria-label="Siguiente paciente">
                    <div class="when"><small>{{ $siguiente->hora_inicio <= $ahora ? 'Ahora' : 'Siguiente' }}</small><b class="num">{{ $siguiente->horaInicio() }}</b></div>
                    <div>
                        <h2>{{ $siguiente->paciente->user->name }}</h2>
                        <p>{{ $siguiente->motivo ?: 'Sin motivo indicado' }}{{ $siguiente->paciente->alergias ? '. Alergias: '.$siguiente->paciente->alergias : '' }}</p>
                    </div>
                    <div class="actions">
                        <a href="{{ route('doctor.atender', $siguiente) }}" class="btn" style="--bg:#F0A64C;--fg:#2A1A05;--bd:transparent"><x-icon name="stethoscope" size="18"/> Atender</a>
                        <a href="{{ route('doctor.citas.show', $siguiente) }}" class="btn">Ver ficha</a>
                    </div>
                </section>
            @endif
            <section class="card">
                <div class="card-head"><h2>Agenda de hoy</h2><p>{{ $doctor->consultorio?->nombre }}</p></div>
                @include('doctor._agenda')
            </section>
        </div>
        <aside class="stack" style="--gap:1.25rem">
            <div class="stats" style="grid-template-columns:1fr">
                <div class="stat"><span class="stat-label">Por confirmar</span><span class="stat-value">{{ $resumen['pendientes'] }}</span><span class="stat-note">reservas en línea de hoy en adelante</span></div>
                <div class="stat"><span class="stat-label">Esta semana</span><span class="stat-value">{{ $resumen['semana'] }}</span><span class="stat-note">citas activas</span></div>
                <div class="stat"><span class="stat-label">Atendidas este mes</span><span class="stat-value">{{ $resumen['atendidasMes'] }}</span></div>
            </div>
        </aside>
    </div>
</x-layouts.app>
