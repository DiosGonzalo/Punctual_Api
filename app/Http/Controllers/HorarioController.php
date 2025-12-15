<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index()
    {
        return response()->json(Horario::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'horas' => ['required', 'string', 'max:255'],
            'dias' => ['required', 'string', 'max:255'],
        ]);

        $horario = Horario::create($data);
        return response()->json($horario, 201);
    }

    public function show(Horario $horario)
    {
        return response()->json($horario);
    }

    public function update(Request $request, Horario $horario)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'horas' => ['sometimes', 'required', 'string', 'max:255'],
            'dias' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $horario->update($data);
        return response()->json($horario);
    }

    public function destroy(Horario $horario)
    {
        $horario->delete();
        return response()->json(null, 204);
    }
}
