<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Inicio') · Transmetro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('head')
    <style>
        :root { --tm-primario: #00796b; --tm-oscuro: #0b2e3a; --tm-acento: #4dd0b5; --sidebar-ancho: 250px; }
        body { background: #f4f6f8; }
        .btn-primary { --bs-btn-bg: var(--tm-primario); --bs-btn-border-color: var(--tm-primario);
            --bs-btn-hover-bg: #00695c; --bs-btn-hover-border-color: #00695c; --bs-btn-active-bg: #004d40; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .kpi { font-size: 2rem; font-weight: 700; line-height: 1; }
        .linea-chip { display: inline-block; width: .8rem; height: .8rem; border-radius: 50%; vertical-align: middle; }
        .table > :not(caption) > * > * { vertical-align: middle; }

        /* Sidebar */
        .sidebar { background: var(--tm-oscuro) !important; color: #cfd8dc; width: var(--sidebar-ancho); }
        @media (min-width: 992px) {
            .sidebar { position: fixed; inset: 0 auto 0 0; }
            .con-sidebar { margin-left: var(--sidebar-ancho); }
        }
        .sidebar .marca { font-weight: 700; font-size: 1.25rem; color: #fff; text-decoration: none; }
        .sidebar .marca i { color: var(--tm-acento); }
        .sidebar .seccion { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; color: #78909c;
            margin: 1.25rem 0 .4rem .75rem; }
        .sidebar .nav-link { color: #cfd8dc; border-radius: .5rem; padding: .55rem .75rem; display: flex; align-items: center; gap: .7rem; }
        .sidebar .nav-link i { font-size: 1.1rem; width: 1.2rem; text-align: center; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar .nav-link.active { background: var(--tm-primario); color: #fff; }
        .sidebar .usuario { border-top: 1px solid rgba(255,255,255,.1); }
        .topbar { background: var(--tm-oscuro); }

        @media print { .sidebar, .topbar, .no-print { display: none !important; } .con-sidebar { margin-left: 0; }
            body { background: #fff; } .card { box-shadow: none; } }
    </style>
</head>
<body>
@auth
@php
    $user = auth()->user();
    $alertasAbiertas = \App\Models\Alerta::where('atendida', false)->count();
    $menu = $user->esAdmin() ? [
        'General' => [
            'dashboard' => ['Dashboard', 'speedometer2'],
            'alertas.index' => ['Alertas', 'bell'],
        ],
        'Red de transporte' => [
            'lineas.index' => ['Líneas', 'signpost-split'],
            'estaciones.index' => ['Estaciones', 'geo-alt'],
            'municipalidades.index' => ['Municipalidades', 'bank'],
        ],
        'Flota y personal' => [
            'buses.index' => ['Buses', 'bus-front'],
            'pilotos.index' => ['Pilotos', 'person-badge'],
            'operadores.index' => ['Operadores', 'pc-display'],
        ],
        'Análisis' => [
            'reportes.index' => ['Reportes', 'file-earmark-bar-graph'],
        ],
    ] : ['Operación' => ['inicio' => ['Mi estación', 'pc-display']]];
@endphp

{{-- Barra superior solo en pantallas pequeñas --}}
<header class="topbar d-lg-none d-flex align-items-center justify-content-between px-3 py-2 mb-3">
    <a class="text-white fw-bold text-decoration-none" href="{{ route('inicio') }}"><i class="bi bi-bus-front-fill" style="color: var(--tm-acento)"></i> Transmetro</a>
    <button class="btn btn-outline-light btn-sm" data-bs-toggle="offcanvas" data-bs-target="#sidebar"><i class="bi bi-list fs-5"></i></button>
</header>

<aside class="sidebar offcanvas-lg offcanvas-start d-flex flex-column" tabindex="-1" id="sidebar">
    <div class="d-flex align-items-center justify-content-between px-3 pt-4 pb-2">
        <a class="marca" href="{{ route('inicio') }}"><i class="bi bi-bus-front-fill"></i> Transmetro</a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
    </div>
    <nav class="flex-grow-1 overflow-auto px-2">
        @foreach ($menu as $seccion => $items)
            <div class="seccion">{{ $seccion }}</div>
            <ul class="nav flex-column gap-1">
                @foreach ($items as $ruta => [$texto, $icono])
                    @php $activo = request()->routeIs($ruta === 'inicio' ? 'panel.*' : str_replace('.index', '.*', $ruta)); @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $activo ? 'active' : '' }}" href="{{ route($ruta) }}">
                            <i class="bi bi-{{ $icono }}"></i> <span class="flex-grow-1">{{ $texto }}</span>
                            @if ($ruta === 'alertas.index' && $alertasAbiertas > 0)
                                <span class="badge rounded-pill text-bg-danger">{{ $alertasAbiertas }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>
    <div class="usuario p-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-person-circle fs-3" style="color: var(--tm-acento)"></i>
            <div class="lh-sm overflow-hidden">
                <div class="text-white text-truncate">{{ $user->name }}</div>
                <div class="small text-secondary text-capitalize">{{ $user->rol }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
        </form>
    </div>
</aside>
@endauth

<main class="@auth con-sidebar px-3 px-lg-4 pt-lg-4 @else container-fluid @endauth pb-5">
    @if (session('ok'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-octagon"></i> <strong>Revise los datos:</strong>
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('contenido')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Confirmación antes de eliminar
    document.addEventListener('submit', e => {
        const msg = e.target.dataset.confirmar;
        if (msg && !confirm(msg)) e.preventDefault();
    });
</script>
@stack('scripts')
</body>
</html>
