<x-layouts.site title="No tienes acceso a esta sección">
<div class="error-page"><div class="stack" style="align-items:center">
    <b>403</b>
    <h1>No tienes acceso a esta sección</h1>
    <p class="muted">{{ $exception->getMessage() && 403 === 403 ? $exception->getMessage() : 'Tu perfil no permite ver esta página.' }}</p>
    <a href="{{ auth()->check() ? route('panel') : route('inicio') }}" class="btn btn-primary mt-2">{{ auth()->check() ? 'Ir a mi panel' : 'Ir al inicio' }}</a>
</div></div>
</x-layouts.site>
