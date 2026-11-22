<x-layouts.site title="Algo falló de nuestro lado">
<div class="error-page"><div class="stack" style="align-items:center">
    <b>500</b>
    <h1>Algo falló de nuestro lado</h1>
    <p class="muted">{{ $exception->getMessage() && 500 === 403 ? $exception->getMessage() : 'Ya quedó registrado. Intenta de nuevo en unos minutos.' }}</p>
    <a href="{{ auth()->check() ? route('panel') : route('inicio') }}" class="btn btn-primary mt-2">{{ auth()->check() ? 'Ir a mi panel' : 'Ir al inicio' }}</a>
</div></div>
</x-layouts.site>
