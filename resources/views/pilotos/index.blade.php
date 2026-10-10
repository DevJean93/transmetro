@extends('layouts.app')
@section('titulo', 'Pilotos')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Pilotos', 'subtitulo' => 'Datos de residencia, comunicación e historial educativo', 'nuevo' => route('pilotos.create')])
<div class="card">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>Nombre</th><th>DPI</th><th>Residencia</th><th>Teléfono</th><th>Bus / Línea</th><th class="text-center">Estudios</th><th></th></tr></thead>
        <tbody>
        @foreach ($pilotos as $p)
            <tr>
                <td><a href="{{ route('pilotos.show', $p) }}" class="fw-semibold">{{ $p->nombre }}</a><div class="small text-secondary">{{ $p->correo }}</div></td>
                <td>{{ $p->dpi }}</td>
                <td>{{ $p->municipio_residencia }}<div class="small text-secondary">{{ $p->direccion }}</div></td>
                <td>{{ $p->telefono }}</td>
                <td>{{ $p->bus?->placa ?? '—' }} <span class="small text-secondary">{{ $p->bus?->linea?->nombre }}</span></td>
                <td class="text-center">{{ $p->historial_count }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('pilotos.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @include('partials.eliminar', ['accion' => route('pilotos.destroy', $p)])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
