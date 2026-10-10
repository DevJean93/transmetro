<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\Operador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperadorController extends Controller
{
    public function index()
    {
        return view('operadores.index', [
            'operadores' => Operador::with(['user', 'estacion'])->get()->sortBy('estacion.nombre'),
            'sinOperador' => Estacion::doesntHave('operador')->orderBy('nombre')->get(),
        ]);
    }

    public function create()
    {
        return view('operadores.form', ['estaciones' => Estacion::doesntHave('operador')->orderBy('nombre')->get()]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'telefono' => ['required', 'max:15'],
            'estacion_id' => ['required', 'exists:estaciones,id', 'unique:operadores,estacion_id'],
        ]);

        DB::transaction(function () use ($datos) {
            $user = User::create([
                'name' => $datos['name'], 'email' => $datos['email'], 'password' => $datos['password'], 'rol' => 'operador',
            ]);
            $user->operador()->create(['estacion_id' => $datos['estacion_id'], 'telefono' => $datos['telefono']]);
        });

        return redirect()->route('operadores.index')->with('ok', 'Operador creado; ya puede iniciar sesión.');
    }

    public function destroy(Operador $operador)
    {
        $operador->user->delete();

        return redirect()->route('operadores.index')->with('ok', 'Operador eliminado.');
    }
}
