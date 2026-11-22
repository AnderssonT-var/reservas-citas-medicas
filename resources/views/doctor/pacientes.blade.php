<x-layouts.app title="Mis pacientes">
    <x-page-head title="Mis pacientes" subtitle="Personas que han tenido al menos una cita contigo."/>
    <form class="filters" method="GET" role="search">
        <div class="field grow"><label for="q" class="sr-only">Buscar</label>
            <div class="search"><x-icon name="search"/><input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Nombre o cédula" type="search"></div></div>
        <button class="btn">Buscar</button>
    </form>
    @if($pacientes->isEmpty())
        <x-empty icon="users" title="Aún no tienes pacientes"><p>Aparecerán aquí cuando reserven contigo.</p></x-empty>
    @else
    <div class="table-wrap" role="region" aria-label="Mis pacientes" tabindex="0">
        <table class="table">
            <thead><tr><th>Paciente</th><th>Edad</th><th>Consultas contigo</th><th>Última cita</th></tr></thead>
            <tbody>
            @foreach($pacientes as $p)
                <tr>
                    <td><a href="{{ route('doctor.pacientes.show', $p) }}" class="person"><x-avatar :user="$p->user"/><span><b>{{ $p->user->name }}</b><span>{{ $p->cedula }}</span></span></a></td>
                    <td data-label="Edad">{{ $p->edad() !== null ? $p->edad().' años' : '—' }}</td>
                    <td data-label="Consultas" class="num">{{ $p->visitas }}</td>
                    <td data-label="Última cita" class="muted">{{ $p->ultima ? \Carbon\Carbon::parse($p->ultima)->translatedFormat('j M Y') : '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $pacientes->links() }}
    @endif
</x-layouts.app>
