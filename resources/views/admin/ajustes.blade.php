<x-layouts.app title="Ajustes">
    <x-page-head title="Ajustes" subtitle="Datos de la clínica y reglas de reserva para todos los doctores."/>

    <form method="POST" action="{{ route('admin.ajustes.update') }}" novalidate>
        @csrf @method('PUT')
        <section class="form-section">
            <header><h2>Clínica</h2><p>Aparece en la portada, los correos y los PDF.</p></header>
            <div class="form-grid">
                <x-field name="nombre_clinica" label="Nombre" :value="$ajustes['nombre_clinica']" required/>
                <x-field name="telefono" type="tel" label="Teléfono" :value="$ajustes['telefono']"/>
                <x-field name="correo" type="email" label="Correo" :value="$ajustes['correo']"/>
                <x-field name="direccion" label="Dirección" :value="$ajustes['direccion']"/>
            </div>
        </section>
        <section class="form-section">
            <header><h2>Reglas de reserva</h2><p>Se aplican a todos los pacientes al reservar y cancelar en línea.</p></header>
            <div class="form-grid">
                <x-field type="number" name="horas_cancelacion" label="Horas mínimas para cancelar" :value="$ajustes['horas_cancelacion']" required min="0" max="72" hint="Con menos tiempo, el paciente debe llamar."/>
                <x-field type="number" name="dias_reserva" label="Días hacia adelante para reservar" :value="$ajustes['dias_reserva']" required min="1" max="180"/>
            </div>
        </section>
        <div class="form-actions"><button class="btn btn-primary">Guardar ajustes</button></div>
    </form>

    <section class="form-section mt-4">
        <header><h2>Administradores</h2><p>Personas con acceso completo al panel.</p></header>
        <div class="stack">
            @if($errors->has('admin'))<p class="notice error"><x-icon name="alert"/> {{ $errors->first('admin') }}</p>@endif
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                    @foreach($admins as $a)
                        <tr>
                            <td><span class="person"><x-avatar :user="$a"/><span><b>{{ $a->name }}</b><span>{{ $a->email }}</span></span></span></td>
                            <td>@if($a->activo)<span class="chip" data-tono="pine">Activo</span>@else<span class="chip">Desactivado</span>@endif</td>
                            <td class="actions">
                                @unless($a->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.ajustes.admin.alternar', $a) }}">@csrf<button class="btn btn-sm btn-ghost">{{ $a->activo ? 'Desactivar' : 'Reactivar' }}</button></form>
                                @else
                                    <span class="muted small">Tu cuenta</span>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <details class="card flat">
                <summary style="cursor:pointer;font-weight:600">Agregar administrador</summary>
                <form method="POST" action="{{ route('admin.ajustes.admin') }}" class="form-grid mt-2" novalidate>
                    @csrf
                    <x-field name="name" label="Nombre" required/>
                    <x-field name="email" type="email" label="Correo" required/>
                    <x-field name="password" type="password" label="Contraseña inicial" required autocomplete="new-password"/>
                    <div class="span-all"><button class="btn btn-primary">Crear administrador</button></div>
                </form>
            </details>
        </div>
    </section>
</x-layouts.app>
