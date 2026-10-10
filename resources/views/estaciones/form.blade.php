@extends('layouts.app')
@section('titulo', 'Estación')
@push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@section('contenido')
@include('partials.encabezado', ['titulo' => $estacion->exists ? 'Editar estación' : 'Nueva estación'])
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card p-4">
            <form method="POST" action="{{ $estacion->exists ? route('estaciones.update', $estacion) : route('estaciones.store') }}">
                @csrf @if ($estacion->exists) @method('PUT') @endif
                <div class="mb-3"><label class="form-label">Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $estacion->nombre) }}" required maxlength="100"></div>
                <div class="mb-3"><label class="form-label">Dirección</label>
                    <input name="direccion" class="form-control" value="{{ old('direccion', $estacion->direccion) }}" required maxlength="200"></div>
                <div class="row">
                    <div class="col mb-3"><label class="form-label">Latitud</label>
                        <input name="latitud" id="lat" type="number" step="any" class="form-control" value="{{ old('latitud', $estacion->latitud ?? 14.6349) }}" required></div>
                    <div class="col mb-3"><label class="form-label">Longitud</label>
                        <input name="longitud" id="lng" type="number" step="any" class="form-control" value="{{ old('longitud', $estacion->longitud ?? -90.5069) }}" required></div>
                </div>
                <div class="mb-3"><label class="form-label">Capacidad de pasajeros en espera</label>
                    <input name="capacidad" type="number" min="1" class="form-control" value="{{ old('capacidad', $estacion->capacidad) }}" required>
                    <div class="form-text">Se genera alerta cuando hay 50 % más pasajeros que esta capacidad.</div></div>
                <div class="mb-3">@include('partials.municipalidad-select', ['valor' => $estacion->municipalidad_id])</div>
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ $estacion->exists ? route('estaciones.show', $estacion) : route('estaciones.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header bg-white small">Haga clic en el mapa para ubicar la estación</div><div id="mapa" style="height: 420px"></div></div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const lat = document.getElementById('lat'), lng = document.getElementById('lng');
    const mapa = L.map('mapa').setView([+lat.value, +lng.value], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(mapa);
    const marca = L.marker([+lat.value, +lng.value]).addTo(mapa);
    mapa.on('click', e => {
        marca.setLatLng(e.latlng);
        lat.value = e.latlng.lat.toFixed(7);
        lng.value = e.latlng.lng.toFixed(7);
    });
</script>
@endpush
