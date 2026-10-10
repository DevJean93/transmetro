@extends('layouts.app')
@section('titulo', 'Buses')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Buses', 'subtitulo' => 'Un bus pertenece a máximo una línea y siempre tiene parqueo', 'nuevo' => route('buses.create')])
<div class="card">
    <form class="p-3 no-print d-flex gap-2" method="GET">
        <select name="linea" class="form-select" style="max-width: 260px" onchange="this.form.submit()">
            <option value="">Todas las líneas</option>
            @foreach ($lineas as $l)<option value="{{ $l->id }}" @selected(request('linea') == $l->id)>{{ $l->nombre }}</option>@endforeach
            <option value="ninguna" @selected(request('linea') === 'ninguna')>Sin línea asignada</option>
        </select>
    </form>
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>Placa</th><th>Modelo</th><th class="text-end">Capacidad</th><th>Línea</th><th>Parqueo</th><th>Piloto</th><th></th></tr></thead>
        <tbody>
        @forelse ($buses as $b)
            <tr>
                <td class="fw-semibold">{{ $b->placa }}</td><td>{{ $b->modelo }}</td><td class="text-end">{{ $b->capacidad_maxima }}</td>
                <td>@if ($b->linea)<span class="badge rounded-pill" style="background: {{ $b->linea->color }}">{{ $b->linea->nombre }}</span>@else<span class="text-secondary">Sin asignar</span>@endif</td>
                <td>{{ $b->parqueo->nombre }}</td>
                <td>{{ $b->piloto?->nombre ?? '—' }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('buses.edit', $b) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @include('partials.eliminar', ['accion' => route('buses.destroy', $b)])
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-secondary">No hay buses.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
