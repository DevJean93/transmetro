@extends('layouts.app')
@section('titulo', 'Alertas')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Alertas de sobrecupo', 'subtitulo' => 'Se generan cuando una estación supera en 50 % su capacidad'])
<div class="card">
    <table class="table mb-0">
        <thead><tr><th>Fecha</th><th>Estación</th><th>Mensaje</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($alertas as $a)
            <tr class="{{ $a->atendida ? 'text-secondary' : '' }}">
                <td class="text-nowrap">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $a->estacion->nombre }}</td><td>{{ $a->mensaje }}</td>
                <td>{!! $a->atendida ? '<span class="badge text-bg-success">Atendida</span>' : '<span class="badge text-bg-danger">Abierta</span>' !!}</td>
                <td class="text-end">@unless ($a->atendida)
                    <form method="POST" action="{{ route('alertas.atender', $a) }}">@csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-success text-nowrap"><i class="bi bi-check2"></i> Bus enviado</button></form>
                @endunless</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-secondary">Sin alertas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $alertas->links('pagination::bootstrap-5') }}</div>
@endsection
