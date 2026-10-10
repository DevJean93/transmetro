<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\HistorialEducativo;
use App\Models\Piloto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PilotoController extends Controller
{
    public function index()
    {
        return view('pilotos.index', ['pilotos' => Piloto::with('bus.linea')->withCount('historial')->orderBy('nombre')->get()]);
    }

    public function create()
    {
        return view('pilotos.form', ['piloto' => new Piloto, 'buses' => $this->busesLibres()]);
    }

    public function store(Request $request)
    {
        $piloto = Piloto::create($this->validar($request));

        return redirect()->route('pilotos.show', $piloto)->with('ok', 'Piloto registrado. Agregue su historial educativo.');
    }

    public function show(Piloto $piloto)
    {
        $piloto->load(['historial', 'bus.linea']);

        return view('pilotos.show', compact('piloto'));
    }

    public function edit(Piloto $piloto)
    {
        return view('pilotos.form', ['piloto' => $piloto, 'buses' => $this->busesLibres($piloto)]);
    }

    public function update(Request $request, Piloto $piloto)
    {
        $piloto->update($this->validar($request, $piloto));

        return redirect()->route('pilotos.show', $piloto)->with('ok', 'Piloto actualizado.');
    }

    public function destroy(Piloto $piloto)
    {
        $piloto->delete();

        return redirect()->route('pilotos.index')->with('ok', 'Piloto eliminado.');
    }

    public function agregarHistorial(Request $request, Piloto $piloto)
    {
        $piloto->historial()->create($request->validate([
            'nivel' => ['required', 'max:50'],
            'institucion' => ['required', 'max:150'],
            'titulo' => ['required', 'max:150'],
            'anio' => ['required', 'integer', 'between:1950,'.date('Y')],
        ]));

        return back()->with('ok', 'Registro educativo agregado.');
    }

    public function quitarHistorial(HistorialEducativo $historial)
    {
        $historial->delete();

        return back()->with('ok', 'Registro educativo eliminado.');
    }

    private function busesLibres(?Piloto $piloto = null)
    {
        return Bus::with('linea')
            ->whereDoesntHave('piloto', fn ($q) => $q->when($piloto, fn ($q) => $q->where('id', '!=', $piloto->id)))
            ->orderBy('placa')->get();
    }

    private function validar(Request $request, ?Piloto $p = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'max:100'],
            'dpi' => ['required', 'digits:13', Rule::unique('pilotos')->ignore($p)],
            'direccion' => ['required', 'max:200'],
            'municipio_residencia' => ['required', 'max:100'],
            'telefono' => ['required', 'max:15'],
            'correo' => ['nullable', 'email', 'max:100'],
            'bus_id' => ['nullable', 'exists:buses,id', Rule::unique('pilotos')->ignore($p)],
        ]);
    }
}
