<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8">
<style>
    @page { margin: 28px 32px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #0F2A26; }
    .head { border-bottom: 2px solid #0F6B5C; padding-bottom: 8px; margin-bottom: 12px; }
    .head h1 { font-size: 16px; margin: 0; color: #0F6B5C; }
    .muted { color: #6F857F; }
    h2 { font-size: 11px; margin: 12px 0 4px; color: #0F6B5C; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 3px 0; vertical-align: top; }
    .box { background: #EEF3F1; padding: 8px 10px; border-radius: 6px; }
    .rx { border-left: 3px solid #0F6B5C; padding-left: 10px; white-space: pre-line; }
    .firma { margin-top: 40px; text-align: center; }
    .firma div { border-top: 1px solid #0F2A26; width: 180px; margin: 0 auto 4px; }
    .pie { position: fixed; bottom: 0; left: 0; right: 0; font-size: 8px; text-align: center; color: #6F857F; }
</style></head>
<body>
    <div class="head">
        <h1>{{ $ajustes['nombre_clinica'] }}</h1>
        <span class="muted">{{ $ajustes['direccion'] }} {{ $ajustes['telefono'] ? '| Tel. '.$ajustes['telefono'] : '' }}</span>
    </div>
    <table>
        <tr><td><b>Paciente:</b> {{ $consulta->paciente->user->name }}</td><td><b>Cédula:</b> {{ $consulta->paciente->cedula }}</td></tr>
        <tr><td><b>Doctor:</b> {{ $consulta->doctor->user->name }} ({{ $consulta->doctor->especialidad->nombre }})</td><td><b>Fecha:</b> {{ $consulta->fecha->format('d/m/Y H:i') }}</td></tr>
        @if($consulta->paciente->alergias)<tr><td colspan="2"><b>Alergias:</b> {{ $consulta->paciente->alergias }}</td></tr>@endif
    </table>
    @if($consulta->presion || $consulta->peso || $consulta->temperatura)
        <h2>Signos vitales</h2>
        <div class="box">
            {{ $consulta->presion ? 'PA '.$consulta->presion.' mmHg' : '' }}
            {{ $consulta->frecuencia_cardiaca ? '  |  FC '.$consulta->frecuencia_cardiaca.' lpm' : '' }}
            {{ $consulta->temperatura ? '  |  T '.$consulta->temperatura.' °C' : '' }}
            {{ $consulta->peso ? '  |  Peso '.$consulta->peso.' kg' : '' }}
            {{ $consulta->talla ? '  |  Talla '.$consulta->talla.' cm' : '' }}
        </div>
    @endif
    <h2>Motivo</h2><p>{{ $consulta->motivo }}</p>
    <h2>Diagnóstico</h2><p>{{ $consulta->diagnostico }}</p>
    @if($consulta->tratamiento)<h2>Tratamiento</h2><p>{{ $consulta->tratamiento }}</p>@endif
    @if($consulta->receta)<h2>Receta</h2><div class="rx">{{ $consulta->receta }}</div>@endif
    <div class="firma"><div></div>{{ $consulta->doctor->user->name }}<br><span class="muted">Reg. {{ $consulta->doctor->licencia_medica }}</span></div>
    <div class="pie">Documento generado por {{ $ajustes['nombre_clinica'] }} el {{ now()->format('d/m/Y H:i') }}. Desarrollado por A.T.</div>
</body></html>
