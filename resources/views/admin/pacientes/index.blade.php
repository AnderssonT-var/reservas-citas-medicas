<x-layouts.app title="Pacientes">
    <x-page-head title="Pacientes" :subtitle="$pacientes->total().' registrados. Los pacientes también pueden crear su propia cuenta desde la web.'">
        <x-slot:actions><a href="{{ route('admin.pacientes.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Nuevo paciente</a></x-slot:actions>
    </x-page-head>

    <form class="filters" method="GET" role="search">
        <div class="field grow"><label for="q" class="sr-only">Buscar</label>
            <div class="search"><x-icon name="search"/><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Nombre, cédula o correo" type="search"></div></div>
        <button class="btn">Buscar</button>
    </form>

    @if($pacientes->isEmpty())
        <x-empty icon="users" title="No encontramos pacientes">
            <p>Revisa la búsqueda o registra uno nuevo.</p>
            <a href="{{ route('admin.pacientes.create') }}" class="btn btn-sm">Registrar paciente</a>
        </x-empty>
    @else
    <div class="table-wrap" role="region" aria-label="Lista de pacientes" tabindex="0">
        <table class="table">
            <thead><tr><th>Paciente</th><th>Cédula</th><th>Teléfono</th><th>Citas</th><th>Última cita</th><th><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
            @foreach($pacientes as $p)
                <tr>
                    <td><a href="{{ route('admin.pacientes.show', $p) }}" class="person"><x-avatar :user="$p->user"/><span><b>{{ $p->user->name }}</b><span>{{ $p->user->email }}</span></span></a></td>
                    <td data-label="Cédula" class="num">{{ $p->cedula }}</td>
                    <td data-label="Teléfono" class="num">{{ $p->user->telefono ?: '—' }}</td>
                    <td data-label="Citas" class="num">{{ $p->citas_count }}</td>
                    <td data-label="Última cita" class="muted">{{ $p->citas_max_fecha ? \Carbon\Carbon::parse($p->citas_max_fecha)->translatedFormat('j M Y') : 'Nunca' }}</td>
                    <td class="actions">
                        @unless($p->user->activo)<span class="chip">Inactivo</span>@endunless
                        <a href="{{ route('admin.pacientes.edit', $p) }}" class="btn btn-sm btn-ghost"><x-icon name="edit" size="16"/> Editar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $pacientes->links() }}
    @endif
</x-layouts.app>
