<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\Linea;
use App\Models\Municipalidad;
use App\Services\ReglasTransmetro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LineaController extends Controller
{
    public function __construct(private ReglasTransmetro $reglas) {}

    public function index()
    {
        $lineas = Linea::with(['municipalidad', 'estaciones'])->withCount('buses')->get();

        return view('lineas.index', [
            'lineas' => $lineas,
            'estados' => $lineas->mapWithKeys(fn ($l) => [$l->id => $this->reglas->estadoBuses($l)]),
        ]);
    }

    public function create()
    {
        return view('lineas.form', ['linea' => new Linea, 'municipalidades' => Municipalidad::orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $linea = Linea::create($this->validar($request));

        return redirect()->route('lineas.show', $linea)->with('ok', 'Línea creada. Ahora agregue sus estaciones en orden.');
    }

    public function show(Linea $linea)
    {
        $linea->load(['estaciones.accesos', 'buses.piloto', 'municipalidad']);

        return view('lineas.show', [
            'linea' => $linea,
            'estado' => $this->reglas->estadoBuses($linea),
            'disponibles' => Estacion::whereNotIn('id', $linea->estaciones->pluck('id'))->orderBy('nombre')->get(),
        ]);
    }

    public function edit(Linea $linea)
    {
        return view('lineas.form', ['linea' => $linea, 'municipalidades' => Municipalidad::orderBy('nombre')->get()]);
    }

    public function update(Request $request, Linea $linea)
    {
        $linea->update($this->validar($request, $linea));

        return redirect()->route('lineas.show', $linea)->with('ok', 'Línea actualizada.');
    }

    public function destroy(Linea $linea)
    {
        // Req. 2: no dejar estaciones huérfanas
        $huerfanas = $linea->estaciones->filter(fn ($e) => $e->lineas()->count() === 1);
        if ($huerfanas->isNotEmpty()) {
            return back()->with('error', 'No se puede eliminar: estas estaciones quedarían sin línea: '.$huerfanas->pluck('nombre')->join(', '));
        }
        $linea->delete();

        return redirect()->route('lineas.index')->with('ok', 'Línea eliminada. Sus buses quedaron sin asignar.');
    }

    /** Agrega una estación en la posición indicada y desplaza las siguientes (Req. 1). */
    public function agregarEstacion(Request $request, Linea $linea)
    {
        $max = $linea->estaciones()->count() + 1;
        $datos = $request->validate([
            'estacion_id' => ['required', 'exists:estaciones,id', Rule::unique('linea_estacion')->where('linea_id', $linea->id)],
            'orden' => ['required', 'integer', 'min:1', "max:{$max}"],
            'distancia_siguiente_km' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        DB::transaction(function () use ($linea, $datos) {
            $this->desplazar($linea, $datos['orden'], +1);
            $linea->estaciones()->attach($datos['estacion_id'], [
                'orden' => $datos['orden'],
                'distancia_siguiente_km' => $datos['distancia_siguiente_km'],
            ]);
        });

        return back()->with('ok', 'Estación agregada a la línea.');
    }

    public function actualizarEstacion(Request $request, Linea $linea, Estacion $estacion)
    {
        $datos = $request->validate(['distancia_siguiente_km' => ['nullable', 'numeric', 'min:0', 'max:999']]);
        $linea->estaciones()->updateExistingPivot($estacion->id, $datos);

        return back()->with('ok', "Distancia desde {$estacion->nombre} actualizada.");
    }

    /** Intercambia la estación con la anterior o la siguiente del recorrido. */
    public function moverEstacion(Linea $linea, Estacion $estacion, string $direccion)
    {
        $actual = $linea->estaciones()->whereKey($estacion->id)->firstOrFail()->pivot->orden;
        $destino = $direccion === 'arriba' ? $actual - 1 : $actual + 1;
        $otra = DB::table('linea_estacion')->where('linea_id', $linea->id)->where('orden', $destino)->first();
        if (! $otra) {
            return back();
        }

        DB::transaction(function () use ($linea, $estacion, $otra, $actual, $destino) {
            $tabla = DB::table('linea_estacion')->where('linea_id', $linea->id);
            (clone $tabla)->where('estacion_id', $estacion->id)->update(['orden' => 0]);
            (clone $tabla)->where('estacion_id', $otra->estacion_id)->update(['orden' => $actual]);
            (clone $tabla)->where('estacion_id', $estacion->id)->update(['orden' => $destino]);
        });

        return back();
    }

    public function quitarEstacion(Linea $linea, Estacion $estacion)
    {
        if ($estacion->lineas()->count() === 1) {
            throw ValidationException::withMessages([
                'estacion' => "{$estacion->nombre} solo pertenece a esta línea y cada estación debe pertenecer al menos a una (Req. 2).",
            ]);
        }
        $this->reglas->validarQuitarEstacion($linea);

        $orden = $linea->estaciones()->whereKey($estacion->id)->firstOrFail()->pivot->orden;
        DB::transaction(function () use ($linea, $estacion, $orden) {
            $linea->estaciones()->detach($estacion->id);
            $this->desplazar($linea, $orden, -1);
        });

        return back()->with('ok', "{$estacion->nombre} se quitó de la línea.");
    }

    /** Mueve en ±1 el orden de las estaciones desde $desde, en dos pasos para no chocar con unique(linea_id, orden). */
    private function desplazar(Linea $linea, int $desde, int $delta): void
    {
        $filas = DB::table('linea_estacion')->where('linea_id', $linea->id)->where('orden', '>=', $desde);
        (clone $filas)->update(['orden' => DB::raw('orden + 10000')]);
        DB::table('linea_estacion')->where('linea_id', $linea->id)->where('orden', '>=', 10000)
            ->update(['orden' => DB::raw('orden - 10000 + ('.$delta.')')]);
    }

    private function validar(Request $request, ?Linea $l = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'max:50', Rule::unique('lineas')->ignore($l)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'municipalidad_id' => ['required', 'exists:municipalidades,id'],
        ]);
    }
}
