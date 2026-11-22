<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('descripcion')->nullable();
            $table->string('color', 7)->default('#0F6B5C');
            $table->timestamps();
        });

        Schema::create('consultorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('ubicacion', 120);
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('doctores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->foreignId('consultorio_id')->nullable()->constrained('consultorios')->nullOnDelete();
            $table->string('licencia_medica', 40)->unique();
            $table->text('biografia')->nullable();
            $table->unsignedSmallInteger('duracion_cita')->default(30);
            $table->decimal('tarifa', 8, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('cedula', 13)->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 12)->nullable();
            $table->string('direccion')->nullable();
            $table->string('grupo_sanguineo', 4)->nullable();
            $table->text('alergias')->nullable();
            $table->string('contacto_emergencia')->nullable();
            $table->timestamps();
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctores')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana'); // ISO: 1 = lunes … 7 = domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
            $table->index(['doctor_id', 'dia_semana']);
        });

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctores')->cascadeOnDelete();
            $table->foreignId('consultorio_id')->nullable()->constrained('consultorios')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('motivo')->nullable();
            $table->string('estado', 20)->default('pendiente')->index();
            // 1 mientras la cita ocupa el bloque; NULL al cancelarse.
            // Los NULL no chocan en el índice único, así un bloque cancelado se puede volver a reservar.
            $table->boolean('bloque_activo')->nullable()->default(true);
            $table->string('cancelada_por', 20)->nullable();
            $table->string('motivo_cancelacion')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'fecha', 'hora_inicio', 'bloque_activo'], 'citas_bloque_unico');
            $table->index(['paciente_id', 'fecha']);
        });

        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->nullable()->unique()->constrained('citas')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctores')->cascadeOnDelete();
            $table->dateTime('fecha');
            $table->string('motivo');
            $table->text('sintomas')->nullable();
            $table->text('diagnostico');
            $table->text('tratamiento')->nullable();
            $table->text('receta')->nullable();
            $table->string('presion', 10)->nullable();
            $table->unsignedSmallInteger('frecuencia_cardiaca')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->decimal('peso', 5, 1)->nullable();
            $table->unsignedSmallInteger('talla')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['paciente_id', 'fecha']);
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('monto', 10, 2);
            $table->string('metodo', 20);
            $table->string('estado', 20)->default('pagado');
            $table->string('referencia', 60)->nullable();
            $table->date('fecha_pago');
            $table->timestamps();
        });

        Schema::create('ajustes', function (Blueprint $table) {
            $table->string('clave', 60)->primary();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['ajustes', 'pagos', 'consultas', 'citas', 'horarios', 'pacientes', 'doctores', 'consultorios', 'especialidades'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
