<x-layouts.app title="Reservar cita">
    <x-page-head title="Reservar cita" subtitle="Solo te mostramos horarios libres. Al confirmar, el bloque queda apartado para ti."/>
    <form method="POST" action="{{ route('paciente.reservar.store') }}" novalidate>
        @csrf
        @include('citas.asistente')
    </form>
</x-layouts.app>
