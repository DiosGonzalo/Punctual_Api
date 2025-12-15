<?php

namespace App\Http\Controllers;

use App\Models\Modalidad;
use Illuminate\Http\Request;

class ModalidadController extends Controller
{
    public function index()
    {
        return response()->json(Modalidad::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $modalidad = Modalidad::create($data);
        return response()->json($modalidad, 201);
    }

    public function show(Modalidad $modalidade)
    {
        return response()->json($modalidade);
    }

    public function update(Request $request, Modalidad $modalidade)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $modalidade->update($data);
        return response()->json($modalidade);
    }

    public function destroy(Modalidad $modalidade)
    {
        $modalidade->delete();
        return response()->json(null, 204);
    }
}
