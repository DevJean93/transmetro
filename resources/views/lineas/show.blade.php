@extends('layouts.app')
@section('titulo', $linea->nombre)
@section('contenido')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0"><span class="linea-chip" style="background: {{ $linea->color }}; width: 1.1rem; height: 1.1rem"></span> {{ $linea->nombre }}</h1>
        <div class="text-secondary">{{ $linea->municipalidad->nombre }} · {{ $linea->estaciones->count() }} estaciones ·
            {{ number_format($linea->distanciaTotal(), 2) }} km · @include('lineas._estado', ['estado' => $estado])</div>
    </div>
    <div class="no-print">
        <a href="{{ route('lineas.edit', $linea) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Editar</a>
        @include('partials.eliminar', ['accion' => route('lineas.destroy', $linea), 'texto' => 'Eliminar', 'confirmar' => '¿Eliminar la línea? Sus buses quedarán sin asignar.'])
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-list-ol"></i> Recorrido (orden de estaciones)</div>
            <table class="table mb-0">
                <thead><tr><th>#</th><th>Estación</th><th>Accesos</th><th style="width: 190px">Km a la siguiente</th><th></th></tr></thead>
                <tbody>
                @forelse ($linea->estaciones as $e)
                    <tr>
                        <td class="fw-bold">{{ $e->pivot->orden }}</td>
                        <td><a href="{{ route('estaciones.show', $e) }}">{{ $e->nombre }}</a></td>
                        <td class="small">{{ $e->accesos->pluck('nombre')->join(', ') }}</td>
                        <td>
                            @if (! $loop->last)
                                <form method="POST" action="{{ route('lineas.estaciones.actualizar', [$linea, $e]) }}" class="input-group input-group-sm">
                                    @csrf @method('PATCH')
                                    <input type="number" step="0.01" min="0" name="distancia_siguiente_km" class="form-control" value="{{ $e->pivot->distancia_siguiente_km }}">
                                    <button class="btn btn-outline-secondary" title="Guardar distancia"><i class="bi bi-check"></i></button>
                                </form>
                            @else
                                <span class="text-secondary small">Terminal</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @foreach (['arriba' => 'up', 'abajo' => 'down'] as $dir => $flecha)
                                <form method="POST" action="{{ route('lineas.estaciones.mover', [$linea, $e, $dir]) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-secondary" @disabled(($dir === 'arriba' && $loop->parent->first) || ($dir === 'abajo' && $loop->parent->last))><i class="bi bi-arrow-{{ $flecha }}"></i></button>
                                </form>
                            @endforeach
                            @include('partials.eliminar', ['accion' => route('lineas.estaciones.quitar', [$linea, $e]), 'confirmar' => "¿Quitar {$e->nombre} de la línea?"])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">Esta línea aún no tiene estaciones.</td></tr>
                @endforelse
                </tbody>
                <tfoot><tr class="table-light"><th colspan="3" class="text-end">Distancia total de la línea</th>
                    <th colspan="2">{{ number_format($linea->distanciaTotal(), 2) }} km</th></tr></tfoot>
            </table>
        </div>

        <div class="card mt-3 p-3 no-print">
            <div class="fw-semibold mb-2"><i class="bi bi-plus-circle"></i> Agregar estación al recorrido</div>
            <form method="POST" action="{{ route('lineas.estaciones.agregar', $linea) }}" class="row g-2">
                @csrf
                <div class="col-md-5"><select name="estacion_id" class="form-select" required>
                    <option value="">Estación…</option>
                    @foreach ($disponibles as $d)<option value="{{ $d->id }}">{{ $d->nombre }}</option>@endforeach
                </select></div>
                <div class="col-md-2"><input type="number" name="orden" class="form-control" min="1" max="{{ $linea->estaciones->count() + 1 }}"
                    value="{{ $linea->estaciones->count() + 1 }}" title="Posición" required></div>
                <div class="col-md-3"><input type="number" step="0.01" min="0" name="distancia_siguiente_km" class="form-control" placeholder="Km a la siguiente"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Agregar</button></div>
            </form>
            <div class="form-text">Al insertar en una posición intermedia las estaciones siguientes se desplazan.</div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-bus-front"></i> Buses asignados ({{ $linea->buses->count() }})</span>
                <span class="text-secondary small">mín. {{ $linea->estaciones->count() }} · máx. {{ $linea->estaciones->count() * 2 }}</span>
            </div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Placa</th><th>Modelo</th><th class="text-end">Cap.</th><th>Piloto</th></tr></thead>
                <tbody>
                @forelse ($linea->buses as $b)
                    <tr><td><a href="{{ route('buses.edit', $b) }}">{{ $b->placa }}</a></td><td>{{ $b->modelo }}</td>
                        <td class="text-end">{{ $b->capacidad_maxima }}</td><td>{{ $b->piloto?->nombre ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-secondary">Sin buses. Asígnelos desde el módulo de Buses.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
