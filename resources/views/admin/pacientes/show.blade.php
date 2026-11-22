<x-layouts.app :title="$paciente->user->name">
    <x-page-head :title="$paciente->user->name" :back="route('admin.pacientes.index')" back-label="Pacientes">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.pacientes.destroy', $paciente) }}" data-confirm="¿Eliminar a {{ $paciente->user->name }}?" data-confirm-detalle="Si tiene historial clínico, solo se desactivará su cuenta." data-confirm-boton="Eliminar">
                @csrf @method('DELETE')<button class="btn btn-danger"><x-icon name="trash" size="18"/> Eliminar</button>
            </form>
            <a href="{{ route('admin.pacientes.edit', $paciente) }}" class="btn"><x-icon name="edit" size="18"/> Editar</a>
            <a href="{{ route('admin.citas.create', ['paciente' => $paciente->id]) }}" class="btn btn-primary"><x-icon name="calendar-plus" size="18"/> Agendar cita</a>
        </x-slot:actions>
    </x-page-head>

    <div class="split">
        <div class="stack" style="--gap:1.25rem">
            <section class="card">
                <div class="card-head"><h2>Citas recientes</h2><a href="{{ route('admin.citas.index', ['q' => $paciente->cedula]) }}">Ver todas</a></div>
                @if($citas->isEmpty())
                    <x-empty title="Aún no tiene citas"/>
                @else
                <div class="appt-list">
                    @foreach($citas as $c)
                        <a href="{{ route('admin.citas.show', $c) }}" class="appt">
                            <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $c->fecha->translatedFormat('j M y') }}</small></span>
                            <span class="appt-who"><b>{{ $c->doctor->user->name }}</b><span>{{ $c->doctor->especialidad->nombre }}</span></span>
                            <x-estado :estado="$c->estado"/>
                        </a>
                    @endforeach
                </div>
                @endif
            </section>
            <section class="card">
                <div class="card-head"><h2>Historial clínico</h2><p>{{ $paciente->consultas->count() }} consultas</p></div>
                @include('pacientes.timeline', ['consultas' => $paciente->consultas->take(6)])
            </section>
        </div>
        <aside class="stack" style="--gap:1.25rem">
            @include('pacientes.ficha')
            <section class="card">
                <div class="card-head"><h2>Pagos</h2><a href="{{ route('admin.pagos.create', ['paciente' => $paciente->id]) }}">Registrar</a></div>
                @forelse($pagos as $pg)
                    <div class="cluster" style="justify-content:space-between;padding:.5rem 0;border-top:1px solid var(--line-soft)">
                        <span><b class="num" style="font-weight:600">${{ number_format($pg->monto, 2) }}</b> <span class="muted small">{{ $pg->fecha_pago->translatedFormat('j M') }}, {{ $pg->metodo->label() }}</span></span>
                        <span class="chip" data-tono="{{ $pg->estado->tono() }}">{{ $pg->estado->label() }}</span>
                    </div>
                @empty
                    <p class="muted small">Sin pagos registrados.</p>
                @endforelse
            </section>
        </aside>
    </div>
</x-layouts.app>
