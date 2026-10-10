@extends('layouts.app')
@section('titulo', $estacion->nombre)
@section('contenido')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $estacion->nombre }}</h1>
        <div class="text-secondary">{{ $estacion->direccion }} · {{ $estacion->municipalidad->nombre }}</div>
        <div class="mt-1">@forelse ($estacion->lineas as $l)<a href="{{ route('lineas.show', $l) }}" class="badge rounded-pill me-1 text-decoration-none" style="background: {{ $l->color }}">{{ $l->nombre }} (#{{ $l->pivot->orden }})</a>@empty
            <span class="badge text-bg-warning">Sin línea: asígnela desde una línea (Req. 2)</span>@endforelse</div>
    </div>
    <div class="no-print">
        <a href="{{ route('panel.show', $estacion) }}" class="btn btn-outline-primary"><i class="bi bi-pc-display"></i> Panel</a>
        <a href="{{ route('estaciones.edit', $estacion) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Editar</a>
        @include('partials.eliminar', ['accion' => route('estaciones.destroy', $estacion), 'texto' => 'Eliminar'])
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-door-open"></i> Accesos y guardias de seguridad</div>
            <ul class="list-group list-group-flush">
                @forelse ($estacion->accesos as $a)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $a->nombre }}</strong>
                            <div class="no-print">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#g{{ $a->id }}"><i class="bi bi-person-plus"></i> Guardia</button>
                                @include('partials.eliminar', ['accion' => route('accesos.destroy', $a), 'confirmar' => "¿Eliminar {$a->nombre} y sus guardias?"])
                            </div>
                        </div>
                        @if ($a->guardias->isEmpty())
                            <div class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Sin guardia asignado (incumple Req. 10)</div>
                        @endif
                        @foreach ($a->guardias as $g)
                            <div class="d-flex justify-content-between small py-1 border-top">
                                <span><i class="bi bi-shield-check text-success"></i> {{ $g->nombre }} · DPI {{ $g->dpi }} · Tel. {{ $g->telefono }}</span>
                                @include('partials.eliminar', ['accion' => route('guardias.destroy', $g), 'confirmar' => "¿Retirar a {$g->nombre}?"])
                            </div>
                        @endforeach
                        <form method="POST" action="{{ route('guardias.store', $a) }}" class="row g-2 mt-1 collapse no-print" id="g{{ $a->id }}">
                            @csrf
                            <div class="col-md-4"><input name="nombre" class="form-control form-control-sm" placeholder="Nombre" required></div>
                            <div class="col-md-3"><input name="dpi" class="form-control form-control-sm" placeholder="DPI (13 dígitos)" pattern="\d{13}" required></div>
                            <div class="col-md-3"><input name="telefono" class="form-control form-control-sm" placeholder="Teléfono" required></div>
                            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Asignar</button></div>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item text-secondary">Sin accesos registrados.</li>
                @endforelse
            </ul>
            <div class="card-footer bg-white no-print">
                <div class="fw-semibold small mb-2">Nuevo acceso (requiere su primer guardia)</div>
                <form method="POST" action="{{ route('accesos.store', $estacion) }}" class="row g-2">
                    @csrf
                    <div class="col-md-3"><input name="acceso" class="form-control form-control-sm" placeholder="Acceso Norte" required></div>
                    <div class="col-md-3"><input name="guardia_nombre" class="form-control form-control-sm" placeholder="Guardia" required></div>
                    <div class="col-md-2"><input name="guardia_dpi" class="form-control form-control-sm" placeholder="DPI" pattern="\d{13}" required></div>
                    <div class="col-md-2"><input name="guardia_telefono" class="form-control form-control-sm" placeholder="Teléfono" required></div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Agregar</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col"><div class="text-secondary small">Capacidad</div><div class="h4">{{ $estacion->capacidad }}</div></div>
                    <div class="col"><div class="text-secondary small">En espera</div><div class="h4">{{ $estacion->pasajeros_espera }}</div></div>
                    <div class="col"><div class="text-secondary small">Ocupación</div><div class="h4 {{ $estacion->porcentajeOcupacion() >= 150 ? 'text-danger' : '' }}">{{ $estacion->porcentajeOcupacion() }}%</div></div>
                </div>
                <div class="small text-secondary text-center">Operador: {{ $estacion->operador?->user->name ?? 'sin asignar' }}</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-p-square"></i> Parqueos</div>
            <ul class="list-group list-group-flush">
                @forelse ($estacion->parqueos as $p)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $p->nombre }} <span class="text-secondary small">· {{ $p->buses->count() }}/{{ $p->capacidad }} buses</span></span>
                        @include('partials.eliminar', ['accion' => route('parqueos.destroy', $p)])
                    </li>
                @empty
                    <li class="list-group-item text-secondary">Esta estación no tiene parqueo.</li>
                @endforelse
            </ul>
            <div class="card-footer bg-white no-print">
                <form method="POST" action="{{ route('parqueos.store', $estacion) }}" class="row g-2">@csrf
                    <div class="col-6"><input name="nombre" class="form-control form-control-sm" placeholder="Nombre" value="Parqueo {{ $estacion->nombre }}" required></div>
                    <div class="col-3"><input name="capacidad" type="number" min="1" class="form-control form-control-sm" placeholder="Cap." required></div>
                    <div class="col-3"><button class="btn btn-sm btn-primary w-100">Agregar</button></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bell"></i> Últimas alertas</div>
            <ul class="list-group list-group-flush small">
                @forelse ($estacion->alertas as $a)
                    <li class="list-group-item">{!! $a->atendida ? '<span class="badge text-bg-success">Atendida</span>' : '<span class="badge text-bg-danger">Abierta</span>' !!}
                        {{ $a->mensaje }} <span class="text-secondary">· {{ $a->created_at->format('d/m/Y H:i') }}</span></li>
                @empty
                    <li class="list-group-item text-secondary">Sin alertas.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
