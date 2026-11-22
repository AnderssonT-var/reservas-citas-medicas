<x-layouts.site title="Crear cuenta · {{ $clinica['nombre_clinica'] }}">
<div class="auth">
    <div class="auth-form">
        <a href="{{ route('inicio') }}" class="back muted small" style="display:inline-flex;gap:.3rem;align-items:center"><x-icon name="arrow-left" size="16"/> Volver al inicio</a>
        <div class="inner">
            <h1>Crea tu cuenta</h1>
            <p class="lede">Con tu cuenta reservas en un minuto y tienes tus consultas siempre a mano.</p>
            <form method="POST" action="{{ route('registro') }}" class="stack">
                @csrf
                <x-field name="name" label="Nombre completo" required autocomplete="name"/>
                <div class="form-grid">
                    <x-field name="cedula" label="Cédula" required inputmode="numeric" maxlength="10" pattern="\d{10}" autocomplete="off"/>
                    <x-field name="telefono" type="tel" label="Celular" required autocomplete="tel" placeholder="09…"/>
                </div>
                <x-field name="email" type="email" label="Correo" required autocomplete="email"/>
                <div class="form-grid">
                    <x-field name="password" type="password" label="Contraseña" required autocomplete="new-password" hint="Mínimo 8 caracteres."/>
                    <x-field name="password_confirmation" type="password" label="Repite la contraseña" required autocomplete="new-password"/>
                </div>
                <button class="btn btn-primary btn-lg w-full mt-1">Crear cuenta</button>
                <p class="muted small" style="text-align:center">¿Ya tienes cuenta? <a class="link" href="{{ route('login') }}">Ingresa</a></p>
            </form>
        </div>
    </div>
    @include('auth.arte')
</div>
</x-layouts.site>
