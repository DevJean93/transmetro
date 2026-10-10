<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Req. 11: cada línea y estación pertenece a una municipalidad
        Schema::create('municipalidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('departamento', 100)->default('Guatemala');
            $table->timestamps();
        });

        Schema::create('lineas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('color', 7)->default('#0d6efd');
            $table->foreignId('municipalidad_id')->constrained('municipalidades')->restrictOnDelete();
            $table->timestamps();
        });

        // Req. 14: capacidad y pasajeros en espera para generar alertas
        Schema::create('estaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('direccion', 200);
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->unsignedInteger('capacidad');
            $table->unsignedInteger('pasajeros_espera')->default(0);
            $table->foreignId('municipalidad_id')->constrained('municipalidades')->restrictOnDelete();
            $table->timestamps();
        });

        // Req. 1, 2, 12: orden de estaciones en la línea y distancia a la siguiente
        Schema::create('linea_estacion', function (Blueprint $table) {
            $table->foreignId('linea_id')->constrained('lineas')->cascadeOnDelete();
            $table->foreignId('estacion_id')->constrained('estaciones')->restrictOnDelete();
            $table->unsignedInteger('orden');
            $table->decimal('distancia_siguiente_km', 6, 2)->nullable();
            $table->primary(['linea_id', 'estacion_id']);
            $table->unique(['linea_id', 'orden']);
        });

        // Req. 3: un acceso pertenece a una sola estación
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('estaciones')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->timestamps();
        });

        // Req. 10: al menos un guardia por acceso
        Schema::create('guardias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('dpi', 13)->unique();
            $table->string('telefono', 15);
            $table->foreignId('acceso_id')->constrained('accesos')->cascadeOnDelete();
            $table->timestamps();
        });

        // Req. 6: algunas estaciones tienen parqueos
        Schema::create('parqueos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->unsignedInteger('capacidad');
            $table->foreignId('estacion_id')->constrained('estaciones')->restrictOnDelete();
            $table->timestamps();
        });

        // Req. 4, 6, 7: bus en máximo una línea; siempre con parqueo
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('modelo', 50);
            $table->unsignedInteger('capacidad_maxima');
            $table->foreignId('linea_id')->nullable()->constrained('lineas')->nullOnDelete();
            $table->foreignId('parqueo_id')->constrained('parqueos')->restrictOnDelete();
            $table->timestamps();
        });

        // Req. 9: datos de residencia y comunicación del piloto
        Schema::create('pilotos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('dpi', 13)->unique();
            $table->string('direccion', 200);
            $table->string('municipio_residencia', 100);
            $table->string('telefono', 15);
            $table->string('correo', 100)->nullable();
            $table->foreignId('bus_id')->nullable()->unique()->constrained('buses')->nullOnDelete();
            $table->timestamps();
        });

        // Req. 9: historial educativo del piloto
        Schema::create('historial_educativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piloto_id')->constrained('pilotos')->cascadeOnDelete();
            $table->string('nivel', 50);
            $table->string('institucion', 150);
            $table->string('titulo', 150);
            $table->unsignedSmallInteger('anio');
            $table->timestamps();
        });

        // Req. 16: un operador controla la PC de una estación
        Schema::create('operadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('estacion_id')->unique()->constrained('estaciones')->restrictOnDelete();
            $table->string('telefono', 15);
            $table->timestamps();
        });

        // Req. 1, 15: visitas de cada unidad durante el recorrido
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->cascadeOnDelete();
            $table->foreignId('linea_id')->constrained('lineas')->cascadeOnDelete();
            $table->foreignId('estacion_id')->constrained('estaciones')->cascadeOnDelete();
            $table->dateTime('llegada');
            $table->unsignedInteger('pasajeros_abordo');
            $table->unsignedInteger('espera_extra_min')->default(0);
            $table->timestamps();
        });

        // Req. 14: alerta para que otro bus llegue
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('estaciones')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->string('mensaje', 255);
            $table->boolean('atendida')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['alertas', 'visitas', 'operadores', 'historial_educativo', 'pilotos', 'buses',
            'parqueos', 'guardias', 'accesos', 'linea_estacion', 'estaciones', 'lineas', 'municipalidades'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
