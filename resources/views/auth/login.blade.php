@extends('layouts.app')
@section('titulo', 'Iniciar sesión')
@section('contenido')
<div class="row justify-content-center align-items-center" style="min-height: 85vh">
    <div class="col-sm-9 col-md-6 col-lg-4">
        <div class="text-center mb-4">
            <div class="display-4" style="color: var(--tm-primario)"><i class="bi bi-bus-front-fill"></i></div>
            <h1 class="h3">Sistema de Control Transmetro</h1>
            <p class="text-secondary">Municipalidades de Guatemala y municipios aledaños</p>
        </div>
        <div class="card p-4">
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="recordar" id="recordar">
                    <label class="form-check-label" for="recordar">Recordarme</label>
                </div>
                <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
            </form>
        </div>
        <div class="card mt-3 p-3 small text-secondary">
            <strong class="text-body">Usuarios de demostración</strong>
            Administrador: admin@transmetro.gt / admin123<br>
            Operador (Plaza Barrios): operador@transmetro.gt / operador123
        </div>
    </div>
</div>
@endsection
