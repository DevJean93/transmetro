<?php

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstacionController;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\MunicipalidadController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\PanelEstacionController;
use App\Http\Controllers\PilotoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Inicio: el admin va al dashboard y el operador al panel de su estación
    Route::get('/', [PanelEstacionController::class, 'inicio'])->name('inicio');

    // Panel de estación (Req. 16): operador de esa estación o admin
    Route::get('/panel/{estacion}', [PanelEstacionController::class, 'show'])->name('panel.show');
    Route::post('/panel/{estacion}/pasajeros', [PanelEstacionController::class, 'pasajeros'])->name('panel.pasajeros');
    Route::post('/panel/{estacion}/visitas', [PanelEstacionController::class, 'visita'])->name('panel.visita');
    Route::patch('/alertas/{alerta}/atender', [AlertaController::class, 'atender'])->name('alertas.atender');

    Route::middleware('rol:admin')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::resource('municipalidades', MunicipalidadController::class)->except('show')
            ->parameters(['municipalidades' => 'municipalidad']);

        Route::resource('lineas', LineaController::class);
        Route::post('/lineas/{linea}/estaciones', [LineaController::class, 'agregarEstacion'])->name('lineas.estaciones.agregar');
        Route::patch('/lineas/{linea}/estaciones/{estacion}', [LineaController::class, 'actualizarEstacion'])->name('lineas.estaciones.actualizar');
        Route::post('/lineas/{linea}/estaciones/{estacion}/{direccion}', [LineaController::class, 'moverEstacion'])
            ->whereIn('direccion', ['arriba', 'abajo'])->name('lineas.estaciones.mover');
        Route::delete('/lineas/{linea}/estaciones/{estacion}', [LineaController::class, 'quitarEstacion'])->name('lineas.estaciones.quitar');

        Route::resource('estaciones', EstacionController::class)->parameters(['estaciones' => 'estacion']);
        Route::post('/estaciones/{estacion}/accesos', [EstacionController::class, 'agregarAcceso'])->name('accesos.store');
        Route::delete('/accesos/{acceso}', [EstacionController::class, 'quitarAcceso'])->name('accesos.destroy');
        Route::post('/accesos/{acceso}/guardias', [EstacionController::class, 'agregarGuardia'])->name('guardias.store');
        Route::delete('/guardias/{guardia}', [EstacionController::class, 'quitarGuardia'])->name('guardias.destroy');
        Route::post('/estaciones/{estacion}/parqueos', [EstacionController::class, 'agregarParqueo'])->name('parqueos.store');
        Route::delete('/parqueos/{parqueo}', [EstacionController::class, 'quitarParqueo'])->name('parqueos.destroy');

        Route::resource('buses', BusController::class)->except('show')->parameters(['buses' => 'bus']);

        Route::resource('pilotos', PilotoController::class);
        Route::post('/pilotos/{piloto}/historial', [PilotoController::class, 'agregarHistorial'])->name('historial.store');
        Route::delete('/historial/{historial}', [PilotoController::class, 'quitarHistorial'])->name('historial.destroy');

        Route::resource('operadores', OperadorController::class)->only(['index', 'create', 'store', 'destroy'])
            ->parameters(['operadores' => 'operador']);

        Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/{reporte}', [ReporteController::class, 'show'])->name('reportes.show');
    });
});
