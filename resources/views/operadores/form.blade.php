@extends('layouts.app')
@section('titulo', 'Nuevo operador')
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Nuevo operador'])
<div class="card p-4" style="max-width: 640px">
    @if ($estaciones->isEmpty())
        <p class="mb-0">Todas las estaciones ya tienen operador.</p>
    @else
    <form method="POST" action="{{ route('operadores.store') }}">@csrf
        <div class="mb-3"><label class="form-label">Estación</label>
            <select name="estacion_id" class="form-select" required>
                @foreach ($estaciones as $e)<option value="{{ $e->id }}" @selected(old('estacion_id') == $e->id)>{{ $e->nombre }}</option>@endforeach
            </select></div>
        <div class="mb-3"><label class="form-label">Nombre</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
        <div class="mb-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" value="{{ old('telefono') }}" required></div>
        <div class="mb-3"><label class="form-label">Correo (usuario)</label><input name="email" type="email" class="form-control" value="{{ old('email') }}" required></div>
        <div class="mb-3"><label class="form-label">Contraseña</label><input name="password" type="password" class="form-control" minlength="8" required></div>
        <button class="btn btn-primary">Crear</button>
        <a href="{{ route('operadores.index') }}" class="btn btn-link">Cancelar</a>
    </form>
    @endif
</div>
@endsection
