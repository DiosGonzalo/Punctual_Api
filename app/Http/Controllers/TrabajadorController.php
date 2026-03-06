<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrabajadorController extends Controller
{
    public function index()
    {
        return response()->json(Trabajador::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'id_rol' => ['nullable', 'exists:roles,id'],
            'email' => ['required', 'email', 'max:255', 'unique:trabajadores,email'],
            'telefono' => ['required', 'string', 'max:255'],
            'id_horario' => ['nullable', 'exists:horarios,id'],
            'id_workplace' => ['nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['nullable', 'exists:modalidades,id'],
            'id_departamento' => ['nullable', 'exists:departamentos,id'],
            'is_presente' => ['boolean'],
        ]);

        $trabajador = Trabajador::create($data);
        return response()->json($trabajador, 201);
    }

    public function show(Trabajador $trabajadore)
    {
        return response()->json($trabajadore);
    }

    public function update(Request $request, Trabajador $trabajadore)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'required', 'string', 'max:255'],
            'id_rol' => ['sometimes', 'nullable', 'exists:roles,id'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('trabajadores', 'email')->ignore($trabajadore->id)],
            'telefono' => ['sometimes', 'required', 'string', 'max:255'],
            'id_horario' => ['sometimes', 'nullable', 'exists:horarios,id'],
            'id_workplace' => ['sometimes', 'nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['sometimes', 'nullable', 'exists:modalidades,id'],
            'id_departamento' => ['sometimes', 'nullable', 'exists:departamentos,id'],
            'is_presente' => ['sometimes', 'boolean'],
        ]);

        $trabajadore->update($data);
        return response()->json($trabajadore);
    }

    public function destroy(Trabajador $trabajadore)
    {
        $trabajadore->delete();
        return response()->json(null, 204);
    }
}
