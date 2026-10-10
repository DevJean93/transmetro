@extends('layouts.app')
@section('titulo', 'Bus')
@section('contenido')
@include('partials.encabezado', ['titulo' => $bus->exists ? "Editar bus {$bus->placa}" : 'Nuevo bus'])
<div class="card p-4" style="max-width: 720px">
    <form method="POST" action="{{ $bus->exists ? route('buses.update', $bus) : route('buses.store') }}">
        @csrf @if ($bus->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">Placa</label>
                <input name="placa" class="form-control text-uppercase" value="{{ old('placa', $bus->placa) }}" required maxlength="10"></div>
            <div class="col-md-5 mb-3"><label class="form-label">Modelo</label>
                <input name="modelo" class="form-control" value="{{ old('modelo', $bus->modelo) }}" required maxlength="50"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Capacidad máx.</label>
                <input name="capacidad_maxima" type="number" min="1" max="300" class="form-control" value="{{ old('capacidad_maxima', $bus->capacidad_maxima) }}" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Línea</label>
            <select name="linea_id" class="form-select">
                <option value="">— Sin asignar (reserva) —</option>
                @foreach ($lineas as $l)
                    @php $lleno = $l->buses_count >= $l->estaciones_count * 2 && $bus->linea_id !== $l->id; @endphp
                    <option value="{{ $l->id }}" @selected(old('linea_id', $bus->linea_id) == $l->id) @disabled($lleno)>
                        {{ $l->nombre }} — {{ $l->buses_count }} buses (permitido {{ $l->estaciones_count }}–{{ $l->estaciones_count * 2 }}){{ $lleno ? ' · completa' : '' }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Regla: cada línea tiene entre n y 2n buses, donde n es su número de estaciones.</div>
        </div>
        <div class="mb-3"><label class="form-label">Parqueo</label>
            <select name="parqueo_id" class="form-select" required>
                <option value="">Seleccione…</option>
                @foreach ($parqueos as $p)
                    <option value="{{ $p->id }}" @selected(old('parqueo_id', $bus->parqueo_id) == $p->id)>{{ $p->nombre }} ({{ $p->estacion->nombre }})</option>
                @endforeach
            </select>
            <div class="form-text">Un bus puede cambiar de parqueo, pero no quedarse sin él.</div>
        </div>
        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('buses.index') }}" class="btn btn-link">Cancelar</a>
    </form>
</div>
@endsection
