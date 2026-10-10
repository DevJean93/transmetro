@extends('layouts.app')
@section('titulo', 'Operadores')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Operadores de estación', 'subtitulo' => 'Cada estación tiene una PC de trabajo controlada por un operador', 'nuevo' => route('operadores.create')])
@if ($sinOperador->isNotEmpty())
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Estaciones sin operador: {{ $sinOperador->pluck('nombre')->join(', ') }}</div>
@endif
<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>Estación</th><th>Operador</th><th>Usuario</th><th>Teléfono</th><th></th></tr></thead>
        <tbody>
        @foreach ($operadores as $o)
            <tr>
                <td><a href="{{ route('panel.show', $o->estacion) }}">{{ $o->estacion->nombre }}</a></td>
                <td>{{ $o->user->name }}</td><td>{{ $o->user->email }}</td><td>{{ $o->telefono }}</td>
                <td class="text-end">@include('partials.eliminar', ['accion' => route('operadores.destroy', $o), 'confirmar' => '¿Eliminar el operador y su usuario?'])</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
