<x-layouts.site title="Ingresar · {{ $clinica['nombre_clinica'] }}">
<div class="auth">
    <div class="auth-form">
        <a href="{{ route('inicio') }}" class="back muted small" style="display:inline-flex;gap:.3rem;align-items:center"><x-icon name="arrow-left" size="16"/> Volver al inicio</a>
        <div class="inner">
            <h1>Hola de nuevo</h1>
            <p class="lede">Ingresa para ver tus citas o tu agenda del día.</p>
            <form method="POST" action="{{ route('login') }}" class="stack">
                @csrf
                <x-field name="email" type="email" label="Correo" required autocomplete="email" autofocus/>
                <x-field name="password" type="password" label="Contraseña" required autocomplete="current-password"/>
                <label class="check"><input type="checkbox" name="recordar" value="1"> Mantener la sesión en este equipo</label>
                <button class="btn btn-primary btn-lg w-full mt-1">Ingresar</button>
                <p class="muted small" style="text-align:center">¿Primera vez? <a class="link" href="{{ route('registro') }}">Crea tu cuenta de paciente</a></p>

                @if(app()->environment('local') || config('app.demo'))
                <div class="demo-accounts">
                    <p style="margin-bottom:.4rem">Cuentas de prueba (contraseña <b>password</b>):</p>
                    <button type="button" data-demo="admin@tovelmedical.com"><b>Administración</b><span>admin@tovelmedical.com</span></button>
                    <button type="button" data-demo="doctor@tovelmedical.com"><b>Doctor</b><span>doctor@tovelmedical.com</span></button>
                    <button type="button" data-demo="paciente@tovelmedical.com"><b>Paciente</b><span>paciente@tovelmedical.com</span></button>
                </div>
                @endif
            </form>
        </div>
    </div>
    @include('auth.arte')
</div>
</x-layouts.site>
