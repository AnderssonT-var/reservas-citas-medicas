@php($editando = $paciente->exists)
<x-layouts.app :title="$editando ? 'Editar paciente' : 'Nuevo paciente'">
    <x-page-head :title="$editando ? $paciente->user->name : 'Nuevo paciente'" :back="$editando ? route('admin.pacientes.show', $paciente) : route('admin.pacientes.index')" :back-label="$editando ? 'Volver a la ficha' : 'Pacientes'"
        :subtitle="$editando ? null : 'La contraseña inicial será su número de cédula. Puede cambiarla desde su perfil.'"/>

    <form method="POST" action="{{ $editando ? route('admin.pacientes.update', $paciente) : route('admin.pacientes.store') }}" novalidate>
        @csrf
        @if($editando) @method('PUT') @endif
        <section class="form-section">
            <header><h2>Identificación</h2><p>Validamos el dígito verificador de la cédula.</p></header>
            <div class="form-grid">
                <x-field name="name" label="Nombre completo" :value="$paciente->user?->name" required wrap="span-all"/>
                <x-field name="cedula" label="Cédula" :value="$paciente->cedula" required inputmode="numeric" maxlength="10"/>
                <x-field name="telefono" type="tel" label="Celular" :value="$paciente->user?->telefono"/>
                <x-field name="email" type="email" label="Correo" :value="$paciente->user?->email" required wrap="span-all"/>
            </div>
        </section>
        <section class="form-section">
            <header><h2>Datos clínicos</h2><p>Lo verá el doctor antes de cada consulta.</p></header>
            <div class="form-grid">@include('admin.pacientes.campos')</div>
        </section>
        <div class="form-actions">
            <a href="{{ url()->previous() }}" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Registrar paciente' }}</button>
        </div>
    </form>
</x-layouts.app>
