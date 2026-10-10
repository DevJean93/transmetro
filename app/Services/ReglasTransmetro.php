<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Bus;
use App\Models\Estacion;
use App\Models\Linea;
use App\Models\Visita;
use Illuminate\Validation\ValidationException;

/**
 * Reglas de negocio del enunciado que no se pueden expresar solo con la estructura de la BD.
 */
class ReglasTransmetro
{
    public const UMBRAL_ALERTA = 1.5;      // Req. 14: 50 % más de la capacidad de la estación

    public const UMBRAL_OCUPACION_BUS = 0.25; // Req. 15: bus por debajo del 25 % de su capacidad

    public const ESPERA_EXTRA_MIN = 5;

    /**
     * Req. 5: una línea tiene como mínimo tantos buses como estaciones y como máximo el doble.
     * El máximo siempre se exige. El mínimo se exige para no "romper" una línea que ya cumple;
     * una línea nueva queda marcada como incompleta hasta que se le asignen buses.
     */
    public function validarCambioLineaBus(Bus $bus, ?int $nuevaLineaId): void
    {
        if ($bus->linea_id === $nuevaLineaId) {
            return;
        }

        if ($nuevaLineaId !== null) {
            $linea = Linea::findOrFail($nuevaLineaId);
            $max = $linea->busesMaximos();
            if ($linea->buses()->count() + 1 > $max) {
                throw ValidationException::withMessages([
                    'linea_id' => "La {$linea->nombre} ya tiene el máximo de buses permitido ({$max} = 2 × {$linea->busesMinimos()} estaciones).",
                ]);
            }
        }

        if ($bus->linea_id !== null) {
            $actual = $bus->linea;
            $min = $actual->busesMinimos();
            $cantidad = $actual->buses()->count();
            if ($cantidad >= $min && $cantidad - 1 < $min) {
                throw ValidationException::withMessages([
                    'linea_id' => "No se puede retirar el bus: la {$actual->nombre} quedaría con menos buses ({$cantidad} - 1) que estaciones ({$min}).",
                ]);
            }
        }
    }

    /**
     * Req. 5 al quitar una estación de una línea: los buses asignados no pueden superar 2 × estaciones restantes.
     */
    public function validarQuitarEstacion(Linea $linea): void
    {
        $restantes = $linea->busesMinimos() - 1;
        if ($linea->buses()->count() > $restantes * 2) {
            throw ValidationException::withMessages([
                'estacion' => "No se puede quitar la estación: la {$linea->nombre} tiene {$linea->buses()->count()} buses y el máximo con {$restantes} estaciones es ".($restantes * 2).'.',
            ]);
        }
    }

    /**
     * Estado de cumplimiento de la regla 5 para mostrar en dashboard y reportes.
     */
    public function estadoBuses(Linea $linea): string
    {
        $buses = $linea->buses()->count();
        if ($buses < $linea->busesMinimos()) {
            return 'faltan';
        }

        return $buses > $linea->busesMaximos() ? 'exceso' : 'ok';
    }

    /**
     * Req. 14: si la estación tiene al menos 50 % más de su capacidad, se genera una alerta para enviar otro bus.
     */
    public function actualizarPasajeros(Estacion $estacion, int $pasajeros): ?Alerta
    {
        $estacion->update(['pasajeros_espera' => $pasajeros]);

        if ($pasajeros < $estacion->capacidad * self::UMBRAL_ALERTA) {
            return null;
        }

        $abierta = $estacion->alertas()->where('atendida', false)->where('tipo', 'sobrecupo')->first();
        if ($abierta) {
            return $abierta;
        }

        return $estacion->alertas()->create([
            'tipo' => 'sobrecupo',
            'mensaje' => "{$estacion->nombre}: {$pasajeros} pasajeros en espera (capacidad {$estacion->capacidad}). Enviar otro bus.",
        ]);
    }

    /**
     * Req. 15: si el bus no llena el 25 % de su capacidad, espera 5 minutos más en la estación.
     */
    public function registrarVisita(Bus $bus, Estacion $estacion, int $pasajerosAbordo): Visita
    {
        if (! $bus->linea_id || ! $bus->linea->estaciones()->whereKey($estacion->id)->exists()) {
            throw ValidationException::withMessages([
                'bus_id' => "El bus {$bus->placa} no pertenece a una línea que pase por {$estacion->nombre}.",
            ]);
        }

        if ($pasajerosAbordo > $bus->capacidad_maxima) {
            throw ValidationException::withMessages([
                'pasajeros_abordo' => "El bus {$bus->placa} tiene capacidad máxima de {$bus->capacidad_maxima} pasajeros.",
            ]);
        }

        $espera = $pasajerosAbordo < $bus->capacidad_maxima * self::UMBRAL_OCUPACION_BUS ? self::ESPERA_EXTRA_MIN : 0;

        return Visita::create([
            'bus_id' => $bus->id,
            'linea_id' => $bus->linea_id,
            'estacion_id' => $estacion->id,
            'llegada' => now(),
            'pasajeros_abordo' => $pasajerosAbordo,
            'espera_extra_min' => $espera,
        ]);
    }
}
