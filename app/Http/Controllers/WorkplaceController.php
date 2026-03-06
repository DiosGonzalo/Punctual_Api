<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use Illuminate\Http\Request;

class WorkplaceController extends Controller
{
    public function index()
    {
        return response()->json(Workplace::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'coordenadas' => ['required', 'string', 'max:255'],
        ]);

        $workplace = Workplace::create($data);
        return response()->json($workplace, 201);
    }

    public function show(Workplace $workplace)
    {
        return response()->json($workplace);
    }

    public function update(Request $request, Workplace $workplace)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'coordenadas' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $workplace->update($data);
        return response()->json($workplace);
    }

    public function destroy(Workplace $workplace)
    {
        $workplace->delete();
        return response()->json(null, 204);
    }
}
