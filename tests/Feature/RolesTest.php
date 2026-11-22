<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\Doctor;
use App\Models\Paciente;
use App\Models\User;
use App\Services\AgendaService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cada_rol_llega_a_su_panel(): void
    {
        $admin = User::factory()->rol(Rol::Admin)->create();
        $doctor = Doctor::factory()->create();
        $paciente = Paciente::factory()->create();

        $this->actingAs($admin)->get('/panel')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($doctor->user)->get('/panel')->assertRedirect(route('doctor.dashboard'));
        $this->actingAs($paciente->user)->get('/panel')->assertRedirect(route('paciente.inicio'));
    }

    public function test_paneles_cargan_para_su_rol(): void
    {
        $this->seed();

        foreach (['admin' => ['admin.dashboard', 'admin.citas.index', 'admin.doctores.index', 'admin.pacientes.index', 'admin.pagos.index', 'admin.ajustes', 'admin.especialidades.index', 'admin.consultorios.index', 'admin.citas.create', 'admin.doctores.create', 'admin.pacientes.create', 'admin.pagos.create', 'perfil'],
            'doctor' => ['doctor.dashboard', 'doctor.agenda', 'doctor.pacientes', 'doctor.horario', 'perfil'],
            'paciente' => ['paciente.inicio', 'paciente.reservar', 'paciente.citas', 'paciente.historial', 'perfil']] as $rol => $rutas) {
            $user = User::where('email', "$rol@tovelmedical.com")->first();
            foreach ($rutas as $ruta) {
                $this->actingAs($user)->get(route($ruta))->assertOk();
            }
        }

        $this->get('/')->assertOk();
    }

    public function test_detalles_cargan_sin_errores(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@tovelmedical.com')->first();
        $doctor = Doctor::first();
        $paciente = Paciente::has('consultas')->first();
        $cita = $doctor->citas()->first();
        $consulta = $paciente->consultas()->first();

        $this->actingAs($admin)->get(route('admin.doctores.show', $doctor))->assertOk();
        $this->actingAs($admin)->get(route('admin.doctores.edit', $doctor))->assertOk();
        $this->actingAs($admin)->get(route('admin.pacientes.show', $paciente))->assertOk();
        $this->actingAs($admin)->get(route('admin.citas.show', $cita))->assertOk();
        $this->actingAs($admin)->get(route('consultas.show', $consulta))->assertOk();
        $this->actingAs($admin)->get(route('consultas.pdf', $consulta))->assertOk();
        $this->actingAs($admin)->get(route('admin.citas.exportar'))->assertOk();
        $this->actingAs($doctor->user)->get(route('doctor.citas.show', $cita))->assertOk();
        $this->actingAs($paciente->user)->get(route('paciente.citas.show', $paciente->citas()->first()))->assertOk();
    }

    public function test_paciente_no_entra_al_admin_ni_al_panel_del_doctor(): void
    {
        $paciente = Paciente::factory()->create();
        $this->actingAs($paciente->user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($paciente->user)->get(route('doctor.dashboard'))->assertForbidden();
    }

    public function test_doctor_no_entra_al_admin(): void
    {
        $doctor = Doctor::factory()->create();
        $this->actingAs($doctor->user)->get(route('admin.doctores.index'))->assertForbidden();
    }

    public function test_paciente_no_ve_citas_ajenas(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-21 10:00'));
        $doctor = Doctor::factory()->create();
        $doctor->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']);
        $duena = Paciente::factory()->create();
        $intruso = Paciente::factory()->create();
        $cita = app(AgendaService::class)->reservar($duena, $doctor, '2026-11-23', '08:00');

        $this->actingAs($intruso->user)->get(route('paciente.citas.show', $cita))->assertForbidden();
        $this->actingAs($intruso->user)->post(route('citas.cancelar', $cita))->assertForbidden();
        $this->actingAs($duena->user)->get(route('paciente.citas.show', $cita))->assertOk();
    }

    public function test_paciente_reserva_desde_la_web(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-21 10:00'));
        $doctor = Doctor::factory()->create();
        $doctor->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']);
        $paciente = Paciente::factory()->create();

        $this->actingAs($paciente->user)->getJson(route('api.bloques', ['doctor' => $doctor, 'fecha' => '2026-11-23']))
            ->assertOk()->assertJsonCount(4);

        $this->actingAs($paciente->user)->post(route('paciente.reservar.store'), [
            'doctor_id' => $doctor->id, 'fecha' => '2026-11-23', 'hora' => '08:30', 'motivo' => 'Control',
        ])->assertRedirect();

        $this->assertDatabaseHas('citas', ['paciente_id' => $paciente->id, 'hora_inicio' => '08:30:00', 'estado' => 'pendiente']);
    }

    public function test_doctor_atiende_y_queda_en_historial(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-21 10:00'));
        $doctor = Doctor::factory()->create();
        $doctor->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']);
        $paciente = Paciente::factory()->create();
        $cita = app(AgendaService::class)->reservar($paciente, $doctor, '2026-11-23', '08:00');
        $this->travelTo(CarbonImmutable::parse('2026-11-23 08:05'));

        $this->actingAs($doctor->user)->get(route('doctor.atender', $cita))->assertOk();
        $this->actingAs($doctor->user)->post(route('doctor.atender.store', $cita), [
            'motivo' => 'Control', 'diagnostico' => 'Sano', 'presion' => '120/80',
        ])->assertRedirect(route('doctor.dashboard'));

        $this->assertSame('completada', $cita->fresh()->estado->value);
        $this->actingAs($paciente->user)->get(route('paciente.historial'))->assertOk()->assertSee('Sano');
    }

    public function test_registro_crea_paciente_y_valida_cedula(): void
    {
        $this->post(route('registro'), [
            'name' => 'Ana Pérez', 'cedula' => '1710034065', 'telefono' => '0990000000',
            'email' => 'ana@example.com', 'password' => 'secreta123', 'password_confirmation' => 'secreta123',
        ])->assertRedirect(route('paciente.reservar'));
        $this->assertDatabaseHas('pacientes', ['cedula' => '1710034065']);

        auth()->logout();
        $this->post(route('registro'), [
            'name' => 'X', 'cedula' => '1234567890', 'telefono' => '0990000000',
            'email' => 'x@example.com', 'password' => 'secreta123', 'password_confirmation' => 'secreta123',
        ])->assertSessionHasErrors('cedula');
    }
}
