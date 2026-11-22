{{-- Asistente de reserva compartido. Requiere $especialidades; opcional $preDoctor, $preEspecialidad, $extra (slot antes de los pasos). --}}
<div class="wizard" data-reserva
     data-api-doctores="{{ route('api.doctores') }}"
     data-api-dias="{{ route('api.dias', ['doctor' => '__id__']) }}"
     data-api-bloques="{{ route('api.bloques', ['doctor' => '__id__']) }}"
     data-pre-doctor="{{ $preDoctor ?? '' }}" data-pre-especialidad="{{ $preEspecialidad ?? '' }}">
    <div class="card">
        @isset($extra){{ $extra }}@endisset

        <section class="wz-step done" data-paso="especialidad">
            <header><span class="wz-num">1</span><h2>¿Qué especialidad necesitas?</h2></header>
            <div class="pills" role="group" aria-label="Especialidades">
                <button type="button" class="pill" aria-pressed="false" data-especialidad="" data-nombre="Todas" style="--c:var(--ink-3)">Todas</button>
                @foreach($especialidades as $e)
                    <button type="button" class="pill" aria-pressed="false" data-especialidad="{{ $e->id }}" data-nombre="{{ $e->nombre }}" style="--c:{{ $e->color }}">{{ $e->nombre }}</button>
                @endforeach
            </div>
        </section>

        <section class="wz-step" data-paso="doctor">
            <header><span class="wz-num">2</span><h2>Elige al doctor</h2></header>
            <div class="doc-options" data-doctores aria-live="polite"></div>
        </section>

        <section class="wz-step" data-paso="dia">
            <header><span class="wz-num">3</span><h2>¿Qué día te queda bien?</h2></header>
            <div class="date-strip" data-dias role="group" aria-label="Días disponibles"></div>
        </section>

        <section class="wz-step" data-paso="hora">
            <header><span class="wz-num">4</span><h2>Elige la hora <span class="muted" style="font:inherit;font-weight:400;font-size:.9em" data-mes></span></h2></header>
            <div class="slots" data-bloques role="group" aria-label="Horarios"></div>
        </section>

        <section class="wz-step" data-paso="motivo">
            <header><span class="wz-num">5</span><h2>Cuéntale al doctor el motivo</h2></header>
            <x-field type="textarea" name="motivo" label="Motivo de la consulta" placeholder="Por ejemplo: dolor de cabeza desde hace tres días" maxlength="255" rows="2"/>
        </section>
    </div>

    <aside class="stub" data-stub aria-live="polite">
        <h2>Tu cita</h2>
        <div class="stub-row"><span>Especialidad</span><b data-r="especialidad">Sin elegir</b></div>
        <div class="stub-row"><span>Doctor</span><b data-r="doctor">Sin elegir</b></div>
        <div class="stub-row"><span>Consultorio</span><b data-r="consultorio">Sin elegir</b></div>
        <div class="stub-row"><span>Día</span><b data-r="fecha">Sin elegir</b></div>
        <div class="stub-row"><span>Tarifa</span><b data-r="tarifa">Sin elegir</b></div>
        <div class="stub-tear" role="presentation"></div>
        <div class="cluster" style="justify-content:space-between;align-items:end">
            <div><span class="muted small">Hora</span><div class="stub-big" data-r="hora">--:--</div></div>
        </div>
        <input type="hidden" name="doctor_id" value="{{ old('doctor_id') }}">
        <input type="hidden" name="fecha" value="{{ old('fecha') }}">
        <input type="hidden" name="hora" value="{{ old('hora') }}">
        @error('hora')<p class="notice error mt-2"><x-icon name="alert"/> {{ $message }}</p>@enderror
        <button class="btn btn-primary btn-lg w-full mt-3" data-enviar disabled>{{ $boton ?? 'Reservar cita' }}</button>
        <p class="muted small mt-1" style="text-align:center">{{ $nota ?? 'Puedes cancelar sin costo hasta '.$clinica['horas_cancelacion'].' horas antes.' }}</p>
    </aside>
</div>
