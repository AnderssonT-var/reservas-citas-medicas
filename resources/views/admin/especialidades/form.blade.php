@php($editando = $especialidad->exists)
<x-layouts.app :title="$editando ? 'Editar especialidad' : 'Nueva especialidad'">
    <x-page-head :title="$editando ? $especialidad->nombre : 'Nueva especialidad'" :back="route('admin.especialidades.index')" back-label="Especialidades"/>
    <form method="POST" action="{{ $editando ? route('admin.especialidades.update', $especialidad) : route('admin.especialidades.store') }}" class="card" style="max-width:640px" novalidate>
        @csrf @if($editando) @method('PUT') @endif
        <div class="form-grid">
            <x-field name="nombre" label="Nombre" :value="$especialidad->nombre" required wrap="span-all"/>
            <x-field type="textarea" name="descripcion" label="Descripción corta" :value="$especialidad->descripcion" wrap="span-all" maxlength="255" hint="Aparece en la portada pública."/>
            <x-field type="color" name="color" label="Color" :value="$especialidad->color" required/>
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.especialidades.index') }}" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Crear especialidad' }}</button>
        </div>
    </form>
</x-layouts.app>
