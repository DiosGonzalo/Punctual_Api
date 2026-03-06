<?php

use App\Http\Controllers\SecuenciaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\ModalidadController;
use App\Http\Controllers\WorkplaceController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\JefeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ruta pública para verificar que la API está funcionando
Route::get('health', function () {
    return response()->json(['status' => 'ok', 'message' => 'API is running']);
});

// Auth API para cliente mobile/web
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('quick-login', [AuthController::class, 'quickLogin']);
});

// Rutas de Dashboard (Públicas por ahora para development)
Route::get('dashboard/stats', [DashboardController::class, 'stats']);
Route::get('dashboard/recent-activity', [DashboardController::class, 'activity']);
Route::get('dashboard/department-attendance', [DashboardController::class, 'attendance']);

// ========================================
// RUTAS PÚBLICAS (Solo para desarrollo/pruebas)
// IMPORTANTE: Comentar o eliminar en producción
// ========================================

// GET - Consultas públicas
Route::get('modalidades', [ModalidadController::class, 'index']);
Route::get('horarios', [HorarioController::class, 'index']);
Route::get('horarios/{horario}', [HorarioController::class, 'show']);
Route::get('workplaces', [WorkplaceController::class, 'index']);
Route::get('departamentos', [DepartamentoController::class, 'index']);
Route::get('roles', [RoleController::class, 'index']);
Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::get('trabajadores', [TrabajadorController::class, 'index']);
Route::get('jefes', [JefeController::class, 'index']);
Route::get('secuencias', [SecuenciaController::class, 'index']);
Route::get('logs', [LogController::class, 'getAllLogs']);

// POST - Creación pública (SOLO PARA DESARROLLO)
Route::post('users', [UserController::class, 'store']);
Route::post('users/{user}/avatar', [UserController::class, 'uploadAvatar']);
Route::post('modalidades', [ModalidadController::class, 'store']);
Route::post('horarios', [HorarioController::class, 'store']);
Route::post('workplaces', [WorkplaceController::class, 'store']);
Route::post('departamentos', [DepartamentoController::class, 'store']);
Route::post('roles', [RoleController::class, 'store']);
Route::post('trabajadores', [TrabajadorController::class, 'store']);
Route::post('jefes', [JefeController::class, 'store']);
Route::post('secuencias', [SecuenciaController::class, 'store']);
Route::post('logs', [LogController::class, 'store']);

// PUT/PATCH - Actualización pública (SOLO PARA DESARROLLO)
Route::put('users/{user}', [UserController::class, 'update']);
Route::put('modalidades/{modalidade}', [ModalidadController::class, 'update']);
Route::put('horarios/{horario}', [HorarioController::class, 'update']);
Route::put('workplaces/{workplace}', [WorkplaceController::class, 'update']);
Route::put('departamentos/{departamento}', [DepartamentoController::class, 'update']);
Route::put('roles/{role}', [RoleController::class, 'update']);

// DELETE - Eliminación pública (SOLO PARA DESARROLLO)
Route::delete('users/{user}', [UserController::class, 'destroy']);
Route::delete('users/{user}/avatar', [UserController::class, 'removeAvatar']);
Route::delete('modalidades/{modalidade}', [ModalidadController::class, 'destroy']);
Route::delete('horarios/{horario}', [HorarioController::class, 'destroy']);
Route::delete('workplaces/{workplace}', [WorkplaceController::class, 'destroy']);
Route::delete('departamentos/{departamento}', [DepartamentoController::class, 'destroy']);
Route::delete('roles/{role}', [RoleController::class, 'destroy']);

// Rutas protegidas por autenticación
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('app')->group(function () {
        Route::get('dashboard', [EmployeeAppController::class, 'dashboard']);

        Route::get('attendance/status', [EmployeeAppController::class, 'attendanceStatus']);
        Route::post('attendance/check-in', [EmployeeAppController::class, 'checkIn']);
        Route::post('attendance/check-out', [EmployeeAppController::class, 'checkOut']);

        Route::get('schedule/week', [EmployeeAppController::class, 'weeklySchedule']);
        Route::get('schedule/month', [EmployeeAppController::class, 'monthlySchedule']);
        Route::get('schedule', [EmployeeAppController::class, 'scheduleScreen']);

        Route::get('profile', [EmployeeAppController::class, 'profile']);
    });

    // Alias de compatibilidad (sin prefijo /app)
    Route::get('dashboard', [EmployeeAppController::class, 'dashboard']);

    Route::get('attendance/status', [EmployeeAppController::class, 'attendanceStatus']);
    Route::post('attendance/check-in', [EmployeeAppController::class, 'checkIn']);
    Route::post('attendance/check-out', [EmployeeAppController::class, 'checkOut']);

    Route::get('schedule/week', [EmployeeAppController::class, 'weeklySchedule']);
    Route::get('schedule/month', [EmployeeAppController::class, 'monthlySchedule']);
    Route::get('schedule', [EmployeeAppController::class, 'scheduleScreen']);

    Route::get('profile', [EmployeeAppController::class, 'profile']);

    // Endpoints explícitos por pantalla (nombres ES)
    Route::get('pantallas/dashboard', [EmployeeAppController::class, 'dashboardScreen']);
    Route::get('pantallas/registro-asistencia', [EmployeeAppController::class, 'attendanceScreen']);
    Route::post('pantallas/registro-asistencia/entrada', [EmployeeAppController::class, 'checkIn']);
    Route::post('pantallas/registro-asistencia/salida', [EmployeeAppController::class, 'checkOut']);
    Route::get('pantallas/mi-horario', [EmployeeAppController::class, 'scheduleScreen']);
    Route::get('pantallas/mi-perfil', [EmployeeAppController::class, 'profileScreen']);

    // Alias cortos en español
    Route::get('registro-asistencia', [EmployeeAppController::class, 'attendanceScreen']);
    Route::post('registro-asistencia/entrada', [EmployeeAppController::class, 'checkIn']);
    Route::post('registro-asistencia/salida', [EmployeeAppController::class, 'checkOut']);
    Route::get('mi-horario', [EmployeeAppController::class, 'scheduleScreen']);
    Route::get('mi-perfil', [EmployeeAppController::class, 'profileScreen']);

    // Alias legacy (frontend antiguo)
    Route::get('attendance-status', [EmployeeAppController::class, 'attendanceStatus']);
    Route::post('attendance-check-in', [EmployeeAppController::class, 'checkIn']);
    Route::post('attendance-check-out', [EmployeeAppController::class, 'checkOut']);
    Route::get('schedule-screen', [EmployeeAppController::class, 'scheduleScreen']);
    Route::get('profile-screen', [EmployeeAppController::class, 'profileScreen']);
    Route::get('check-status', [EmployeeAppController::class, 'attendanceStatus']);
    Route::post('check-in', [EmployeeAppController::class, 'checkIn']);
    Route::post('check-out', [EmployeeAppController::class, 'checkOut']);
});

// ====================================
// RUTAS PROTEGIDAS - COMENTADAS TEMPORALMENTE PARA DESARROLLO
// TODO: Descomentar en producción y eliminar las rutas públicas de arriba
// ====================================
/*
Route::middleware('auth:sanctum')->group(function () {
    // Secuencias
    Route::apiResource('secuencias', SecuenciaController::class);
    
    // Usuarios
    Route::apiResource('users', UserController::class);
    
    // Horarios
    Route::apiResource('horarios', HorarioController::class);
    
    // Modalidades
    Route::apiResource('modalidades', ModalidadController::class);
    
    // Workplaces (Lugares de trabajo)
    Route::apiResource('workplaces', WorkplaceController::class);
    
    // Trabajadores
    Route::apiResource('trabajadores', TrabajadorController::class);
    
    // Departamentos
    Route::apiResource('departamentos', DepartamentoController::class);
    
    // Jefes
    Route::apiResource('jefes', JefeController::class);
    
    // Roles
    Route::apiResource('roles', RoleController::class);
    
    // Logs
    Route::get('logs', [LogController::class, 'index']);
    Route::get('logs/{id}', [LogController::class, 'show']);
});
*/
