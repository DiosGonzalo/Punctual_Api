<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        return response()->json(LogEntry::orderByDesc('fecha_hora')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'accion' => ['required', 'string'],
            'fecha_hora' => ['nullable', 'date'],
            'id_usuario' => ['nullable', 'exists:users,id'],
        ]);

        $data['fecha_hora'] = $data['fecha_hora'] ?? now();

        $log = LogEntry::create($data);
        return response()->json($log, 201);
    }

    public function show(LogEntry $log)
    {
        return response()->json($log);
    }

    public function update(Request $request, LogEntry $log)
    {
        $data = $request->validate([
            'accion' => ['sometimes', 'required', 'string'],
            'fecha_hora' => ['sometimes', 'nullable', 'date'],
            'id_usuario' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        $log->update($data);
        return response()->json($log);
    }

    public function destroy(LogEntry $log)
    {
        $log->delete();
        return response()->json(null, 204);
    }
}
