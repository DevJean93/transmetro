<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\Estacion;
use App\Models\Guardia;
use App\Models\Municipalidad;
use App\Models\Parqueo;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EstacionController extends Controller
{
    public function index()
    {
        return view('estaciones.index', [
            'estaciones' => Estacion::with(['municipalidad', 'lineas', 'parqueos'])->withCount(['accesos', 'guardias'])->orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('estaciones.form', ['estacion' => new Estacion, 'municipalidades' => Municipalidad::orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $estacion = Estacion::create($this->validar($request));

        return redirect()->route('estaciones.show', $estacion)
            ->with('ok', 'Estación creada. Agregue sus accesos (con guardia) y asígnela a una línea.');
    }

    public function show(Estacion $estacion)
    {
        $estacion->load(['municipalidad', 'lineas', 'accesos.guardias', 'parqueos.buses', 'operador.user', 'alertas' => fn ($q) => $q->latest()->limit(10)]);

        return view('estaciones.show', compact('estacion'));
    }

    public function edit(Estacion $estacion)
    {
        return view('estaciones.form', ['estacion' => $estacion, 'municipalidades' => Municipalidad::orderBy('nombre')->get()]);
    }

    public function update(Request $request, Estacion $estacion)
    {
        $estacion->update($this->validar($request, $estacion));

        return redirect()->route('estaciones.show', $estacion)->with('ok', 'Estación actualizada.');
    }

    public function destroy(Estacion $estacion)
    {
        try {
            $estacion->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar: la estación está en una línea o tiene parqueos u operador asignados.');
        }

        return redirect()->route('estaciones.index')->with('ok', 'Estación eliminada.');
    }

    /** Req. 3 y 10: un acceso nuevo siempre se crea con su primer guardia. */
    public function agregarAcceso(Request $request, Estacion $estacion)
    {
        $datos = $request->validate([
            'acceso' => ['required', 'max:100'],
            'guardia_nombre' => ['required', 'max:100'],
            'guardia_dpi' => ['required', 'digits:13', 'unique:guardias,dpi'],
            'guardia_telefono' => ['required', 'max:15'],
        ]);

        DB::transaction(function () use ($estacion, $datos) {
            $acceso = $estacion->accesos()->create(['nombre' => $datos['acceso']]);
            $acceso->guardias()->create([
                'nombre' => $datos['guardia_nombre'], 'dpi' => $datos['guardia_dpi'], 'telefono' => $datos['guardia_telefono'],
            ]);
        });

        return back()->with('ok', 'Acceso y guardia registrados.');
    }

    public function quitarAcceso(Acceso $acceso)
    {
        $acceso->delete();

        return back()->with('ok', 'Acceso eliminado.');
    }

    public function agregarGuardia(Request $request, Acceso $acceso)
    {
        $acceso->guardias()->create($request->validate([
            'nombre' => ['required', 'max:100'],
            'dpi' => ['required', 'digits:13', 'unique:guardias,dpi'],
            'telefono' => ['required', 'max:15'],
        ]));

        return back()->with('ok', 'Guardia asignado al acceso.');
    }

    public function quitarGuardia(Guardia $guardia)
    {
        if ($guardia->acceso->guardias()->count() === 1) {
            return back()->with('error', 'Cada acceso debe tener al menos un guardia (Req. 10). Asigne otro antes de quitar este.');
        }
        $guardia->delete();

        return back()->with('ok', 'Guardia retirado.');
    }

    public function agregarParqueo(Request $request, Estacion $estacion)
    {
        $estacion->parqueos()->create($request->validate([
            'nombre' => ['required', 'max:100'],
            'capacidad' => ['required', 'integer', 'min:1'],
        ]));

        return back()->with('ok', 'Parqueo agregado.');
    }

    public function quitarParqueo(Parqueo $parqueo)
    {
        if ($parqueo->buses()->exists()) {
            return back()->with('error', 'Hay buses en este parqueo. Cámbielos de parqueo primero (Req. 7: un bus no puede quedar sin parqueo).');
        }
        $parqueo->delete();

        return back()->with('ok', 'Parqueo eliminado.');
    }

    private function validar(Request $request, ?Estacion $e = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'max:100', Rule::unique('estaciones')->ignore($e)],
            'direccion' => ['required', 'max:200'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'capacidad' => ['required', 'integer', 'min:1'],
            'municipalidad_id' => ['required', 'exists:municipalidades,id'],
        ]);
    }
}
