<?php

namespace App\Http\Controllers;

use App\Models\Municipalidad;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MunicipalidadController extends Controller
{
    public function index()
    {
        return view('municipalidades.index', [
            'municipalidades' => Municipalidad::withCount(['lineas', 'estaciones'])->orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('municipalidades.form', ['municipalidad' => new Municipalidad]);
    }

    public function store(Request $request)
    {
        Municipalidad::create($this->validar($request));

        return redirect()->route('municipalidades.index')->with('ok', 'Municipalidad creada.');
    }

    public function edit(Municipalidad $municipalidad)
    {
        return view('municipalidades.form', compact('municipalidad'));
    }

    public function update(Request $request, Municipalidad $municipalidad)
    {
        $municipalidad->update($this->validar($request, $municipalidad));

        return redirect()->route('municipalidades.index')->with('ok', 'Municipalidad actualizada.');
    }

    public function destroy(Municipalidad $municipalidad)
    {
        try {
            $municipalidad->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar: tiene líneas o estaciones asociadas.');
        }

        return redirect()->route('municipalidades.index')->with('ok', 'Municipalidad eliminada.');
    }

    private function validar(Request $request, ?Municipalidad $m = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'max:100', Rule::unique('municipalidades')->ignore($m)],
            'departamento' => ['required', 'max:100'],
        ]);
    }
}
