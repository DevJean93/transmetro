<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Linea;
use App\Models\Parqueo;
use App\Services\ReglasTransmetro;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusController extends Controller
{
    public function __construct(private ReglasTransmetro $reglas) {}

    public function index(Request $request)
    {
        $buses = Bus::with(['linea', 'parqueo.estacion', 'piloto'])
            ->when($request->filled('linea'), fn ($q) => $request->linea === 'ninguna'
                ? $q->whereNull('linea_id')
                : $q->where('linea_id', $request->linea))
            ->orderBy('placa')->get();

        return view('buses.index', ['buses' => $buses, 'lineas' => Linea::orderBy('id')->get()]);
    }

    public function create()
    {
        return view('buses.form', $this->opciones(new Bus));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $this->reglas->validarCambioLineaBus(new Bus, $datos['linea_id'] ?? null);
        Bus::create($datos);

        return redirect()->route('buses.index')->with('ok', 'Bus registrado.');
    }

    public function edit(Bus $bus)
    {
        return view('buses.form', $this->opciones($bus));
    }

    public function update(Request $request, Bus $bus)
    {
        $datos = $this->validar($request, $bus);
        $this->reglas->validarCambioLineaBus($bus, isset($datos['linea_id']) ? (int) $datos['linea_id'] : null);
        $bus->update($datos);

        return redirect()->route('buses.index')->with('ok', "Bus {$bus->placa} actualizado.");
    }

    public function destroy(Bus $bus)
    {
        $this->reglas->validarCambioLineaBus($bus, null);
        $bus->delete();

        return redirect()->route('buses.index')->with('ok', 'Bus eliminado.');
    }

    private function opciones(Bus $bus): array
    {
        return [
            'bus' => $bus,
            'lineas' => Linea::withCount(['buses', 'estaciones'])->orderBy('id')->get(),
            'parqueos' => Parqueo::with('estacion')->orderBy('nombre')->get(),
        ];
    }

    private function validar(Request $request, ?Bus $b = null): array
    {
        return $request->validate([
            'placa' => ['required', 'max:10', Rule::unique('buses')->ignore($b)],
            'modelo' => ['required', 'max:50'],
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:300'],
            'linea_id' => ['nullable', 'exists:lineas,id'],
            'parqueo_id' => ['required', 'exists:parqueos,id'], // Req. 7: nunca sin parqueo
        ], ['parqueo_id.required' => 'Todo bus debe tener un parqueo asignado.']);
    }
}
