@props(['title' => null])
@php
    $u = auth()->user();
    $r = request()->route()?->getName() ?? '';
    $on = fn (string ...$p) => collect($p)->contains(fn ($x) => \Illuminate\Support\Str::is($x, $r)) ? 'page' : null;
    $nav = match ($u->rol) {
        \App\Enums\Rol::Admin => [
            'Clínica' => [
                ['admin.dashboard', 'home', 'Resumen', ['admin.dashboard']],
                ['admin.citas.index', 'calendar', 'Citas', ['admin.citas.*']],
                ['admin.pacientes.index', 'users', 'Pacientes', ['admin.pacientes.*']],
                ['admin.doctores.index', 'stethoscope', 'Doctores', ['admin.doctores.*']],
                ['admin.pagos.index', 'wallet', 'Pagos', ['admin.pagos.*']],
            ],
            'Configuración' => [
                ['admin.especialidades.index', 'tag', 'Especialidades', ['admin.especialidades.*']],
                ['admin.consultorios.index', 'door', 'Consultorios', ['admin.consultorios.*']],
                ['admin.ajustes', 'settings', 'Ajustes', ['admin.ajustes*']],
            ],
        ],
        \App\Enums\Rol::Doctor => [
            'Consulta' => [
                ['doctor.dashboard', 'home', 'Hoy', ['doctor.dashboard', 'doctor.atender*']],
                ['doctor.agenda', 'calendar', 'Agenda', ['doctor.agenda', 'doctor.citas.*']],
                ['doctor.pacientes', 'users', 'Mis pacientes', ['doctor.pacientes*', 'doctor.consultas.*']],
                ['doctor.horario', 'clock', 'Mi horario', ['doctor.horario']],
            ],
        ],
        \App\Enums\Rol::Paciente => [
            'Mi salud' => [
                ['paciente.inicio', 'home', 'Inicio', ['paciente.inicio']],
                ['paciente.reservar', 'calendar-plus', 'Reservar', ['paciente.reservar']],
                ['paciente.citas', 'calendar', 'Mis citas', ['paciente.citas*']],
                ['paciente.historial', 'heart', 'Historial', ['paciente.historial', 'consultas.*']],
            ],
        ],
    };
    $tabs = collect($nav)->flatten(1)->take(4);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    @include('components.layouts.head')
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ $clinica['nombre_clinica'] }}</title>
</head>
<body>
<a href="#contenido" class="sr-only">Saltar al contenido</a>
<div class="shell">
    <aside class="sidebar" aria-label="Navegación principal">
        <a href="{{ route('panel') }}" class="brand">
            <span class="brand-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
            <span>{{ $clinica['nombre_clinica'] }}<small>{{ $u->rol->label() }}</small></span>
        </a>

        <nav class="nav">
            @foreach($nav as $grupo => $items)
                <span class="nav-title">{{ $grupo }}</span>
                @foreach($items as [$ruta, $icono, $texto, $patrones])
                    <a href="{{ route($ruta) }}" aria-current="{{ $on(...$patrones) }}"><x-icon :name="$icono"/> {{ $texto }}</a>
                @endforeach
            @endforeach
        </nav>

        <div class="sidebar-foot">
            <button type="button" class="me" popovertarget="menu-usuario" style="anchor-name:--me">
                <x-avatar :user="$u"/>
                <span style="min-width:0"><span class="me-name">{{ $u->name }}</span><span class="me-role">{{ $u->email }}</span></span>
            </button>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <a href="{{ route('panel') }}" class="brand" style="padding:0">
                <span class="brand-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
                <span>{{ $clinica['nombre_clinica'] }}</span>
            </a>
            <button type="button" popovertarget="menu-usuario" aria-label="Menú de {{ $u->name }}" style="anchor-name:--me-m"><x-avatar :user="$u"/></button>
        </header>

        <main id="contenido" class="canvas" tabindex="-1">
            {{ $slot }}
        </main>
    </div>
</div>

<nav class="tabbar" aria-label="Accesos rápidos">
    @foreach($tabs as [$ruta, $icono, $texto, $patrones])
        <a href="{{ route($ruta) }}" aria-current="{{ $on(...$patrones) }}"><x-icon :name="$icono"/> {{ $texto }}</a>
    @endforeach
    @if(collect($nav)->flatten(1)->count() > 4)
        <button type="button" popovertarget="menu-usuario"><x-icon name="menu"/> Más</button>
    @endif
</nav>

<div id="menu-usuario" class="menu" popover style="position-anchor:--me">
    <div style="padding:.5rem .7rem .4rem"><b style="display:block;font-size:.9rem">{{ $u->name }}</b><span class="muted small">{{ $u->rol->label() }}</span></div>
    <hr>
    @foreach(collect($nav)->flatten(1)->slice(4) as [$ruta, $icono, $texto])
        <a href="{{ route($ruta) }}"><x-icon :name="$icono"/> {{ $texto }}</a>
    @endforeach
    <a href="{{ route('perfil') }}"><x-icon name="user"/> Mi perfil y apariencia</a>
    <hr>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icon name="logout"/> Cerrar sesión</button></form>
</div>

<div id="toasts" class="toasts" role="region" aria-label="Notificaciones"></div>
<p class="sr-only">Anuncios para lectores de pantalla</p>
<div id="anuncios" class="sr-only" aria-live="polite"></div>
@if(session('ok'))<template data-toast="ok">{{ session('ok') }}</template>@endif
@if($errors->has('cita') || $errors->has('hora') || $errors->has('especialidad') || $errors->has('admin'))
    <template data-toast="error">{{ $errors->first('cita') ?: $errors->first('hora') ?: $errors->first('especialidad') ?: $errors->first('admin') }}</template>
@endif

<dialog id="confirmar" class="modal" aria-labelledby="confirmar-titulo" closedby="any">
    <form method="dialog">
        <div class="modal-body">
            <h2 id="confirmar-titulo">¿Confirmar?</h2>
            <p data-detalle></p>
            <div class="field" data-motivo hidden>
                <label for="confirmar-motivo">Motivo <span class="muted" style="font-weight:400">(opcional)</span></label>
                <textarea id="confirmar-motivo" class="textarea" rows="2" style="min-height:70px" placeholder="Ayuda a la clínica a reorganizar la agenda"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn" value="cancelar" autofocus>Volver</button>
            <button class="btn btn-danger" value="ok">Confirmar</button>
        </div>
    </form>
</dialog>
</body>
</html>
