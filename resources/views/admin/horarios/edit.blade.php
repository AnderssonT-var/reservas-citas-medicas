@extends('layouts.admin')
@section('content')
    <div class="row">
        <h1>Editar Horario</h1>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Modifique los datos</h3>
                </div>
                <div class="card-body row">
                    <div class="col-md-3">
                        <form action="{{ route('admin.horarios.update', $horario->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="consultorio_select">Consultorios</label> <b>*</b>
                                        <select name="consultorio_id" id="consultorio_select" class="form-control">
                                            <option value="">Seleccionar consultorio</option>
                                            @foreach($consultorios as $consultorio)
                                                <option value="{{ $consultorio->id }}"
                                                    {{ $horario->consultorio_id == $consultorio->id ? 'selected' : '' }}>
                                                    {{ $consultorio->nombre . " - " . $consultorio->ubicacion }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('consultorio_id')
                                            <small style="color:red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="doctor_id">Doctores</label> <b>*</b>
                                        <select name="doctor_id" id="doctor_id" class="form-control">
                                            @foreach($doctores as $doctore)
                                                <option value="{{ $doctore->id }}"
                                                    {{ $horario->doctor_id == $doctore->id ? 'selected' : '' }}>
                                                    {{ $doctore->nombres . " " . $doctore->apellidos . " - " . $doctore->especialidad }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <small style="color:red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="dia">Día</label> <b>*</b>
                                        <select name="dia" id="dia" class="form-control">
                                            @foreach(['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'] as $dia)
                                                <option value="{{ $dia }}"
                                                    {{ $horario->dia == $dia ? 'selected' : '' }}>
                                                    {{ $dia }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('dia')
                                            <small style="color:red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="hora_inicio">Hora Inicio</label> <b>*</b>
                                        <input type="time" value="{{ $horario->hora_inicio }}" name="hora_inicio" id="hora_inicio" class="form-control" required>
                                        @error('hora_inicio')
                                            <small style="color:red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="hora_fin">Hora Final</label> <b>*</b>
                                        <input type="time" value="{{ $horario->hora_fin }}" name="hora_fin" id="hora_fin" class="form-control" required>
                                        @error('hora_fin')
                                            <small style="color:red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="{{ route('admin.horarios.index') }}" class="btn btn-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-success">Actualizar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-9">
                        <div id="consultorio_info">
                            <!-- Aquí se cargarán los datos del consultorio seleccionado -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            function loadConsultorioInfo(consultorio_id) {
                if (consultorio_id) {
                    $.ajax({
                        url: "{{ url('/admin/horarios/consultorios/') }}/" + consultorio_id,
                        type: 'GET',
                        success: function (data) {
                            $('#consultorio_info').html(data);
                        },
                        error: function (xhr, status, error) {
                            console.error('Error en la llamada AJAX:', status, error, xhr.responseText);
                            $('#consultorio_info').html('<p style="color:red">Error al cargar los horarios. Por favor, intenta de nuevo.</p>');
                        }
                    });
                } else {
                    $('#consultorio_info').html('<p>Selecciona un consultorio para ver los horarios.</p>');
                }
            }

            // Vincular el evento change
            $('#consultorio_select').on('change', function () {
                var consultorio_id = $(this).val();
                loadConsultorioInfo(consultorio_id);
            });

            // Cargar los datos iniciales
            var initial_consultorio_id = $('#consultorio_select').val();
            loadConsultorioInfo(initial_consultorio_id);
        });
    </script>
@endsection
