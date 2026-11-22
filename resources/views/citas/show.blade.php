@php
    $u = auth()->user();
    $volver = match (true) { $u->esPaciente() => route('paciente.citas'), $u->esDoctor() => route('doctor.agenda', ['fecha' => $cita->fecha->toDateString()]), default => route('admin.citas.index') };
    $puedeGestionar = $u->can('gestionar', $cita);
@endphp
<x-layouts.app title="Cita del {{ $cita->fecha->translatedFormat('j M') }}">
    <x-page-head :title="ucfirst($cita->fecha->translatedFormat('l j \d\e F')).', '.$cita->horaInicio()" :back="$volver" :back-label="$u->esPaciente() ? 'Mis citas' : ($u->esDoctor() ? 'Agenda' : 'Citas')">
        <x-slot:actions>
            @if($puedeCancelar)
                <form method="POST" action="{{ route('citas.cancelar', $cita) }}" data-confirm="¿Cancelar esta cita?" data-confirm-detalle="El horario quedará libre para otra persona." data-confirm-boton="Cancelar cita" data-confirm-motivo>
                    @csrf <button class="btn btn-danger"><x-icon name="x" size="18"/> Cancelar cita</button>
                </form>
            @endif
            @if($puedeGestionar && $cita->estado === \App\Enums\EstadoCita::Pendiente)
                <form method="POST" action="{{ route('citas.confirmar', $cita) }}">@csrf <button class="btn"><x-icon name="check" size="18"/> Confirmar</button></form>
            @endif
            @if($puedeGestionar && $cita->estado->esModificable() && $cita->inicio()->isPast())
                <form method="POST" action="{{ route('citas.no-asistio', $cita) }}" data-confirm="¿Marcar como inasistencia?" data-confirm-boton="Marcar inasistencia">@csrf <button class="btn">No asistió</button></form>
            @endif
            @if($u->can('atender', $cita) && $cita->estado->esModificable())
                <a href="{{ route('doctor.atender', $cita) }}" class="btn btn-primary"><x-icon name="stethoscope" size="18"/> Atender</a>
            @endif
            @if($u->esAdmin() && $cita->estado === \App\Enums\EstadoCita::Completada && $cita->pagos->isEmpty())
                <a href="{{ route('admin.pagos.create', ['cita' => $cita->id]) }}" class="btn btn-primary"><x-icon name="wallet" size="18"/> Registrar pago</a>
            @endif
        </x-slot:actions>
    </x-page-head>

    <div class="split">
        <div class="stack" style="--gap:1.25rem">
            <section class="card">
                <div class="cluster" style="justify-content:space-between">
                    <h2>Detalle</h2>
                    <x-estado :estado="$cita->estado"/>
                </div>
                <dl class="kv mt-2">
                    <dt>Horario</dt><dd class="num">{{ $cita->horaInicio() }} a {{ $cita->horaFin() }}</dd>
                    <dt>Doctor</dt><dd>{{ $cita->doctor->user->name }}, {{ $cita->doctor->especialidad->nombre }}</dd>
                    <dt>Consultorio</dt><dd>{{ $cita->consultorio ? $cita->consultorio->nombre.' ('.$cita->consultorio->ubicacion.')' : 'Por asignar' }}</dd>
                    <dt>Motivo</dt><dd>{{ $cita->motivo ?: 'No indicado' }}</dd>
                    @if(!$u->esPaciente())<dt>Reservada</dt><dd>{{ $cita->created_at->translatedFormat('j M Y, H:i') }}</dd>@endif
                    @if($cita->estado === \App\Enums\EstadoCita::Cancelada)
                        <dt>Cancelada por</dt><dd>{{ ['paciente' => 'el paciente', 'doctor' => 'el doctor', 'admin' => 'la clínica'][$cita->cancelada_por] ?? '—' }}</dd>
                        @if($cita->motivo_cancelacion)<dt>Motivo</dt><dd>{{ $cita->motivo_cancelacion }}</dd>@endif
                    @endif
                </dl>
                @if($u->esPaciente() && $cita->estado->esModificable() && !$puedeCancelar)
                    <p class="notice mt-2"><x-icon name="alert"/> Faltan menos de {{ $clinica['horas_cancelacion'] }} horas. Para cambiarla llama a la clínica{{ $clinica['telefono'] ? ' al '.$clinica['telefono'] : '' }}.</p>
                @endif
            </section>

            @if($cita->consulta)
                <section class="card">
                    <div class="card-head"><h2>Resultado de la consulta</h2><a href="{{ route('consultas.show', $cita->consulta) }}">Ver completa</a></div>
                    <p><b style="font-weight:600">Diagnóstico:</b> {{ $cita->consulta->diagnostico }}</p>
                    @if($cita->consulta->tratamiento)<p class="mt-1"><b style="font-weight:600">Tratamiento:</b> {{ $cita->consulta->tratamiento }}</p>@endif
                    <a href="{{ route('consultas.pdf', $cita->consulta) }}" class="btn btn-sm mt-2" target="_blank"><x-icon name="download" size="16"/> Descargar PDF</a>
                </section>
            @endif

            @if($u->esDoctor() && $cita->paciente->consultas->isNotEmpty())
                <section class="card">
                    <div class="card-head"><h2>Consultas anteriores</h2><a href="{{ route('doctor.pacientes.show', $cita->paciente) }}">Historial completo</a></div>
                    @include('pacientes.timeline', ['consultas' => $cita->paciente->consultas->where('id', '!=', $cita->consulta?->id)->take(3)])
                </section>
            @endif
        </div>

        <aside class="stack" style="--gap:1.25rem">
            @if($u->esPaciente())
                <section class="card">
                    <div class="profile-head">
                        <x-avatar :user="$cita->doctor->user" :color="$cita->doctor->especialidad->color" size="lg"/>
                        <div><h2>{{ $cita->doctor->user->name }}</h2><p class="small">{{ $cita->doctor->especialidad->nombre }}</p></div>
                    </div>
                    @if($cita->doctor->biografia)<p class="muted small mt-2">{{ $cita->doctor->biografia }}</p>@endif
                    <dl class="kv mt-2"><dt>Tarifa</dt><dd class="num">${{ number_format($cita->doctor->tarifa, 2) }}</dd></dl>
                </section>
                @if($clinica['direccion'])
                <section class="card flat">
                    <h3>Cómo llegar</h3>
                    <p class="small mt-1">{{ $clinica['direccion'] }}</p>
                    <p class="small muted mt-1">Llega 10 minutos antes con tu cédula.</p>
                </section>
                @endif
            @else
                @include('pacientes.ficha', ['paciente' => $cita->paciente])
            @endif
        </aside>
    </div>
</x-layouts.app>
