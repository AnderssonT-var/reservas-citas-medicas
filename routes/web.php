<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AgendaApiController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\SesionController;
use App\Http\Controllers\CitaAccionController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\ConsultaPdfController;
use App\Http\Controllers\Doctor;
use App\Http\Controllers\Paciente;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'inicio'])->name('inicio');

Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [SesionController::class, 'create'])->name('login');
    Route::post('/ingresar', [SesionController::class, 'store']);
    Route::get('/registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'store']);
});

Route::middleware(['auth', 'activo'])->group(function () {
    Route::post('/salir', [SesionController::class, 'destroy'])->name('logout');
    Route::get('/panel', PanelController::class)->name('panel');

    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/contrasena', [PerfilController::class, 'password'])->name('perfil.password');

    // Agenda en JSON para el asistente de reserva (paciente y admin).
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/doctores', [AgendaApiController::class, 'doctores'])->name('doctores');
        Route::get('/doctores/{doctor}/dias', [AgendaApiController::class, 'dias'])->name('dias');
        Route::get('/doctores/{doctor}/bloques', [AgendaApiController::class, 'bloques'])->name('bloques');
    });

    // Acciones de estado: cada una valida permisos con CitaPolicy y AgendaService.
    Route::post('/citas/{cita}/cancelar', [CitaAccionController::class, 'cancelar'])->name('citas.cancelar');
    Route::post('/citas/{cita}/confirmar', [CitaAccionController::class, 'confirmar'])->name('citas.confirmar');
    Route::post('/citas/{cita}/no-asistio', [CitaAccionController::class, 'noAsistio'])->name('citas.no-asistio');

    Route::get('/consultas/{consulta}', [ConsultaController::class, 'show'])->name('consultas.show');
    Route::get('/consultas/{consulta}/pdf', ConsultaPdfController::class)->name('consultas.pdf');

    // ---------- Administración ----------
    Route::middleware('rol:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::resource('doctores', Admin\DoctorController::class)->parameters(['doctores' => 'doctor']);
        Route::resource('pacientes', Admin\PacienteController::class);
        Route::resource('especialidades', Admin\EspecialidadController::class)->except('show')->parameters(['especialidades' => 'especialidad']);
        Route::resource('consultorios', Admin\ConsultorioController::class)->except('show');
        Route::get('/citas/exportar', [Admin\CitaController::class, 'exportar'])->name('citas.exportar');
        Route::resource('citas', Admin\CitaController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/pagos', [Admin\PagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/nuevo', [Admin\PagoController::class, 'create'])->name('pagos.create');
        Route::post('/pagos', [Admin\PagoController::class, 'store'])->name('pagos.store');
        Route::post('/pagos/{pago}/anular', [Admin\PagoController::class, 'anular'])->name('pagos.anular');
        Route::post('/pagos/{pago}/cobrar', [Admin\PagoController::class, 'marcarPagado'])->name('pagos.cobrar');
        Route::get('/ajustes', [Admin\AjusteController::class, 'edit'])->name('ajustes');
        Route::put('/ajustes', [Admin\AjusteController::class, 'update'])->name('ajustes.update');
        Route::post('/ajustes/administradores', [Admin\AjusteController::class, 'crearAdmin'])->name('ajustes.admin');
        Route::post('/ajustes/administradores/{user}', [Admin\AjusteController::class, 'alternarAdmin'])->name('ajustes.admin.alternar');
    });

    // ---------- Doctor ----------
    Route::middleware('rol:doctor')->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/', [Doctor\PanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/agenda', [Doctor\PanelController::class, 'agenda'])->name('agenda');
        Route::get('/citas/{cita}', [Doctor\PanelController::class, 'cita'])->name('citas.show');
        Route::get('/citas/{cita}/atender', [Doctor\ConsultaController::class, 'create'])->name('atender');
        Route::post('/citas/{cita}/atender', [Doctor\ConsultaController::class, 'store'])->name('atender.store');
        Route::get('/consultas/{consulta}/editar', [Doctor\ConsultaController::class, 'edit'])->name('consultas.edit');
        Route::put('/consultas/{consulta}', [Doctor\ConsultaController::class, 'update'])->name('consultas.update');
        Route::get('/pacientes', [Doctor\PacienteController::class, 'index'])->name('pacientes');
        Route::get('/pacientes/{paciente}', [Doctor\PacienteController::class, 'show'])->name('pacientes.show');
        Route::get('/horario', [Doctor\HorarioController::class, 'edit'])->name('horario');
        Route::put('/horario', [Doctor\HorarioController::class, 'update'])->name('horario.update');
    });

    // ---------- Paciente ----------
    Route::middleware('rol:paciente')->prefix('mi')->name('paciente.')->group(function () {
        Route::get('/', [Paciente\PanelController::class, 'inicio'])->name('inicio');
        Route::get('/reservar', [Paciente\PanelController::class, 'reservar'])->name('reservar');
        Route::post('/reservar', [Paciente\PanelController::class, 'guardarReserva'])->name('reservar.store');
        Route::get('/citas', [Paciente\PanelController::class, 'citas'])->name('citas');
        Route::get('/citas/{cita}', [Paciente\PanelController::class, 'cita'])->name('citas.show');
        Route::get('/historial', [Paciente\PanelController::class, 'historial'])->name('historial');
    });
});
