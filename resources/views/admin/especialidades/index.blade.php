<x-layouts.app title="Especialidades">
    <x-page-head title="Especialidades" subtitle="El color identifica la especialidad en la agenda, el historial y la portada.">
        <x-slot:actions><a href="{{ route('admin.especialidades.create') }}" class="btn btn-primary"><x-icon name="plus" size="18"/> Nueva especialidad</a></x-slot:actions>
    </x-page-head>
    @if($errors->has('especialidad'))<p class="notice error" style="margin-bottom:1rem"><x-icon name="alert"/> {{ $errors->first('especialidad') }}</p>@endif
    @if($especialidades->isEmpty())
        <x-empty icon="tag" title="Aún no hay especialidades"><a class="btn btn-sm" href="{{ route('admin.especialidades.create') }}">Crear la primera</a></x-empty>
    @else
    <div class="grid grid-3">
        @foreach($especialidades as $e)
            <article class="card" style="border-top:3px solid {{ $e->color }}">
                <h3>{{ $e->nombre }}</h3>
                <p class="muted small mt-1">{{ $e->descripcion ?: 'Sin descripción' }}</p>
                <div class="cluster mt-2" style="justify-content:space-between">
                    <span class="small muted">{{ trans_choice('{0} Sin doctores|{1} 1 doctor|[2,*] :count doctores', $e->doctores_count) }}</span>
                    <span class="cluster" style="--gap:.25rem">
                        <a href="{{ route('admin.especialidades.edit', $e) }}" class="btn btn-sm btn-ghost">Editar</a>
                        <form method="POST" action="{{ route('admin.especialidades.destroy', $e) }}" data-confirm="¿Eliminar {{ $e->nombre }}?" data-confirm-boton="Eliminar">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" aria-label="Eliminar {{ $e->nombre }}"><x-icon name="trash" size="16"/></button></form>
                    </span>
                </div>
            </article>
        @endforeach
    </div>
    @endif
</x-layouts.app>
