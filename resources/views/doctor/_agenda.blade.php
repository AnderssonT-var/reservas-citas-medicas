{{-- Lista del día compartida por "Hoy" y "Agenda". --}}
@php($ahora = now()->format('H:i:s'))
@php($esHoy = $fecha->isToday())
@if($citas->isEmpty())
    <x-empty title="{{ $doctor->horarios->contains('dia_semana', $fecha->dayOfWeekIso) ? 'Agenda libre este día' : 'No atiendes este día' }}">
        <p>{{ $doctor->horarios->contains('dia_semana', $fecha->dayOfWeekIso) ? 'Aún nadie ha reservado. Los pacientes ven tus horarios en línea.' : 'Puedes agregar turnos desde tu horario.' }}</p>
        @unless($doctor->horarios->contains('dia_semana', $fecha->dayOfWeekIso))<a class="btn btn-sm" href="{{ route('doctor.horario') }}">Editar mi horario</a>@endunless
    </x-empty>
@else
<div class="appt-list">
    @foreach($citas as $c)
        @php($pasada = $esHoy ? $c->hora_fin < $ahora : $fecha->isPast())
        @php($enCurso = $esHoy && $c->hora_inicio <= $ahora && $c->hora_fin > $ahora)
        <div class="appt {{ $pasada && !$c->estado->esModificable() ? 'is-past' : '' }} {{ $enCurso ? 'is-now' : '' }}">
            <span class="appt-time">{{ $c->horaInicio() }}<small>{{ $enCurso ? 'en curso' : 'hasta '.$c->horaFin() }}</small></span>
            <a href="{{ route('doctor.citas.show', $c) }}" class="appt-who">
                <b>{{ $c->paciente->user->name }}</b>
                <span>{{ $c->motivo ?: 'Sin motivo indicado' }}</span>
            </a>
            <span class="cluster" style="--gap:.4rem;justify-content:flex-end">
                @if($c->estado->esModificable() && ($esHoy || $fecha->isPast()))
                    <a href="{{ route('doctor.atender', $c) }}" class="btn btn-sm {{ $enCurso ? 'btn-primary' : '' }}">Atender</a>
                @else
                    <x-estado :estado="$c->estado"/>
                @endif
            </span>
        </div>
    @endforeach
</div>
@endif
