@extends('layouts.app')
@section('titulo', 'Líneas')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Líneas', 'subtitulo' => 'Recorrido ordenado, distancia y buses asignados', 'nuevo' => route('lineas.create')])
<div class="row g-3">
@foreach ($lineas as $l)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100" style="border-top: 4px solid {{ $l->color }} !important">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h2 class="h5"><a href="{{ route('lineas.show', $l) }}" class="text-body">{{ $l->nombre }}</a></h2>
                    @include('lineas._estado', ['estado' => $estados[$l->id]])
                </div>
                <div class="text-secondary small mb-2"><i class="bi bi-bank"></i> {{ $l->municipalidad->nombre }}</div>
                <div class="small mb-3">{{ $l->estaciones->pluck('nombre')->join(' → ') ?: 'Sin estaciones' }}</div>
                <div class="d-flex gap-4 small">
                    <div><div class="text-secondary">Estaciones</div><strong>{{ $l->estaciones->count() }}</strong></div>
                    <div><div class="text-secondary">Distancia</div><strong>{{ number_format($l->distanciaTotal(), 2) }} km</strong></div>
                    <div><div class="text-secondary">Buses</div><strong>{{ $l->buses_count }}</strong>
                        <span class="text-secondary">({{ $l->estaciones->count() }}–{{ $l->estaciones->count() * 2 }})</span></div>
                </div>
            </div>
        </div>
    </div>
@endforeach
</div>
@endsection
