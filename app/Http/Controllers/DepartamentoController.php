<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function index()
    {
        return response()->json(Departamento::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'id_jefe' => ['nullable', 'exists:jefes,id'],
        ]);

        $departamento = Departamento::create($data);
        return response()->json($departamento, 201);
    }

    public function show(Departamento $departamento)
    {
        return response()->json($departamento);
    }

    public function update(Request $request, Departamento $departamento)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'id_jefe' => ['sometimes', 'nullable', 'exists:jefes,id'],
        ]);

        $departamento->update($data);
        return response()->json($departamento);
    }

    public function destroy(Departamento $departamento)
    {
        $departamento->delete();
        return response()->json(null, 204);
    }
}
