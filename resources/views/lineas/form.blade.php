@extends('layouts.app')
@section('titulo', 'Línea')
@section('contenido')
@include('partials.encabezado', ['titulo' => $linea->exists ? 'Editar línea' : 'Nueva línea'])
<div class="card p-4" style="max-width: 640px">
    <form method="POST" action="{{ $linea->exists ? route('lineas.update', $linea) : route('lineas.store') }}">
        @csrf @if ($linea->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Nombre</label>
            <input name="nombre" class="form-control" value="{{ old('nombre', $linea->nombre) }}" required maxlength="50" placeholder="Línea 7"></div>
        <div class="mb-3"><label class="form-label">Color en el mapa</label>
            <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', $linea->color ?? '#00796b') }}"></div>
        <div class="mb-3">@include('partials.municipalidad-select', ['valor' => $linea->municipalidad_id])</div>
        <button class="btn btn-primary">Guardar</button>
        <a href="{{ $linea->exists ? route('lineas.show', $linea) : route('lineas.index') }}" class="btn btn-link">Cancelar</a>
    </form>
</div>
@endsection
