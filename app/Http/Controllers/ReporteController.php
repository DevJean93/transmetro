<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/**
 * Req. 15: los reportes ejecutan los mismos archivos .sql que se documentan en el informe.
 */
class ReporteController extends Controller
{
    public const REPORTES = [
        'estaciones' => ['archivo' => '1_estaciones.sql', 'titulo' => 'Estaciones', 'icono' => 'geo-alt',
            'descripcion' => 'Líneas, accesos, guardias, parqueos y ocupación de cada estación.'],
        'lineas' => ['archivo' => '2_lineas.sql', 'titulo' => 'Líneas', 'icono' => 'signpost-split',
            'descripcion' => 'Recorrido ordenado, distancia total y cumplimiento de la regla de buses.'],
        'buses' => ['archivo' => '3_buses_por_linea.sql', 'titulo' => 'Buses por línea', 'icono' => 'bus-front',
            'descripcion' => 'Buses asignados a cada línea con su piloto y parqueo.'],
        'accesos' => ['archivo' => '4_accesos_por_linea.sql', 'titulo' => 'Accesos por línea', 'icono' => 'door-open',
            'descripcion' => 'Todos los accesos de cada línea y sus guardias de seguridad.'],
    ];

    /** Encabezados legibles para los alias de columna de los queries. */
    private const ENCABEZADOS = [
        'estacion' => 'Estación', 'linea' => 'Línea', 'lineas' => 'Líneas', 'distancia_km' => 'Distancia (km)',
        'buses_minimo' => 'Buses mínimo', 'buses_maximo' => 'Buses máximo', 'capacidad_maxima' => 'Capacidad máx.',
        'ocupacion_pct' => 'Ocupación %', 'estacion_parqueo' => 'Estación del parqueo', 'nombres_guardias' => 'Guardias asignados',
        'pasajeros_espera' => 'Pasajeros en espera', 'id' => 'ID',
    ];

    public function index()
    {
        return view('reportes.index', ['reportes' => self::REPORTES]);
    }

    public function show(string $reporte)
    {
        abort_unless(isset(self::REPORTES[$reporte]), 404);
        $info = self::REPORTES[$reporte];
        $sql = file_get_contents(database_path('sql/reportes/'.$info['archivo']));

        return view('reportes.show', [
            'clave' => $reporte,
            'info' => $info,
            'sql' => $sql,
            'filas' => $filas = collect(DB::select($sql))->map(fn ($f) => (array) $f),
            'encabezados' => array_map(
                fn ($c) => self::ENCABEZADOS[$c] ?? ucfirst(str_replace('_', ' ', $c)),
                array_keys($filas->first() ?? []),
            ),
        ]);
    }
}
