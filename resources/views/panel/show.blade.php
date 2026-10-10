@extends('layouts.app')
@section('titulo', 'Panel '.$estacion->nombre)
@section('contenido')
@php $pct = $estacion->porcentajeOcupacion(); @endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <div class="text-secondary small text-uppercase"><i class="bi bi-pc-display"></i> Panel del operador</div>
        <h1 class="h3 mb-0">{{ $estacion->nombre }}</h1>
        <div>@foreach ($estacion->lineas as $l)<span class="badge rounded-pill me-1" style="background: {{ $l->color }}">{{ $l->nombre }}</span>@endforeach</div>
    </div>
    <div class="text-secondary small" id="reloj"></div>
</div>

@foreach ($estacion->alertas as $a)
    <div class="alert alert-danger d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-triangle-fill"></i> {{ $a->mensaje }}</span>
        <form method="POST" action="{{ route('alertas.atender', $a) }}">@csrf @method('PATCH')
            <button class="btn btn-sm btn-light"><i class="bi bi-check2"></i> Bus enviado</button></form>
    </div>
@endforeach

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 h-100">
            <div class="fw-semibold mb-2"><i class="bi bi-people"></i> Pasajeros en espera</div>
            <div class="d-flex align-items-end gap-2 mb-2">
                <div class="kpi {{ $pct >= 150 ? 'text-danger' : '' }}">{{ $estacion->pasajeros_espera }}</div>
                <div class="text-secondary">/ {{ $estacion->capacidad }} ({{ $pct }}%)</div>
            </div>
            <div class="progress mb-3" style="height: 10px">
                <div class="progress-bar {{ $pct >= 150 ? 'bg-danger' : ($pct >= 100 ? 'bg-warning' : 'bg-success') }}" style="width: {{ min($pct / 1.5, 100) }}%"></div>
            </div>
            <div class="small text-secondary mb-3">La alerta se dispara al llegar a {{ (int) ceil($estacion->capacidad * 1.5) }} pasajeros (150 %).</div>
            <form method="POST" action="{{ route('panel.pasajeros', $estacion) }}" class="input-group">@csrf
                <input type="number" min="0" name="pasajeros_espera" class="form-control" value="{{ $estacion->pasajeros_espera }}" required>
                <button class="btn btn-primary">Actualizar</button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3 h-100">
            <div class="fw-semibold mb-2"><i class="bi bi-bus-front"></i> Registrar llegada de bus</div>
            <form method="POST" action="{{ route('panel.visita', $estacion) }}">@csrf
                <div class="mb-2"><select name="bus_id" id="bus" class="form-select" required>
                    <option value="">Bus…</option>
                    @foreach ($buses as $b)
                        <option value="{{ $b->id }}" data-cap="{{ $b->capacidad_maxima }}">{{ $b->placa }} · {{ $b->linea->nombre }} (cap. {{ $b->capacidad_maxima }})</option>
                    @endforeach
                </select></div>
                <div class="mb-2"><input type="number" min="0" name="pasajeros_abordo" id="abordo" class="form-control" placeholder="Pasajeros a bordo" required></div>
                <div class="small mb-2" id="aviso"></div>
                <button class="btn btn-primary w-100">Registrar visita</button>
            </form>
            <div class="small text-secondary mt-2">Si el bus lleva menos del 25 % de su capacidad debe esperar 5 minutos más.</div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-door-open"></i> Accesos y guardias</div>
            <ul class="list-group list-group-flush small">
                @foreach ($estacion->accesos as $a)
                    <li class="list-group-item"><strong>{{ $a->nombre }}</strong>:
                        @forelse ($a->guardias as $g){{ $g->nombre }}{{ $loop->last ? '' : ', ' }}@empty<span class="text-danger">sin guardia</span>@endforelse</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history"></i> Últimas visitas en esta estación</div>
    <div class="table-responsive">
    <table class="table table-sm mb-0">
        <thead><tr><th>Hora</th><th>Bus</th><th>Línea</th><th class="text-end">A bordo</th><th class="text-end">Ocupación</th><th>Espera extra</th></tr></thead>
        <tbody>
        @forelse ($visitas as $v)
            <tr>
                <td>{{ $v->llegada->format('d/m H:i') }}</td><td>{{ $v->bus->placa }}</td><td>{{ $v->linea->nombre }}</td>
                <td class="text-end">{{ $v->pasajeros_abordo }}</td>
                <td class="text-end">{{ round($v->pasajeros_abordo * 100 / $v->bus->capacidad_maxima) }}%</td>
                <td>{!! $v->espera_extra_min ? '<span class="badge text-bg-warning">+'.$v->espera_extra_min.' min</span>' : '—' !!}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-secondary">Sin visitas registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const bus = document.getElementById('bus'), abordo = document.getElementById('abordo'), aviso = document.getElementById('aviso');
    function revisar() {
        const cap = +bus.selectedOptions[0]?.dataset.cap || 0, n = +abordo.value;
        if (!cap || abordo.value === '') { aviso.textContent = ''; return; }
        if (n > cap) { aviso.innerHTML = '<span class="text-danger">Supera la capacidad del bus.</span>'; return; }
        aviso.innerHTML = n < cap * 0.25
            ? '<span class="text-warning-emphasis">⏱ Menos del 25 %: esperará 5 min extra.</span>'
            : '<span class="text-success">Ocupación ' + Math.round(n * 100 / cap) + ' %: puede continuar.</span>';
    }
    bus.addEventListener('change', revisar);
    abordo.addEventListener('input', revisar);
    const reloj = document.getElementById('reloj');
    setInterval(() => reloj.textContent = new Date().toLocaleString('es-GT'), 1000);
</script>
@endpush
