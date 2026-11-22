@php($editando = $consultorio->exists)
<x-layouts.app :title="$editando ? 'Editar consultorio' : 'Nuevo consultorio'">
    <x-page-head :title="$editando ? $consultorio->nombre : 'Nuevo consultorio'" :back="route('admin.consultorios.index')" back-label="Consultorios"/>
    <form method="POST" action="{{ $editando ? route('admin.consultorios.update', $consultorio) : route('admin.consultorios.store') }}" class="card" style="max-width:640px" novalidate>
        @csrf @if($editando) @method('PUT') @endif
        <div class="form-grid">
            <x-field name="nombre" label="Nombre" :value="$consultorio->nombre" required/>
            <x-field name="telefono" type="tel" label="Extensión o teléfono" :value="$consultorio->telefono"/>
            <x-field name="ubicacion" label="Ubicación" :value="$consultorio->ubicacion" required wrap="span-all" placeholder="Piso, ala o referencia"/>
            <label class="check span-all"><input type="hidden" name="activo" value="0"><input type="checkbox" name="activo" value="1" @checked(old('activo', $consultorio->activo))> En funcionamiento</label>
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.consultorios.index') }}" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Crear consultorio' }}</button>
        </div>
    </form>
</x-layouts.app>
