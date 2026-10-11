@extends('layouts.admin')
@section('content')
    <div class="row">
        <h1>Horario: {{ $horario->dia }} de {{ $horario->hora_inicio }} a {{ $horario->hora_fin }}</h1>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">¿Está seguro de eliminar este horario?</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.horarios.destroy', $horario->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Día</label>
                                    <p>{{ $horario->dia }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Hora Inicio</label>
                                    <p>{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Hora Fin</label>
                                    <p>{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Consultorio</label>
                                    <p>{{ $horario->consultorio ? $horario->consultorio->nombre . ' - ' . $horario->consultorio->ubicacion : 'Sin asignar' }}</p>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Doctor</label>
                                    <p>{{ $horario->doctor ? $horario->doctor->nombres . ' ' . $horario->doctor->apellidos . ' - ' . $horario->doctor->especialidad : 'Sin asignar' }}</p>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <a href="{{ route('admin.horarios.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-danger">Eliminar registro</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
