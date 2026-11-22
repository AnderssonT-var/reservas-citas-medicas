<x-layouts.app title="Doctores">
    <x-page-head title="Doctores" subtitle="Cada doctor tiene su usuario, su especialidad y sus turnos semanales.">
        <x-slot:actions><a href="{{ route('admin.doctores.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Nuevo doctor</a></x-slot:actions>
    </x-page-head>

    <form class="filters" method="GET" role="search">
        <div class="field grow"><label for="q" class="sr-only">Buscar</label>
            <div class="search"><x-icon name="search"/><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Buscar por nombre" type="search"></div></div>
        <div class="field"><label for="esp" class="sr-only">Especialidad</label>
            <select id="esp" name="especialidad" class="select" data-autosubmit>
                <option value="">Todas las especialidades</option>
                @foreach($especialidades as $e)<option value="{{ $e->id }}" @selected(request('especialidad') == $e->id)>{{ $e->nombre }}</option>@endforeach
            </select></div>
    </form>

    @if($doctores->isEmpty())
        <x-empty icon="stethoscope" title="No hay doctores con ese filtro"><a href="{{ route('admin.doctores.create') }}" class="btn btn-sm">Registrar doctor</a></x-empty>
    @else
    <div class="table-wrap" role="region" aria-label="Lista de doctores" tabindex="0">
        <table class="table">
            <thead><tr><th>Doctor</th><th>Especialidad</th><th>Consultorio</th><th>Cita</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
            @foreach($doctores as $d)
                <tr>
                    <td><a href="{{ route('admin.doctores.show', $d) }}" class="person"><x-avatar :user="$d->user" :color="$d->especialidad->color"/><span><b>{{ $d->user->name }}</b><span>{{ $d->licencia_medica }}</span></span></a></td>
                    <td data-label="Especialidad">{{ $d->especialidad->nombre }}</td>
                    <td data-label="Consultorio">{{ $d->consultorio?->nombre ?? '—' }}</td>
                    <td data-label="Cita" class="num">{{ $d->duracion_cita }} min, ${{ number_format($d->tarifa, 2) }}</td>
                    <td data-label="Estado">
                        @if(!$d->activo)<span class="chip">Inactivo</span>
                        @elseif($d->horarios_count === 0)<span class="chip" data-tono="amber">Sin horario</span>
                        @else<span class="chip" data-tono="pine">Recibe citas</span>@endif
                    </td>
                    <td class="actions"><a href="{{ route('admin.doctores.edit', $d) }}" class="btn btn-sm btn-ghost"><x-icon name="edit" size="16"/> Editar</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-layouts.app>
