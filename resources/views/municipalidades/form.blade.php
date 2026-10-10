@extends('layouts.app')
@section('titulo', 'Municipalidad')
@section('contenido')
@include('partials.encabezado', ['titulo' => $municipalidad->exists ? 'Editar municipalidad' : 'Nueva municipalidad'])
<div class="card p-4" style="max-width: 640px">
    <form method="POST" action="{{ $municipalidad->exists ? route('municipalidades.update', $municipalidad) : route('municipalidades.store') }}">
        @csrf @if ($municipalidad->exists) @method('PUT') @endif
        <div class="mb-3"><label class="form-label">Nombre</label>
            <input name="nombre" class="form-control" value="{{ old('nombre', $municipalidad->nombre) }}" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label">Departamento</label>
            <input name="departamento" class="form-control" value="{{ old('departamento', $municipalidad->departamento ?? 'Guatemala') }}" required maxlength="100"></div>
        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('municipalidades.index') }}" class="btn btn-link">Cancelar</a>
    </form>
</div>
@endsection
