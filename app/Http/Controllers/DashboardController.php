<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\User;
use App\Models\Role;
use App\Models\Horario;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_employees' => User::count(),
            'present_today' => User::where('is_presente', true)->count(),
            'late_arrivals' => 0, // Implementar lógica real según registros
            'attendance_rate' => '0%'
        ]);
    }

    public function activity()
    {
        $logs = LogEntry::with('usuario:id,nombre,apellidos')
            ->orderByDesc('fecha_hora')
            ->limit(1000)
            ->get();

        $reportes = $logs
            ->groupBy(function ($log) {
                $fecha = optional($log->fecha_hora)->toDateString() ?? now()->toDateString();
                return ($log->id_usuario ?? 0) . '|' . $fecha;
            })
            ->map(function ($group) {
                $entrada = $group
                    ->filter(function ($log) {
                        return str_contains(strtolower($log->accion ?? ''), 'entrada');
                    })
                    ->sortBy('fecha_hora')
                    ->first();

                $salida = $group
                    ->filter(function ($log) {
                        return str_contains(strtolower($log->accion ?? ''), 'salida');
                    })
                    ->sortByDesc('fecha_hora')
                    ->first();

                $ultimoLog = $group->sortByDesc('fecha_hora')->first();
                $usuario = $ultimoLog?->usuario
                    ? trim($ultimoLog->usuario->nombre . ' ' . $ultimoLog->usuario->apellidos)
                    : 'Usuario';

                return [
                    '_orden' => optional($ultimoLog->fecha_hora)->timestamp ?? 0,
                    'user' => $usuario,
                    'fecha' => optional($ultimoLog->fecha_hora)?->format('d/m/Y'),
                    'hora_entrada' => optional($entrada?->fecha_hora)?->format('H:i'),
                    'hora_salida' => optional($salida?->fecha_hora)?->format('H:i'),
                    'time' => optional($ultimoLog->fecha_hora)?->format('h:i A'),
                    'action' => 'Registro diario',
                    'status' => $salida ? 'Checked Out' : 'Checked In',
                ];
            })
            ->sortByDesc('_orden')
            ->values()
            ->map(function ($row) {
                unset($row['_orden']);
                return $row;
            })
            ->take(100)
            ->values();

        return response()->json($reportes);
    }

    public function attendance()
    {
        return response()->json([
            ['name' => 'General', 'present' => 0, 'total' => User::count(), 'color' => 'primary']
        ]);
    }
}
