<x-layouts.app title="Mi perfil">
    <x-page-head title="Mi perfil" subtitle="Tus datos de contacto, tu contraseña y cómo se ve la aplicación."/>

    <form method="POST" action="{{ route('perfil.update') }}" novalidate>
        @csrf @method('PUT')
        <section class="form-section">
            <header><h2>Datos personales</h2></header>
            <div class="form-grid">
                <x-field name="name" label="Nombre completo" :value="$user->name" required wrap="span-all" autocomplete="name"/>
                <x-field name="email" type="email" label="Correo" :value="$user->email" required autocomplete="email"/>
                <x-field name="telefono" type="tel" label="Celular" :value="$user->telefono" autocomplete="tel"/>
                @if($user->esPaciente())
                    <x-field name="cedula" label="Cédula" :value="$user->paciente->cedula" required inputmode="numeric" maxlength="10"/>
                @endif
            </div>
        </section>
        @if($user->esPaciente())
            <section class="form-section">
                <header><h2>Ficha clínica</h2><p>Solo la ven tus doctores y la clínica.</p></header>
                <div class="form-grid">@include('admin.pacientes.campos', ['paciente' => $user->paciente])</div>
            </section>
        @endif
        @if($user->esDoctor())
            <section class="form-section">
                <header><h2>Presentación</h2><p>La ven los pacientes en la portada.</p></header>
                <div class="form-grid"><x-field type="textarea" name="biografia" label="Sobre ti" :value="$user->doctor->biografia" wrap="span-all" maxlength="800"/></div>
            </section>
        @endif
        <div class="form-actions"><button class="btn btn-primary">Guardar perfil</button></div>
    </form>

    <section class="form-section mt-4">
        <header><h2>Apariencia</h2><p>Se guarda en este dispositivo.</p></header>
        <fieldset>
            <legend class="sr-only">Tema de color</legend>
            <div class="theme-options" style="max-width:480px">
                <label><input type="radio" name="tema" value="system"><span class="swatch sw-system"></span>Sistema <span class="muted small" data-tema-sistema></span></label>
                <label><input type="radio" name="tema" value="light"><span class="swatch sw-light"></span>Claro</label>
                <label><input type="radio" name="tema" value="dark"><span class="swatch sw-dark"></span>Oscuro</label>
            </div>
        </fieldset>
    </section>

    <form method="POST" action="{{ route('perfil.password') }}" class="form-section" novalidate>
        @csrf @method('PUT')
        <header><h2>Contraseña</h2></header>
        <div class="form-grid">
            <x-field name="actual" type="password" label="Contraseña actual" required autocomplete="current-password" wrap="span-all"/>
            <x-field name="password" type="password" label="Nueva contraseña" required autocomplete="new-password" hint="Mínimo 8 caracteres."/>
            <x-field name="password_confirmation" type="password" label="Repite la nueva" required autocomplete="new-password"/>
            <div class="span-all"><button class="btn">Cambiar contraseña</button></div>
        </div>
    </form>
</x-layouts.app>
