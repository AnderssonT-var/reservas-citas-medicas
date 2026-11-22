<x-layouts.app title="Registrar pago">
    <x-page-head title="Registrar pago" :back="route('admin.pagos.index')" back-label="Pagos"
        :subtitle="$cita ? 'Consulta del '.$cita->fecha->translatedFormat('j \d\e F').' con '.$cita->doctor->user->name.'.' : null"/>
    <form method="POST" action="{{ route('admin.pagos.store') }}" class="card" style="max-width:720px" novalidate>
        @csrf
        <input type="hidden" name="cita_id" value="{{ old('cita_id', $cita?->id) }}">
        <div class="form-grid">
            <x-field type="select" name="paciente_id" label="Paciente" required wrap="span-all">
                <option value="">Elige un paciente</option>
                @foreach($pacientes as $p)<option value="{{ $p->id }}" @selected(old('paciente_id', $cita?->paciente_id ?? request('paciente')) == $p->id)>{{ $p->user->name }} ({{ $p->cedula }})</option>@endforeach
            </x-field>
            <x-field type="number" name="monto" label="Monto (USD)" :value="$cita?->doctor->tarifa" required min="0.01" step="0.01"/>
            <x-field type="date" name="fecha_pago" label="Fecha" :value="now()->toDateString()" required max="{{ now()->toDateString() }}"/>
            <x-field type="select" name="metodo" label="Método" required>
                @foreach($metodos as $m)<option value="{{ $m->value }}" @selected(old('metodo') === $m->value)>{{ $m->label() }}</option>@endforeach
            </x-field>
            <x-field type="select" name="estado" label="Estado" required>
                <option value="pagado" @selected(old('estado', 'pagado') === 'pagado')>Cobrado</option>
                <option value="pendiente" @selected(old('estado') === 'pendiente')>Pendiente</option>
            </x-field>
            <x-field name="referencia" label="Referencia" hint="Número de comprobante o transferencia." wrap="span-all"/>
        </div>
        @error('cita_id')<p class="notice error mt-2"><x-icon name="alert"/> {{ $message }}</p>@enderror
        <div class="form-actions">
            <a href="{{ route('admin.pagos.index') }}" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary">Registrar pago</button>
        </div>
    </form>
</x-layouts.app>
