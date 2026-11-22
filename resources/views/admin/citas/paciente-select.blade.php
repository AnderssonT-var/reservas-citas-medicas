<section class="wz-step done">
    <header><span class="wz-num"><x-icon name="user" size="15"/></span><h2>Paciente</h2></header>
    <x-field type="select" name="paciente_id" label="¿Para quién es la cita?" required>
        <option value="">Busca y elige un paciente</option>
        @foreach($pacientes as $p)<option value="{{ $p->id }}" @selected(old('paciente_id', request('paciente')) == $p->id)>{{ $p->user->name }} ({{ $p->cedula }})</option>@endforeach
    </x-field>
    <p class="small muted mt-1">¿No está registrado? <a class="link" href="{{ route('admin.pacientes.create') }}">Créalo primero</a>.</p>
</section>
