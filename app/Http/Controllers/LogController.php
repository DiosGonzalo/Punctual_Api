<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function getAllLogs()
    {
        return response()->json(LogEntry::orderByDesc('fecha_hora')->get());
    }

    public function index()
    {
        return $this->getAllLogs();
    }

    public function store(Request $request)
    {
        if ($request->filled('accion')) {
            $data = $request->validate([
                'accion' => ['required', 'string'],
                'fecha_hora' => ['nullable', 'date'],
                'id_usuario' => ['nullable', 'exists:users,id'],
            ]);

            $data['fecha_hora'] = $data['fecha_hora'] ?? now();

            $log = LogEntry::create($data);

            return response()->json($log, 201);
        }

        $data = $request->validate([
            'id_usuario' => ['required', 'exists:users,id'],
            'fecha' => ['required', 'date'],
            'hora_entrada' => ['nullable', 'date_format:H:i'],
            'hora_salida' => ['nullable', 'date_format:H:i'],
        ]);

        if (empty($data['hora_entrada']) && empty($data['hora_salida'])) {
            return response()->json([
                'message' => 'Debes indicar al menos hora_entrada o hora_salida.',
            ], 422);
        }

        $createdLogs = DB::transaction(function () use ($data) {
            $logs = [];
            $date = Carbon::parse($data['fecha'])->toDateString();

            if (!empty($data['hora_entrada'])) {
                $logs[] = LogEntry::create([
                    'accion' => 'Entrada',
                    'fecha_hora' => Carbon::parse($date . ' ' . $data['hora_entrada']),
                    'id_usuario' => $data['id_usuario'],
                ]);
            }

            if (!empty($data['hora_salida'])) {
                $logs[] = LogEntry::create([
                    'accion' => 'Salida',
                    'fecha_hora' => Carbon::parse($date . ' ' . $data['hora_salida']),
                    'id_usuario' => $data['id_usuario'],
                ]);
            }

            return $logs;
        });

        return response()->json([
            'message' => 'Registro manual guardado correctamente.',
            'logs' => $createdLogs,
        ], 201);
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
