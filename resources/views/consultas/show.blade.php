@php($u = auth()->user())
<x-layouts.app title="Consulta">
    <x-page-head :title="$consulta->motivo" :subtitle="ucfirst($consulta->fecha->translatedFormat('l j \d\e F Y, H:i')).' con '.$consulta->doctor->user->name"
        :back="$u->esPaciente() ? route('paciente.historial') : ($u->esDoctor() ? route('doctor.pacientes.show', $consulta->paciente) : route('admin.pacientes.show', $consulta->paciente))" back-label="Historial">
        <x-slot:actions>
            @can('update', $consulta)<a href="{{ route('doctor.consultas.edit', $consulta) }}" class="btn"><x-icon name="edit" size="18"/> Editar</a>@endcan
            <a href="{{ route('consultas.pdf', $consulta) }}" class="btn btn-primary" target="_blank"><x-icon name="download" size="18"/> Descargar PDF</a>
        </x-slot:actions>
    </x-page-head>

    <div class="split">
        <div class="stack" style="--gap:1.25rem">
            @if($consulta->presion || $consulta->peso || $consulta->temperatura || $consulta->frecuencia_cardiaca)
            <section class="vitals" aria-label="Signos vitales">
                @if($consulta->presion)<div class="vital"><small>Presión</small><b>{{ $consulta->presion }}<em>mmHg</em></b></div>@endif
                @if($consulta->frecuencia_cardiaca)<div class="vital"><small>Pulso</small><b>{{ $consulta->frecuencia_cardiaca }}<em>lpm</em></b></div>@endif
                @if($consulta->temperatura)<div class="vital"><small>Temperatura</small><b>{{ $consulta->temperatura }}<em>°C</em></b></div>@endif
                @if($consulta->peso)<div class="vital"><small>Peso</small><b>{{ $consulta->peso }}<em>kg</em></b></div>@endif
                @if($consulta->talla)<div class="vital"><small>Talla</small><b>{{ $consulta->talla }}<em>cm</em></b></div>@endif
                @if($consulta->imc())<div class="vital"><small>IMC</small><b>{{ $consulta->imc() }}</b></div>@endif
            </section>
            @endif
            <section class="card stack">
                @foreach(['sintomas' => 'Síntomas', 'diagnostico' => 'Diagnóstico', 'tratamiento' => 'Tratamiento', 'notas' => 'Notas'] as $campo => $titulo)
                    @if($consulta->$campo)
                        <div><h3>{{ $titulo }}</h3><p class="mt-1" style="white-space:pre-line">{{ $consulta->$campo }}</p></div>
                    @endif
                @endforeach
            </section>
            @if($consulta->receta)
            <section class="card" style="border-left:3px solid var(--pine)">
                <h3>Receta</h3>
                <p class="mt-1" style="white-space:pre-line">{{ $consulta->receta }}</p>
            </section>
            @endif
        </div>
        <aside>@include('pacientes.ficha', ['paciente' => $consulta->paciente])</aside>
    </div>
</x-layouts.app>
