@extends('layouts.app')
@section('titulo', 'Reportes')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Reportes', 'subtitulo' => 'Cada reporte ejecuta un query SQL que se puede consultar y copiar'])
<div class="row g-3">
    @foreach ($reportes as $clave => $r)
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('reportes.show', $clave) }}" class="card p-4 h-100 text-decoration-none">
                <div class="display-6 mb-2" style="color: var(--tm-primario)"><i class="bi bi-{{ $r['icono'] }}"></i></div>
                <div class="h5 text-body">{{ $r['titulo'] }}</div>
                <div class="text-secondary small">{{ $r['descripcion'] }}</div>
            </a>
        </div>
    @endforeach
</div>
@endsection
