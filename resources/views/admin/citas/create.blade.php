<x-layouts.app title="Agendar cita">
    <x-page-head title="Agendar cita" subtitle="Las citas que agenda la clínica quedan confirmadas de inmediato." :back="route('admin.citas.index')" back-label="Citas"/>
    <form method="POST" action="{{ route('admin.citas.store') }}" novalidate>
        @csrf
        @include('citas.asistente', ['boton' => 'Agendar y confirmar', 'nota' => 'El paciente verá la cita en su cuenta.', 'extra' => new \Illuminate\Support\HtmlString(view('admin.citas.paciente-select', ['pacientes' => $pacientes])->render())])
    </form>
</x-layouts.app>
