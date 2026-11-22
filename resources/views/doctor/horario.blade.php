<x-layouts.app title="Mi horario">
    <x-page-head title="Mi horario" :subtitle="'Bloques de '.$doctor->duracion_cita.' minutos. Los pacientes solo ven los horarios que publiques aquí.'"/>
    <form method="POST" action="{{ route('doctor.horario.update') }}" class="card" style="max-width:760px" novalidate>
        @csrf @method('PUT')
        <x-turnos :horarios="$doctor->horarios"/>
        <p class="notice info mt-3"><x-icon name="alert"/> Cambiar el horario no cancela citas ya reservadas. Si necesitas liberar un día con citas, cancélalas desde tu agenda.</p>
        <div class="form-actions mt-2"><button class="btn btn-primary">Guardar horario</button></div>
    </form>
</x-layouts.app>
