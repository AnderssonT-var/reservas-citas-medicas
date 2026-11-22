@php
    $editando = $consulta->exists;
    $paciente = $paciente ?? $cita->paciente;
@endphp
<x-layouts.app :title="'Consulta de '.$paciente->user->name">
    <x-page-head :title="$paciente->user->name" :subtitle="$editando ? 'Editando la consulta del '.$consulta->fecha->translatedFormat('j \d\e F') : 'Cita de las '.$cita->horaInicio().'. Al guardar, la cita queda como atendida.'"
        :back="$editando ? route('consultas.show', $consulta) : route('doctor.citas.show', $cita)" back-label="Volver"/>

    <div class="split">
        <form method="POST" action="{{ $editando ? route('doctor.consultas.update', $consulta) : route('doctor.atender.store', $cita) }}" class="card" novalidate>
            @csrf @if($editando) @method('PUT') @endif
            <section class="form-section">
                <header><h2>Signos vitales</h2><p>Todos opcionales.</p></header>
                <div class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr))">
                    <x-field name="presion" label="Presión" :value="$consulta->presion" placeholder="120/80" inputmode="numeric"/>
                    <x-field type="number" name="frecuencia_cardiaca" label="Pulso (lpm)" :value="$consulta->frecuencia_cardiaca" min="20" max="250"/>
                    <x-field type="number" name="temperatura" label="Temp. (°C)" :value="$consulta->temperatura" step="0.1" min="30" max="45"/>
                    <x-field type="number" name="peso" label="Peso (kg)" :value="$consulta->peso" step="0.1"/>
                    <x-field type="number" name="talla" label="Talla (cm)" :value="$consulta->talla"/>
                </div>
            </section>
            <section class="form-section">
                <header><h2>Evaluación</h2></header>
                <div class="form-grid">
                    <x-field name="motivo" label="Motivo de consulta" :value="$consulta->motivo" required wrap="span-all"/>
                    <x-field type="textarea" name="sintomas" label="Síntomas y exploración" :value="$consulta->sintomas" wrap="span-all"/>
                    <x-field type="textarea" name="diagnostico" label="Diagnóstico" :value="$consulta->diagnostico" required wrap="span-all"/>
                    <x-field type="textarea" name="tratamiento" label="Tratamiento e indicaciones" :value="$consulta->tratamiento" wrap="span-all"/>
                </div>
            </section>
            <section class="form-section">
                <header><h2>Receta</h2><p>El paciente la verá y podrá descargarla en PDF.</p></header>
                <div class="form-grid">
                    <x-field type="textarea" name="receta" label="Medicamentos" :value="$consulta->receta" wrap="span-all" placeholder="Un medicamento por línea: nombre, dosis, frecuencia y duración"/>
                    <x-field type="textarea" name="notas" label="Notas internas" :value="$consulta->notas" wrap="span-all" hint="Visibles en el historial."/>
                </div>
            </section>
            <div class="form-actions">
                <button class="btn btn-primary btn-lg">{{ $editando ? 'Guardar cambios' : 'Guardar y cerrar consulta' }}</button>
            </div>
        </form>
        <aside class="stack" style="--gap:1.25rem">
            @include('pacientes.ficha')
            @if(!$editando && $paciente->consultas->isNotEmpty())
                <section class="card">
                    <div class="card-head"><h2>Última consulta</h2></div>
                    @php($u = $paciente->consultas->first())
                    <p class="small muted">{{ $u->fecha->translatedFormat('j M Y') }}, {{ $u->doctor->user->name }}</p>
                    <p class="mt-1"><b style="font-weight:600">{{ $u->motivo }}:</b> {{ $u->diagnostico }}</p>
                    <a href="{{ route('doctor.pacientes.show', $paciente) }}" class="link small mt-1" style="display:inline-block">Ver historial completo</a>
                </section>
            @endif
        </aside>
    </div>
</x-layouts.app>
