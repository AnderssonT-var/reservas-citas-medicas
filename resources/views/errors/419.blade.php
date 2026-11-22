<x-layouts.site title="La sesión expiró">
<div class="error-page"><div class="stack" style="align-items:center">
    <b>419</b>
    <h1>La sesión expiró</h1>
    <p class="muted">{{ $exception->getMessage() && 419 === 403 ? $exception->getMessage() : 'Recarga la página e inténtalo de nuevo.' }}</p>
    <a href="{{ auth()->check() ? route('panel') : route('inicio') }}" class="btn btn-primary mt-2">{{ auth()->check() ? 'Ir a mi panel' : 'Ir al inicio' }}</a>
</div></div>
</x-layouts.site>
