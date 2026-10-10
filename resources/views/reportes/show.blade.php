@extends('layouts.app')
@section('titulo', 'Reporte de '.$info['titulo'])
@section('contenido')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Reporte de {{ $info['titulo'] }}</h1>
        <div class="text-secondary">{{ $info['descripcion'] }} · {{ $filas->count() }} filas · generado {{ now()->format('d/m/Y H:i') }}</div>
    </div>
    <div class="no-print d-flex gap-2">
        <a href="{{ route('reportes.index') }}" class="btn btn-link">← Reportes</a>
        <button class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#sql"><i class="bi bi-code-slash"></i> Ver SQL</button>
        <button class="btn btn-outline-secondary" id="csv"><i class="bi bi-filetype-csv"></i> CSV</button>
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / PDF</button>
    </div>
</div>

<div class="collapse mb-3" id="sql">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="small text-secondary">database/sql/reportes/{{ $info['archivo'] }}</span>
            <button class="btn btn-sm btn-outline-secondary" id="copiar"><i class="bi bi-clipboard"></i> Copiar</button>
        </div>
        <pre class="mb-0 p-3 bg-dark text-light small" style="white-space: pre-wrap"><code id="codigo">{{ $sql }}</code></pre>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
    <table class="table table-sm table-striped table-hover mb-0" id="reporte">
        @if ($filas->isNotEmpty())
            <thead class="table-dark"><tr>@foreach ($encabezados as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
        @endif
        <tbody>
        @forelse ($filas as $fila)
            <tr>
                @foreach ($fila as $col => $valor)
                    <td>
                        @if ($col === 'estado')
                            <span class="badge {{ $valor === 'Cumple' ? 'text-bg-success' : 'text-bg-danger' }}">{{ $valor }}</span>
                        @else
                            {{ $valor ?? '—' }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @empty
            <tr><td class="text-secondary">Sin datos.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('copiar').addEventListener('click', () => navigator.clipboard.writeText(document.getElementById('codigo').innerText));
    document.getElementById('csv').addEventListener('click', () => {
        const filas = [...document.querySelectorAll('#reporte tr')].map(tr =>
            [...tr.children].map(c => '"' + c.innerText.trim().replaceAll('"', '""') + '"').join(','));
        const blob = new Blob(['﻿' + filas.join('\n')], { type: 'text/csv;charset=utf-8' });
        const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(blob), download: 'reporte-{{ $clave }}.csv' });
        a.click();
    });
</script>
@endpush
