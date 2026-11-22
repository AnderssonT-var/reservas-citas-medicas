<section class="card">
    <div class="profile-head">
        <x-avatar :user="$paciente->user" size="xl"/>
        <div>
            <h2>{{ $paciente->user->name }}</h2>
            <p class="small">{{ $paciente->edad() !== null ? $paciente->edad().' años' : 'Edad no registrada' }}@if($paciente->genero), {{ $paciente->genero }}@endif</p>
        </div>
    </div>
    @if($paciente->alergias)
        <p class="notice error mt-2"><x-icon name="alert"/> <span><b>Alergias:</b> {{ $paciente->alergias }}</span></p>
    @endif
    <hr class="divider">
    <dl class="kv">
        <dt>Cédula</dt><dd class="num">{{ $paciente->cedula }}</dd>
        <dt>Sangre</dt><dd>{{ $paciente->grupo_sanguineo ?: '—' }}</dd>
        <dt>Celular</dt><dd class="num">{{ $paciente->user->telefono ?: '—' }}</dd>
        <dt>Correo</dt><dd style="overflow-wrap:anywhere">{{ $paciente->user->email }}</dd>
        <dt>Dirección</dt><dd>{{ $paciente->direccion ?: '—' }}</dd>
        <dt>Emergencia</dt><dd>{{ $paciente->contacto_emergencia ?: '—' }}</dd>
    </dl>
</section>
