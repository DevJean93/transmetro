<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Estacion;
use App\Models\Linea;
use App\Models\Municipalidad;
use App\Models\Parqueo;
use App\Models\Piloto;
use App\Models\User;
use App\Services\ReglasTransmetro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Datos de demostración. No usa Faker para poder correr en producción (composer --no-dev).
 * Es idempotente: si ya hay municipalidades no hace nada, así puede ejecutarse en cada deploy.
 */
class DatabaseSeeder extends Seeder
{
    public function run(ReglasTransmetro $reglas): void
    {
        if (Municipalidad::exists()) {
            $this->command?->info('La base ya tiene datos; se omite el seeder.');

            return;
        }

        $muni = collect([
            'Guatemala' => Municipalidad::create(['nombre' => 'Guatemala']),
            'Mixco' => Municipalidad::create(['nombre' => 'Mixco']),
            'Villa Nueva' => Municipalidad::create(['nombre' => 'Villa Nueva']),
            'San Miguel Petapa' => Municipalidad::create(['nombre' => 'San Miguel Petapa']),
        ]);

        // nombre => [dirección, lat, lng, capacidad, municipalidad, accesos]
        $datosEstaciones = [
            'Plaza Barrios' => ['10a. Av. y 18 Calle, Zona 1', 14.6345, -90.5155, 300, 'Guatemala', ['Norte', 'Sur', 'Mercado']],
            'Plaza Municipal' => ['21 Calle y 6a. Av., Zona 1', 14.6420, -90.5130, 200, 'Guatemala', ['Principal', 'Palacio']],
            'Tipografía' => ['18 Calle y 2a. Av., Zona 1', 14.6380, -90.5200, 180, 'Guatemala', ['Principal']],
            'Bolívar' => ['Av. Bolívar y 29 Calle, Zona 8', 14.6260, -90.5240, 160, 'Guatemala', ['Oriente', 'Poniente']],
            'El Trébol' => ['Calzada Roosevelt y Av. Bolívar, Zona 8', 14.6178, -90.5300, 350, 'Guatemala', ['Norte', 'Sur', 'Roosevelt']],
            'Cenma' => ['Calzada Atanasio Tzul, Zona 12', 14.6080, -90.5480, 220, 'Guatemala', ['Mercado', 'Calzada']],
            'Hangares' => ['Av. Hincapié, Zona 13', 14.5880, -90.5300, 150, 'Guatemala', ['Principal']],
            'Usac' => ['Av. Petapa, Zona 12', 14.5870, -90.5530, 260, 'Guatemala', ['Periférico', 'Campus']],
            'Plaza España' => ['Av. La Reforma y 7a. Calle, Zona 9', 14.6070, -90.5150, 170, 'Guatemala', ['Reforma']],
            'Montúfar' => ['Av. La Reforma y 12 Calle, Zona 9', 14.5990, -90.5100, 150, 'Guatemala', ['Reforma', 'Montúfar']],
            'Centra Sur' => ['Km 11.5 Carretera al Pacífico', 14.5560, -90.5560, 400, 'Villa Nueva', ['Andén A', 'Andén B', 'Comercial']],
            'Roosevelt Mixco' => ['Calzada Roosevelt Km 13, Mixco', 14.6300, -90.5900, 200, 'Mixco', ['Principal', 'Pasarela']],
            'El Naranjo' => ['Blvd. El Naranjo, Mixco', 14.6500, -90.5500, 180, 'Mixco', ['Principal']],
            'Villa Hermosa' => ['Av. Petapa y 1a. Calle, San Miguel Petapa', 14.5300, -90.5500, 190, 'San Miguel Petapa', ['Principal', 'Mercado']],
        ];

        $nombresGuardias = ['Carlos Ajú', 'María Coc', 'José Pérez', 'Ana López', 'Luis Xicay', 'Rosa Tzoc', 'Pedro Gómez',
            'Lucía Morales', 'Juan Cux', 'Elena Batz', 'Mario Chávez', 'Sofía Quiej', 'Diego Ramírez', 'Carmen Yax'];
        $dpi = 2000000000000;
        $g = 0;

        $estaciones = [];
        foreach ($datosEstaciones as $nombre => [$dir, $lat, $lng, $cap, $m, $accesos]) {
            $e = Estacion::create([
                'nombre' => $nombre, 'direccion' => $dir, 'latitud' => $lat, 'longitud' => $lng,
                'capacidad' => $cap, 'pasajeros_espera' => intdiv($cap, 3), 'municipalidad_id' => $muni[$m]->id,
            ]);
            foreach ($accesos as $acc) {
                $acceso = $e->accesos()->create(['nombre' => "Acceso {$acc}"]);
                // Req. 10: un guardia por acceso. El acceso "Montúfar" se deja sin guardia a propósito
                // para que el reporte de accesos muestre cómo detecta el incumplimiento.
                if ($nombre === 'Montúfar' && $acc === 'Montúfar') {
                    continue;
                }
                $acceso->guardias()->create([
                    'nombre' => $nombresGuardias[$g++ % count($nombresGuardias)],
                    'dpi' => (string) $dpi++,
                    'telefono' => '5'.str_pad((string) (1000000 + $g * 7919 % 9000000), 7, '0', STR_PAD_LEFT),
                ]);
            }
            $estaciones[$nombre] = $e;
        }

        // Req. 6: algunas estaciones tienen parqueo
        $parqueos = collect(['Centra Sur' => 30, 'Plaza Barrios' => 12, 'El Trébol' => 15, 'El Naranjo' => 10, 'Villa Hermosa' => 10])
            ->map(fn ($cap, $est) => Parqueo::create(['nombre' => "Parqueo {$est}", 'capacidad' => $cap, 'estacion_id' => $estaciones[$est]->id]))
            ->values();

        // nombre => [color, municipalidad, [[estación, km a la siguiente], ...], buses]
        $datosLineas = [
            'Línea 1' => ['#e91e63', 'Guatemala', [['Plaza Barrios', 1.2], ['Bolívar', 1.5], ['El Trébol', 2.0], ['Cenma', 5.5], ['Centra Sur', null]], 7],
            'Línea 2' => ['#ff9800', 'Guatemala', [['Plaza Municipal', 0.8], ['Tipografía', 0.9], ['Plaza Barrios', 3.2], ['Plaza España', 1.4], ['Montúfar', null]], 6],
            'Línea 12' => ['#4caf50', 'Guatemala', [['El Trébol', 2.3], ['Hangares', 1.6], ['Usac', 4.0], ['Centra Sur', null]], 5],
            'Línea 13' => ['#2196f3', 'Mixco', [['Tipografía', 6.0], ['Roosevelt Mixco', 3.5], ['El Naranjo', null]], 4],
            'Línea 18' => ['#9c27b0', 'Villa Nueva', [['Centra Sur', 4.5], ['Villa Hermosa', 5.0], ['Usac', null]], 4],
        ];

        $modelos = ['Volvo B340M', 'Mercedes-Benz O500', 'Scania K310', 'Marcopolo Gran Viale'];
        $nombresPilotos = ['Roberto Santizo', 'Julio Monterroso', 'Edgar Pocasangre', 'Byron Estrada', 'Walter Ixcot',
            'Hugo Barrios', 'Óscar Velásquez', 'Fernando Cabrera', 'Sergio Alvarado', 'Mynor Herrera', 'Kevin Son',
            'Erick Tobar', 'Josué Mazariegos', 'Rony Cifuentes', 'Gustavo Recinos', 'Marvin Ordóñez', 'Alex Choc',
            'Selvin García', 'Henry Ajcip', 'Danilo Reyes', 'Wilson Tum', 'Rudy Hernández', 'Esteban Catalán',
            'Allan Juárez', 'Nery Paz', 'Iván Coronado'];
        $municipios = ['Guatemala', 'Mixco', 'Villa Nueva', 'San Miguel Petapa', 'Chinautla', 'Santa Catarina Pinula'];
        $placa = 100;
        $p = 0;

        foreach ($datosLineas as $nombre => [$color, $m, $paradas, $nBuses]) {
            $linea = Linea::create(['nombre' => $nombre, 'color' => $color, 'municipalidad_id' => $muni[$m]->id]);
            foreach ($paradas as $orden => [$est, $km]) {
                $linea->estaciones()->attach($estaciones[$est]->id, ['orden' => $orden + 1, 'distancia_siguiente_km' => $km]);
            }

            // Req. 5: entre n y 2n buses por línea
            for ($i = 0; $i < $nBuses; $i++) {
                $bus = Bus::create([
                    'placa' => 'U'.$placa++.'BCD',
                    'modelo' => $modelos[$i % count($modelos)],
                    'capacidad_maxima' => [80, 120, 160][$i % 3],
                    'linea_id' => $linea->id,
                    'parqueo_id' => $parqueos[$placa % $parqueos->count()]->id,
                ]);
                $this->crearPiloto($bus, $nombresPilotos[$p], $municipios[$p % count($municipios)], $p);
                $p++;
            }
        }

        // Req. 4: buses sin línea asignada (reserva)
        foreach (range(1, 3) as $i) {
            Bus::create([
                'placa' => 'U'.$placa++.'BCD', 'modelo' => 'Volvo B340M', 'capacidad_maxima' => 120,
                'linea_id' => null, 'parqueo_id' => $parqueos[0]->id,
            ]);
        }

        // Usuarios: un administrador y un operador por estación (Req. 16)
        User::create(['name' => 'Administrador', 'email' => 'admin@transmetro.gt', 'password' => 'admin123', 'rol' => 'admin']);
        foreach ($estaciones as $nombre => $e) {
            $email = $nombre === 'Plaza Barrios' ? 'operador@transmetro.gt' : 'op.'.Str::slug($nombre).'@transmetro.gt';
            $u = User::create(['name' => "Operador {$nombre}", 'email' => $email, 'password' => 'operador123', 'rol' => 'operador']);
            $u->operador()->create(['estacion_id' => $e->id, 'telefono' => '2'.str_pad((string) (2200000 + $e->id), 7, '0')]);
        }

        // Escenario de demostración: una estación con sobrecupo (Req. 14) y algunas visitas (Req. 15)
        $reglas->actualizarPasajeros($estaciones['El Trébol'], 560);
        foreach (Bus::whereNotNull('linea_id')->with('linea.estaciones')->take(10)->get() as $i => $bus) {
            $est = $bus->linea->estaciones[$i % $bus->linea->estaciones->count()];
            $reglas->registrarVisita($bus, $est, (int) ($bus->capacidad_maxima * [0.1, 0.5, 0.9, 0.2][$i % 4]));
        }
    }

    private function crearPiloto(Bus $bus, string $nombre, string $municipio, int $i): void
    {
        $piloto = Piloto::create([
            'nombre' => $nombre,
            'dpi' => (string) (3000000000000 + $i),
            'direccion' => ($i % 20 + 1).'a. Calle '.($i * 3 % 15 + 1).'-'.($i * 7 % 90 + 10).', Zona '.($i % 18 + 1),
            'municipio_residencia' => $municipio,
            'telefono' => '4'.str_pad((string) (5500000 + $i * 131), 7, '0'),
            'correo' => Str::slug($nombre, '.').'@correo.gt',
            'bus_id' => $bus->id,
        ]);
        $piloto->historial()->create(['nivel' => 'Diversificado', 'institucion' => 'INCA', 'titulo' => 'Perito Contador', 'anio' => 2005 + $i % 12]);
        if ($i % 3 === 0) {
            $piloto->historial()->create(['nivel' => 'Técnico', 'institucion' => 'INTECAP', 'titulo' => 'Conducción de transporte pesado', 'anio' => 2018 + $i % 5]);
        }
        if ($i % 5 === 0) {
            $piloto->historial()->create(['nivel' => 'Universitario', 'institucion' => 'USAC', 'titulo' => 'Pensum cerrado Administración', 'anio' => 2015 + $i % 7]);
        }
    }
}
