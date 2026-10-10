@extends('layouts.app')
@section('titulo', 'Municipalidades')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Municipalidades', 'subtitulo' => 'Cada línea y estación pertenece a una municipalidad', 'nuevo' => route('municipalidades.create')])
<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>Nombre</th><th>Departamento</th><th class="text-center">Líneas</th><th class="text-center">Estaciones</th><th></th></tr></thead>
        <tbody>
        @foreach ($municipalidades as $m)
            <tr>
                <td>{{ $m->nombre }}</td><td>{{ $m->departamento }}</td>
                <td class="text-center">{{ $m->lineas_count }}</td><td class="text-center">{{ $m->estaciones_count }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('municipalidades.edit', $m) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @include('partials.eliminar', ['accion' => route('municipalidades.destroy', $m)])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
