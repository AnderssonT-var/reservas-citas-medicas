<x-layouts.app :title="$doctor->user->name">
    <x-page-head :title="$doctor->user->name" :back="route('admin.doctores.index')" back-label="Doctores">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.doctores.destroy', $doctor) }}" data-confirm="¿Eliminar a {{ $doctor->user->name }}?" data-confirm-detalle="Si ya tiene citas registradas, se desactivará para conservar el historial." data-confirm-boton="Eliminar">
                @csrf @method('DELETE')
                <button class="btn btn-danger"><x-icon name="trash" size="18"/> Eliminar</button>
            </form>
            <a href="{{ route('admin.doctores.edit', $doctor) }}" class="btn btn-primary"><x-icon name="edit" size="18"/> Editar</a>
        </x-slot:actions>
    </x-page-head>

    <div class="split">
        <div class="stack" style="--gap:1.25rem">
            <div class="stats">
                <div class="stat"><span class="stat-label">Consultas atendidas</span><span class="stat-value">{{ $stats['atendidas'] }}</span></div>
                <div class="stat"><span class="stat-label">Pacientes distintos</span><span class="stat-value">{{ $stats['pacientes'] }}</span></div>
                <div class="stat"><span class="stat-label">Citas por venir</span><span class="stat-value">{{ $stats['proximas'] }}</span></div>
            </div>
            <section class="card">
                <div class="card-head"><h2>Próximas citas</h2><a href="{{ route('admin.citas.index', ['doctor' => $doctor->id]) }}">Ver todas</a></div>
                @if($proximas->isEmpty())
                    <x-empty title="Sin citas próximas"/>
                @else
                <div class="appt-list">
                    @foreach($proximas as $c)
                        <a href="{{ route('admin.citas.show', $c) }}" class="appt">
                            <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $c->fecha->translatedFormat('D j M') }}</small></span>
                            <span class="appt-who"><b>{{ $c->paciente->user->name }}</b><span>{{ $c->motivo ?: 'Sin motivo indicado' }}</span></span>
                            <x-estado :estado="$c->estado"/>
                        </a>
                    @endforeach
                </div>
                @endif
            </section>
        </div>
        <aside class="stack" style="--gap:1.25rem">
            <section class="card">
                <div class="profile-head">
                    <x-avatar :user="$doctor->user" :color="$doctor->especialidad->color" size="xl"/>
                    <div><h2>{{ $doctor->especialidad->nombre }}</h2><p class="small">{{ $doctor->licencia_medica }}</p>
                        @if($doctor->activo)<span class="chip" data-tono="pine">Recibe citas</span>@else<span class="chip">Inactivo</span>@endif</div>
                </div>
                <hr class="divider">
                <dl class="kv">
                    <dt>Correo</dt><dd>{{ $doctor->user->email }}</dd>
                    <dt>Teléfono</dt><dd>{{ $doctor->user->telefono ?: '—' }}</dd>
                    <dt>Consultorio</dt><dd>{{ $doctor->consultorio?->nombre ?? 'Sin asignar' }}</dd>
                    <dt>Cada cita</dt><dd class="num">{{ $doctor->duracion_cita }} min, ${{ number_format($doctor->tarifa, 2) }}</dd>
                </dl>
                @if($doctor->biografia)<p class="muted small mt-2">{{ $doctor->biografia }}</p>@endif
            </section>
            <section class="card">
                <div class="card-head"><h2>Turnos</h2></div>
                @forelse($doctor->horarios->groupBy('dia_semana') as $dia => $turnos)
                    <div class="cluster" style="justify-content:space-between;padding:.45rem 0;border-top:1px solid var(--line-soft)">
                        <b style="font-weight:600">{{ \App\Models\Horario::DIAS[$dia] }}</b>
                        <span class="num muted">{{ $turnos->map(fn ($t) => $t->inicio.'–'.$t->fin)->join(', ') }}</span>
                    </div>
                @empty
                    <p class="notice">Sin turnos: no aparece para reservar.</p>
                @endforelse
            </section>
        </aside>
    </div>
</x-layouts.app>
