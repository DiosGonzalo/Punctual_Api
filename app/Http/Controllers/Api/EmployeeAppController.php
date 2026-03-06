<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeAppController extends Controller
{
    public function dashboardScreen(Request $request)
    {
        return $this->dashboard($request);
    }

    public function dashboard(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load(['rol', 'horario', 'workplace', 'departamento']);

        $today = now();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);

        $todaySummary = $this->buildDaySummary($user, $today);
        $weekStats = $this->buildRangeStats($user, $weekStart, $weekStart->copy()->endOfWeek(Carbon::SUNDAY));

        $scheduledDailySeconds = $this->scheduledDailySeconds($user, $today);
        $progress = $scheduledDailySeconds > 0
            ? min(100, round(($todaySummary['worked_seconds'] / $scheduledDailySeconds) * 100, 1))
            : 0;

        return response()->json([
            'saludo' => [
                'titulo' => 'Hola',
                'nombre' => trim($user->nombre . ' ' . $user->apellidos),
            ],
            'jornada' => [
                'is_presente' => (bool) $user->is_presente,
                'entrada_desde' => $todaySummary['first_entry_time'],
                'horas_hoy' => $this->hoursDecimal($todaySummary['worked_seconds']),
                'horas_hoy_texto' => $this->hoursAndMinutes($todaySummary['worked_seconds']),
                'horas_semana' => $this->hoursDecimal($weekStats['worked_seconds']),
                'progreso_hoy_pct' => $progress,
            ],
            'acciones' => [
                ['key' => 'entrada', 'label' => $user->is_presente ? 'Marcar salida' : 'Marcar entrada'],
                ['key' => 'horario', 'label' => 'Mi Horario'],
                ['key' => 'perfil', 'label' => 'Perfil'],
            ],
            'resumen_hoy' => [
                [
                    'titulo' => $todaySummary['first_entry_time'] ? 'Entrada registrada' : 'Sin entrada registrada',
                    'detalle' => $todaySummary['first_entry_time']
                        ? 'Entrada a las ' . $todaySummary['first_entry_time']
                        : 'Aún no hay fichaje de entrada',
                    'estado' => $todaySummary['is_late'] ? 'late' : 'ok',
                ],
                [
                    'titulo' => $user->is_presente ? 'Sesión activa' : 'Sesión cerrada',
                    'detalle' => $this->hoursAndMinutes($todaySummary['worked_seconds']) . ' trabajados',
                    'estado' => $user->is_presente ? 'active' : 'idle',
                ],
            ],
        ]);
    }

    public function attendanceScreen(Request $request)
    {
        return $this->attendanceStatus($request);
    }

    public function attendanceStatus(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load(['workplace', 'horario']);
        $todaySummary = $this->buildDaySummary($user, now());

        return response()->json([
            'ahora' => now()->format('H:i:s'),
            'fecha' => now()->translatedFormat('l, d \\d\\e F \\d\\e Y'),
            'sesion' => [
                'activa' => (bool) $user->is_presente,
                'desde' => $todaySummary['active_since'],
                'horas_trabajadas' => $this->hoursAndMinutes($todaySummary['worked_seconds']),
                'entrada_hoy' => $todaySummary['first_entry_time'],
                'salida_hoy' => $todaySummary['last_exit_time'],
            ],
            'ubicacion' => [
                'workplace' => $user->workplace?->nombre ?? 'No asignado',
                'coordenadas' => $user->workplace?->coordenadas,
                'wifi_ok' => true,
                'gps_ok' => true,
            ],
            'resumen_dia' => [
                'dia' => now()->translatedFormat('D'),
                'entrada' => $todaySummary['first_entry_time'],
                'horas' => $this->hoursAndMinutes($todaySummary['worked_seconds']),
            ],
        ]);
    }

    public function checkIn(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->is_presente) {
            return response()->json([
                'message' => 'Ya existe una sesión activa.',
            ], 409);
        }

        DB::transaction(function () use ($user) {
            LogEntry::create([
                'accion' => 'Entrada',
                'fecha_hora' => now(),
                'id_usuario' => $user->id,
            ]);

            $user->update(['is_presente' => true]);
        });

        return response()->json([
            'message' => 'Entrada registrada correctamente.',
            'timestamp' => now()->toDateTimeString(),
        ], 201);
    }

    public function checkOut(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->is_presente) {
            return response()->json([
                'message' => 'No hay una sesión activa para cerrar.',
            ], 409);
        }

        DB::transaction(function () use ($user) {
            LogEntry::create([
                'accion' => 'Salida',
                'fecha_hora' => now(),
                'id_usuario' => $user->id,
            ]);

            $user->update(['is_presente' => false]);
        });

        return response()->json([
            'message' => 'Salida registrada correctamente.',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function weeklySchedule(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load('horario');

        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : now();

        return response()->json($this->buildWeeklyPayload($user, $date));
    }

    public function scheduleScreen(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load('horario');

        $date = $request->filled('date') ? Carbon::parse($request->string('date')) : now();
        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', (string) $request->string('month'))
            : $date->copy();

        return response()->json([
            'semana' => $this->buildWeeklyPayload($user, $date),
            'mes' => $this->buildMonthlyPayload($user, $month),
        ]);
    }

    public function monthlySchedule(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', (string) $request->string('month'))
            : now();

        return response()->json($this->buildMonthlyPayload($user, $month));
    }

    public function profileScreen(Request $request)
    {
        return $this->profile($request);
    }

    public function profile(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load(['rol', 'departamento', 'workplace']);

        $monthSummary = $this->monthlySummary($user, now());
        $yearSummary = $this->yearSummary($user, now()->year);

        return response()->json([
            'usuario' => [
                'id' => $user->id,
                'nombre' => trim($user->nombre . ' ' . $user->apellidos),
                'email' => $user->email,
                'telefono' => $user->telefono,
                'foto_perfil_url' => $user->foto_perfil_url,
                'rol' => $user->rol?->nombre,
                'departamento' => $user->departamento?->nombre,
                'workplace' => $user->workplace?->nombre,
            ],
            'este_mes' => $monthSummary,
            'resumen_anual' => $yearSummary,
            'balance_permisos' => [
                'vacaciones' => ['usados' => 12, 'total' => 15],
                'dias_personales' => ['usados' => 5, 'total' => 6],
                'permisos_medicos' => ['usados' => 8, 'total' => 10],
            ],
            'logros' => $this->achievements($monthSummary['puntualidad_pct']),
        ]);
    }

    private function buildWeeklyPayload(User $user, Carbon $date): array
    {
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $assignedSchedule = $this->assignedSchedulePayload($user);

        $days = [];
        $workedSeconds = 0;
        $entrySum = 0;
        $entryCount = 0;
        $onTime = 0;
        $workedDays = 0;

        foreach (CarbonPeriod::create($weekStart, '1 day', $weekEnd) as $day) {
            $daySummary = $this->buildDaySummary($user, Carbon::parse($day));
            $dayWorkedSeconds = $daySummary['worked_seconds'];
            $workedSeconds += $dayWorkedSeconds;

            $status = $this->resolveDayStatus(Carbon::parse($day), $daySummary);

            if ($daySummary['first_entry']) {
                $entrySum += $daySummary['first_entry']->hour * 60 + $daySummary['first_entry']->minute;
                $entryCount++;
            }

            if ($dayWorkedSeconds > 0) {
                $workedDays++;

                if (!$daySummary['is_late']) {
                    $onTime++;
                }
            }

            $days[] = [
                'fecha' => Carbon::parse($day)->toDateString(),
                'dia' => Carbon::parse($day)->translatedFormat('D'),
                'numero' => Carbon::parse($day)->day,
                'estado' => $status,
                'horario' => $assignedSchedule['horario'],
                'horario_nombre' => $assignedSchedule['horario_nombre'],
                'horario_texto' => $assignedSchedule['horario_texto'],
                'horario_asignado' => $assignedSchedule['horario_asignado'],
                'hora_inicio' => $assignedSchedule['hora_entrada'],
                'hora_fin' => $assignedSchedule['hora_salida'],
                'dias_laborales' => $assignedSchedule['dias_laborales'],
                'entrada' => $daySummary['first_entry_time'],
                'salida' => $daySummary['last_exit_time'],
                'horas' => $this->hoursDecimal($dayWorkedSeconds),
                'horas_texto' => $this->hoursAndMinutes($dayWorkedSeconds),
            ];
        }

        $attendance = $workedDays > 0 ? round(($onTime / $workedDays) * 100) : 0;
        $avgEntry = $entryCount > 0 ? intval(round($entrySum / $entryCount)) : null;

        return [
            'semana' => [
                'inicio' => $weekStart->toDateString(),
                'fin' => $weekEnd->toDateString(),
            ],
            'horario' => $assignedSchedule['horario'],
            'horario_nombre' => $assignedSchedule['horario_nombre'],
            'horario_texto' => $assignedSchedule['horario_texto'],
            'horario_asignado' => $assignedSchedule['horario_asignado'],
            'hora_entrada' => $assignedSchedule['hora_entrada'],
            'hora_salida' => $assignedSchedule['hora_salida'],
            'dias_laborales' => $assignedSchedule['dias_laborales'],
            'estadisticas' => [
                'total_horas' => $this->hoursDecimal($workedSeconds),
                'promedio_entrada' => $avgEntry !== null ? sprintf('%02d:%02d', intdiv($avgEntry, 60), $avgEntry % 60) : null,
                'asistencia_pct' => $attendance,
                'dias_asistidos' => $workedDays,
            ],
            'dias' => $days,
        ];
    }

    private function assignedSchedulePayload(User $user): array
    {
        $horario = $user->horario;

        if (!$horario) {
            return [
                'horario' => null,
                'horario_nombre' => null,
                'horario_texto' => null,
                'horario_asignado' => null,
                'hora_entrada' => null,
                'hora_salida' => null,
                'dias_laborales' => null,
            ];
        }

        $parsed = $this->parseScheduleRange((string) $horario->horas);

        return [
            'horario' => $horario->nombre,
            'horario_nombre' => $horario->nombre,
            'horario_texto' => trim($horario->nombre . ' (' . $horario->horas . ')'),
            'horario_asignado' => trim($horario->nombre . ' - ' . $horario->horas),
            'hora_entrada' => $parsed['entrada'],
            'hora_salida' => $parsed['salida'],
            'dias_laborales' => $horario->dias,
        ];
    }

    private function parseScheduleRange(string $hours): array
    {
        if (!preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/', $hours, $matches)) {
            return [
                'entrada' => null,
                'salida' => null,
            ];
        }

        return [
            'entrada' => $matches[1],
            'salida' => $matches[2],
        ];
    }

    private function buildMonthlyPayload(User $user, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $workedSeconds = 0;
        $workingDays = 0;
        $workedDays = 0;
        $onTimeDays = 0;

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            $date = Carbon::parse($day);

            if ($date->isWeekend()) {
                continue;
            }

            $workingDays++;
            $summary = $this->buildDaySummary($user, $date);
            $workedSeconds += $summary['worked_seconds'];

            if ($summary['worked_seconds'] > 0) {
                $workedDays++;

                if (!$summary['is_late']) {
                    $onTimeDays++;
                }
            }
        }

        return [
            'mes' => $month->format('Y-m'),
            'resumen' => [
                'dias_laborados' => $workedDays,
                'dias_totales' => $workingDays,
                'total_horas' => $this->hoursDecimal($workedSeconds),
                'puntualidad_pct' => $workedDays > 0 ? round(($onTimeDays / $workedDays) * 100) : 0,
            ],
        ];
    }

    private function monthlySummary(User $user, Carbon $date): array
    {
        $month = $date->copy()->startOfMonth();
        $stats = $this->monthStats($user, $month);

        return [
            'horas_trabajadas' => $this->hoursDecimal($stats['worked_seconds']),
            'dias_asistidos' => $stats['worked_days'],
            'dias_totales' => $stats['working_days'],
            'puntualidad_pct' => $stats['punctuality_pct'],
            'productividad_pct' => min(100, max(0, $stats['punctuality_pct'] + 3)),
        ];
    }

    private function yearSummary(User $user, int $year): array
    {
        $workedSeconds = 0;
        $workedDays = 0;
        $workingDays = 0;
        $onTime = 0;

        for ($month = 1; $month <= 12; $month++) {
            $stats = $this->monthStats($user, Carbon::create($year, $month, 1));
            $workedSeconds += $stats['worked_seconds'];
            $workedDays += $stats['worked_days'];
            $workingDays += $stats['working_days'];
            $onTime += $stats['on_time_days'];
        }

        return [
            'dias_laborados' => $workedDays,
            'horas_totales' => $this->hoursDecimal($workedSeconds),
            'puntualidad_pct' => $workedDays > 0 ? round(($onTime / $workedDays) * 100) : 0,
            'dias_totales' => $workingDays,
        ];
    }

    private function monthStats(User $user, Carbon $monthStart): array
    {
        $start = $monthStart->copy()->startOfMonth();
        $end = $monthStart->copy()->endOfMonth();

        $workedSeconds = 0;
        $workedDays = 0;
        $workingDays = 0;
        $onTimeDays = 0;

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            $date = Carbon::parse($day);

            if ($date->isWeekend()) {
                continue;
            }

            $workingDays++;

            $summary = $this->buildDaySummary($user, $date);
            $workedSeconds += $summary['worked_seconds'];

            if ($summary['worked_seconds'] > 0) {
                $workedDays++;

                if (!$summary['is_late']) {
                    $onTimeDays++;
                }
            }
        }

        return [
            'worked_seconds' => $workedSeconds,
            'worked_days' => $workedDays,
            'working_days' => $workingDays,
            'on_time_days' => $onTimeDays,
            'punctuality_pct' => $workedDays > 0 ? round(($onTimeDays / $workedDays) * 100) : 0,
        ];
    }

    private function achievements(int $punctuality): array
    {
        $items = [];

        if ($punctuality >= 95) {
            $items[] = ['titulo' => 'Asistencia perfecta', 'detalle' => 'Sin retrasos en el mes'];
        }

        if ($punctuality >= 90) {
            $items[] = ['titulo' => 'Puntualidad', 'detalle' => $punctuality . '% del mes'];
        }

        if (count($items) === 0) {
            $items[] = ['titulo' => 'Constancia', 'detalle' => 'Sigue registrando tus jornadas'];
        }

        return $items;
    }

    private function buildRangeStats(User $user, Carbon $start, Carbon $end): array
    {
        $workedSeconds = 0;

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            $workedSeconds += $this->buildDaySummary($user, Carbon::parse($day))['worked_seconds'];
        }

        return [
            'worked_seconds' => $workedSeconds,
        ];
    }

    private function buildDaySummary(User $user, Carbon $date): array
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $logs = LogEntry::where('id_usuario', $user->id)
            ->whereBetween('fecha_hora', [$dayStart, $dayEnd])
            ->orderBy('fecha_hora')
            ->get();

        $workedSeconds = 0;
        $openEntry = null;
        $firstEntry = null;
        $lastExit = null;

        foreach ($logs as $log) {
            $timestamp = Carbon::parse($log->fecha_hora);

            if ($this->isEntryAction($log->accion)) {
                $openEntry ??= $timestamp;
                $firstEntry ??= $timestamp;
                continue;
            }

            if ($this->isExitAction($log->accion) && $openEntry) {
                $workedSeconds += max(0, $openEntry->diffInSeconds($timestamp, false));
                $lastExit = $timestamp;
                $openEntry = null;
            }
        }

        $activeSince = null;
        if ($openEntry && $date->isToday() && $user->is_presente) {
            $now = now();
            $workedSeconds += max(0, $openEntry->diffInSeconds($now, false));
            $activeSince = $openEntry;
        }

        $scheduleStart = $this->scheduleStart($user, $date);
        $isLate = $firstEntry && $scheduleStart
            ? $firstEntry->gt($scheduleStart->copy()->addMinutes(5))
            : false;

        return [
            'worked_seconds' => $workedSeconds,
            'first_entry' => $firstEntry,
            'first_entry_time' => $firstEntry?->format('H:i'),
            'last_exit' => $lastExit,
            'last_exit_time' => $lastExit?->format('H:i'),
            'active_since' => $activeSince?->format('H:i'),
            'is_late' => $isLate,
        ];
    }

    private function scheduleStart(User $user, Carbon $date): ?Carbon
    {
        $hours = $user->horario?->horas;

        if (!$hours || !preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/', $hours, $matches)) {
            return null;
        }

        return Carbon::parse($date->toDateString() . ' ' . $matches[1]);
    }

    private function scheduledDailySeconds(User $user, Carbon $date): int
    {
        $hours = $user->horario?->horas;

        if (!$hours || !preg_match('/(\d{1,2}:\d{2})\s*[-–]\s*(\d{1,2}:\d{2})/', $hours, $matches)) {
            return 0;
        }

        $start = Carbon::parse($date->toDateString() . ' ' . $matches[1]);
        $end = Carbon::parse($date->toDateString() . ' ' . $matches[2]);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        return $start->diffInSeconds($end);
    }

    private function resolveDayStatus(Carbon $day, array $daySummary): string
    {
        if ($day->isWeekend()) {
            return 'descanso';
        }

        if ($daySummary['worked_seconds'] === 0) {
            return $day->greaterThan(now()) ? 'pendiente' : 'ausente';
        }

        return $daySummary['is_late'] ? 'late' : 'complete';
    }

    private function isEntryAction(string $action): bool
    {
        $normalized = $this->normalizeAction($action);

        return in_array($normalized, ['entrada', 'checkin', 'clockin', 'in'], true);
    }

    private function isExitAction(string $action): bool
    {
        $normalized = $this->normalizeAction($action);

        return in_array($normalized, ['salida', 'checkout', 'clockout', 'out'], true);
    }

    private function normalizeAction(string $action): string
    {
        $normalized = strtolower(trim($action));
        $normalized = str_replace([' ', '-', '_'], '', $normalized);

        return $normalized;
    }

    private function hoursDecimal(int $seconds): float
    {
        return round($seconds / 3600, 2);
    }

    private function hoursAndMinutes(int $seconds): string
    {
        $totalMinutes = intdiv($seconds, 60);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%dh %02dm', $hours, $minutes);
    }
}
