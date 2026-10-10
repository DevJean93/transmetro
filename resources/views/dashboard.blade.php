@extends('layouts.app')
@section('titulo', 'Dashboard')
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@section('contenido')
@include('partials.encabezado', ['titulo' => 'Dashboard', 'subtitulo' => 'Estado general de la red del Transmetro'])

<div class="row g-3 mb-3">
    @foreach ([
        ['Líneas', $lineas->count(), 'signpost-split', route('lineas.index')],
        ['Estaciones', $estaciones->count(), 'geo-alt', route('estaciones.index')],
        ['Buses en línea', $busesAsignados.' / '.$totalBuses, 'bus-front', route('buses.index')],
        ['Alertas abiertas', $alertas->count(), 'bell', route('alertas.index')],
    ] as [$texto, $valor, $icono, $url])
        <div class="col-6 col-lg-3">
            <a href="{{ $url }}" class="card p-3 text-decoration-none h-100">
                <div class="text-secondary small"><i class="bi bi-{{ $icono }}"></i> {{ $texto }}</div>
                <div class="kpi mt-2 {{ $texto === 'Alertas abiertas' && $valor > 0 ? 'text-danger' : 'text-body' }}">{{ $valor }}</div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-map"></i> Mapa de la red</div>
            <div id="mapa" style="height: 460px"></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bell"></i> Alertas de sobrecupo</div>
            <ul class="list-group list-group-flush">
                @forelse ($alertas as $a)
                    <li class="list-group-item d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold text-danger"><i class="bi bi-exclamation-triangle-fill"></i> {{ $a->estacion->nombre }}</div>
                            <div class="small">{{ $a->mensaje }}</div>
                            <div class="small text-secondary">{{ $a->created_at->diffForHumans() }}</div>
                        </div>
                        <form method="POST" action="{{ route('alertas.atender', $a) }}">@csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-success text-nowrap"><i class="bi bi-check2"></i> Bus enviado</button>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item text-secondary">Sin alertas abiertas.</li>
                @endforelse
            </ul>
        </div>
        <div class="card">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-signpost-split"></i> Líneas — buses asignados (mín. n, máx. 2n)</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Línea</th><th class="text-end">Km</th><th class="text-center">Buses</th><th>Estado</th></tr></thead>
                <tbody>
                @foreach ($lineas as $l)
                    <tr>
                        <td><span class="linea-chip" style="background: {{ $l->color }}"></span>
                            <a href="{{ route('lineas.show', $l) }}">{{ $l->nombre }}</a></td>
                        <td class="text-end">{{ number_format($l->distanciaTotal(), 1) }}</td>
                        <td class="text-center">{{ $l->buses_count }} <span class="text-secondary small">({{ $l->estaciones->count() }}–{{ $l->estaciones->count() * 2 }})</span></td>
                        <td>@include('lineas._estado', ['estado' => $estados[$l->id]])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const estaciones = @json($mapaEstaciones);
    const lineas = @json($mapaLineas);

    const mapa = L.map('mapa');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18, attribution: '&copy; OpenStreetMap',
    }).addTo(mapa);

    lineas.forEach(l => L.polyline(l.puntos, { color: l.color, weight: 5, opacity: .8 }).bindTooltip(l.nombre).addTo(mapa));

    const urlPanel = @json(url('/panel'));
    const marcadores = estaciones.map(e => {
        const color = e.ocupacion >= 150 ? '#dc3545' : e.ocupacion >= 100 ? '#fd7e14' : '#0b2e3a';
        return L.circleMarker([e.lat, e.lng], { radius: 8, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1 })
            .bindPopup(`<strong>${e.nombre}</strong><br>${e.lineas}<br>Ocupación: ${e.ocupacion}%<br><a href="${urlPanel}/${e.id}">Abrir panel</a>`)
            .addTo(mapa);
    });
    mapa.fitBounds(L.featureGroup(marcadores).getBounds().pad(0.1));
</script>
@endpush
