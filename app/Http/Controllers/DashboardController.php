<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Bus;
use App\Models\Estacion;
use App\Models\Linea;
use App\Services\ReglasTransmetro;

class DashboardController extends Controller
{
    public function __invoke(ReglasTransmetro $reglas)
    {
        $lineas = Linea::with(['estaciones', 'municipalidad'])->withCount('buses')->get();
        $estaciones = Estacion::with('lineas')->get();

        return view('dashboard', [
            'lineas' => $lineas,
            'estados' => $lineas->mapWithKeys(fn ($l) => [$l->id => $reglas->estadoBuses($l)]),
            'estaciones' => $estaciones,
            'mapaEstaciones' => $estaciones->map(fn ($e) => [
                'id' => $e->id, 'nombre' => $e->nombre, 'lat' => (float) $e->latitud, 'lng' => (float) $e->longitud,
                'ocupacion' => $e->porcentajeOcupacion(), 'lineas' => $e->lineas->pluck('nombre')->join(', '),
            ]),
            'mapaLineas' => $lineas->map(fn ($l) => [
                'nombre' => $l->nombre, 'color' => $l->color,
                'puntos' => $l->estaciones->map(fn ($e) => [(float) $e->latitud, (float) $e->longitud]),
            ]),
            'totalBuses' => Bus::count(),
            'busesAsignados' => Bus::whereNotNull('linea_id')->count(),
            'alertas' => Alerta::with('estacion')->where('atendida', false)->latest()->get(),
        ]);
    }
}
