<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 422);
        }

        /** @var User $user */
        $user = User::with(['rol', 'horario', 'workplace', 'modalidad', 'departamento'])
            ->findOrFail(Auth::id());

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function quickLogin(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'in:admin,manager,employee'],
        ]);

        $roleMap = [
            'admin' => ['administrador', 'admin'],
            'manager' => ['jefe', 'manager'],
            'employee' => ['trabajador', 'empleado', 'employee'],
        ];

        $candidates = $roleMap[$data['role']];

        $user = User::with(['rol', 'horario', 'workplace', 'modalidad', 'departamento'])
            ->whereHas('rol', function ($query) use ($candidates) {
                $query->where(function ($inner) use ($candidates) {
                    foreach ($candidates as $candidate) {
                        $inner->orWhereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($candidate) . '%']);
                    }
                });
            })
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'No existe un usuario para el acceso rápido solicitado.',
            ], 404);
        }

        $token = $user->createToken('quick-access')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user()->load(['rol', 'horario', 'workplace', 'modalidad', 'departamento']);

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apellidos' => $user->apellidos,
            'nombre_completo' => trim($user->nombre . ' ' . $user->apellidos),
            'email' => $user->email,
            'telefono' => $user->telefono,
            'foto_perfil_url' => $user->foto_perfil_url,
            'is_presente' => (bool) $user->is_presente,
            'rol' => $user->rol?->nombre,
            'horario' => $user->horario?->nombre,
            'horario_horas' => $user->horario?->horas,
            'horario_dias' => $user->horario?->dias,
            'workplace' => $user->workplace?->nombre,
            'coordenadas' => $user->workplace?->coordenadas,
            'modalidad' => $user->modalidad?->nombre,
            'departamento' => $user->departamento?->nombre,
        ];
    }
}
