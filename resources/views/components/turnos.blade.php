@props(['horarios' => collect()])
@php
    $filas = old('horarios', $horarios->map(fn ($h) => ['dia_semana' => $h->dia_semana, 'hora_inicio' => $h->inicio, 'hora_fin' => $h->fin])->values()->all());
    $dias = \App\Models\Horario::DIAS;
@endphp
<div data-turnos>
    <div data-turnos-lista>
        @foreach($filas as $i => $f)
            <div class="shift-row">
                <x-field type="select" name="horarios[{{ $i }}][dia_semana]" label="Día" required>
                    @foreach($dias as $n => $d)<option value="{{ $n }}" @selected((int) ($f['dia_semana'] ?? 0) === $n)>{{ $d }}</option>@endforeach
                </x-field>
                <x-field type="time" name="horarios[{{ $i }}][hora_inicio]" label="Desde" :value="$f['hora_inicio'] ?? ''" required step="900"/>
                <x-field type="time" name="horarios[{{ $i }}][hora_fin]" label="Hasta" :value="$f['hora_fin'] ?? ''" required step="900"/>
                <button type="button" class="btn btn-ghost btn-icon" data-turnos-quitar aria-label="Quitar turno" style="margin-top:1.65rem"><x-icon name="trash" size="18"/></button>
            </div>
        @endforeach
    </div>
    <p class="muted small" data-turnos-vacio hidden>Sin turnos: el doctor no aparecerá para reservar hasta que agregues al menos uno.</p>
    <button type="button" class="btn btn-sm mt-2" data-turnos-agregar><x-icon name="plus" size="16"/> Agregar turno</button>
    <template>
        <div class="shift-row">
            <div class="field"><label for="h-__i__-d">Día</label>
                <select class="select" id="h-__i__-d" name="horarios[__i__][dia_semana]" required>@foreach($dias as $n => $d)<option value="{{ $n }}">{{ $d }}</option>@endforeach</select></div>
            <div class="field"><label for="h-__i__-i">Desde</label><input class="input" type="time" step="900" id="h-__i__-i" name="horarios[__i__][hora_inicio]" required></div>
            <div class="field"><label for="h-__i__-f">Hasta</label><input class="input" type="time" step="900" id="h-__i__-f" name="horarios[__i__][hora_fin]" required></div>
            <button type="button" class="btn btn-ghost btn-icon" data-turnos-quitar aria-label="Quitar turno" style="margin-top:1.65rem"><x-icon name="trash" size="18"/></button>
        </div>
    </template>
</div>
