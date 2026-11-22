<?php

namespace Tests\Feature;

use App\Enums\EstadoCita;
use App\Models\Doctor;
use App\Models\Paciente;
use App\Services\AgendaService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private CarbonImmutable $lunes;

    protected function setUp(): void
    {
        parent::setUp();
        // Sábado 21/11/2026 a las 10:00; el próximo lunes es el 23.
        $this->travelTo(CarbonImmutable::parse('2026-11-21 10:00'));
        $this->lunes = CarbonImmutable::parse('2026-11-23');
        $this->doctor = Doctor::factory()->create(['duracion_cita' => 30]);
        $this->doctor->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']);
    }

    private function agenda(): AgendaService
    {
        return app(AgendaService::class);
    }

    public function test_genera_bloques_segun_duracion_y_horario(): void
    {
        $bloques = $this->agenda()->bloques($this->doctor, $this->lunes);

        $this->assertSame(['08:00', '08:30', '09:00', '09:30'], array_column($bloques, 'inicio'));
        $this->assertTrue(collect($bloques)->every(fn ($b) => $b['disponible']));
    }

    public function test_no_hay_bloques_en_dias_sin_horario_ni_en_el_pasado(): void
    {
        $this->assertSame([], $this->agenda()->bloques($this->doctor, $this->lunes->addDay()));
        $this->assertSame([], $this->agenda()->bloques($this->doctor, $this->lunes->subWeek()));
    }

    public function test_reservar_ocupa_el_bloque(): void
    {
        $paciente = Paciente::factory()->create();
        $cita = $this->agenda()->reservar($paciente, $this->doctor, $this->lunes->toDateString(), '08:30');

        $this->assertSame(EstadoCita::Pendiente, $cita->estado);
        $this->assertSame('09:00', $cita->horaFin());
        $libres = collect($this->agenda()->bloques($this->doctor, $this->lunes))->where('disponible', true)->pluck('inicio')->all();
        $this->assertSame(['08:00', '09:00', '09:30'], $libres);
    }

    public function test_no_permite_doble_reserva_del_mismo_bloque(): void
    {
        $this->agenda()->reservar(Paciente::factory()->create(), $this->doctor, $this->lunes->toDateString(), '08:00');

        $this->expectException(ValidationException::class);
        $this->agenda()->reservar(Paciente::factory()->create(), $this->doctor, $this->lunes->toDateString(), '08:00');
    }

    public function test_rechaza_horas_fuera_de_la_grilla(): void
    {
        $this->expectException(ValidationException::class);
        $this->agenda()->reservar(Paciente::factory()->create(), $this->doctor, $this->lunes->toDateString(), '08:15');
    }

    public function test_un_paciente_no_puede_tener_dos_citas_a_la_misma_hora(): void
    {
        $paciente = Paciente::factory()->create();
        $otro = Doctor::factory()->create(['duracion_cita' => 30]);
        $otro->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']);

        $this->agenda()->reservar($paciente, $this->doctor, $this->lunes->toDateString(), '08:00');

        $this->expectException(ValidationException::class);
        $this->agenda()->reservar($paciente, $otro, $this->lunes->toDateString(), '08:00');
    }

    public function test_al_cancelar_el_bloque_se_puede_volver_a_reservar(): void
    {
        $paciente = Paciente::factory()->create();
        $cita = $this->agenda()->reservar($paciente, $this->doctor, $this->lunes->toDateString(), '09:00');
        $this->agenda()->cancelar($cita, $paciente->user);

        $this->assertNull($cita->fresh()->bloque_activo);
        $nueva = $this->agenda()->reservar(Paciente::factory()->create(), $this->doctor, $this->lunes->toDateString(), '09:00');
        $this->assertTrue($nueva->exists);
    }

    public function test_paciente_no_cancela_con_menos_de_las_horas_minimas(): void
    {
        $paciente = Paciente::factory()->create();
        $cita = $this->agenda()->reservar($paciente, $this->doctor, $this->lunes->toDateString(), '08:00');
        $this->travelTo($this->lunes->setTime(7, 0));

        $this->assertFalse($this->agenda()->puedeCancelar($cita, $paciente->user));
        $this->assertTrue($this->agenda()->puedeCancelar($cita, $this->doctor->user));
    }

    public function test_los_bloques_de_hoy_respetan_la_anticipacion_minima(): void
    {
        $this->travelTo($this->lunes->setTime(8, 40));
        $libres = collect($this->agenda()->bloques($this->doctor, $this->lunes))->where('disponible', true)->pluck('inicio')->all();
        // 09:00 queda a 20 min (< 30), así que el primero libre es 09:30.
        $this->assertSame(['09:30'], $libres);
    }

    public function test_horarios_que_se_cruzan_son_rechazados(): void
    {
        $this->actingAs($this->doctor->user)
            ->put(route('doctor.horario.update'), ['horarios' => [
                ['dia_semana' => 2, 'hora_inicio' => '08:00', 'hora_fin' => '12:00'],
                ['dia_semana' => 2, 'hora_inicio' => '11:00', 'hora_fin' => '14:00'],
            ]])
            ->assertSessionHasErrors('horarios.1.hora_inicio');
    }
}
