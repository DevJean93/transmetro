@extends('layouts.app')
@section('titulo', 'Piloto')
@section('contenido')
@include('partials.encabezado', ['titulo' => $piloto->exists ? 'Editar piloto' : 'Nuevo piloto'])
<div class="card p-4" style="max-width: 820px">
    <form method="POST" action="{{ $piloto->exists ? route('pilotos.update', $piloto) : route('pilotos.store') }}">
        @csrf @if ($piloto->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-8 mb-3"><label class="form-label">Nombre completo</label>
                <input name="nombre" class="form-control" value="{{ old('nombre', $piloto->nombre) }}" required maxlength="100"></div>
            <div class="col-md-4 mb-3"><label class="form-label">DPI</label>
                <input name="dpi" class="form-control" value="{{ old('dpi', $piloto->dpi) }}" required pattern="\d{13}" title="13 dígitos"></div>
        </div>
        <h2 class="h6 text-secondary mt-2">Residencia</h2>
        <div class="row">
            <div class="col-md-8 mb-3"><label class="form-label">Dirección</label>
                <input name="direccion" class="form-control" value="{{ old('direccion', $piloto->direccion) }}" required maxlength="200"></div>
            <div class="col-md-4 mb-3"><label class="form-label">Municipio</label>
                <input name="municipio_residencia" class="form-control" value="{{ old('municipio_residencia', $piloto->municipio_residencia) }}" required maxlength="100"></div>
        </div>
        <h2 class="h6 text-secondary mt-2">Comunicación</h2>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">Teléfono</label>
                <input name="telefono" class="form-control" value="{{ old('telefono', $piloto->telefono) }}" required maxlength="15"></div>
            <div class="col-md-8 mb-3"><label class="form-label">Correo</label>
                <input name="correo" type="email" class="form-control" value="{{ old('correo', $piloto->correo) }}" maxlength="100"></div>
        </div>
        <div class="mb-3"><label class="form-label">Bus asignado</label>
            <select name="bus_id" class="form-select">
                <option value="">— Ninguno —</option>
                @foreach ($buses as $b)
                    <option value="{{ $b->id }}" @selected(old('bus_id', $piloto->bus_id) == $b->id)>{{ $b->placa }} · {{ $b->linea?->nombre ?? 'sin línea' }}</option>
                @endforeach
            </select></div>
        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('pilotos.index') }}" class="btn btn-link">Cancelar</a>
    </form>
</div>
@endsection
