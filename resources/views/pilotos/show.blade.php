@extends('layouts.app')
@section('titulo', $piloto->nombre)
@section('contenido')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">{{ $piloto->nombre }}</h1><div class="text-secondary">DPI {{ $piloto->dpi }}</div></div>
    <a href="{{ route('pilotos.edit', $piloto) }}" class="btn btn-outline-secondary no-print"><i class="bi bi-pencil"></i> Editar</a>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3">
            <dl class="mb-0">
                <dt>Residencia</dt><dd>{{ $piloto->direccion }}, {{ $piloto->municipio_residencia }}</dd>
                <dt>Teléfono</dt><dd>{{ $piloto->telefono }}</dd>
                <dt>Correo</dt><dd>{{ $piloto->correo ?? '—' }}</dd>
                <dt>Bus</dt><dd class="mb-0">{{ $piloto->bus?->placa ?? 'Sin bus' }} {{ $piloto->bus?->linea ? '· '.$piloto->bus->linea->nombre : '' }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-mortarboard"></i> Historial educativo</div>
            <table class="table mb-0">
                <thead><tr><th>Año</th><th>Nivel</th><th>Título</th><th>Institución</th><th></th></tr></thead>
                <tbody>
                @forelse ($piloto->historial as $h)
                    <tr><td>{{ $h->anio }}</td><td>{{ $h->nivel }}</td><td>{{ $h->titulo }}</td><td>{{ $h->institucion }}</td>
                        <td class="text-end">@include('partials.eliminar', ['accion' => route('historial.destroy', $h)])</td></tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">Sin registros.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="card-footer bg-white no-print">
                <form method="POST" action="{{ route('historial.store', $piloto) }}" class="row g-2">@csrf
                    <div class="col-md-2"><input name="anio" type="number" class="form-control form-control-sm" placeholder="Año" required></div>
                    <div class="col-md-3"><select name="nivel" class="form-select form-select-sm" required>
                        @foreach (['Primaria', 'Básico', 'Diversificado', 'Técnico', 'Universitario', 'Posgrado'] as $n)<option>{{ $n }}</option>@endforeach
                    </select></div>
                    <div class="col-md-3"><input name="titulo" class="form-control form-control-sm" placeholder="Título" required></div>
                    <div class="col-md-2"><input name="institucion" class="form-control form-control-sm" placeholder="Institución" required></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Agregar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
