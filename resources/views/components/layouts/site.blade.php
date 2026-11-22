@props(['title' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    @include('components.layouts.head')
    <title>{{ $title ?? $clinica['nombre_clinica'].' · Reserva de citas médicas' }}</title>
</head>
<body>
    {{ $slot }}
    <div id="toasts" class="toasts" role="region" aria-label="Notificaciones"></div>
    <div id="anuncios" class="sr-only" aria-live="polite"></div>
    @if(session('ok'))<template data-toast="ok">{{ session('ok') }}</template>@endif
</body>
</html>
