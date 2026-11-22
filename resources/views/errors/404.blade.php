<x-layouts.site title="No encontramos esta página">
<div class="error-page"><div class="stack" style="align-items:center">
    <b>404</b>
    <h1>No encontramos esta página</h1>
    <p class="muted">{{ $exception->getMessage() && 404 === 403 ? $exception->getMessage() : 'Puede que el enlace esté incompleto o que el registro se haya eliminado.' }}</p>
    <a href="{{ auth()->check() ? route('panel') : route('inicio') }}" class="btn btn-primary mt-2">{{ auth()->check() ? 'Ir a mi panel' : 'Ir al inicio' }}</a>
</div></div>
</x-layouts.site>
