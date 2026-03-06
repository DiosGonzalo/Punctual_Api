<?php

namespace App\Http\Controllers;

use App\Models\Secuencia;
use Illuminate\Http\Request;

class SecuenciaController extends Controller
{
    public function index()
    {
        return response()->json(Secuencia::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:secuencias,nombre'],
            'prefijo' => ['nullable', 'string', 'max:50'],
            'valor_actual' => ['nullable', 'integer', 'min:0'],
        ]);

        $secuencia = Secuencia::create($data);
        return response()->json($secuencia, 201);
    }

    public function show(Secuencia $secuencia)
    {
        return response()->json($secuencia);
    }

    public function update(Request $request, Secuencia $secuencia)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255', 'unique:secuencias,nombre,' . $secuencia->id],
            'prefijo' => ['sometimes', 'nullable', 'string', 'max:50'],
            'valor_actual' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $secuencia->update($data);
        return response()->json($secuencia);
    }

    public function destroy(Secuencia $secuencia)
    {
        $secuencia->delete();
        return response()->json(null, 204);
    }
}
