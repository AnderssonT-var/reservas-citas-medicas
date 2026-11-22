@php
    $ahora = now();
    $hora = $ahora->hour + $ahora->minute / 60;
    $now = ($hora >= 8 && $hora <= 13) ? $hora - 8 : 2.35;
    $c = $especialidades->pluck('color', 'nombre');
    $tablero = [
        [0, 1, 'Control de niño sano', 'Pediatría'],
        [1, .5, 'Chequeo general', 'Medicina general'],
        [1.5, 1, 'Electrocardiograma', 'Cardiología'],
        [3, .5, null, null],
        [3.5, 1, 'Revisión de lunar', 'Dermatología'],
    ];
    $dias = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
@endphp
<x-layouts.site>
<header class="site-head">
    <a href="{{ route('inicio') }}" class="brand" style="padding:0">
        <span class="brand-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
        {{ $clinica['nombre_clinica'] }}
    </a>
    <nav aria-label="Principal">
        <a class="plain" href="#especialidades">Especialidades</a>
        <a class="plain" href="#doctores">Doctores</a>
        <a class="plain" href="#como-funciona">Cómo reservar</a>
        @auth
            <a class="btn btn-primary" href="{{ route('panel') }}">Ir a mi panel</a>
        @else
            <a class="btn btn-ghost" href="{{ route('login') }}">Ingresar</a>
            <a class="btn btn-primary" href="{{ route('registro') }}">Reservar cita</a>
        @endauth
    </nav>
</header>

<main>
    <section class="wrap hero">
        <div>
            <h1>Tu cita médica, a la hora que te sirve.</h1>
            <p class="hero-lede">Mira los horarios libres de cada doctor, elige el tuyo y recibe la confirmación al instante. Sin llamadas ni filas.</p>
            <div class="hero-cta">
                <a class="btn btn-primary btn-lg" href="{{ auth()->check() ? route('panel') : route('registro') }}">Reservar mi cita</a>
                <a class="btn btn-lg" href="#doctores">Ver doctores</a>
            </div>
            <div class="hero-facts">
                <div><b class="num">{{ $especialidades->count() }}</b>especialidades</div>
                <div><b class="num">{{ $especialidades->sum('doctores_count') }}</b>doctores con agenda en línea</div>
                <div><b>{{ $clinica['horas_cancelacion'] }} h</b>para cancelar sin costo</div>
            </div>
        </div>

        <div class="day-board">
            <header>
                <h2>Hoy en la clínica</h2>
                <span class="num">{{ $ahora->translatedFormat('l j \d\e F') }}</span>
            </header>
            <div class="board-rows" style="--now:{{ round($now, 2) }}">
                @foreach(range(8, 12) as $i => $h)
                    <span class="board-hour">{{ sprintf('%02d:00', $h) }}</span>
                    <div class="board-lane">
                        @if($i === 0)
                            @foreach($tablero as $k => [$start, $len, $motivo, $esp])
                                @if($motivo)
                                    <div class="board-appt {{ $len < 1 ? 'short' : '' }}" style="--start:{{ $start }};--len:{{ $len }};--c:{{ $c[$esp] ?? '#0F6B5C' }};--i:{{ $k }}"><b>{{ $motivo }}</b><span>{{ $esp }}</span></div>
                                @else
                                    <div class="board-appt open" style="--start:{{ $start }};--len:{{ $len }};--i:{{ $k }}">11:00 libre</div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                @endforeach
                <div class="board-now" role="presentation"></div>
            </div>
        </div>
    </section>

    <section class="section" id="especialidades">
        <div class="wrap">
            <div class="section-head reveal">
                <h2>Especialidades</h2>
                <p>Elige el área y te mostramos solo los doctores y horarios que corresponden.</p>
            </div>
            <div class="spec-grid">
                @foreach($especialidades as $e)
                    <a class="spec" style="--c:{{ $e->color }}" href="{{ auth()->check() && auth()->user()->esPaciente() ? route('paciente.reservar', ['especialidad' => $e->id]) : route('registro') }}">
                        <span class="dot"></span>
                        <h3>{{ $e->nombre }}</h3>
                        <p>{{ $e->descripcion }}</p>
                        <small>{{ trans_choice('{0} Pronto disponible|{1} 1 doctor|[2,*] :count doctores', $e->doctores_count) }}</small>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" id="doctores" style="padding-top:0">
        <div class="wrap">
            <div class="section-head reveal">
                <h2>Conoce a quién te atiende</h2>
                <p>Cada doctor publica sus días de consulta. Lo que ves aquí es lo que puedes reservar.</p>
            </div>
            <div class="doc-grid">
                @foreach($doctores as $d)
                    @php($diasOn = $d->horarios->pluck('dia_semana')->unique())
                    <article class="doc-card reveal">
                        <header>
                            <x-avatar :user="$d->user" :color="$d->especialidad->color" size="lg"/>
                            <div><h3>{{ $d->user->name }}</h3><span>{{ $d->especialidad->nombre }}</span></div>
                        </header>
                        @if($d->biografia)<p class="muted small">{{ $d->biografia }}</p>@endif
                        <div class="doc-days" aria-label="Días de atención">
                            @foreach($dias as $n => $l)
                                <i class="{{ $diasOn->contains($n + 1) ? 'on' : '' }}" title="{{ \App\Models\Horario::DIAS[$n + 1] }}">{{ $l }}</i>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" id="como-funciona" style="padding-top:0">
        <div class="wrap">
            <div class="section-head reveal"><h2>Reservar toma tres pasos</h2></div>
            <div class="steps">
                <div class="step reveal"><h3>Crea tu cuenta</h3><p>Con tu cédula y un correo. Lo haces una sola vez.</p></div>
                <div class="step reveal"><h3>Elige doctor y hora</h3><p>Solo ves horarios realmente libres; nadie más puede tomar el que elegiste.</p></div>
                <div class="step reveal"><h3>Llega a tu consulta</h3><p>El doctor registra tu diagnóstico y receta. Los encuentras en tu historial.</p></div>
            </div>
        </div>
    </section>

    <section class="wrap">
        <div class="cta-band reveal">
            <div>
                <h2>¿Te duele algo hoy? Mira qué horarios quedan.</h2>
                <p>{{ $clinica["direccion"] }}@if($clinica["telefono"])<br>Teléfono {{ $clinica["telefono"] }}@endif</p>
            </div>
            <a class="btn btn-primary btn-lg" href="{{ auth()->check() ? route('panel') : route('registro') }}">Reservar ahora</a>
        </div>
    </section>
</main>

<footer class="wrap site-foot">
    <span>© {{ date('Y') }} {{ $clinica['nombre_clinica'] }}</span>
    <span>Desarrollado por A.T.</span>
</footer>
</x-layouts.site>
