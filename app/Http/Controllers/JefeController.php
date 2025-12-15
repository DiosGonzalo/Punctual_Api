<?php

namespace App\Http\Controllers;

use App\Models\Jefe;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JefeController extends Controller
{
    public function index()
    {
        return response()->json(Jefe::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:jefes,email'],
            'telefono' => ['required', 'string', 'max:255'],
            'id_horario' => ['nullable', 'exists:horarios,id'],
            'id_workplace' => ['nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['nullable', 'exists:modalidades,id'],
            'is_presente' => ['boolean'],
        ]);

        $jefe = Jefe::create($data);
        return response()->json($jefe, 201);
    }

    public function show(Jefe $jefe)
    {
        return response()->json($jefe);
    }

    public function update(Request $request, Jefe $jefe)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('jefes', 'email')->ignore($jefe->id)],
            'telefono' => ['sometimes', 'required', 'string', 'max:255'],
            'id_horario' => ['sometimes', 'nullable', 'exists:horarios,id'],
            'id_workplace' => ['sometimes', 'nullable', 'exists:workplaces,id'],
            'id_modalidad' => ['sometimes', 'nullable', 'exists:modalidades,id'],
            'is_presente' => ['sometimes', 'boolean'],
        ]);

        $jefe->update($data);
        return response()->json($jefe);
    }

    public function destroy(Jefe $jefe)
    {
        $jefe->delete();
        return response()->json(null, 204);
    }
}
