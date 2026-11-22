@if($consultas->isEmpty())
    <x-empty icon="heart" title="Todavía no hay consultas registradas"/>
@else
<div class="timeline">
    @foreach($consultas as $c)
        <article class="timeline-item card" style="--dot:{{ $c->doctor->especialidad->color }}">
            <div class="cluster" style="justify-content:space-between;--gap:.4rem 1rem">
                <div>
                    <h3>{{ $c->motivo }}</h3>
                    <p class="muted small">{{ ucfirst($c->fecha->translatedFormat('l j \d\e F Y')) }}, {{ $c->doctor->user->name }} ({{ $c->doctor->especialidad->nombre }})</p>
                </div>
                <a href="{{ route('consultas.show', $c) }}" class="btn btn-sm">Ver detalle</a>
            </div>
            <p class="mt-1"><b style="font-weight:600">Diagnóstico:</b> {{ $c->diagnostico }}</p>
            @if($c->receta)<p class="muted small mt-1">Receta: {{ \Illuminate\Support\Str::limit($c->receta, 120) }}</p>@endif
        </article>
    @endforeach
</div>
@endif
