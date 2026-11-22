@php($editando = $doctor->exists)
<x-layouts.app :title="$editando ? 'Editar doctor' : 'Nuevo doctor'">
    <x-page-head :title="$editando ? $doctor->user->name : 'Nuevo doctor'" :back="$editando ? route('admin.doctores.show', $doctor) : route('admin.doctores.index')" :back-label="$editando ? 'Volver al perfil' : 'Doctores'"/>

    <form method="POST" action="{{ $editando ? route('admin.doctores.update', $doctor) : route('admin.doctores.store') }}" novalidate>
        @csrf
        @if($editando) @method('PUT') @endif

        <section class="form-section">
            <header><h2>Cuenta</h2><p>Con este correo y contraseña el doctor entra a su agenda.</p></header>
            <div class="form-grid">
                <x-field name="name" label="Nombre completo" :value="$doctor->user?->name" required wrap="span-all"/>
                <x-field name="email" type="email" label="Correo" :value="$doctor->user?->email" required autocomplete="off"/>
                <x-field name="telefono" type="tel" label="Teléfono" :value="$doctor->user?->telefono"/>
                <x-field name="password" type="password" :label="$editando ? 'Nueva contraseña' : 'Contraseña inicial'" :required="!$editando" autocomplete="new-password" :hint="$editando ? 'Déjala vacía para no cambiarla.' : 'Mínimo 8 caracteres.'"/>
            </div>
        </section>

        <section class="form-section">
            <header><h2>Práctica médica</h2><p>La duración define el tamaño de cada bloque en la agenda.</p></header>
            <div class="form-grid">
                <x-field type="select" name="especialidad_id" label="Especialidad" required>
                    <option value="">Elige una</option>
                    @foreach($especialidades as $e)<option value="{{ $e->id }}" @selected(old('especialidad_id', $doctor->especialidad_id) == $e->id)>{{ $e->nombre }}</option>@endforeach
                </x-field>
                <x-field type="select" name="consultorio_id" label="Consultorio">
                    <option value="">Sin asignar</option>
                    @foreach($consultorios as $c)<option value="{{ $c->id }}" @selected(old('consultorio_id', $doctor->consultorio_id) == $c->id)>{{ $c->nombre }}</option>@endforeach
                </x-field>
                <x-field name="licencia_medica" label="Registro médico (MSP)" :value="$doctor->licencia_medica" required/>
                <x-field type="number" name="duracion_cita" label="Duración de cada cita (min)" :value="$doctor->duracion_cita" required min="10" max="180" step="5"/>
                <x-field type="number" name="tarifa" label="Tarifa por consulta (USD)" :value="$doctor->tarifa" required min="0" step="0.01"/>
                <x-field type="textarea" name="biografia" label="Presentación para pacientes" :value="$doctor->biografia" wrap="span-all" maxlength="800"/>
                <label class="check span-all"><input type="hidden" name="activo" value="0"><input type="checkbox" name="activo" value="1" @checked(old('activo', $doctor->activo))> Recibe citas en línea</label>
            </div>
        </section>

        <section class="form-section">
            <header><h2>Turnos semanales</h2><p>Puedes tener dos turnos el mismo día, por ejemplo mañana y tarde. No pueden cruzarse.</p></header>
            <x-turnos :horarios="$doctor->horarios ?? collect()"/>
        </section>

        <div class="form-actions">
            <a href="{{ url()->previous() }}" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Registrar doctor' }}</button>
        </div>
    </form>
</x-layouts.app>
