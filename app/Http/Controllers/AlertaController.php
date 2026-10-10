<?php

namespace App\Http\Controllers;

use App\Models\Alerta;

class AlertaController extends Controller
{
    public function index()
    {
        return view('alertas.index', ['alertas' => Alerta::with('estacion')->orderBy('atendida')->latest()->paginate(25)]);
    }

    public function atender(Alerta $alerta)
    {
        $user = auth()->user();
        abort_unless($user->esAdmin() || $user->operador?->estacion_id === $alerta->estacion_id, 403);
        $alerta->update(['atendida' => true]);

        return back()->with('ok', 'Alerta marcada como atendida (bus enviado).');
    }
}
