<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Estacion;
use App\Models\Linea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglasTransmetroTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('rol', 'admin')->first();
    }

    private function datosBus(Bus $bus, ?int $lineaId): array
    {
        return ['placa' => $bus->placa, 'modelo' => $bus->modelo, 'capacidad_maxima' => $bus->capacidad_maxima,
            'parqueo_id' => $bus->parqueo_id, 'linea_id' => $lineaId];
    }

    public function test_regla_5_no_permite_superar_el_doble_de_estaciones(): void
    {
        $linea = Linea::where('nombre', 'Línea 18')->first(); // 3 estaciones, 4 buses
        $reserva = Bus::whereNull('linea_id')->get();

        $this->actingAs($this->admin());
        $this->put(route('buses.update', $reserva[0]), $this->datosBus($reserva[0], $linea->id))->assertSessionHasNoErrors();
        $this->put(route('buses.update', $reserva[1]), $this->datosBus($reserva[1], $linea->id))->assertSessionHasNoErrors();
        $this->put(route('buses.update', $reserva[2]), $this->datosBus($reserva[2], $linea->id))->assertSessionHasErrors('linea_id');

        $this->assertSame(6, $linea->buses()->count());
    }

    public function test_regla_5_no_permite_dejar_la_linea_con_menos_buses_que_estaciones(): void
    {
        $linea = Linea::where('nombre', 'Línea 13')->first(); // 3 estaciones, 4 buses
        [$a, $b] = $linea->buses()->take(2)->get();

        $this->actingAs($this->admin());
        $this->put(route('buses.update', $a), $this->datosBus($a, null))->assertSessionHasNoErrors();
        $this->put(route('buses.update', $b), $this->datosBus($b, null))->assertSessionHasErrors('linea_id');
    }

    public function test_regla_7_un_bus_no_puede_quedar_sin_parqueo(): void
    {
        $bus = Bus::first();
        $this->actingAs($this->admin())
            ->put(route('buses.update', $bus), ['parqueo_id' => ''] + $this->datosBus($bus, $bus->linea_id))
            ->assertSessionHasErrors('parqueo_id');
    }

    public function test_regla_14_sobrecupo_genera_alerta(): void
    {
        $estacion = Estacion::where('nombre', 'Plaza Barrios')->first(); // capacidad 300
        $operador = User::where('email', 'operador@transmetro.gt')->first();

        $this->actingAs($operador)->post(route('panel.pasajeros', $estacion), ['pasajeros_espera' => 449]);
        $this->assertSame(0, $estacion->alertas()->count());

        $this->post(route('panel.pasajeros', $estacion), ['pasajeros_espera' => 450]);
        $this->assertSame(1, $estacion->alertas()->where('atendida', false)->count());
    }

    public function test_regla_15_bus_bajo_25_por_ciento_espera_5_minutos(): void
    {
        $estacion = Estacion::where('nombre', 'Plaza Barrios')->first();
        $bus = Bus::where('placa', 'U100BCD')->first(); // Línea 1, capacidad 80
        $operador = User::where('email', 'operador@transmetro.gt')->first();

        $this->actingAs($operador)->post(route('panel.visita', $estacion), ['bus_id' => $bus->id, 'pasajeros_abordo' => 19]);
        $this->post(route('panel.visita', $estacion), ['bus_id' => $bus->id, 'pasajeros_abordo' => 20]);

        $this->assertSame([5, 0], $bus->visitas()->latest('id')->take(2)->pluck('espera_extra_min')->reverse()->values()->all());
    }

    public function test_operador_solo_accede_a_su_estacion(): void
    {
        $operador = User::where('email', 'operador@transmetro.gt')->first();
        $otra = Estacion::where('nombre', 'El Trébol')->first();

        $this->actingAs($operador)->get('/')->assertRedirect(route('panel.show', $operador->operador->estacion_id));
        $this->get(route('panel.show', $otra))->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
    }
}
