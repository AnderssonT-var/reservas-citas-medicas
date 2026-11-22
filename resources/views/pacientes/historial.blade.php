@php($esPaciente = auth()->user()->esPaciente())
<x-layouts.app :title="$esPaciente ? 'Mi historial' : 'Historial de '.$paciente->user->name">
    <x-page-head :title="$esPaciente ? 'Mi historial clínico' : $paciente->user->name"
        :subtitle="$esPaciente ? 'Diagnósticos, tratamientos y recetas de tus consultas. Puedes descargar cada una en PDF.' : 'Historial clínico completo del paciente.'"
        :back="$esPaciente ? null : route('doctor.pacientes')" back-label="Mis pacientes"/>
    <div class="split">
        <section>
            @include('pacientes.timeline', ['consultas' => $paciente->consultas])
        </section>
        <aside>@include('pacientes.ficha')</aside>
    </div>
</x-layouts.app>
