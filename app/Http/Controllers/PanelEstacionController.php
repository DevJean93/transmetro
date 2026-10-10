<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Estacion;
use App\Models\Visita;
use App\Services\ReglasTransmetro;
use Illuminate\Http\Request;

/**
 * Req. 16: panel que usa el operador desde la PC de su estación.
 */
class PanelEstacionController extends Controller
{
    public function __construct(private ReglasTransmetro $reglas) {}

    public function inicio(Request $request)
    {
        $user = $request->user();
        if ($user->esAdmin()) {
            return redirect()->route('dashboard');
        }
        abort_unless($user->operador, 403, 'Su usuario no tiene una estación asignada.');

        return redirect()->route('panel.show', $user->operador->estacion_id);
    }

    public function show(Request $request, Estacion $estacion)
    {
        $this->autorizar($request, $estacion);
        $estacion->load(['lineas', 'accesos.guardias', 'alertas' => fn ($q) => $q->where('atendida', false)->latest()]);
        $lineaIds = $estacion->lineas->pluck('id');

        return view('panel.show', [
            'estacion' => $estacion,
            'buses' => Bus::with('linea')->whereIn('linea_id', $lineaIds)->orderBy('linea_id')->orderBy('placa')->get(),
            'visitas' => Visita::with(['bus', 'linea'])->where('estacion_id', $estacion->id)->latest('llegada')->limit(15)->get(),
        ]);
    }

    public function pasajeros(Request $request, Estacion $estacion)
    {
        $this->autorizar($request, $estacion);
        $datos = $request->validate(['pasajeros_espera' => ['required', 'integer', 'min:0', 'max:100000']]);
        $alerta = $this->reglas->actualizarPasajeros($estacion, $datos['pasajeros_espera']);

        return back()->with($alerta ? 'error' : 'ok', $alerta
            ? '⚠ Sobrecupo: se generó una alerta para enviar otro bus.'
            : 'Pasajeros en espera actualizados.');
    }

    public function visita(Request $request, Estacion $estacion)
    {
        $this->autorizar($request, $estacion);
        $datos = $request->validate([
            'bus_id' => ['required', 'exists:buses,id'],
            'pasajeros_abordo' => ['required', 'integer', 'min:0'],
        ]);
        $visita = $this->reglas->registrarVisita(Bus::findOrFail($datos['bus_id']), $estacion, $datos['pasajeros_abordo']);

        return back()->with('ok', $visita->espera_extra_min > 0
            ? "Visita registrada. El bus va por debajo del 25 % de su capacidad: debe esperar {$visita->espera_extra_min} minutos más."
            : 'Visita registrada. El bus puede continuar.');
    }

    private function autorizar(Request $request, Estacion $estacion): void
    {
        $user = $request->user();
        abort_unless($user->esAdmin() || $user->operador?->estacion_id === $estacion->id, 403, 'Solo puede operar su propia estación.');
    }
}
