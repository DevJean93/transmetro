@extends('layouts.app')
@section('titulo', 'Estaciones')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Estaciones', 'subtitulo' => 'Accesos, guardias, parqueos y ocupación', 'nuevo' => route('estaciones.create')])
<div class="card">
    <div class="p-3 no-print"><input type="search" id="buscar" class="form-control" placeholder="Buscar estación, línea o municipalidad…"></div>
    <div class="table-responsive">
    <table class="table table-hover mb-0" id="tabla">
        <thead><tr><th>Estación</th><th>Municipalidad</th><th>Líneas</th><th class="text-center">Accesos</th><th class="text-center">Guardias</th>
            <th>Parqueo</th><th style="width: 180px">Ocupación</th><th></th></tr></thead>
        <tbody>
        @foreach ($estaciones as $e)
            @php $pct = $e->porcentajeOcupacion(); @endphp
            <tr>
                <td><a href="{{ route('estaciones.show', $e) }}" class="fw-semibold">{{ $e->nombre }}</a><div class="small text-secondary">{{ $e->direccion }}</div></td>
                <td>{{ $e->municipalidad->nombre }}</td>
                <td>@forelse ($e->lineas as $l)<span class="badge rounded-pill me-1" style="background: {{ $l->color }}">{{ $l->nombre }}</span>@empty
                    <span class="badge text-bg-warning">Sin línea</span>@endforelse</td>
                <td class="text-center">{{ $e->accesos_count }}</td>
                <td class="text-center {{ $e->guardias_count < $e->accesos_count ? 'text-danger fw-bold' : '' }}">{{ $e->guardias_count }}</td>
                <td>{{ $e->parqueos->isNotEmpty() ? '✔' : '—' }}</td>
                <td>
                    <div class="progress" role="progressbar" title="{{ $e->pasajeros_espera }} / {{ $e->capacidad }}">
                        <div class="progress-bar {{ $pct >= 150 ? 'bg-danger' : ($pct >= 100 ? 'bg-warning' : 'bg-success') }}" style="width: {{ min($pct, 100) }}%">{{ $pct }}%</div>
                    </div>
                </td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('panel.show', $e) }}" class="btn btn-sm btn-outline-primary" title="Panel del operador"><i class="bi bi-pc-display"></i></a>
                    <a href="{{ route('estaciones.edit', $e) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('buscar').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#tabla tbody tr').forEach(tr => tr.hidden = !tr.innerText.toLowerCase().includes(q));
    });
</script>
@endpush
