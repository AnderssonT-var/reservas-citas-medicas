{{-- Campos clínicos compartidos por el formulario del admin y el perfil del paciente. --}}
<x-field name="fecha_nacimiento" type="date" label="Fecha de nacimiento" :value="$paciente->fecha_nacimiento?->toDateString()" max="{{ now()->toDateString() }}"/>
<x-field type="select" name="genero" label="Género">
    <option value="">Prefiero no decir</option>
    @foreach(['femenino' => 'Femenino', 'masculino' => 'Masculino', 'otro' => 'Otro'] as $v => $t)
        <option value="{{ $v }}" @selected(old('genero', $paciente->genero) === $v)>{{ $t }}</option>
    @endforeach
</x-field>
<x-field type="select" name="grupo_sanguineo" label="Grupo sanguíneo">
    <option value="">No lo sé</option>
    @foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $g)<option @selected(old('grupo_sanguineo', $paciente->grupo_sanguineo) === $g)>{{ $g }}</option>@endforeach
</x-field>
<x-field name="direccion" label="Dirección" :value="$paciente->direccion" autocomplete="street-address"/>
<x-field name="contacto_emergencia" label="Contacto de emergencia" :value="$paciente->contacto_emergencia" placeholder="Nombre y teléfono" wrap="span-all"/>
<x-field type="textarea" name="alergias" label="Alergias" :value="$paciente->alergias" wrap="span-all" placeholder="Medicamentos, alimentos u otras" maxlength="500"/>
