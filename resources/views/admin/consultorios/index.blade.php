<x-layouts.app title="Consultorios">
    <x-page-head title="Consultorios" subtitle="Dónde atiende cada doctor. Se muestra al paciente al confirmar su cita.">
        <x-slot:actions><a href="{{ route('admin.consultorios.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Nuevo consultorio</a></x-slot:actions>
    </x-page-head>
    @if($consultorios->isEmpty())
        <x-empty icon="door" title="Aún no hay consultorios"><a class="btn btn-sm" href="{{ route('admin.consultorios.create') }}">Crear el primero</a></x-empty>
    @else
    <div class="grid grid-3">
        @foreach($consultorios as $c)
            <article class="card">
                <div class="cluster" style="justify-content:space-between">
                    <h3>{{ $c->nombre }}</h3>
                    @if($c->activo)<span class="chip" data-tono="pine">Activo</span>@else<span class="chip">Cerrado</span>@endif
                </div>
                <p class="muted small mt-1"><x-icon name="pin" size="14" style="display:inline;vertical-align:-2px"/> {{ $c->ubicacion }}</p>
                <p class="small mt-2">{{ $c->doctores->isEmpty() ? 'Sin doctores asignados' : $c->doctores->map(fn ($d) => $d->user->name)->join(', ') }}</p>
                <div class="cluster mt-2" style="justify-content:flex-end;--gap:.25rem">
                    <a href="{{ route('admin.consultorios.edit', $c) }}" class="btn btn-sm btn-ghost">Editar</a>
                    <form method="POST" action="{{ route('admin.consultorios.destroy', $c) }}" data-confirm="¿Eliminar {{ $c->nombre }}?" data-confirm-detalle="Los doctores asignados quedarán sin consultorio." data-confirm-boton="Eliminar">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" aria-label="Eliminar {{ $c->nombre }}"><x-icon name="trash" size="16"/></button></form>
                </div>
            </article>
        @endforeach
    </div>
    @endif
</x-layouts.app>
